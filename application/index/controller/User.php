<?php
namespace app\index\controller;
use app\tools\controller\Smsbao;
use app\index\controller\Index;
use think\Db;
use app\tools\controller\Message;
use Think\Exception;

class User extends Base
{
    private $log_path = APP_PATH.'log/User.log';
    //private $vip_phone = ['13682694631','13128820643','13168088229','18823397801','18823397802','18112345678','17324476831','15524574410','18175994824'];
    private $vip_phone;
    private $vip_code = ['44244','68989'];

    public function __construct()
    {
        parent::__construct();
        $rdname = 'admin_phone';
        $admin_phone = $this->redis->get($rdname);
        if(empty($admin_phone)){
            $admin_phone = db($rdname)->select();
            if(!empty($admin_phone)){
                $this->vip_phone = array_column($admin_phone,'tel');
                $this->redis->set($rdname,json_encode($this->vip_phone));
            }else{
                $this->vip_phone = [];
            }
        }else{
            $this->vip_phone = json_decode($admin_phone,true);
        }
    }

    /**
     * 发送短信接口
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
        //$send = Message::sendSms($phone,$code,ALIYUN_TEMP_CODE.$type,'890',ALIYUN_SIGN_TEST);
        $mb = '';
        switch ($type){
            case 0:$mb = '【天雁商学院】会员信息变更,您的验证码为:'.$code;break;
            case 1:$mb = '【天雁商学院】会员修改密码,您的验证码为:'.$code;break;
            case 2:$mb = '【天雁商学院】会员正在注册,您的验证码为:'.$code;break;
            case 3:$mb = '【天雁商学院】会员登录异常,您的验证码为:'.$code;break;
            case 4:$mb = '【天雁商学院】会员正在登录,您的验证码为:'.$code;break;
            case 5:$mb = '【天雁商学院】会员身份验证,您的验证码为:'.$code;break;
        }
        $send = Smsbao::sendSms($phone,$mb);
        /*if(empty($send)){
            wlog($this->log_path,'send_sms:发送短信返回内容为空:'.$phone.'-'.$code);
            $this->return_json(E_OP_FAIL,'短信发送失败，请检查网络1');
        }
        if($send->Message != 'OK'){
            wlog($this->log_path,'send_sms:'.$phone.'-'.$code.'-'.json_encode($send,JSON_UNESCAPED_UNICODE));
            $this->return_json(E_OP_FAIL,'短信发送失败，请检查网络2');
        }*/
        if($send['msg']!='success'){
            wlog($this->log_path,'send_sms:'.$phone.'-'.$code.'发送失败,'.$send['msg']);
            $this->return_json(E_OP_FAIL,'短信发送失败'.$send['msg']);
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
        $phone_id = input('post.phone_id');
        //数据验证
        $result = $this->validate(
            [
                'tel'  => $tel,
                'company' => $company,
                'name' => $name,
                'code' => $code,
                'phone_id'  => $phone_id,
            ],
            [
                'tel'  => 'require|number|max:11|min:11',
                'company'  => 'chsAlphaNum', //汉字字母数字
                'name'  => 'require|chsAlphaNum',//汉字字母
                'code'  => 'require|number|max:5|min:5',
                'phone_id'  => 'alphaNum',
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

       //游客注册为用户的情况
        $is_repeat = Db::name('member')->field('id')->where(array('tel'=>$tel))->find();
        if(!empty($is_repeat)){
            $this->return_json(E_OP_FAIL,'注册失败,重复注册');
        }
        if(!empty($phone_id)){
            $data['tel'] = $tel;
            $data['company'] = $company;
            $data['name'] = $name;
            $data['nickname'] = $name;
            $memberid = Db::name('member')->where(array('openid'=>$phone_id))->update($data);
        }else{
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
        }
        $return_data_json = json_encode($data,JSON_UNESCAPED_UNICODE);
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
     * 游客自动注册接口
     */
    public function visitor_reg()
    {
        $phone_id = input('post.phone_id');

        //数据验证
        $result = $this->validate(
            [
                'phone_id'  => $phone_id,
            ],
            [
                'phone_id'  => 'require|alphaNum',
            ]
        );
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }

        $member = Db::name('member')->field('id,sumearn')->where(array('openid'=>$phone_id))->find();
        if(empty($member)){
            //$unique = date('YmdHis').mt_rand(10000,99999);
            $data['tel'] = '';
            $data['company'] = '';
            $data['name'] = '天雁学员'.time().mt_rand(10,99);
            $data['nickname'] = $data['name'];
            $data['sex'] = 2;
            $data['headimg'] = DEFAULT_IMG;
            $data['img'] = DEFAULT_IMG;
            $data['openid'] = $phone_id;
            $data['unionid'] = $phone_id;
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
            $memberid = Db::name('member')->insertGetId($data);
            //var_dump($memberid);exit();
            if(empty($memberid)){
                wlog($this->log_path,'visitor_reg:注册失败:插入数据错误'.$phone_id.'-'.$return_data_json);
                $this->return_json(E_OP_FAIL,'注册失败');
            }
            $sumearn = 0;
        }else{
            $memberid = $member['id'];
            $sumearn = $member['sumearn'];
        }

        //生成用户token，记录登录状态与日志
        $this->get_user_redis($memberid);
        //$this->set_login_log($data['uid'],1,$data['in_type']);
        $this->return_json(OK,['phone_id'=>$phone_id,'sumearn'=>$sumearn]);
    }


    /**
     * 登录接口
     */
    public function login()
    {
        $phone = input('post.phone');
        $code = input('post.code');
        if(in_array($phone,$this->vip_phone) && in_array($code,$this->vip_code)){

        }else{
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
            $this->check_code((string)$phone,'4',$code);
        }
        /*$redis_code = $this->redis->hGet(REDIS_YZM_KEY,$phone.'_4');
        if($code != $redis_code){
            //$this->return_json(E_ARGS,'验证码错误');//测试时暂时注释
        }
        $this->redis->hdel(REDIS_YZM_KEY,$phone.'_4');*/
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
        //$error_log_name = APP_PATH.'log/wechat_login_error.log';
        wlog($log_name,'进入微信登录方法'."\n");
        $appid = WECHAT_APPID;
        $secret = WECHAT_APPSECRET;
        $code = input('post.code');
        //数据验证
        $result = $this->validate(
            [
                'code' => $code,
            ],
            [
                'code'  => 'require',
            ]
        );
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }
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
            $this->user['memberid'] = $this->user['id'];
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
            $this->user['memberid'] = $this->user['id'];
            $this->return_json(OK,$this->user);
        }
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        $type = input('get.type');
        $result = $this->validate(['type' => $type,],['type'  => 'require|in:1',]);
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }
        $a = $this->del_user_redis($this->user['id']);
        $this->return_json(OK,['msg'=>"$a"]);
    }

    /**
     * 修改用户信息
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
            'headimg'=>$img,
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
        $liveroom = db('home');
        $liveroom = $liveroom->where(['memberid'=>$member['id']])->find();
        if(empty($liveroom)){
            $lecture = new Lecture();
            $st = $lecture->add_room($member);
            if(!empty($st['msg'])){
                Db::rollback();
                $this->return_json(E_OP_FAIL,$st['msg']);
            }
        }else{
            $homedata = array(
                'name'=>$name,
                'description'=>$member['intro'],
                //'avatar_url'=>($member['headimg']==$member['img'])?$member['headimg']:$member['img'],
                'avatar_url'=>$member['headimg'],
            );
            $mcount = db('home')->where(['id'=>$liveroom['id']])->update($homedata);
           /* if(empty($mcount)){
                Db::rollback();
                wlog($this->log_path,'user_update 插入场景数据失败:'.$liveroom['id']);
                $this->return_json(E_OP_FAIL,'更新home数据失败');
            }*/
        }
        Db::commit();
        $this->return_json(OK,$this->user);
    }



    /**
     * 获取个人信息(个人中心)
     */
    public function get_user_info()
    {
        $this->get_user_redis($this->user['id'],true);
        $verify = db('verify')->field('status')->where('memberid='.$this->user['id'])->order('id','desc')->find();
        if(!empty($verify)){
            if($verify['status'] == 'wait' && $this->user['isauth'] == 'wait'){
                $this->user['isauth'] = 'vering';
            }
        }
        if(empty($this->user['name'])){
            $this->user['name'] = $this->user['nickname'];
        }
        //$data['sumearn'] = Cash::memberEarnings($this->user['id']);
        //$data['can_withdraw'] = $data['sumearn'] - $this->user['useearn'] - $this->user['unpassnum'];
        if($this->source == 'IOS'){
            if($this->user['title']=='lecturer' || $this->user['isauth'] == 'pass'){
                $data = $this->get_yue();
                $data['can_withdraw'] = $this->user['money'];
                //unset($data['sumearn']);
            }else{
                $data['can_withdraw'] = $this->user['money'];
                $data['sumearn'] = 0;
            }
        }else{
            $data = $this->get_yue();
            //unset($data['sumearn']);
        }
        $this->user['sumearn'] = $data['sumearn'];//总收益
        $this->user['can_withdraw'] = $data['can_withdraw'];//余额（可提现金额）
        $this->user['attention_count'] = db('attention')->where(['memberid'=>$this->user['id'],'type'=>1])->count('id');//关注数量（专栏）
        $this->user['collect_count'] = db('ask_comments')->where("acitivity = 2 and action = 3 and memberid=".$this->user['id'])->count('id');//收藏数量（头条）
        $this->return_json(OK,$this->user);
    }

    /**
     * 获取余额
     * @return mixed
     */
    private function get_yue()
    {
        $data['sumearn'] = $this->redis->hget('memberEarnings',$this->user['id']);
        if(empty($data['sumearn'])){
            $data['sumearn'] = Cash::memberEarnings($this->user['id']);
            $this->redis->hset('memberEarnings',$this->user['id'],$data['sumearn']);
        }
        //$data['sumearn'] = $this->user['sumearn'];
        $data['can_withdraw'] = $data['sumearn'] - $this->user['useearn'] - $this->user['unpassnum'];
        $data['sumearn'] =  $this->floor_down($data['sumearn']);
        $data['can_withdraw'] =  $this->floor_down($data['can_withdraw']);
        return $data;
    }


    /**
     * 个人中心-我的余额
     */
    public function get_user_money($type = '')
    {
        $this->get_user_redis($this->user['id'],true);
        if($this->source == 'IOS'){//IOS版和安卓版區別對待
            if($this->user['title']=='lecturer' || $this->user['isauth'] == 'pass'){
                $data = $this->get_yue();
                $data['can_withdraw'] = $this->user['money'];
                unset($data['sumearn']);
            }else{
                $data['can_withdraw'] = $this->user['money'];
            }
        }else{
            $data = $this->get_yue();
            unset($data['sumearn']);
        }
        $data['money_list'] = [6,68,88,208,388,998];
        if($type == 2){
            return $data['can_withdraw'];
        }
        $this->return_json(OK,$data);
    }

    /**
     * 个人中心-获取我的银行卡列表
     */
    public function get_bankcard_list()
    {
        $data = db('bank')->where(['memberid'=>$this->user['id']])->select();
        if(empty($data)){
            $this->return_json(E_OP_FAIL,['msg'=>'结果为空']);
        }
        $this->return_json(OK,$data);
    }

    /**
     * 个人中心-添加银行卡
     */
    public function bankcard_add()
    {
        $data['name'] = input('post.name');//持卡人姓名
        $data['bankcard'] = input('post.bankcard');//银行卡号
        $data['bank'] = input('post.bank');//银行
        $data['type'] = input('post.type');//类型 1储蓄卡 2信用卡
        $data['tel'] = input('post.tel');//手机号

        $result = $this->validate(
            [
                'name' => $data['name'],
                'bankcard'  => $data['bankcard'],
                'bank'  => $data['bank'],
                'type' => $data['type'],
                'tel'  => $data['tel'],
            ],
            [
                'name'  => 'require|chsAlphaNum',
                'bankcard'  => 'require|number',
                'bank'  => 'require|chsAlphaNum',
                'type'  => 'require|in:1,2',
                'tel'  => 'require|number|max:11|min:11',
            ]
        );
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }
        $data['memberid'] = $this->user['id'];
        $a = db('bank')->where(['bankcard'=>$data['bankcard']])->find();
        if(!empty($a)){
            $this->return_json(E_OP_FAIL,'请不要重复添加银行卡');
        }
        $id = db('bank')->insertGetId($data);
        if(empty($id)){
            $this->return_json(E_OP_FAIL,'添加失败');
        }
        $data['id'] = $id;
        $this->return_json(OK,$data);
    }

    /**
     * 个人中心-交易记录
     */
    public function get_user_jy_record()
    {
       /* $this->get_user_redis($this->user['id'],true);

        if($this->user['title']=='lecturer'){
            $data['can_withdraw'] = $this->user['sumearn'] - $this->user['useearn'] - $this->user['unpassnum'];
        }else{
            $data['sumearn'] = Cash::memberEarnings($this->user['id']);
            $data['can_withdraw'] = $data['sumearn'] - $this->user['useearn'] - $this->user['unpassnum'];
            $data['sumearn'] =  $this->floor_down($data['sumearn']);
            $data['can_withdraw'] =  $this->floor_down($data['can_withdraw']);
            unset($data['sumearn']);
        }
        $data['money_list'] = [6,68,88,208,388,998];
        $this->return_json(OK,$data);*/
    }

    /**
     * 加V认证
     */
    public function vip()
    {
        $tel = input('post.tel');
        $wxcode = input('post.wxcode');
        $name = input('post.name');
        $company = input('post.company');
        $intro = input('post.intro');
        $result = $this->validate(
            [
                'tel'  => $tel,
                'wxcode' => $wxcode,
                'name' => $name,
                'company' => $company,
                'intro' => $intro,
            ],
            [
                'tel'  => 'require|number|max:11|min:11',
                'wxcode'  => 'require|alphaDash',//字母数字下划线和减号
                'name'  => 'require|chsAlphaNum',
                'company'  => 'require|chsAlphaNum',
                'intro'  => 'require',
            ]
        );
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }
        $data['tel'] = $tel;
        $data['wxcode'] = $wxcode;
        $data['name'] = $name;
        $data['company'] = $company;
        $data['address'] = '';
        $data['check_time'] = '';
        $data['memberid'] = $this->user['id'];
        $data['apply_time'] = date("Y-m-d H:i");
        $data['status'] = 'wait';
        $verify = db('verify')->where('memberid='.$this->user['id'])->select();
        if(empty($verify)){
            $count = db('verify')->insertGetId($data);
        }else{
            if($verify[count($verify)-1]['status'] == 'sucess'){
                $data['status'] = 'sucess';
                $count = db('verify')->where('id='.$verify[count($verify)-1]['id'])->update($data);
            }else{
                $count = db('verify')->insertGetId($data);
            }
        }
        if(empty($count)){
            $this->return_json(E_OP_FAIL,'数据插入或更新失败');
        }
        $data['intro'] = $intro;
        $data['lastupdate'] = $data['apply_time'];
        unset($data['apply_time'],$data['status'],$data['memberid'],$data['check_time'],$data['address']);
        db('member')->where(['id'=>$this->user['id']])->update($data);
        $this->return_json(OK,['memberid'=>$this->user['id']]);
    }

    /**
     * 个人中心-我的关注列表
     */
    public function attention(){
        $limit = input('get.limit');
        $result = $this->validate(['limit' => $limit,],['limit'  => 'require|number',]);
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }
        $member = $this->user;
        $limit = !empty($limit)?abs($limit):1;
        $count = 10;
        $offset = ($limit-1)*$count;
        $list = db('channel')->alias('h')->join('attention a','h.id=a.roomid')->field('a.roomid as channel_id,h.memberid,h.lecturer,h.name as title,h.cover_url')
            ->where(['a.memberid'=>$member['id'],'a.type'=>1])->limit($offset,$count)->select();
        $count = db('channel')->alias('h')->join('attention a','h.id=a.roomid')->field('a.roomid as channel_id,h.memberid,h.lecturer,h.name as title,h.cover_url')
            ->where(['a.memberid'=>$member['id'],'a.type'=>1])->count();
        if(empty($list)){
            $this->returns('结果为空');
        }
        foreach($list as $key => $value){
            if($value['memberid']==BANZHUREN){
                $where['id'] =  $value['lecturer'];
            }else{
                $where['id'] =  $value['memberid'];
            }
            $member = db('member')->field('name,nickname')->where($where)->find();
            if(empty($member['name'])){
                $member['name'] = $member['nickname'];
            }
            $list[$key]['name'] = $member['name'];
        }
        $data['count'] = $count;
        $data['limit'] = $limit;
        $data['list'] = $list;
        $this->return_json(OK,$data);
    }


    /**
     * 个人中心-我的收益
     */
    public function my_income()
    {
        $type = input('get.type');
        $result = $this->validate(['type' => $type,],['type'  => 'require|in:1,2',]);
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }
        $this->get_user_redis($this->user['id'],true);
        $data['sumearn'] = Cash::memberEarnings($this->user['id']);
        /*if($this->user['sumearn'] != $data['sumearn']){

        }*/
        //$data['sumearn'] = $this->user['sumearn'];
        $data['can_withdraw'] = $data['sumearn'] - $this->user['useearn'] - $this->user['unpassnum'];
        $data['sumearn'] =  $this->floor_down($data['sumearn']);
        $data['can_withdraw'] =  $this->floor_down($data['can_withdraw']);
        $data['memberid'] = $this->user['id'];
        $this->return_json(OK,$data);
    }

    /**
     * 更新用户可用余额
     * @param $data
     */
    private function update_sumearn($data)
    {
        db('member')->where(['id'=>$this->user['id']])->update($data);
    }

    /**
     * 个人中心-收益规则
     */
    public function bill()
    {
        $type = input('get.type');
        $result = $this->validate(['type' => $type,],['type'  => 'require|in:1,2']);
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }
        $shuju = Cash::memberEarnings_array($this->user['id']);
        $data['channel_income'] = round($shuju['channel_pay'],0);//专栏总收益
        $data['channel_jiangshi'] = round($shuju['channel_pay']*0.5,0);//专栏讲师收益
        $data['channel_liuliang'] = round($shuju['channel_pay']*0.3,0);//专栏流量费用
        $data['channel_tuiguang'] = $data['channel_fuwu'] = round($shuju['channel_pay']*0.1,0);//推广费用和服务费用
        $data['jiangshi_income'] = round($shuju['course_pay']+$shuju['course_pay1']+$shuju['course_play']+$shuju['course_play1']+$shuju['popular'],0);//讲师总收益
        $data['lecture_pay'] = round($shuju['course_pay']+$shuju['course_pay1'],0);//课程付费收益
        $data['lecture_reward'] = round($shuju['course_play']+$shuju['course_play1'],0);//课程打赏收益
        $data['lecture_tuiguang'] = round($shuju['popular'],2);//推广收益
        $data['recharge'] = round($shuju['recharge'],2);//充值的余额
        $data['memberid'] = $this->user['id'];
        $this->return_json(OK,$data);
    }


    /**
     * 课程收益明细
     */
    public function lecture_income_detail(){
        $limit = input('get.limit');
        $type = input('get.type');
        $hashkey = 'lecture_income_detail:';
        $result = $this->validate(
            ['limit' => $limit, 'type' => $type,],
            ['limit'  => 'require|number' , 'type'  => 'in:1,2']);
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }
        //$member = $this->user;
        $limit = $limit?$limit:1;
        $count = 10;
        $lecture_data = db('invete')->join('live_course','live_invete.courseid=live_course.id')->field('live_invete.courseid,live_course.name,live_course.sumearns,live_course.playearns,live_course.payearns')
            ->where('beinviteid='.$this->user['id'])->order("live_course.starttime desc")->limit($limit,$count)->select();
        if(!empty($lecture_data)){
            foreach($lecture_data as $k=>$v){
                $pay_lecture = $this->redis->hget($hashkey.'pay_lecture',$v['courseid']);
                if(empty($pay_lecture)){
                    $lecture_data[$k]['pay_lecture'] = db('coursepay')->join('live_orders','live_coursepay.out_trade_no = live_orders.out_trade_no')
                        ->field('live_coursepay.id as coursepay_id,live_coursepay.fee,live_coursepay.addtime,live_coursepay.status,live_orders.body')
                        ->where(['live_coursepay.courseid'=>$v['courseid'],'live_orders.goods_tag'=>'pay_lecture','live_coursepay.status'=>'finish'])->order('live_coursepay.addtime desc')->select();
                    if(!empty($lecture_data[$k]['pay_lecture'])){
                        foreach($lecture_data[$k]['pay_lecture'] as $c => $d) {
                            $lecture_data[$k]['pay_lecture'][$c]['name'] = strstr($d['body'],'支付',true);;
                            preg_match("|《([^^]*?)》|u", $d['body'], $matches);
                            $lecture_data[$k]['pay_lecture'][$c]['title'] = $matches[1].'》';
                            $lecture_data[$k]['pay_lecture'][$c]['money'] = sprintf('%.2f',$d['fee']);
                            unset($lecture_data[$k]['pay_lecture'][$c]['fee']);
                        }
                        $this->redis->hset($hashkey.'pay_lecture',$v['courseid'],json_encode($lecture_data[$k]['pay_lecture'],JSON_UNESCAPED_UNICODE));
                    }
                }else{
                    $lecture_data[$k]['pay_lecture'] = json_decode($pay_lecture,true);
                }
                //$reward = $this->redis->hget($hashkey.'reward',$v['courseid']);//暂时去掉
                if(empty($reward)) {
                   /* $lecture_data[$k]['reward'] = db('coursepay')->join('live_orders', 'live_coursepay.out_trade_no = live_orders.out_trade_no')
                        ->field('live_coursepay.id as coursepay_id,live_coursepay.fee,live_coursepay.addtime,live_coursepay.status,live_orders.body')
                        ->where(['live_coursepay.courseid' => $v['courseid'], 'live_orders.goods_tag' => 'reward', 'live_coursepay.status' => 'finish'])
                        ->order('live_coursepay.addtime desc')->select();*/
                    $lecture_data[$k]['reward'] = db('earns')->join('live_orders', 'live_earns.out_trade_no = live_orders.out_trade_no')
                        ->field('live_earns.id as earns_id,live_earns.fee,live_earns.addtime,live_earns.status,live_orders.body')
                        ->where(['live_earns.lectureid' => $v['courseid'],'live_earns.type' => 'play', 'live_earns.status' => 'finish'])
                        ->order('live_earns.addtime desc')->select();
                    if(!empty($lecture_data[$k]['reward'])){
                        foreach($lecture_data[$k]['reward'] as $a => $b) {
                            $body_arr = explode(' ',$b['body']);
                            $lecture_data[$k]['reward'][$a]['name'] = $body_arr[0];
                            $lecture_data[$k]['reward'][$a]['title'] = $body_arr[1].'老师';
                            $lecture_data[$k]['reward'][$a]['money'] = sprintf('%.2f',$b['fee']);
                            unset($lecture_data[$k]['reward'][$a]['fee']);
                        }
                        //$this->redis->hset($hashkey . 'reward', $v['courseid'], json_encode($lecture_data[$k]['reward'], JSON_UNESCAPED_UNICODE));
                    }
                    //$course_pay_sql = "select * from live_earns n where n.lectureid in (select c.id from live_course c where c.channel_id=" . $v['id'] . ") and n.type='play' and n.status='finish' and n.memberid=" . $this->user;
                    //$course_play_t = M()->query($course_pay_sql);
                }else{
                    $lecture_data[$k]['reward'] = json_decode($reward,true);
                }
            }
        }else{
            $this->return_json(E_OP_FAIL,'暂无收益');
        }
        //dump($lecture_data);exit;
        $this->return_json(OK,$lecture_data);
    }


    /**
     * 专栏收益明细
     */
    public function channel_income_detail(){
        $limit = input('get.limit');
        $result = $this->validate(['limit' => $limit,],['limit'  => 'number']);
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }
        $limit = !empty($limit)?abs($limit):1;
        $count = 10;
        $limit_start = ($limit-1)*$count;
        //$member = $this->user;
        $sql = "select p.fee,p.addtime,m.body from live_channelpay as p inner join live_orders as m on p.out_trade_no=m.out_trade_no and p.status='finish' and p.channelid in (select id from live_channel where lecturer=".$this->user['id']." or memberid=".$this->user['id'].") order by p.channelid desc,p.addtime desc limit $limit_start,$count";
        $lists = db()->query($sql);
        $sql2 = "select COUNT(*) as count from live_channelpay as p inner join live_orders as m on p.out_trade_no=m.out_trade_no and p.status='finish' and p.channelid in (select id from live_channel where lecturer=".$this->user['id']." or memberid=".$this->user['id'].")";
        $allcount = db()->query($sql2);
        foreach($lists as $k => $v) {
            $lists[$k]['name'] = strstr($v['body'],'支付',true);;
            preg_match("|《([^^]*?)》|u", $v['body'], $matches);
            $lists[$k]['title'] = '《'.$matches[1].'》';
            //$lists[$k]['money'] = ltrim(strstr($v['body'],'》'),'》');
            $lists[$k]['money'] = sprintf('%.1f',$v['fee']/100);
            unset($lists[$k]['body'],$lists[$k]['fee']);
        }
        $data['limit'] = $limit;
        $data['count'] = $allcount[0]['count'];
        $data['list'] = $lists;
        $this->return_json(OK,$data);
    }

    /**
     * 提现记录
     */
    public function withdraw_record(){
        $limit = input('get.limit');
        $result = $this->validate(['limit' => $limit,],['limit'  => 'number']);
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }
        $limit = !empty($limit)?abs($limit):1;
        $count = 10;
        $limit_start = ($limit-1)*$count;

        $list = db('takeout')->where('memberid='.$this->user['id'])->order('applytime desc')->limit($limit_start,$count)->select();
        $allcount = db('takeout')->field('count(id) as count')->where('memberid='.$this->user['id'])->select();
        $data['limit'] = $limit;
        $data['count'] = $allcount[0]['count'];
        $data['list'] = $list;
        $this->return_json(OK,$data);
    }

    /**
     * 购买记录
     */
    public function shop_record(){
        $type = input('get.type');
        $result = $this->validate(['type' => $type,],['type'  => 'number']);
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }
        if($type==1){//课程购买记录
            $data['coursepay']  = db('coursepay')->alias('p')->join('live_course c','p.courseid=c.id')->field("c.name,c.coverimg,c.sub_title,c.mode,c.type,p.*")->where("p.status='finish' and p.memberid=".$this->user['id'])->order("p.addtime desc")->select();
        }elseif($type==2){//专栏购买记录
            $data['channelpay'] = db('channelpay')->alias('p')->join('live_channel c','p.channelid=c.id')->field("c.name,c.cover_url,p.*,p.fee/100 as fee")->where("p.status='finish' and p.memberid=".$this->user['id']." and ((unix_timestamp(p.expire)>unix_timestamp(now())) or ( p.expire is null))")->order("p.addtime desc")->select();
            foreach($data['channelpay'] as $k => $v){
                $sub_title = db('course')->field('name')->where(['channel_id'=>$v['channelid']])->order('clicknum','desc')->limit(2)->select();
                $data['channelpay'][$k]['sub_title1'] = empty($sub_title[0]['name'])?'天雁商学院特级讲师':$sub_title[0]['name'];
                $data['channelpay'][$k]['sub_title2'] = empty($sub_title[1]['name'])?'天雁商学院特级讲师':$sub_title[1]['name'];
            }
        }else{
            $data['onlinebookpay']  = db('onlinebookpay')->alias('p')->join('live_onlinebooks c','p.bookid=c.id')->field("c.name,c.cover as cover_url,c.intro as sub_title1,detail as sub_title2,c.type,p.*")->where("p.status='finish' and p.memberid=".$this->user['id'])->order("p.addtime desc")->select();
            foreach($data['onlinebookpay'] as $k => $v){
                //$data['onlinebookpay'][$k]['sub_title1'] = substr($v['sub_title1'],0,10).'..';
                //$data['onlinebookpay'][$k]['sub_title2'] = substr($v['sub_title2'],0,10).'..';
                $data['onlinebookpay'][$k]['fee'] = $v['fee']/100;
            }
        }
        $data['memberid'] = $this->user['id'];
        $this->return_json(OK,$data);
    }

    /**
     * 我的专栏和我的课程
     */
    public function my_channel_and_lecture(){
        $type = input('get.type');
        $result = $this->validate(['type' => $type,],['type'  => 'number']);
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }
        if($type==1){//课程
            //$channel = db('channel')->field('id')->where('memberid='.$this->user['id'].' or '.'lecturer='.$this->user['id'])->select();
            //$cidlist = implode(',',array_column($channel,'channel_id'));
            $data = db('course')->field('id as lecture_id,live_homeid,coverimg,name,sub_title,mode,type,starttime,cost')
                ->where(['isshow'=>'show'])->where(['memberid'=>$this->user['id']])->order('clicknum','desc')->select();
                //->where('name','like', '%'.$jiangshi['name'].'%')
        }else{//专栏
            $data = db('channel')->field('id as channel_id,type,memberid as channel_memberid,cover_url,name as title,roomid,permanent,money,price_list,lecturer,is_pay_only_channel,create_time')->where('memberid='.$this->user['id'].' or '.'lecturer='.$this->user['id'])->select();
            //$data = $channel;
        }
        //$data['memberid'] = $this->user['id'];
        if(empty($data)){
            $this->returns('暂无 课程/专栏');
        }
        $this->return_json(OK,$data);
    }

    /**
     * 处理申请提现--新版
     */
    public function withdraw_business(){
        //$real_name = input('post.real_name');
        $money = input('post.money');
        $bankcard = input('post.bankcard');
        //$wxcode = input('post.wxcode');
        $result = $this->validate(
            [
                'money'  => $money,
                //'real_name' => $real_name,
                'bankcard' => $bankcard,
            ],
            [
                'money'  => 'require|float',
                //'real_name'  => 'require|chsAlphaNum',
                'bankcard'  => 'require|number',
            ]
        );
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }

        $code = "transfers".date("YmdHis") . rand(000000, 999999);
        $bank = db('bank')->where(['memberid'=>$this->user['id'],'bankcard'=>$bankcard])->find();
        if(empty($bank)){
            $this->return_json(E_OP_FAIL,"找不到该银行卡信息！");
        }
        $cardtype = ['1'=>'储蓄卡','2'=>'信用卡'];
       /* if($real_name!=$bank['name']){
            $this->return_json(E_OP_FAIL,"您输入的姓名与所选银行卡姓名不一致！");
        }*/
        $data = array(
            'code'=>$code,
            'memberid'=>$this->user['id'],
            'num'=>$money,
            'applytime'=>date("Y-m-d H:i:s"),
            'status'=>"wait",
            'name'=>$bank['name']."-银行卡提现 | 卡号:".$bank['bankcard']." | 银行:".$bank['bank']." | 银行预留手机号:".$bank['tel']." | 卡类型:".$cardtype[$bank['type']],
            'checktime'=>'',
        );
        $USE = db('member')->field('unpassnum')->find($this->user['id']);
        $datas['unpassnum'] = $money+$USE['unpassnum'];

        Db::startTrans();
        $count = db('takeout')->insertGetId($data);
        if(!$count){
            Db::rollback();
            $this->return_json(E_OP_FAIL,"申请提现失败！");
        }
        //改变可提现收益
        $a = db('member')->where('id='.$this->user['id'])->setField('unpassnum',$datas['unpassnum']);
        if(!$a){
            Db::rollback();
            $this->return_json(E_OP_FAIL,"申请提现失败！");
        }
        Db::commit();
        $this->return_json(OK,['memberid'=>$this->user['id'],'takeout_id'=>$count]);
    }


    /**
     * 处理申请提现--旧版
     */
    private function withdraw_business_jiu(){
        $real_name = input('post.real_name');
        $money = input('post.money');
        $bankcard = input('post.bankcard');
        //$wxcode = input('post.wxcode');
        $result = $this->validate(
            [
                'money'  => $money,
                'real_name' => $real_name,
                'bankcard' => $bankcard,
            ],
            [
                'money'  => 'require|float',
                'real_name'  => 'require|chsAlphaNum',
                'bankcard'  => 'number',
            ]
        );
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }

        $code = "transfers".date("YmdHis") . rand(000000, 999999);
        $data = array(
            'code'=>$code,
            'memberid'=>$this->user['id'],
            'num'=>$money,
            'applytime'=>date("Y-m-d H:i:s"),
            'status'=>"wait",
            'name'=>$real_name,
            'checktime'=>'',
        );
        $USE = db('member')->find($this->user['id']);
        $datas['unpassnum'] = $money+$USE['unpassnum'];

        Db::startTrans();
        $count = db('takeout')->insertGetId($data);
        if(!$count){
            Db::rollback();
            $this->return_json(E_OP_FAIL,"申请提现失败！");
        }
        //改变可提现收益
        $a = db('member')->where('id='.$this->user['id'])->setField('unpassnum',$datas['unpassnum']);
        if(!$a){
            Db::rollback();
            $this->return_json(E_OP_FAIL,"申请提现失败！");
        }
        Db::commit();
        $this->return_json(OK,['memberid'=>$this->user['id'],'takeout_id'=>$count]);
    }

    /**
     * 提现详情
     */
    public function withdraw_detail()
    {
        //$status_arr = ['success','wait','fail','refuse'];
        $takeout_id = input('get.takeout_id');
        $result = $this->validate(['takeout_id' => $takeout_id,],['takeout_id'  => 'require|number']);
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }
        $data = db('takeout')->alias('a')->join('member b','a.memberid = b.id')->field('a.id as takeout_id,a.status,a.applytime,a.num,b.wxcode')->where(['a.id'=>$takeout_id])->find();
        if(empty($data)){
            $this->return_json(E_OP_FAIL,"没有该提现记录！");
        }
        /*$timestramp = strtotime($data['applytime']);
        $time = date('m-d',$timestramp);
        $hour = date('H',$timestramp);
        $day = date('w',$timestramp);//还需完善*/
        $data['finish_time'] = date('m-d',strtotime($data['applytime'])+86400);
        $data['bankcard'] = '用户默认银行卡';
        unset($data['applytime']);
        $this->return_json(OK,$data);
    }

    /**
     * 获取收藏列表
     */
    public function get_collect_list()
    {
        $id_list = db('ask_comments')->field('questionid')->where("acitivity = 2 and action = 3 and memberid=".$this->user['id'])->select();//收藏
        if(empty($id_list)){
            $this->returns('暂无收藏');
        }
        $id_list = array_column($id_list,'questionid');
        $id_str = implode(',',$id_list);
        $arr = db('frontpage')->field('id,title,descip,news_date,url')->where("isshow='show' and title != '' and id in ($id_str)")->order('orderby','desc')->select();
        if(empty($arr)){
            $this->returns('暂无收藏');
        }
        $list = Index::build_toutiao_list($arr);
        $this->return_json(OK,$list);
    }

    /**
     * 我的客服
     */
    public function my_kefu()
    {
        $data['qrcode'] = OSS_URL.'/Public/img/ty_kf.png';
        $data['phone'] = '13925227539';
        $this->return_json(OK,$data);
    }

    /**
     * 意见反馈
     */
    public function feedback()
    {
        $feedback = input('post.feedback');
        $phone = input('post.phone');
        $name = input('post.name');
        $result = $this->validate(
            [
                'feedback'  => $feedback,
                'phone' => $phone,
                'name' => $name,
            ],
            [
                'feedback'  => 'require|chsAlphaNum',
                'phone'  => 'require|min:11|max:11',
                'name'  => 'require|chsAlphaNum',
            ]
        );
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }
        $data['feedback'] = $feedback;
        $data['phone'] = $phone;
        $data['name'] = $name;
        $data['addtime'] = date('Y-m-d H:i:s');
        $a = db('feedback')->insertGetId($data);
        if($a){
            $this->return_json(OK,['msg'=>'提交成功']);
        }else{
            $this->return_json(E_OP_FAIL,'提交失败');
        }
    }

    /**
     * floor向下取整
     * @param $num
     * @return float|int
     */
    private function floor_down($num)
    {
        $a = floor($num*100)/100;
        return $a ;
    }



}
