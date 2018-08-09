<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use think\Input;
use think\Db;
use think\Session;
use think\Validate;

class User extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 注册发送短信接口
     */
    public function sms()
    {
        $phone = input('post.phone');
        $type = input('post.type');
        $result = $this->validate(
            ['phone'  => $phone , 'type'  => $type ],
            ['phone'  => 'require|number|max:11|min:11', 'type'  => 'require|in:0,1,2,3,4,5']
        );
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }

        $code = mt_rand(10000,99999);
        //$date = date('Ymd');
        //var_dump(ALIYUN_TEMP_CODE.$type);exit;
        $send = Message::sendSms($phone,$code,ALIYUN_TEMP_CODE.$type,'890',ALIYUN_SIGN_TEST);
        if(empty($send)){
            wlog(APP_PATH.'log/Send_Sms_Error.log','发送短信返回内容为空:'.$phone.'-'.$code);
            $this->return_json(E_OP_FAIL,'短信发送失败，请检查网络1');
        }
        if($send->Message != 'OK'){
            wlog(APP_PATH.'log/Send_Sms_Error.log',$phone.'-'.$code.'-'.json_encode($send,JSON_UNESCAPED_UNICODE));
            $this->return_json(E_OP_FAIL,'短信发送失败，请检查网络2');
        }
        $this->redis->set(REDIS_YZM_KEY.':'.$phone.'_'.$type,$code,REDIS_EXPIRE_5M);//暂存到redis
        $this->return_json(OK,['code'=>$code]);
    }


    /**
     * 注册接口
     */
    public function reg()
    {
        $tel = input('post.phone');
        $company = input('post.company');
        $name = input('post.name');
        $code = input('post.code');
        //数据验证
        $result = $this->validate(
            [
                'tel'  => $tel,
                'company' => $company,
                'name' => $name,
                'code' => $code,
            ],
            [
                'tel'  => 'require|number|max:11|min:11',
                'company'  => 'chsAlphaNum', //汉字字母数字
                'name'  => 'require|chsAlpha',//汉字字母
                'code'  => 'require|number|max:5|min:5',
            ]
        );
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }
        $redis_code = $this->redis->hGet(REDIS_YZM_KEY,$tel.'_2');
        if($code != $redis_code){
            //$this->return_json(E_ARGS,'验证码错误');//测试时暂时注释
        }
        $this->redis->hdel(REDIS_YZM_KEY,$tel.'_2');
        $is_repeat = Db::name('member')->field('id')->where(array('tel'=>$tel))->find();
        if(!empty($is_repeat)){
            $this->return_json(E_OP_FAIL,'注册失败,重复注册');
        }
        $unique = date('YmdHis').mt_rand(10000,99999);
        $data['tel'] = $tel;
        $data['company'] = $company;
        $data['name'] = $name;
        $data['nickname'] = $name;
        $data['sex'] = 2;
        $data['headimg'] = DEFAULT_IMG;
        $data['img'] = DEFAULT_IMG;
        $data['openid'] = $unique;
        $data['unionid'] = $unique;
        $data['isfocus'] = 'other';
        $ip = get_ip();
        if($ip == '127.0.0.1'){
            $ip = '183.238.1.246';
        }
        $dizhi = get_city($ip);
        if($dizhi !== false){
            $data['province'] = $dizhi[1];
            $data['city'] = $dizhi[2];
        }
        $data['addtime'] = date('Y-m-d H:i:s');
        $data['issubmit'] = 1;
        $data['source'] = $this->source;
        //dump($data);exit;
        $return_data_json = json_encode($data,JSON_UNESCAPED_UNICODE);
        // 启动事务,插入用户数据
        /*Db::startTrans();
        try{

            //Db::table('hot_hx_account')->insert($hx);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            wlog(APP_PATH.'log/Reg_Success.log','注册失败:插入数据错误，已回滚'.$tel.'-'.$return_data_json);
            $this->return_json(E_OP_FAIL,'注册失败');
        }*/

        $memberid = Db::name('member')->insertGetId($data);
        //var_dump($memberid);exit();
        if(empty($memberid)){
            wlog(APP_PATH.'log/Reg_Success.log','注册失败:插入数据错误'.$tel.'-'.$return_data_json);
            $this->return_json(E_OP_FAIL,'注册失败');
        }
        //生成用户token，记录登录状态与日志
        $token = $this->get_user_token($memberid);
        //$this->set_login_log($data['uid'],1,$data['in_type']);
        $this->return_json(OK,['memberid'=>$memberid,'token'=>$token]);
    }


    /**
     * 登录接口
     */
    public function login()
    {
        $phone = input('post.phone');
        $code = input('post.code');
        //数据验证
        $result = $this->validate(
            [
                'phone'  => $phone,
                'code' => $code,
            ],
            [
                'phone'  => 'require|number|max:11|min:11',
                'code'  => 'require|number|max:5|min:5',
            ]
        );
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }
        $redis_code = $this->redis->hGet(REDIS_YZM_KEY,$phone.'_4');
        if($code != $redis_code){
            //$this->return_json(E_ARGS,'验证码错误');//测试时暂时注释
        }
        $this->redis->hdel(REDIS_YZM_KEY,$phone.'_4');
        $where['tel'] = $phone;
        $user = db('member')->field('id')->where($where)->find();
        if(empty($user)){
            $this->return_json(E_OP_FAIL,'该用户尚未注册');
        }
        $data['memberid'] = (string)$user['id'];
        $data['token'] = $this->get_user_token($user['id']);
        //$this->set_login_log($user['uid'],1,1);
        $this->return_json(OK,$data);
    }

    /**
     * 自动登录接口
     */
    public function auto_login()
    {
        $memberid = $this->user['id'];
        $type = input('post.type');
        if($type != '6666'){
            $this->return_json(E_ARGS,'参数错误');
        }
        $where['id'] = $memberid;
        $user = db('member')->field('id')->where($where)->find();
        if(empty($user)){
            $this->return_json(E_OP_FAIL,'该用户尚未注册');
        }
        $token = $this->get_user_token($memberid,true);
        if($token===false){
            $this->return_json(E_OP_FAIL,'请重新登录2');
        }
        $data['memberid'] = $memberid;
        $data['token'] = $token;
        //自动登录是否需要插入登录日志--待定
        $this->return_json(OK,$data);
    }

    /**
     * 微信授权登录接口
     */
    public function  wechat_login(){
        //LogController::W_H_Log("进入callback方法");
        $appid = WECHAT_APPID;
        $secret = WECHAT_APPSECRET;
        $code = input('post.code');
        $state = base64_decode(input('post.state'));
        /*$state = "http://tianyan199.com".urldecode($state);*/
        $get_token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$secret.'&code='.$code.'&grant_type=authorization_code';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);  // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch,CURLOPT_URL,$get_token_url);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        $res = curl_exec($ch);
        curl_close($ch);
        $json_obj = json_decode($res,true);
        //LogController::W_H_Log("get_token_url 为：".$get_token_url);
        //LogController::W_H_Log("callback 返回原始数据：".$res);
        curl_close($ch);
        //根据openid和access_token查询用户信息
        $access_token = $json_obj['access_token'];
        $openid = $json_obj['openid'];

        if (!($access_token && $openid)){
            $this->return_json(E_OP_FAIL,'登录失败，无法获取到access_token或openid');
        }
        $user = db("member");
        $list = $user->where(['openid'=>$openid])->find();
        //error_log("openid is :".$openid."\n",3,"./logs/info.log");
        //LogController::W_H_Log("获取用户后的重定向地址为：".$state);
        if(is_array($list) && $list){
            $_SESSION['CurrenMember'] = $user->data();
            //LogController::W_H_Log(date('y-m-d H:i:s',time())."提前返回数据：\n"."\n",3,"./logs/info.log");

            if (empty($SESSION['CurrenMember']['unionid'])){
                $get_user_info_url = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $access_token . '&openid=' . $openid . '&lang=zh_CN';
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $get_user_info_url);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                $res = curl_exec($ch);
                curl_close($ch);
                //error_log(date('callback 方法'.'y-m-d H:i:s',time()).$res,3,"./logs/info.log");
                //解析json
                error_log("result is :" . $res, 3, "./logs/info.log");
                $user_obj = json_decode($res, true);
                $data = array(
                    'unionid' => $user_obj['unionid'],
                    'headimg' => $user_obj['headimgurl'],
                    'lastupdate' => date("Y-m-d H:i:s")
                );
                LogController::W_H_Log($data);
                M("member")->where("openid='".$openid."'")->save($data);

            }
            header("Location:".$state);
        }else {
            $get_user_info_url = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $access_token . '&openid=' . $openid . '&lang=zh_CN';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $get_user_info_url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            $res = curl_exec($ch);
            curl_close($ch);
            //error_log(date('callback 方法'.'y-m-d H:i:s',time()).$res,3,"./logs/info.log");
            //解析json
            error_log("result is :" . $res, 3, "./logs/info.log");
            $user_obj = json_decode($res, true);
            $data = array(
                'nickname' => $user_obj['nickname'],
                'openid' => $user_obj['openid'],
                'unionid' => $user_obj['unionid'],
                'img' => $user_obj['headimgurl'],
                'headimg' => $user_obj['headimgurl'],
                'sex' => $user_obj['sex'],
                'country' => $user_obj['country'],
                'city' => $user_obj['city'],
                'province' => $user_obj['province'],
                'addtime' => date("Y-m-d H:i:s"),
                'isfocus' => "no"
            );
            $member = M("member");
            $result = $member->add($data);
            if ($result) {
                LogController::W_H_Log("返回数据为：".$result);
                $membert = M("member");
                $membert->where("id=" . $result)->find();
                dump($membert->data());
                $_SESSION['CurrenMember'] = $membert->data();
            } else {
                LogController::W_H_Log("未执行插入操作：".$data['openid']);
            }
            LogController::W_H_Log("获得返回数据：".$res);
            header("Location:" . $state);
        }
    }

}
