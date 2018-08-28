<?php
namespace app\index\controller;
use think\Db;
use app\tools\controller\Message;
use Think\Exception;

class User extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    private $log_path = APP_PATH.'log/User.log';

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
            wlog($this->log_path,'send_sms:发送短信返回内容为空:'.$phone.'-'.$code);
            $this->return_json(E_OP_FAIL,'短信发送失败，请检查网络1');
        }
        if($send->Message != 'OK'){
            wlog($this->log_path,'send_sms:'.$phone.'-'.$code.'-'.json_encode($send,JSON_UNESCAPED_UNICODE));
            $this->return_json(E_OP_FAIL,'短信发送失败，请检查网络2');
        }
        $this->redis->set(REDIS_YZM_KEY.':'.$phone.'_'.$type,$code,REDIS_EXPIRE_5M);//暂存到redis
        $this->return_json(OK,['code'=>(string)$code]);
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

        //检测验证码
        $this->check_code($tel,'2',$code);

       /* $redis_code = $this->redis->hGet(REDIS_YZM_KEY,$tel.'_2');
        if($code != $redis_code){
            //$this->return_json(E_ARGS,'验证码错误');//测试时暂时注释
        }
        $this->redis->hdel(REDIS_YZM_KEY,$tel.'_2');*/

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
            wlog($this->log_path,'reg:注册失败:插入数据错误'.$tel.'-'.$return_data_json);
            $this->return_json(E_OP_FAIL,'注册失败');
        }
        //生成用户token，记录登录状态与日志
        $this->get_user_redis($memberid);
        //$this->set_login_log($data['uid'],1,$data['in_type']);
        $this->return_json(OK,['memberid'=>$memberid]);
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
        $this->get_user_redis($user['id']);
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
        $this->get_user_redis($memberid,true);
        $data['memberid'] = $memberid;
        //$data['token'] = $token;
        //自动登录是否需要插入登录日志--待定
        $this->return_json(OK,$data);
    }

    /**
     * 微信授权登录接口
     */
    public function  wechat_login(){
        $log_name = APP_PATH.'log/wechat_login.log';
        $error_log_name = APP_PATH.'log/wechat_login_error.log';
        wlog($log_name,'进入微信登录方法'."\n");
        $appid = WECHAT_APPID;
        $secret = WECHAT_APPSECRET;
        $code = input('post.code');
        $get_token_url = WECHAT_OAUTH_URL.'?appid='.$appid.'&secret='.$secret.'&code='.$code.'&grant_type=authorization_code';
        $res = esay_curl($get_token_url);
        wlog($log_name,"get_token_url 为：".$get_token_url."\n");
        wlog($log_name,"callback 返回原始数据：".$res."\n");
        $json_obj = json_decode($res,true);
        //根据openid和access_token查询用户信息
        if (empty($json_obj['access_token']) || empty($json_obj['openid'])){
            $this->return_json(E_OP_FAIL,'登录失败，无法获取到access_token或openid');
        }
        $access_token = $json_obj['access_token'];
        $unionid = $json_obj['unionid'];
        $user = db('member');
        $list = $user->where(['unionid'=>$unionid])->find();
        if(is_array($list) && !empty($list)){
            $this->get_user_redis($list['id']);
            if (empty($this->user['unionid'])){
                $get_user_info_url = WECHAT_USER_URL.'?access_token=' . $access_token . '&openid=' . $list['openid'] . '&lang=zh_CN';
                $res = esay_curl($get_user_info_url);
                //解析json
                wlog($log_name,"result 1 is :" . $res."\n");
                $user_obj = json_decode($res, true);
                $data = array(
                    'unionid' => $user_obj['unionid'],
                    'headimg' => $user_obj['headimgurl'],
                    'lastupdate' => date("Y-m-d H:i:s")
                );
                wlog($log_name,json_encode($data,JSON_UNESCAPED_UNICODE)."\n");
                db("member")->where(['openid'=>$list['openid']])->update($data);
            }
            $this->return_json(OK,$this->user);
        }else {
            $get_user_info_url = WECHAT_USER_URL.'?access_token=' . $access_token . '&openid=' . $list['openid'] . '&lang=zh_CN';
            $res = esay_curl($get_user_info_url);
            wlog($log_name,"result 2 is :" . $res."\n");
            $user_obj = json_decode($res, true);
            $data = array(
                'nickname' => $user_obj['nickname'],
                'openid' => $user_obj['openid'],
                'unionid' => $user_obj['unionid'],
                'img' => $user_obj['headimgurl'],
                'headimg' => $user_obj['headimgurl'],
                'sex' => $user_obj['sex'],
                'city' => $user_obj['city'],
                'province' => $user_obj['province'],
                'addtime' => date("Y-m-d H:i:s"),
                'isfocus' => "no"
            );
            $data['issubmit'] = 1;
            $data['source'] = $this->source;
            $result = Db::name('member')->insertGetId($data);
            wlog($log_name,"返回数据为：".$result."\n");
            if ($result) {
                $this->get_user_redis($result);
            } else {
                wlog($log_name,"未执行插入操作：".$data['openid']."\n");
            }
            wlog($log_name,"获得返回数据：".$res."\n");
            $this->return_json(OK,$this->user);
        }
    }

    /**
     *修改用户信息
     */
    public function user_update(){
        //$today = date("Y-m-d");
        $name = input('post.name');
        $tel = input('post.phone');
        $company = input('post.company');
        $img = input('post.img');
        $code = input('post.code');
        $result = $this->validate(
            [
                'tel'  => $tel,
                'code' => $code,
                'name' => $name,
                'company' => $company,
                'img' => $img,
            ],
            [
                'tel'  => 'require|number|max:11|min:11',
                'code'  => 'require|number|max:5|min:5',
                'name'  => 'require|chsAlphaNum',
                'company'  => 'require|chsAlphaNum',
                'img'  => 'require',
            ]
        );
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }
        $this->check_code($tel,'0',$code);
        //$numbers = input('post.numbers');
        //$introduction = input('post.introduction');
        //$paynickname = input('post.paynickname');
        $data = array(
            'name'=>$name,
            'tel'=>$tel,
            'company'=>$company,
            'title'=>'lecturer',
            'issubmit'=>1,
            'img'=>$img,
            'lastupdate'=>date('Y-m-d H:i:s')
            //'numbers'=>$numbers,
            //'intro'=>$introduction,
            //'paynickname'=>$paynickname,
        );

        Db::startTrans();
        $count = db('member')->where("id=".$this->user['id'])->update($data);
        if(empty($count)){
            Db::rollback();
            wlog($this->log_path,'user_update 修改个人信息失败:'.$this->user['id']);
            $this->return_json(E_OP_FAIL,'修改个人信息失败');

        }
        $this->get_user_redis($this->user['id'],true);
        wlog($this->log_path,'user_update 修改个人信息成功:'.$this->user['id']);
        //添加到讲师排行中去
        $ld = ['memberid'=>$this->user['id']];
        $a = db('teacherrank')->insertGetId($ld);
        if(empty($a)){
            Db::rollback();
            wlog($this->log_path,'user_update 添加讲师排行失败:'.$this->user['id']);
            $this->return_json(E_OP_FAIL,'添加讲师排行失败');
        }
        $member = $this->user;
        //插入直播间数据
        $liveroom = db("home");
        $liveroom = $liveroom->where(['memberid'=>$member['id']])->find();
        if(empty($liveroom)){
            $homedata = array(
                'name'=>$member['name']?$member['name']:$member['nickname'],
                'memberid'=>$member['id'],
                'description'=>$member['intro'],
                'avatar_url'=>($member['headimg']==$member['img'])?$member['headimg']:$member['img'],
                'attentionnum'=>0,
                'listennum'=>0,
                'liveroom_qrcode_url'=>LIVEROOM_QRCODE_URL,
                'addtime'=>date('Y-m-d H:i:s'),
                'showstatus'=>"show"
            );
            $mcount = db("home")->insertGetId($homedata);
            if(empty($mcount)){
                Db::rollback();
                wlog($this->log_path,'user_update 添加直播间数据失败:'.$this->user['id']);
                $this->return_json(E_OP_FAIL,'添加直播间数据失败');
            }
            //设置课程二维码
            //插入场景
            $expend = array(
                'type'=>'sub_room',
                'memberid'=>$member['id'],
                'eventid'=>$mcount
            );
            $expendid = db("expend")->insertGetId($expend);
            if(empty($expendid)){
                Db::rollback();
                wlog($this->log_path,'user_update 插入场景数据失败:'.$this->user['id']);
                $this->return_json(E_OP_FAIL,'插入场景数据失败');
            }
            //设置二维码
            $lecture = Factory::create_obj('lecture');
            $b = $lecture->setqrcode($mcount,$expendid,'');
            if(empty($b[0]) ||  empty($b[1])){
                Db::rollback();
                wlog($this->log_path,'user_update 设置二维码失败:'.$this->user['id']);
                $this->return_json(E_OP_FAIL,'设置二维码失败');
            }
        }else{
            $homedata = array(
                'name'=>$member['name']?$member['name']:$member['nickname'],
                'description'=>$member['intro'],
                'avatar_url'=>($member['headimg']==$member['img'])?$member['headimg']:$member['img'],
            );
            $mcount = db('home')->where(['id'=>$liveroom['id']])->update($homedata);
            if(empty($mcount)){
                Db::rollback();
                wlog($this->log_path,'user_update 插入场景数据失败:'.$this->user['id']);
                $this->return_json(E_OP_FAIL,'更新home数据失败');
            }
        }
        Db::commit();
        $this->return_json(OK,$this->user);
    }

    /**
     * 获取个人信息(个人中心)
     */
    public function get_user_info()
    {
        $this->return_json(OK,$this->user);
    }
}
