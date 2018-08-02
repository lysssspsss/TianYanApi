<?php
namespace app\index\controller;
use think\Db;
use think\Request;
use clt\Form;
class Error extends Common{
    protected  $dao,$fields;
    public function _initialize()
    {
        parent::_initialize();

        $this->dao = db(DBNAME);
    }
    public function index(){
        if(DBNAME=='page'){
            $info = $this->dao->where('id',input('catId'))->find();
            $this->assign('info',$info);
            $template = $info['template'] ? $info['template'] : DBNAME.'-show';
            return $this->fetch($template);
        }else{
            $pos = $this->dao->where(array('catid'=>input('catId'),'posid'=>3))->limit('1')->order('listorder desc,updatetime desc')->find();
            if($pos){

                $map['id'] = array('neq',$pos['id']);
            }
            $this->assign('pos',$pos);
            $map['catid'] = input('catId');
            $list = $this->dao->where($map)->order('listorder desc,updatetime desc')->paginate($this->pagesize);
			$cattemplate = db('category')->where('id',input('catId'))->value('template_list');
			$template =$cattemplate ? $cattemplate : DBNAME.'-list';
            // 获取分页显示
            $page = $list->render();

            $this->assign('list',$list);
            $this->assign('page',$page);
            return $this->fetch($template);
        }
    }
    public function info(){
        $this->dao->where('id',input('id'))->setInc('hits');
        $info = $this->dao->where('id',input('id'))->find();

        if(DBNAME=='picture'){
            $pics = explode(':::',$info['pics']);
            foreach ($pics as $k=>$v){
                $info['pic'][$k] = explode('|',$v);
            }
        }
        $this->assign('info',$info);

        //上一篇
        $front=$this->dao->where(array('id'=>array('lt',input('id')),'catid'=>$info['catid']))->order('id desc')->limit('1')->find();
        if(!$front){
            $front['title'] = '没有了';
            $front['url'] = '#';
        }else{
            $front['url'] = url('info',array('id'=>$front['id'],'catId'=>$front['catid']));
        }
        $this->assign('front',$front);
        //下一篇
        $after=$this->dao->where(array('id'=>array('gt',input('id')),'catid'=>$info['catid']))->order('id asc')->limit('1')->find();
        if(!$after){
            $after['title'] = '没有了';
            $after['url'] = '#';
        }else{
            $after['url'] = url('info',array('id'=>$after['id'],'catId'=>$after['catid']));
        }
        $this->assign('after',$after);
        if($info['template']){
			$template = $info['template'];
		}else{
			$cattemplate = db('category')->where('id',$info['catid'])->value('template_show');
			if($cattemplate){
				$template = $cattemplate;
			}else{
				$template = DBNAME.'-show';
			}
		}
        return $this->fetch($template);
    }
}