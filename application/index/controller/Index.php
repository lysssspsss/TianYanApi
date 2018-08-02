<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use think\Input;
use think\Db;
use think\Session;
use think\Validate;
use Qiniu\Auth;


class Index extends Base
{
    public function __construct()
    {
        parent::__construct();
    }


    public function index()
    {
        $a = get_auth_headers();
        //wlog(APP_PATH.'log/test.log',json_encode($a));//日誌測試
        //$auth = new Auth(QINIU_ACCESS_KEY, QINIU_SECRET_KEY);//七牛云测试
        //$auth2 = Qiniu::getInstance(); var_dump($auth2);exit;//七牛云测试2
        //$aa = Message::sendSms('13682694631','12345'); var_dump($aa);exit;//短信测试
        $this->return_json($a,true,true);
        //exit;
    }

    public function aaa()
    {
        echo '123';
    }





}
