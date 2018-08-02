<?php
namespace app\index\controller;
use think\Input;
use think\Db;
use think\Request;
use think\Controller;
class Common extends Controller{
    protected $pagesize;
    public function _initialize(){
        $sys = F('Sys');
        $this->assign('sys',$sys);
        //获取控制方法
        $request = Request::instance();
        $action = $request->action();
        $controller = $request->controller();
        $this->assign('action',($action));
        $this->assign('controller',strtolower($controller));
        define('MODULE_NAME',strtolower($controller));
        define('ACTION_NAME',strtolower($action));
        //主导航
        $category = db('category');
        $thisCat = $category->where('id',input('catId'))->find();
        $this->assign('title',$thisCat['catname']);
        $this->assign('keywords',$thisCat['keywords']);
        $this->assign('description',$thisCat['description']);
        define('DBNAME',strtolower($thisCat['module']));
        $this->pagesize = $thisCat['pagesize']>0 ? $thisCat['pagesize'] : '';
        $cate = $category->where('parentid',0)->order('listorder')->select();
        foreach ($cate as $k=>$v){
            if($v['module']=='page'){
                $cate[$k]['first'] = 'index';
                $cate[$k]['firstId'] =$v['id'];
            }else{
                $sub = $category->where('parentid',$v['id'])->order('listorder')->select();
                if($sub){
                    $cate[$k]['first'] = 'index';
                    $cate[$k]['firstId'] = $sub['0']['id'];
                    $cate[$k]['sub'] =   $sub;
                }else{
                    $cate[$k]['first'] = 'index';
                    $cate[$k]['firstId'] =$v['id'];
                }
            }
        }
        $this->assign('category',$cate);

        //广告
        $adList = db('ad')->where(['type_id'=>1,'open'=>1])->order('sort asc')->limit('4')->select();
        $this->assign('adList', $adList);
        //友情链接
		$linkList = db('link')->order('sort asc')->select();
		$this->assign('linkList', $linkList);
		//碎片
		$contact = db('debris')->where('id',3)->find();
		$this->assign('contact', $contact);

    }
    public function _empty(){
        return $this->error('空操作，返回上次访问页面中...');
    }
}