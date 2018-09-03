<?php
namespace app\index\controller;
use Think\Exception;
use app\tools\controller\Tools;
use think\Request;
use think\Db;
use think\Config;


class Wxpay extends Base
{
    private $log_path = APP_PATH.'log/Wxpay.log';//日志路径
    /**
     * 初始化
     */
    public function __construct()
    {
        parent::__construct();
        /*vendor('wxpay.JsApiPay');
        vendor('wxpay.log');
        vendor('wxpay.notify');
        vendor('wxpay.WxPayNativePay');*/
        import('wxpay.JsApiPay',EXTEND_PATH,EXT);
        import('wxpay.log',EXTEND_PATH,EXT);
        import('wxpay.notify',EXTEND_PATH,EXT);
        import('wxpay.WxPayNativePay',EXTEND_PATH,EXT);
    }

    //打印输出数组信息
    public function printf_info($data)
    {
        foreach($data as $key=>$value){
            echo "<font color='#00ff55;'>$key</font> : $value <br/>";
        }
    }

    public function jsApiCall()
    {
        //LogController::W_P_Log("进入支付方法!");

        $lecture_id = $_GET['lecture_id'];
        $lecture = db('course')->find($lecture_id);
        $channel_id = $_GET['channel_id'];
        $channel_expire = $_GET['expire'];
        if (!empty($channel_id)){
            $channel = db('channel')->find($channel_id);
        }
        $bookid = $_GET['bookid'];
        if ($bookid){
            $book = db('onlinebooks')->find($bookid);
        }
        $reciterid = $_GET['reciterid'];
        if ($reciterid){
            $reciter = db('reciter')->find($reciterid);
        }
        $fee = $_GET['fee'];
        $target = $_GET['target'];
        $product = $_GET['product']; // pay_lecture 支付课程 reward 打赏讲师  pay_channel支付频道 pay_onlinebook支付在线听书 pay_reciter 最美保险声音评选
        $member = $_SESSION['CurrenMember'];
        if($member){
            $openId = $member['openid'];
        }

        if ($product!='pay_onlinebook'&&$product!='pay_reciter'&&$product!='pay_wuhan'&&$product!='pay_register'&&$product!='pay_zlhd'){
            $targetmember = db('member')->find($target);
        }else{ //当支付类型为pay_onlinebook时，支付的目标用户为系统，则$targetmember['id'] = 0
            $targetmember['id'] = 0;
        }
        $pay_amount = $fee/100.00;
        $add_time = date("Y-m-d H:i:s").".".rand(000000,999999);

        //初始化日志
        $logHandler= new \CLogFileHandler("./logs/".date('Y-m-d').'.log');
        $log = \Log::Init($logHandler, 15);

        //①、获取用户openid
        $tools = new \JsApiPay();

        if (!$openId){
            $openId = $tools->GetOpenid();
        }
        LogController::W_P_Log("下单用户openid 为：".$openId);
        //②、统一下单
        $input = new \WxPayUnifiedOrder();
        try{
            $membername = $member['name']?$member['name']:$member['nickname'];
            $membername =  $member['paynickname']?$member['paynickname']:$membername;
        }catch (Exception $e){
            $membername = $member['nickname'];
        }

        $out_trade_no = $product.date("YmdHis").rand(000000,999999);
        switch ($product){
            case 'reward' :
                try{
                    $tmembername = $targetmember['name']?$targetmember['name']:$targetmember['nickname'];
                }catch (Exception $e){
                    $tmembername = $targetmember['nickname'];
                }
                $input->SetBody(($membername)."打赏了 ".$tmembername." ".$pay_amount."元红包");
                $input->SetAttach(($membername)."打赏了 ".$tmembername." ".$pay_amount."元红包");
                break;
            case 'pay_lecture':
                $input->SetBody($membername."支付了《".$lecture['name']."》".$pay_amount."元");
                $input->SetAttach($membername."支付了《".$lecture['name']."》".$pay_amount."元");
                break;
            case 'pay_channel':
                $input->SetBody($membername."支付了频道《".$channel['name']."》".$pay_amount."元");
                $input->SetAttach($membername."支付了频道《".$channel['name']."》".$pay_amount."元");
                break;
            case 'pay_onlinebook':
                $input->SetBody($membername."支付了在线听书《".$book['name']."》".$pay_amount."元");
                $input->SetAttach($membername."支付了在线听书《".$book['name']."》".$pay_amount."元");
                $out_trade_no =  $product.date("YmdHis").rand(0000,9999);
                break;
            case 'pay_register':
                $input->SetBody($membername."支付了天雁论坛会员购买".$pay_amount."元");
                $input->SetAttach($membername."支付了天雁论坛会员购买".$pay_amount."元");
                break;
            case 'pay_reciter':
                $input->SetBody($membername."支付了保险公益杯《".$reciter['id']."》".$pay_amount."元");
                $input->SetAttach($membername."支付了保险公益杯《".$reciter['id']."》".$pay_amount."元");
                break;
            case 'pay_zlhd':
                $input->SetBody($membername."支付了专栏购买赠送活动".$pay_amount."元");
                $input->SetAttach($membername."支付了专栏购买赠送活动".$pay_amount."元");
                break;
            case 'pay_wuhan': //武汉论道
                $num1 = $_GET['num1'];
                $num2 = $_GET['num2'];

                $num11 = $_GET['num11'];
                $num22 = $_GET['num22'];

                $str14 = '；14日：';
                if ($num11>0){
                    $str14 .= "25元".$num11."餐；";
                }
                if ($num22>0){
                    $str14 .= "60元".$num22."餐";
                }
                $str15 = '；15日：';
                if ($num1>0){
                    $str15 .= "25元".$num1."餐；";
                }
                if ($num2>0){
                    $str15 .= "60元".$num2."餐";
                }

                $input->SetBody($membername."支付了<武汉论道>".$pay_amount."元".$str14.$str15);
                $input->SetAttach($membername."支付了<武汉论道>".$pay_amount."元".$str14.$str15);
                break;
            default:
                LogController::W_H_Log($product.":支付类型未定义,支付用户为：".$member['id'].":".$membername);
        }

        LogController::W_P_Log("body参数为：".$input->GetBody());
        $input->SetOut_trade_no($out_trade_no);
        $input->SetTotal_fee($fee);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag($product);
        $input->SetNotify_url(C('WxPayConf_pub.NOTIFY_URL'));
        LogController::W_P_Log("支付类型为：".$_SESSION['thirdparty']);
        if ($_SESSION['thirdparty']==1){
            $input->SetTrade_type("MWEB");
            $res['thirdparty']= 1;
        }else{
            $input->SetTrade_type("JSAPI");
        }
        $input->SetOpenid($openId);
        if(I("Trade_type") =='NATIVE'){
            LogController::W_P_Log("调用扫码支付！");
            $input->SetTrade_type("NATIVE");
            $input->SetProduct_id($input->GetOut_trade_no());
            $notify = new \NativePay();
            $result = $notify->GetPayUrl($input);
            $url = $result["code_url"];
            LogController::W_P_Log("调用扫码支付！URL为：".$url);
            $res['url'] = $url;
        }else{
            $order = \WxPayApi::unifiedOrder($input);
            $res['mweb_url'] = $order['mweb_url'];
            LogController::W_P_Log("中间页为：".$res['mweb_url']);
            LogController::W_P_Log("统一下单支付单信息");
//            LogController::W_P_Log("订单号：".$input->getOut_trade_no());
            foreach($order as $key=>$value){
                LogController::W_P_Log("$key:::$value");
            }
            $jsApiParameters = $tools->GetJsApiParameters($order);
            $jsApiParameters = json_decode($jsApiParameters);
            $res['params'] = $jsApiParameters;
        }
        $orderData = $input->GetValues();
        $orderData['paymember'] = $member['id'];
        $orderData['getmember'] = $targetmember['id'];
        $orderData['status'] = "wait";
        M('orders')->add($orderData); //保存订单数据

        switch ($product){
            case 'reward' :
                $data['sender_id'] = $member['id'];
                $data['lecture_id'] = $lecture_id;
                $data['pay_amount'] = $pay_amount;
                $data['sender_headimg'] = $member['headimg'];
                $data['message_type'] = $product;
                $data['sender_nickname'] = $member['nickname'];
                $data['add_time'] = $add_time;
                $data['content'] = ($membername)."打赏了 " . ($tmembername). " ".$pay_amount . "元红包";
                $data['length'] = 0;
                $data['ppt_id'] = null;
                $data['ppt_url'] = null;
                $data['reply'] = null;
                $data['isshow'] = 'hiden';
                $data['out_trade_no'] = $input->GetOut_trade_no();
                $count = M('msg')->add($data); //保存消息数据
                $data['message_id'] = $count;
                $res['data'] = $data;
                break;
            case 'pay_lecture':
                $fee =$pay_amount;
                $paydata = array(
                    'memberid'=>$member['id'],
                    'courseid'=>$lecture_id,
                    'fee'=>$fee,
                    'status'=>'wait',
                    'addtime'=>date("Y-m-d H:i:s"),
                    'out_trade_no'=>$orderData['out_trade_no']
                );
                M('coursepay')->add($paydata);

                //处理是否加入分销推广收益
                $popular = M("popularize")->where("lecture_id=".$lecture_id." and bpid=".$member['id'])->order("id desc")->find();
                if (isset($popular)&&(!empty($popular))){
                    //收益表添加记录
                    $earnsDatas['memberid'] = $popular['pid'];
                    $earnsDatas['paymemberid'] = $member['id'];
                    $earnsDatas['lectureid'] = $lecture_id;
                    $earnsDatas['fee'] = $lecture['cost']*$lecture['resell_percent']/100;
                    $earnsDatas['type'] = "pay";
                    $earnsDatas['out_trade_no'] = $orderData['out_trade_no'];
                    $earnsDatas['status'] = 'wait';
                    $earnsDatas['remarks'] = '分销推广';
                    $earnsDatas['addtime'] = date("Y-m-d H:i:s");
                    $e = M('earns')->add($earnsDatas);//收益表添加记录
                    LogController::W_P_Log("加入分销推广记录：".$e);
                    $fee = $lecture['cost']*(100-$lecture['resell_percent'])/100;
                    $pay_amount = $fee;
                }else{
                    LogController::W_P_Log("未取到推广记录信息！");
                }

                break;
            case 'pay_channel':
                if(strchr($channel_expire,"w")){
                    $expire = str_replace("w","",$channel_expire)*7;
                }elseif (strchr($channel_expire,"y")){
                    $expire = str_replace("y","",$channel_expire)*365;
                }else{
                    $expire = $channel_expire*30;
                }
                $channeldata = array(
                    'memberid'=>$member['id'],
                    'channelid'=>$channel_id,
                    'fee'=>$fee,
                    'status'=>'wait',
                    'addtime'=>date("Y-m-d H:i:s"),
                    'expire' => $expire ? date("Y-m-d H:i:s",strtotime("+$expire day")) : null,
                    'out_trade_no'=>$orderData['out_trade_no']
                );
                M('channelpay')->add($channeldata);

                //处理活动逻辑
                if ($channel_id == 454){
                    $onlinebooks = array(4,12,2,29,66,15,1,3,8,9);
                    foreach ($onlinebooks as $k=>$v){
                        $bookdata = array(
                            'memberid'=>$member['id'],
                            'bookid'=>$v,
                            'fee'=>0,
                            'status'=>'wait',
                            'addtime'=>date("Y-m-d H:i:s"),
                            'out_trade_no'=>$orderData['out_trade_no']
                        );
//                LogController::W_P_Log("数据为 写入:".json_encode($bookdata));
                        M('onlinebookpay')->add($bookdata);
                    }
                }

                break;
            case 'pay_onlinebook':
//                LogController::W_P_Log("before 写入onlinebookpay");
                $bookdata = array(
                    'memberid'=>$member['id'],
                    'bookid'=>$bookid,
                    'fee'=>$fee,
                    'status'=>'wait',
                    'addtime'=>date("Y-m-d H:i:s"),
                    'out_trade_no'=>$orderData['out_trade_no']
                );
//                LogController::W_P_Log("数据为 写入:".json_encode($bookdata));
                M('onlinebookpay')->add($bookdata);
                break;
            case 'pay_reciter':
                $reciterdata = array(
                    'memberid'=>$member['id'],
                    'reciterid'=>$reciterid,
                    'fee'=>$fee,
                    'status'=>'wait',
                    'addtime'=>date("Y-m-d H:i:s"),
                    'out_trade_no'=>$orderData['out_trade_no']
                );
                M('reciterpay')->add($reciterdata);
                break;
            case  'pay_zlhd':
                $channel_arr = [119,155,175,154,19,174,56,80,103,85,131,135,32];
                foreach ($channel_arr as $k => $v){
                    $channeldata = array(
                        'memberid'=>$member['id'],
                        'channelid'=>$v,
                        'fee'=>0,
                        'status'=>'wait',
                        'addtime'=>date("Y-m-d H:i:s"),
                        'expire' => date("Y-m-d H:i:s",strtotime("+365 day")),
                        'out_trade_no'=>$orderData['out_trade_no']
                    );
                    M('channelpay')->add($channeldata);
                }
                break;
            default:
                LogController::W_H_Log($product.":支付类型未定义,支付用户为：".$member['id'].":".$membername);
        }

        //收益表添加记录
        $earnsData['memberid'] = $orderData['getmember'];
        $earnsData['paymemberid'] = $orderData['paymember'];
        $earnsData['lectureid'] = $lecture_id;
        $earnsData['channelid'] = $channel_id;
        $earnsData['bookid'] = $bookid;
        $earnsData['fee'] = $pay_amount;
        switch ($product){
            case 'reward' :
                $earnsData['type'] = 'play';
                break;
            case 'pay_lecture':
                $earnsData['type'] = 'pay';
                break;
            case 'pay_channel':
                $earnsData['type'] = 'pay_channel';
                break;
            case 'pay_onlinebook':
                $earnsData['type'] = 'pay_onlinebook';
                break;
            case 'pay_reciter':
                $earnsData['type'] = 'pay_reciter';
                break;
            case 'pay_wuhan':
                $earnsData['type'] = 'pay_wuhan';
                break;
            case 'pay_newregister':
                $earnsData['type'] = 'pay_newregister';
                break;
            case 'pay_zlhd':
                $earnsData['type'] = 'pay_zlhd';
                break;
        }
        $earnsData['out_trade_no'] = $orderData['out_trade_no'];
        $earnsData['status'] = 'wait';
        $earnsData['addtime'] = date("Y-m-d H:i:s");
        M('earns')->add($earnsData);//收益表添加记录
        $res['code'] = 0;
        $this->ajaxReturn($res,'JSON');

    }


