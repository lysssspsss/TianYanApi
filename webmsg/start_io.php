<?php
use Workerman\Worker;
use Workerman\WebServer;
use Workerman\Lib\Timer;
use PHPSocketIO\SocketIO;

include __DIR__ . '/vendor/autoload.php';

 Worker::$stdoutFile = '/data/wwwlogs/Worker/stdout.log';//输出日志, 如echo，var_dump等
// 全局数组保存uid在线数据
$uidConnectionMap = array();
// 记录最后一次广播的在线用户数
$last_online_count = 0;
// 记录最后一次广播的在线页面数
$last_online_page_count = 0;
// 传入ssl选项，包含证书的路径
$context = array(
    'ssl' => array(
        'local_cert'  => '/usr/local/nginx/conf/1_tianyan199.com_bundle.crt', // pem 文件一样的
        'local_pk'    => '/usr/local/nginx/conf/2_tianyan199.com.key',
        'verify_peer' => false,
    )
);
// PHPSocketIO服务 服务端方法
$sender_io = new SocketIO(2120,$context);
// 客户端发起连接事件时，设置连接socket的各种事件回调
$sender_io->on('connection', function($socket){
    // 当客户端发来登录事件时触发
    $socket->on('login', function ($uid)use($socket){
        global $uidConnectionMap, $last_online_count, $last_online_page_count;
        // 已经登录过了
        if(isset($socket->uid)){
            return;
        }
        // 更新对应uid的在线数据
        $uid = (string)$uid;
        if(!isset($uidConnectionMap[$uid]))
        {
            $uidConnectionMap[$uid] = 0;
        }
        // 这个uid有++$uidConnectionMap[$uid]个socket连接
        ++$uidConnectionMap[$uid];
        // 将这个连接加入到uid分组，方便针对uid推送数据
        $socket->join($uid);
        $socket->uid = $uid;
        // 更新这个socket对应页面的在线数据
        $socket->emit('update_online_count', "当前<b>{$last_online_count}</b>人在线，共打开<b>{$last_online_page_count}</b>个页面");
    });

	 $socket->on('join', function ($data)use($socket){
        global $uidConnectionMap, $last_online_count, $last_online_page_count;
        // 已经登录过了
	//	var_dump($data);

		$uid = $data['chatRoomId'];
	//	var_dump("uid is :".$uid);
        if(isset($socket->$uid)){
            return;
        }
        // 更新对应uid的在线数据
        $uid = (string)$uid;
        if(!isset($uidConnectionMap[$uid]))
        {
            $uidConnectionMap[$uid] = 0;
        }
        // 这个uid有++$uidConnectionMap[$uid]个socket连接
        ++$uidConnectionMap[$uid];
        // 将这个连接加入到uid分组，方便针对uid推送数据
        $socket->join($uid);
        $socket->uid = $uid;
        // 更新这个socket对应页面的在线数据
        $socket->emit('update_online_count', "当前<b>{$last_online_count}</b>人在线，共打开<b>{$last_online_page_count}</b>个页面");
    });

	$socket->on('broadcast',function($data)use($socket){
		//var_dump($data);
		$toChatroomId = $data['toChatroomId'];
		$content = $data['content'];
		$fromUserId = $data['fromUserId'];
		 if($toChatroomId){
             broadcast($toChatroomId,$data);
           /*  $sender_io->broadcast->to($toChatroomId)->emit('update_online_count', $content);
             $socket->broadcast->to($toChatroomId)->emit('update_online_count', $content);*/
            // 否则向所有uid推送数据
           }else{
             broadcast(null,$content);
			   /* $socket->broadcast->to($toChatroomId)->emit('update_online_count', $content);
				$socket->emit('chat', $content);*/
        }
	});
    // 当客户端断开连接是触发（一般是关闭网页或者跳转刷新导致）
    $socket->on('disconnect', function ($data) use($socket) {
        if(!isset($socket->uid))
        {
             return;
        }
        global $uidConnectionMap, $sender_io;
        // 将uid的在线socket数减一
        if(--$uidConnectionMap[$socket->uid] <= 0)
        {
            unset($uidConnectionMap[$socket->uid]);
        }
    });

    $socket->on('out', function ($data) use($socket) {
        //echo $data;
        //echo "我要out了呼呼！";
    });

});



// 当$sender_io启动后监听一个http端口，通过这个端口可以给任意uid或者所有uid推送数据 客户端方法
$sender_io->on('workerStart', function(){

    $context = array(
        'ssl' => array(
            'local_cert'  => '/usr/local/nginx/conf/ssl/server.pem', // 也可以是crt文件
            'local_pk'    => '/usr/local/nginx/conf/ssl/server.key',
            'verify_peer' => false,
        )
    );
// 这里设置的是http协议
// 设置transport开启ssl，变成http+SSL即https
    // 监听一个http端口
    $inner_http_worker = new Worker('http://127.0.0.1:2121');
   // $inner_http_worker = new Worker('http://127.0.0.1:2121',$context);
   // $inner_http_worker->transport = 'ssl';
    // 当http客户端发来数据时触发
    $inner_http_worker->onMessage = function($http_connection, $data){
        global $uidConnectionMap;
        $_POST = $_POST ? $_POST : $_GET;
        // 推送数据的url格式 type=publish&to=uid&content=xxxx
       // var_dump($_POST);
        switch(@$_POST['type']){
            case 'ty_publish':
                global $sender_io;
                $to = @$_POST['to'];
               // $_POST['content'] = htmlspecialchars(@$_POST['content']);
                $datas['content'] = $_POST['content'];
                $datas['toChatroomId'] = $_POST['to'];
                $datas['fromUserId'] = $_POST['fromUserId'];
                // 有指定uid则向uid所在socket组发送数据
                if($to){
                    $sender_io->to($to)->emit('update_online_count', $datas);
                // 否则向所有uid推送数据
                }else{
                    $sender_io->emit('update_online_count', $datas);
                }
                // http接口返回，如果用户离线socket返回fail
                if($to && !isset($uidConnectionMap[$to])){
                    return $http_connection->send('offline');
                }else{
                    return $http_connection->send('ok');
                }
        }
        return $http_connection->send('fail');
    };
    // 执行监听
    $inner_http_worker->listen();



    // 一个定时器，定时向所有uid推送当前uid在线数及在线页面数
    Timer::add(1, function(){
        global $uidConnectionMap, $sender_io, $last_online_count, $last_online_page_count;
        $online_count_now = count($uidConnectionMap);
        $online_page_count_now = array_sum($uidConnectionMap);
        // 只有在客户端在线数变化了才广播，减少不必要的客户端通讯
        if($last_online_count != $online_count_now || $last_online_page_count != $online_page_count_now)
        {
            $sender_io->emit('update_online_count', "当前<b>{$online_count_now}</b>人在线，共打开<b>{$online_page_count_now}</b>个页面");
            $last_online_count = $online_count_now;
            $last_online_page_count = $online_page_count_now;
        }
    });
});

    function broadcast($to,$content){
        global $uidConnectionMap, $sender_io, $last_online_count, $last_online_page_count;
        $online_count_now = count($uidConnectionMap);
        $online_page_count_now = array_sum($uidConnectionMap);
        if ($to){
            // 有指定uid则向uid所在socket组发送数据
            $sender_io->to($to)->emit('update_online_count', $content);
        }else{
            $sender_io->emit('update_online_count', $content);
        }
    }

if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}
