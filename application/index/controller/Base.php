<?php
namespace app\index\controller;
use app\tools\controller\Tools;
use think\Controller;
use think\Request;
//use think\Input;
use think\Db;
use think\Session;
use think\Validate;
//use think\Redis;

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
        $pass = [
            'user' => ['reg', 'sms', 'login','wechat_login','my_kefu','visitor_reg'],
            'index'=>['index','xieyi','main','all_lecture','get_mszt','get_hydk','get_search_info','search','clear_search_history','about','get_toutiao_list','get_toutiao_detail','get_toutiao_next','get_yuedu_list'],
            //'lecture'=>['get_jiangshi','get_kecheng','get_zhuanlan'],
            'live'=>['save_video_url','baocun']
        ];
        //$pass2 = ['live'=>['uploadfile']];

        $request = Request::instance();
        //$url = $request->controller().$request->action();
        $header = get_auth_headers();
        $this->source = empty($header['Source'])?'APP':$header['Source'];/* 获取请求来源 */
        // || empty(input('request.bujiami')
        if($this->source!='ANDROID'){
            $pass['lecture'] = ['get_jiangshi','get_kecheng','get_zhuanlan','get_free'];
        }
        $this_class = strtolower($request->controller());
        $this_method = strtolower($request->action());

        if(!($this->source=='ANDROID' && $this_class=='live' && $this_method=='uploadfile')){
            $this->check_sign($this->source);/*签名校验*/
        }
        $this->is_repeat(); /* 重放检测 */
        if(!empty(input('request.phone_id')) && empty($header['Memberid'])){
            $phone_id = input('request.phone_id');//IOS 部分功能必须不登录即使用，此时需要把phone_id
            $m = db('member')->field('id')->where(['openid'=>$phone_id])->find();
            if(!empty($m)){
                $header['Memberid'] = $m['id'];
            }
        }
        if (isset($pass[$this_class]) && in_array($this_method, $pass[$this_class])) {
            if(isset($pass['lecture']) && in_array($this_method, $pass['lecture'])){
                if(!empty($header['Memberid'])){
                    goto KJ;
                }
            }
            if($this_class == 'index' && in_array($this_method,['main','get_toutiao_detail','get_toutiao_next','search','get_search_info'])){
                //治外法权:在不需要登录权限的方法当中，如果有需要用到用户ID的地方，需要这个步骤
                if(!empty($header['Memberid'])){
                    goto KJ;
                }
            }
        }else{
            KJ:
            $memberid = empty($header['Memberid'])?'':$header['Memberid'];
            $result = $this->validate(
                [
                    'uid'  => $memberid,
                ],
                [
                    'uid'  => 'number|max:16',
                ]
            );
            if($result !== true){
                $this->return_json(E_ARGS,'参数错误1');
            }

            $user = $this->check_user_redis($memberid);
            if(!$user){
                $this->return_json(E_OP_FAIL,'请重新登录1','',true,true);
            }
            $this->user = $user;
        }
        $this->sn = 'ty';
    }

    /**
     * 签名验证
     * @return bool
     */
    protected function check_sign($source = 'IOS')
    {
        $param = input('request.');
        if(empty($param['sign'])){
            $this->return_json(422,'参数为空：sign');
        }
        $sign = $param['sign'];
        unset($param['sign']);
        wlog(APP_PATH.'log/sign.log',$sign.'  |  '.json_encode($param));
        if($source=='ANDROID'){
            $is = vsign_for_android($sign,$param);
        }else{
            $is = vsign($sign,$param);
        }
        ob_clean();
        if(!$is){
            $this->return_json(E_SIGN,'验签失败');
        }
        return true;
    }


    /**
     * 判断是否已关注专栏
     * @param $memberid
     * @param $channel_id
     * @return int
     */
    protected function is_attention($memberid,$channel_id)
    {
        $atten = db('attention')->field('id')->where(['memberid'=>$memberid,'roomid'=>$channel_id,'type'=>1])->find();
        if (!empty($atten)) {
            return  1;
        } else {
            return  0;
        }
    }

    /**
     * 根据来源返回不同结构的提示语
     */
    protected function returns($msg='')
    {
        if($this->source == 'ANDROID'){
            $this->return_json(E_OP_FAIL,$msg);
        }else{
            $this->return_json(OK,['msg'=>$msg]);
        }
    }

    /**
     * 检测直播状态
     */
    protected function check_live_status($lecture_id,$countdown = '')
    {
        $live_status = $this->redis->get(REDIS_LIVE_STATUS.':'.$lecture_id);
        if(empty($live_status)){
            /*if($countdown>0){
                $live_status = 'wait';
            }else{
                $live_status = 'start';
            }*/
            $live_status = 'start';//暂时就2个状态
        }else{
            $live_status = 'stop';
        }
        return $live_status;
    }

    /**
     * 统一专栏type字段
     * @param $data
     * @return mixed
     */
    protected function check_channel_type($data)
    {
        if($data['type']=='open' || $data['type']=='open_channel'){
            $data['type'] = 'open_channel';
        }
        return $data;
    }

    /**
     * 检测课程所属专栏是否免费
     * @param $data
     * @param string $is_selected
     * @return array|string
     */
    protected function check_pay_type($data,$is_selected='')
    {
        if(empty($data)){
            return [];
        }
        if($is_selected){
            if($data['is_pay_only_channel']==1){
                return 'pay_lecture';
            }else{
                return $data['type'];
            }
        }
        if(empty($data[0])){//单个
            if(empty($data['channel_id'])){
                $data['channel_id'] = BANZHUREN;
            }
            $type = db('channel')->where(['id'=>$data['channel_id']])->value('type');
            if($type=='pay_channel'){
                $data['type'] = 'pay_lecture';
            }
        }else{//多个
            foreach($data as $key => $value){
                if(empty($value['channel_id'])){
                    $value['channel_id'] = BANZHUREN;
                }
                $type = db('channel')->where(['id'=>$value['channel_id']])->value('type');
                if($type=='pay_channel'){
                    $data[$key]['type'] = 'pay_lecture';
                }
            }
        }
        return $data;
    }

    /**
     * 获取price_list中的支付金额
     * @param $content
     * @return mixed
     */
    protected function get_price_list_money($content)
    {
        /*if(!empty($content['money'])){
            $data['cost'] = $content['money'];
        } else*/
        if(!empty($content['price_list'])){
            $price_list = json_decode($content['price_list'],true);
            $data['cost'] = $price_list[0]['money'];
        }else{
            $data['cost'] = 0;
        }
        return $data['cost'];
    }

    /**
     * 设置显示到前端的金额
     * @param $value  专栏/课程信息数组
     * @param $cost   课程价格
     * @param $money  专栏固定收费价格
     * @return mixed
     */
    protected function set_show_pay_money($value,$cost)
    {
        if($value['is_pay_only_channel'] == 1 && $value['permanent']==1){
            $cost = $value['money'];
        }elseif($value['is_pay_only_channel'] == 1){
            $cost = $this->get_price_list_money($value);
        }
        return $cost;
    }


    /**
     * json格式返回数据
     * @param bool $code     返回数据的状态
     * @param array $data    返回的数据
     * @param bool $is_mrl   是否返回后面2个参数
     * @param bool $relogin  是否需要登录
     * @param bool $needRegister 是否需要注册
     */
    protected function return_json($code = OK,$data = array(), $count = '',$is_mrl = false, $relogin=false, $needRegister=false)
    {
        $result['code'] = $code;
        $result['count'] = $count;
        if ($code != OK) {
            if (!empty($data) && !is_array($data)) {
                $result['msg'] = $data;
            } elseif (is_array($data)) {
                $result['data'] = $data;
            }
            exit(json_encode($result, JSON_UNESCAPED_UNICODE));
        }
        if (!empty($data)) {
            $result['data'] = arr_val_tran_str($data,$this->source);//数组内所有值转成字符类型
            $result['sign'] = encode_private_sign($result['data']);//返回数据用私钥加密
        }
        if($is_mrl === true){
            $result['relogin'] = $relogin;
            $result['needRegister'] = $needRegister;
        }
        exit(json_encode($result, JSON_UNESCAPED_UNICODE));
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
     * 上传文件
     * @param bool $bool
     * @return mixed
     */
    public function upload_file($is_local = false)
    {
        //header("Content-type:application/octet-stream");        //这句告诉以流的形式来接收数据；
        header('Content-type: text/json; charset=UTF-8' );
        $houzui_array = ['.jpg','.bmp','.png','.mp4','.m3u8','.mp3','.amr','.ppt','.mov','.wav'];
        $log_path = APP_PATH.'log/uploadFile.log';
        wlog($log_path,'接收参数file：'.json_encode($_FILES,JSON_UNESCAPED_UNICODE));
       /* wlog($log_path,'接收参数post：'.json_encode($_POST,JSON_UNESCAPED_UNICODE));
        wlog($log_path,'接收参数input：'.json_encode(file_get_contents('php://input')));*/
        if(empty($_FILES)){
            $this->return_json(E_OP_FAIL, '文件为空！');
        }
        if ($_FILES["file"]["error"] > 0) {
            $error = get_file_error_info();
            $this->return_json(E_OP_FAIL, '操作失败:'. $error[$_FILES['file']['error']]);
        }
        $houzui = strtolower(strrchr($_FILES["file"]["name"], '.'));//后缀名
        if(!in_array($houzui,$houzui_array)){
            $this->return_json(E_OP_FAIL, '该文件类型不支持');
        }
        $config['max_size'] = '1073741824';
        if ($_FILES['file']['size']>=$config['max_size']) {
            $msg = '您上传的文件有'.$_FILES['logo']['size']/1048576 .'MB，不要大于'.$config['max_size']/1048576 . 'MB!';
            $this->return_json(E_OP_FAIL, $msg);
        }
        $is = false;
        $path = '';
        $name = time().mt_rand(100,999);//文件名
        $filename = $name.$houzui;//带后缀的文件名
        $path = 'Public/Uploads/Chat/app/'.$filename; //上传到oss的路径
        if($houzui == '.mp4' || $houzui == '.m3u8' || $houzui == '.mov'){//视频先上传到本地
            $path_local = FILE_PATH."video/";//本地保存路径
            $path_local_file = $path_local .$filename;//包含文件名的本地保存路径
            $cover = str_replace($houzui,".jpg",$path_local_file);//本地视频截图保存路径
            $cover_path = 'Public/Uploads/Chat/app/'.$name.'.jpg';//oss视频截图保存路径
            $is = move_uploaded_file($_FILES["file"]["tmp_name"],$path_local_file);//视频保存到本地
            if(empty($is)){
                wlog($log_path,'文件保存本地失败');
                $this->return_json(E_OP_FAIL, '文件保存本地失败');
            }
            if($houzui == '.m3u8' || $houzui == '.mov'){
                $pd = Tools::get_mp4($path_local_file,$path_local_file.'.mp4');
                if(!$pd){
                    wlog($log_path,'视频转码失败'.$path_local_file);
                    $this->return_json(E_OP_FAIL, '视频转码失败');
                }
                $path_local_file = $path_local_file.'.mp4';
                $path = $path.'.mp4';
                $filename = $filename.'.mp4';
            }
            //var_dump($path_local_file,$cover);exit;
            Tools::getVideoCover($path_local_file,1,$cover);//获取截图
            $is1 = Tools::UploadFile_OSS($cover_path,$cover);//上传截图到oss
            $is2 = Tools::UploadFile_OSS($path,$path_local_file);//上传视频到oss
            if ($is1 && $is2) {
                $data = array(
                    'addtime' => date("Y-m-d H:i:s") . "." . rand(000000, 999999),
                    'memberid' => $this->user['id'],
                    'mediaid' => '',
                    'path' => SERVER_URL.'/public/video/'.$filename,
                    'OSS_path' => OSS_REMOTE_PATH.'/'.$path,
                    'type' => "video",
                    'cover' => OSS_REMOTE_PATH.'/'.$cover_path,
                    'main' => null,
                );
                $count = db('material')->insertGetId($data);//数据库插入一条信息
                if(empty($count)){
                    wlog($log_path,'文件信息插入material表失败1');
                    $this->return_json(E_OP_FAIL, '文件信息插入material表失败1');
                }
                $result['video_id'] = $count;
            } else {
                wlog($log_path,'文件上传到oss失败 video');
                $this->return_json(E_OP_FAIL, '文件上传到oss失败video');
            }
        }elseif($houzui == '.amr' || $houzui == '.wav'){
            $path_local = FILE_PATH."audio/";//本地保存路径
            $path_local_file = $path_local .$filename;//包含文件名的本地保存路径
            $is = move_uploaded_file($_FILES["file"]["tmp_name"],$path_local_file);//视频保存到本地
            if(empty($is)){
                wlog($log_path,'文件保存本地失败4');
                $this->return_json(E_OP_FAIL, '文件保存本地失败4');
            }
            $pd = Tools::get_mp3($path_local_file,$path_local_file.'.mp3');//转成mp3
            if(empty($pd)){
                wlog($log_path,'音频转码失败'.$path_local_file);
                $this->return_json(E_OP_FAIL, '音频转码失败');
            }
            $is2 = Tools::UploadFile_OSS($path.'.mp3',$path_local_file.'.mp3');//上传到oss
            if(empty($is2)){
                wlog($log_path,'文件上传到oss失败mp3');
                $this->return_json(E_OP_FAIL, '文件上传到oss失败 mp3');
            }
            $path = $path.'.mp3';
        }else{
            $path = 'Public/Uploads/Chat/app/'.$filename;
            $is = Tools::UploadFile_OSS($path,$_FILES["file"]["tmp_name"]);
        }
        if ($is) {
            $result['path'] = OSS_REMOTE_PATH.'/'.$path;
            $this->return_json(OK, $result);
        } else {
            wlog($log_path,'文件上传到oss失败2');
            $this->return_json(E_OP_FAIL, '文件上传到oss失败2');
        }
    }


    /**
     * 验证 手机验证码是否正确
     * @param $tel 手机号码
     * @param $num 阿里云短信TEMP_CODE后缀
     * @param $code 验证码
     * @return bool
     */
    protected function check_code($tel,$num='2',$code)
    {
        $key = $tel.'_'.$num;
        $redis_code = $this->redis->get(REDIS_YZM_KEY.':'.$key);
        if($code != $redis_code){
            //$this->return_json(E_ARGS,'验证码错误');//测试时暂时注释
        }
        $this->redis->del(REDIS_YZM_KEY.':'.$key);
        return true;
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
     * @param $memberid 用户ID
     * @param bool $refresh 是否刷新redis缓存
     * @return bool
     */
    protected function get_user_redis($memberid,$refresh = false)
    {
        $idtotoken = $this->usertoken_rediskey.'_id:'.$memberid;//用戶token索引
        $time = TOKEN_USER_LIVE_6MOON * 3600;
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
     * 删除用户redis
     * @param $uid
     * @return bool|string
     */
    protected function del_user_redis($memberid = '')
    {
        //dump($token);exit;
        if(empty($memberid)){
            return false;
        }
        $tokentokey = $this->usertoken_rediskey.'_id:'.$memberid;
        $user = $this->redis->del($tokentokey);
        if(empty($user)){
            return false;
        }
        return true;
    }

    protected function check_js_member_id($data)
    {
        foreach($data as $k1 =>$v1){
            $data[$k1]['js_memberid'] = $v1['memberid'];
            if($v1['memberid']==294 && !empty($v1['channel_id'])){
                $a = db('channel')->where(['id'=>$v1['channel_id']])->value('lecturer');
                if(!empty($a)){
                    $data[$k1]['js_memberid'] = $a;
                }
            }
        }
        return $data;
    }


    /**
     * 生成用户token
     * @param $url
     * @return string
     */
    /*protected function get_user_token($memberid,$refresh = false)
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
    }*/

    /**
     * 删除用户token
     * @param $uid
     * @return bool
     */
    /*protected function del_user_token($uid)
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
    }*/


    /**
     * 检查用户token
     * @param $uid
     * @return bool|string
     */
   /* protected function check_user_token($token)
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
    }*/

    /**
     * 检查用户token,需要用户ID
     * @param $uid
     * @return bool|string
     */
    /*protected function check_user_token_by_memberid($uid)
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
    }*/

    /**
     * 获取请求的token
     * @param $url
     * @return string
     */
    /*protected function check_token($header_token,$url)
    {
        $token = md5(strtolower($url).date('Y-m-d').USER_TOKEN_KEY);
        //var_dump($token);exit;
        if($header_token == $token){
            return true;
        }
        return false;
    }*/

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

        if (!defined('CLIENT_IP')) {
            define('CLIENT_IP', getenv('HTTP_X_FORWARDED_FOR') ? getenv('HTTP_X_FORWARDED_FOR') : getenv('REMOTE_ADDR'));
        }
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
     * 获取打赏文字信息
     * @param $membername
     * @param $tmembername
     * @param $pay_amount
     * @return string
     */
    protected function get_pay_param($membername,$tmembername,$pay_amount)
    {
        switch ($pay_amount){
            case 2: return $membername.'送了'.$tmembername.'一颗闪亮星星 '.$pay_amount.'天雁币';break;
            case 6: return $membername.'送了'.$tmembername.'一个怦然心动 '.$pay_amount.'天雁币';break;
            case 9: return $membername.'送了'.$tmembername.'一座黄金屋 '.$pay_amount.'天雁币';break;
            case 66: return $membername.'送了'.$tmembername.'一枚真爱火箭 '.$pay_amount.'天雁币';break;
            case 88: return $membername.'送了'.$tmembername.'一枚颗永恒钻石'.$pay_amount.'天雁币';break;
            case 520: return $membername.'送了'.$tmembername.'一顶无冕冠军 '.$pay_amount.'天雁币';break;
            default: return $membername.'打赏了'.$tmembername.$pay_amount.'元红包';break;
        }
    }

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

    /**
     * 数据类型转换
     * @param $data
     * @return array|string
     */
    public function tranfer($data)
    {
        $data = arr_val_tran_str($data,$this->source);
        $data = json_encode($data,JSON_UNESCAPED_UNICODE);
        return $data;
    }


}