    public static function transfers($id){
        vendor("wxpay.Data");
        vendor("wxpay.wxPayApi");
        if ($id){//提现记录id
            $take_out = M('takeout')->find($id);
            $member = M('member')->find($take_out['memberid']);
            $amount = $take_out['num'];
            if ($amount*100<100){
                $result['code'] = 1;
                $result['msg'] = "金额最低为1元";
                return $result;
            }else{
                $data['partner_trade_no'] = $take_out['code'];
                $data['openid'] = $member['openid'];
                $data['check_name'] = "FORCE_CHECK"; //NO_CHECK：不校验真实姓名     FORCE_CHECK：强校验真实姓名
                $data['re_user_name'] = $take_out["name"];
                $data['transfersdesc'] = "用户提现";
                $data['addtime'] = date("Y-m-d H:i:s");
                $tid = M('transfers')->add($data);
                $input = new \WxPayTransfers();
                $input->SetAmount($amount*100);
                $input->SetCheck_name( $data['check_name']);
                $input->SetDesc($data['transfersdesc']);
                $input->SetOpenid($data['openid']);
                $input->SetPartner_trade_no($data['partner_trade_no']);
                $input->SetRe_user_name($data['re_user_name']);
                $input->SetSpbill_create_ip($_SERVER['REMOTE_ADDR']);//终端ip
                $transfers_result = \WxPayApi::transfers($input);
                LogController::W_H_Log("微信企业付款接口返回信息");
                foreach($transfers_result as $key=>$value){
                    LogController::W_H_Log("$key:::$value");
                }
                $tdata['return_code'] = $transfers_result['return_code'];
                $tdata['return_msg'] = $transfers_result['return_msg'];
                $tdata['result_code'] = $transfers_result['result_code'];
                $tdata['err_code'] = $transfers_result['err_code'];
                $tdata['err_code_des'] = $transfers_result['err_code_des'];
                $tdata['payment_no'] = $transfers_result['payment_no'];
                $tdata['payment_time'] = $transfers_result['payment_time'];
                $tdata['updatetime'] = date("Y-m-d H:i:s");
                $tdata['id'] = $tid;
                M("transfers")->save($tdata);
                if ($tdata['return_code']=='SUCCESS'&&$tdata['result_code']=='SUCCESS'){
                    $result['code'] = 0;
                    $result['msg'] = "提现处理成功";
                }else{
                    $result['code'] = 1;
                    $result['msg'] = "提现处理失败";
                    $result['return_msg'] = $tdata['return_msg'];
                    $result['result_code'] = $tdata['result_code'];
                    $result['err_code'] = $tdata['err_code'];
                    $result['err_code_des'] = $tdata['err_code_des'];
                }
            }
        }else{
            $result['code'] = 1;
            $result['msg'] = "提现处理失败,缺少提现记录id";
        }
        return $result;
    }

