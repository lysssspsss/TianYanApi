<?php
namespace app\index\controller;
use think\Input;
use Qiniu\Auth;


/**
 * 工厂模式-生成七牛云接口
 * Class Qiniu
 * @package app\index\controller
 */
class Qiniu extends Base
{
    protected $Access_Key;
    protected $Secret_Key;
    protected static $instance;

    public function __construct()
    {
        parent::__construct();
        /*$this->Access_Key = $Access_Key;
        $this->Secret_Key = $Secret_Key;*/
    }

    public static function getInstance($Access_Key = QINIU_ACCESS_KEY,$Secret_Key = QINIU_SECRET_KEY)
    {
        if (empty(self::$instance))
        {
            self::$instance =  new Auth($Access_Key, $Secret_Key);
        }
        return self::$instance;
    }

}
