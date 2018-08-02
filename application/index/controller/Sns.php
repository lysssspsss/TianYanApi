<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use think\Input;
use think\Db;
use think\Session;
use think\Validate;
use Qiniu\Auth;


class Sns extends Base
{
    public function __construct()
    {
        parent::__construct();
    }


}
