<?php
namespace app\index\controller;
use think\Controller;
use think\Input;
use think\Db;
use think\Request;
class Index extends Common{
    public function _initialize(){
        parent::_initialize();
    }
    public function index(){
        $this->assign('title','首页');
        //公共
        $this->assign('action','home');
        //公司简介
        $about = db('page')->where('id',8)->find();
        $this->assign('about',$about);

        //案例展示
        $picture = db('picture')->where('thumb','neq','')->order('listorder asc,createtime desc')->limit('4')->select();
        $this->assign('picture',$picture);

        return $this->fetch();
    }
}