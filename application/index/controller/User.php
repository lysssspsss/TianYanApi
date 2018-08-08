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

        //数据验证
        $result = $this->validate(
            [
                'tel'  => $tel,
                'company' => $company,
                'name' => $name,
            ],
            [
                'tel'  => 'require|number|max:11|min:11',
                'company'  => 'chsAlphaNum', //汉字字母数字
                'name'  => 'require|chsAlpha',//汉字字母
                //'code'  => 'number|max:5|min:5'
            ]
        );
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }
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
        $pwd = input('post.pwd');

        //数据验证
        $result = $this->validate(
            [
                'phone'  => $phone,
                'pwd' => $pwd,
            ],
            [
                'phone'  => 'require|number|max:11|min:11',
                'pwd'  => 'require|alphaNum|min:6|max:30',
            ]
        );
        if($result !== true){
            $this->return_json([],false,true,'参数错误',true);
        }
        $where['mobile'] = $phone;
        $user = db('hot_account')->field('uid,password,u_state')->where($where)->find();
        if(empty($user)){
            $this->return_json([],false,true,'该用户尚未注册',true);
        }
        if($user['u_state']!=1){
            $this->return_json([],false,true,'该用户已被禁用',true);
        }
        if(strtoupper(md5($pwd)) != $user['password']){
            $this->return_json([],false,true,'密码错误',true);
        }
        $data['token'] = $this->get_user_token($user['uid']);
        $this->set_login_log($user['uid'],1,1);
        $data['uid'] = $user['uid'];
        $this->return_json($data,true);
    }

}
