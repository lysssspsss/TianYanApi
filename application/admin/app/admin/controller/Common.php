<?php
namespace app\admin\controller;

use think\Request;
use think\Db;
use think\Session;
use think\Controller;
class Common extends Controller
{
    protected   $mod,$sys , $nav , $menudata , $cache_model,$categorys,$module,$moduleid;
    public function _initialize()
    {
        //判断管理员是否登录
        if (!session('aid')) {
            $this->redirect('Login/login');
        }
        $this->sys = F('Sys');
        $this->categorys = F('Category');
        $this->module = F('Module');
        $this->mod = F('Mod');
        $this->cache_model=array('Module','Category','Posid','Field','Sys');
        if(empty($this->sys)){
            foreach($this->cache_model as $r){
                savecache($r);
            }
        }
        $se = input('se');
        if($se){
            session('se',$se);
        }
        $adminId = session('aid');
        $request = Request::instance();
        $controllerName = $request->controller();
        $actionName = $request->action();
        define('MODULE_NAME',strtolower($controllerName));
        define('ACTION_NAME',strtolower($actionName));
        $this->moduleid = $this->mod[MODULE_NAME];
        $this->m = $this->module[$this->moduleid];
        $action = $controllerName.'/'.$actionName;
        $adminInfo=Db::table('clt_admin')->alias('a')
            ->join(config('database.prefix').'auth_group ag','a.group_id = ag.group_id','left')
            ->where('a.admin_id',$adminId)
            ->find();
        $adminRule = explode(',',$adminInfo['rules'] );
        if($adminId!=1){
            $actionId = db('auth_rule')->where('name',$action)->value('id');
            if(!in_array($actionId,$adminRule)){
                $this->error('您没有次操作权限',0,0);
            }
        }
        //导航
        $auth_rule = db('auth_rule');
        $nav = $auth_rule->field('id,name,title,css')->where('pid=0 AND menustatus=1')->order('sort')->select();
        foreach ($nav as $k=>$v){
            if(in_array($v['id'],$adminRule)){
                $nav[$k]['sub'] = $auth_rule->where('pid= '.$v["id"].' and menustatus=1')->select();
                foreach ($nav[$k]['sub'] as $key=>$val){
                    if(!in_array($val['id'],$adminRule)){
                        unset($nav[$k]['sub'][$key]);
                    }
                }
            }else{
                unset($nav[$k]);
            }
        }
        $this->assign('nav', $nav);
        $headNav = $auth_rule->where(array('pid'=>Session::get('se'),'menustatus'=>1))->order('sort')->select();
        foreach ($headNav as $i => $j) {
            if (!in_array($j['id'], $adminRule)) {
                unset($headNav[$i]);
            }
        }
        $this->assign('headNav', $headNav);
        $this->assign('sysname', $action);
        $this->assign('controllerName', $controllerName);
        $this->assign('actionName', $actionName);
    }

    //清除缓存
    public function clear(){
        $R = RUNTIME_PATH;
        if ($this->_deleteDir($R)) {
            $result['info'] = '清除缓存成功!';
            $result['status'] = 1;
        } else {
            $result['info'] = '清除缓存失败!';
            $result['status'] = 0;
        }
        $result['url'] = url('Index/index');
        return $result;
    }
    //空操作
    public function _empty(){
        return $this->error('空操作，返回上次访问页面中...');
    }
    private function _deleteDir($R)
    {
        $handle = opendir($R);
        while (($item = readdir($handle)) !== false) {
            if ($item != '.' and $item != '..') {
                if (is_dir($R . '/' . $item)) {
                    $this->_deleteDir($R . '/' . $item);
                } else {
                    if (!unlink($R . '/' . $item))
                        die('error!');
                }
            }
        }
        closedir($handle);
        return rmdir($R);
    }
}
