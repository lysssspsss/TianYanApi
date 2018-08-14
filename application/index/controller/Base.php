<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use think\Input;
use think\Db;
use think\Session;
use think\Validate;
use think\Redis;

//基类
class Base extends Controller
{
    public $sn;//站点标识
    protected $usertoken_rediskey = 'user_token';
    protected $bk_rediskey = 'black_keyword';
    protected $hb_rediskey = 'heartbeat';
    protected $user = null;//用户session
    protected $source;//来源 IOS、IOS_WX、ANDROID、ANDROID_WX
    //protected $is_mrl;//是否返回errorMsg，relogin，needRegister
    public function __construct()
    {
        parent::__construct();
        //var_dump(md5('phone=13168088229&type=2'));exit;
        if (PHP_SAPI != 'cli') {
            header("Access-Control-Allow-Origin: *");//跨域
            // header('Content-Type:text/html; charset=utf-8');
            // header("Content-Type: application/json");
        }
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
            header("Access-Control-Allow-Headers: Accept, Authorization, Content-Type, Pragma, Origin, Cache-Control, AuthGC, FROMWAY");
            exit;
        }


        /* 不需要登陆权限的控制器和方法[小写] */
        $pass = ['user' => ['reg', 'sms', 'login','wechat_login'],'index'=>['index']];

        $request = Request::instance();
        $url = $request->controller().$request->action();

        //$this->check_sign();/* 签名校验 */
        $this->is_repeat(); /* 重放检测 */
        $header = get_auth_headers();
        $this->source = empty($header['Source'])?'APP':$header['Source'];/* 获取请求来源 */
        $this_class = strtolower($request->controller());
        $this_method = strtolower($request->action());
        if (isset($pass[$this_class]) && in_array($this_method, $pass[$this_class])) {

        }else{
            //$memberid = input('param.memberid');
            $memberid = empty($header['Memberid'])?'':$header['Memberid'];
            $channel = input('param.channel');
            !empty($channel) or $channel = '';
            $result = $this->validate(
                [
                   // 'user_token'  => $user_token,
                    'uid'  => $memberid,
                    'channel'  => $channel,
                ],
                [
                   // 'user_token'  => 'require|alphaNum|max:32',
                    'uid'  => 'number|max:16',
                    'channel'  => 'alphaNum',
                ]
            );
            if($result !== true){
                $this->return_json(E_ARGS,'参数错误1');
            }
            $user = $this->check_user_token($memberid);
            if(!$user){
                $this->return_json(E_OP_FAIL,'请重新登录1',true,true);
            }
            $user['channel'] = $channel;
            $this->user = $user;
        }