    public function notify()
    {
        $notify = new \PayNotifyCallBack();
        $notify->Handle(false);
        echo 'success';
    }


    function  log_result($file,$word)
    {
        $fp = fopen($file,"a");
        flock($fp, LOCK_EX) ;
        fwrite($fp,"执行日期：".strftime("%Y-%m-%d-%H：%M：%S",time())."\n".$word."\n\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    function sendgroupredpack(){
        $member = $_SESSION['CurrenMember'];
        $openid = $member['openid'];
        $tdata = date('Y-m-d',time());
        $todayisget = M('rpslog')->where("memberid=".$member['id']." and sendtime like '".$tdata."%'")->select();
        if ((empty($todayisget)||(!isset($todayisget)))) {
           /* if ($openid) {
                $input['re_openid'] = $openid;
                $input['send_name'] = "天雁商学院";
                $input['total_amount'] = 500;
                $input['total_num'] = 5;
                $input['amt_type'] = "ALL_RAND";
                $input['wishing'] = "天雁商学院祝您事事顺心！";
                $input['act_name'] = "祝福红包";
                $input['remark'] = "发送给好友一起领红包";
                $input['mch_billno'] = C('WxPayConf_pub.MCHID') . date("Ymd") . rand(0000000000, 9999999999);
                $count = M('rpslog')->where("sendtime like '".$tdata."%'")->count();
                if ($count <= 200){
                    $result = \WxPayApi::sendGroupRedPack($input);
                }
                $data['memberid'] = $member['id'];
                $data['sendtime'] = date("Y-m-d H:i:s");
                $data['num'] = 500;
                $data['status'] = "success";
                M('rpslog')->add($data);
                LogController::W_H_Log("操作发红包接口返回数据为：" . json_decode($result));
            }*/
        }else{

        }
    }

}