        $this->sn = 'ty';
    }

    /**
     * 签名验证
     * @return bool
     */
    protected function check_sign()
    {
        $param = input('param.');
        if(empty($param['sign'])){
            $this->return_json(422,'参数为空：sign');
        }
        $sign = $param['sign'];
        unset($param['sign']);
        wlog(APP_PATH.'log/sign.log',$sign.'  |  '.json_encode($param));
        $is = vsign($sign,$param);
        if(!$is){
            $this->return_json(E_SIGN,'验签失败');
        }
        return true;
    }



    /**
     * json格式返回数据
     * @param bool $code     返回数据的状态
     * @param array $data    返回的数据
     * @param bool $is_mrl   是否返回后面2个参数
     * @param bool $relogin  是否需要登录
     * @param bool $needRegister 是否需要注册
     */
    protected function return_json($code = OK, $data = array(), $is_mrl = false, $relogin=false, $needRegister=false)
    {
        $result['code'] = $code;
        //$result['msg'] = $msg;
        if ($code != OK) {
            if (!empty($data) && !is_array($data)) {
                $result['msg'] = $data;
            } elseif (is_array($data)) {
                $result['data'] = $data;
            }
            header('Content-Type:application/json; charset=utf-8');
            $handler  =   isset($_GET[C('VAR_JSONP_HANDLER')]) ? $_GET[C('VAR_JSONP_HANDLER')] : C('DEFAULT_JSONP_HANDLER');
            exit($handler.'('.json_encode($data,JSON_UNESCAPED_UNICODE).');');
            //exit(json_encode($result, JSON_UNESCAPED_UNICODE));
        }
        if (!empty($data)) {
            $result['data'] = $data;
            $result['sign'] = encode_private_sign($data);//返回数据用私钥加密
        }
        if($is_mrl === true){
            $result['relogin'] = $relogin;
            $result['needRegister'] = $needRegister;
        }
        //$result['result'] = $data;
        //header('Content-Type:application/json; charset=utf-8');
        header('Content-Type:application/json; charset=utf-8');
        $handler  =   isset($_GET[C('VAR_JSONP_HANDLER')]) ? $_GET[C('VAR_JSONP_HANDLER')] : C('DEFAULT_JSONP_HANDLER');
        exit($handler.'('.json_encode($data,JSON_UNESCAPED_UNICODE).');');
        //exit(json_encode($result, JSON_UNESCAPED_UNICODE));
    }


    /**
     * api return json
     * @param   mixed $data   要返回的数据/出错消息
     * @param   int   $code   结果编辑码
     * @return void
     */
    /*protected function return_json($code = OK, $data = array())
    {
        $result['code'] = $code;
        //$result['msg'] = $msg;
        if ($code != OK) {
            if (!empty($data) && !is_array($data)) {
                $result['msg'] = $data;
            } elseif (is_array($data)) {
                $result['data'] = $data;
            }
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            exit;
        }
        if (!empty($data)) {
            $result['data'] = $data;
        }
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        exit;
    }*/

    /**
     * Ajax方式返回数据到客户端
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type AJAX返回数据格式
     * @return void
     */
    protected function ajaxReturn($data,$type='') {
        if(empty($type)) $type  =   C('DEFAULT_AJAX_RETURN');
        switch (strtoupper($type)){
            case 'JSON' :
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode($data));
            case 'XML'  :
                // 返回xml格式数据
                header('Content-Type:text/xml; charset=utf-8');
                exit(xml_encode($data));
            case 'JSONP':
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                $handler  =   isset($_GET[C('VAR_JSONP_HANDLER')]) ? $_GET[C('VAR_JSONP_HANDLER')] : C('DEFAULT_JSONP_HANDLER');
                exit($handler.'('.json_encode($data).');');
            case 'EVAL' :
                // 返回可执行的js脚本
                header('Content-Type:text/html; charset=utf-8');
                exit($data);
        }
    }

    /**
     * 检测词语是否列入黑名单
     * @param string $key_word
     * @return bool
     */
    protected function check_keyword($key_word = '')
    {
        $is = $this->redis->sCard($this->bk_rediskey);//返回集合总数
        if(empty($is)){
            $keyword_list = db('key_word')->field('word')->select();
            foreach($keyword_list as $key => $value){
                $this->redis->sAdd($this->bk_rediskey,$value['word']);
            }
        }
        $is2 = $this->redis->sIsMember($this->bk_rediskey,$key_word);//检测是否有该成员
        if($is2){
            $this->return_json([],'非法字符！');
        }
        return false;
    }



    /**
     * 获取单个用户
     * @param $uid
     * @return array|false|\PDOStatement|string|\think\Model
     */
    protected function get_user($memberid)
    {
        $user = db('member')->where(array('id'=>$memberid))->find();
        return $user;
    }

    /**
     * 生成用户redis
     * @param $url
     * @return string
     */
    protected function get_user_redis($memberid,$refresh = false)
    {
        $idtotoken = $this->usertoken_rediskey.'_id:'.$memberid;//用戶token索引
        $time = TOKEN_USER_LIVE_15DAY * 3600;
        $user = $this->get_user($memberid);
        if(empty($user)){
            $this->return_json(E_OP_FAIL,'没有这个用户');
        }
        if($refresh){//自动登录后刷新token
            $token_old = $this->redis->get($idtotoken);
            if(empty($token_old)){
                $this->return_json(E_OP_FAIL,'自动登录有效期已过，请重新登录');
            }
        }
        $is = $this->redis->setex($idtotoken,$time,json_encode($user));
        if($is){
            $this->user = $user;
            return true;
        }
        $this->return_json(E_OP_FAIL,'获取用户信息失败，请重新登录');
    }

    /**
     * 检查用户redis
     * @param $uid
     * @return bool|string
     */
    protected function check_user_redis($memberid = '')
    {
        //dump($token);exit;
        if(empty($memberid)){
            return false;
        }
        $tokentokey = $this->usertoken_rediskey.'_id:'.$memberid;
        $user = $this->redis->get($tokentokey);
        if(empty($user)){
            return false;
        }
        return json_decode($user,true);
    }


    /**
     * 生成用户token
     * @param $url
     * @return string
     */
    protected function get_user_token($memberid,$refresh = false)
    {
        $idtotoken = $this->usertoken_rediskey.'_id:'.$memberid;//用戶token索引
        $time = TOKEN_USER_LIVE_15DAY * 3600;
        $user = $this->get_user($memberid);
        if($refresh){//自动登录后刷新token
            $token_old = $this->redis->get($idtotoken);
            if(empty($token_old)){
                return false;
            }
            $tokentokey = $this->usertoken_rediskey.':'.$token_old;
            $this->redis->expire($idtotoken,$time);
            $this->redis->expire($tokentokey,$time);
            $this->user = $user;
            return $token_old;
        }
        $token = md5($memberid.date('Y-m-d H:i:s').USER_TOKEN_KEY);
        $tokentokey = $this->usertoken_rediskey.':'.$token;//用redis代替session存儲用戶信息
        if(empty($user)){
            $this->return_json(E_OP_FAIL,'没有这个用户');
        }
        $this->user = $user;
        $this->redis->setex($idtotoken,$time,$token);
        $this->redis->setex($tokentokey,$time,json_encode($user));
        return $token;
    }

    /**
     * 删除用户token
     * @param $uid
     * @return bool
     */
    protected function del_user_token($uid)
    {
        $idtotoken = $this->usertoken_rediskey.'_id:'.$uid;//用戶token索引
        $token_old = $this->redis->get($idtotoken);
        if(empty($token_old)){
            return true;
        }
        $tokentokey = $this->usertoken_rediskey.':'.$token_old;
        $this->redis->del($idtotoken);
        $this->redis->del($tokentokey);
        return true;
    }


    /**
     * 检查用户token
     * @param $uid
     * @return bool|string
     */
    protected function check_user_token($token)
    {
        //dump($token);exit;
        if(empty($token)){
            return false;
        }
        $tokentokey = $this->usertoken_rediskey.':'.$token;
        $user = $this->redis->get($tokentokey);
        if(empty($user)){
            return false;
        }
        return json_decode($user,true);
    }

    /**
     * 检查用户token,需要用户ID
     * @param $uid
     * @return bool|string
     */
    protected function check_user_token_by_memberid($uid)
    {
        if(empty($uid)){
            return false;
        }
        $idtotoken = $this->usertoken_rediskey.'_id:'.$uid;//用戶token索引
        $token = $this->redis->get($idtotoken);
        if(empty($token)){
            return false;
        }
        $tokentokey = $this->usertoken_rediskey.':'.$token;
        $user = $this->redis->get($tokentokey);
        if(empty($user)){
            $this->redis->del($idtotoken);
            return false;
        }
        return json_decode($user,true);
    }

    /**
     * 获取请求的token
     * @param $url
     * @return string
     */
    protected function check_token($header_token,$url)
    {
        $token = md5(strtolower($url).date('Y-m-d').USER_TOKEN_KEY);
        //var_dump($token);exit;
        if($header_token == $token){
            return true;
        }
        return false;
    }

    /**
     * @brief 防重放攻击
     *      只需要防范 在非 cli 运行模式下的 post 提交
     */
    protected function is_repeat() /* {{{ */
    {
        if (PHP_SAPI == 'cli') {
            return false;
        }

        $post_string = '';
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $post_string = file_get_contents('php://input');
        } else {
            //return false;
        }

        if (!defined('CLIENT_IP')) { define('CLIENT_IP', getenv('HTTP_X_FORWARDED_FOR') ? getenv('HTTP_X_FORWARDED_FOR') : getenv('REMOTE_ADDR')); }
        //$chk_string = CLIENT_IP.':'.$_SERVER['REQUEST_URI'];
        $chk_string = CLIENT_IP.':'.$_SERVER['REQUEST_URI'].':'.$post_string;
        $redis_key = 'api-repeat:'.md5($chk_string);

        $is_repeat = $this->redis->get($redis_key);
        if (($_SERVER['REQUEST_METHOD'] == 'POST' && $is_repeat) || $is_repeat > 5) {   /* POST 超过1次，GET 超过5次 */
            //$this->redis->setex($redis_key, 2, $is_repeat + 1);
            header('HTTP/1.1 403 fuck!');
            wlog(APP_PATH.'log/'.$this->sn.'_repeat_'.date('Ym').'.log', $_SERVER['REQUEST_METHOD'].' '.$_SERVER['REQUEST_URI'].' '.$post_string);
            return $this->return_json(REQUEST_REPEAT, '操作频繁');
        } else {
            $this->redis->setex($redis_key, 2, (int) $is_repeat + 1);     /* 2秒后过期 */
        }

        return false;
    } /* }}} */


    /**
     * 上傳文件
     * @param bool $bool
     * @return mixed
     */
    public function upload_file_do($bool = true) /* {{{ */
    {
        if(empty($_FILES)){
            $this->return_json(E_OP_FAIL, '文件为空！');
        }
        $config['max_size'] = '2048000';
        if ($_FILES['file']['size']>=$config['max_size']) {
            $msg = '您上传的文件有'.$_FILES['logo']['size']/1024 .'KB，不要大于'.$config['max_size']/1024 . 'KB!';
            $this->return_json(E_OP_FAIL, $msg);
        }
        $upload_api = UPLOAD_URL;
        $tmpname = $_FILES['file']['name'];
        $tmpfile = $_FILES['file']['tmp_name'];
        $tmpType = $_FILES['file']['type'];
        $result_json = upload_file($upload_api, $tmpname, $tmpfile, $tmpType);//curl上传
        if ($result_json) {
            if (!$bool) {
                return $result_json;
            }
            //$result = json_decode($result_json,true);
            //$result = str_replace('\\','/',$result);
            $this->return_json(OK, $result_json);
        } else {
            $this->return_json(E_OP_FAIL, '操作失败！');
        }
    } /* }}} */

    /**
     * redis分布式锁，解决并发问题或者只允许操作一次的问题
     * @param   $rk     redis的key值
     * @param   $lockTime   锁定时间（秒数），超过这个时间自动解锁，解决死锁问题
     * @return  $bool   true：正常操作，false：处于锁状态
     */
    protected function fbs_lock($rk='', $lockTime=5)
    {
        $b = $this->redis->setnx($rk, $_SERVER['REQUEST_TIME']);
        if ($b) {
            $this->redis->expire($rk, 5);
            return true;
        }//没锁
        //锁已超时
        return false;
    }

    /**
     * redis分布式解锁
     * @param   $rk     redis的key值
     * @return  $bool   true：正常操作，false：处于锁状态
     */
    protected function fbs_unlock($rk='')
    {
        return $this->redis->del($rk);
    }




}
