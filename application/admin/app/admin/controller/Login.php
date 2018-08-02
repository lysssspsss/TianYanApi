<?php
namespace app\admin\controller;
use think\Controller;
use think\Request;
use think\Session;
class Login extends Controller
{
    private $sysConfig ,$cache_model,$siteConfig,$menudata ;
    function _initialize()
    {

    }
    public function login()
    {
        //判断管理员是否登录
        $aid = session('aid');
        if($aid){
            $this->redirect('index/index');
        }
        $this->cache_model=array('Module','Role','Category','Posid','Field','Sys');
        if(empty($this->Sys)){
            foreach($this->cache_model as $r){
                savecache($r);
            }
        }
        return $this->fetch();
    }
    public function action(){
        $admin = db('admin');
        $map['username']=input('username');
        $map['is_open']=1;
        $password=md5(input('password'));
        $admininfo=$admin->where($map)->find();
        if (!$admininfo){
            $this->error('用户名或者密码错误，重新输入');
            exit();
        }else{
            if($password == $admininfo['pwd']){
                //登录后更新数据库，登录IP，登录次数,登录时间
                $data['ip'] = Request::instance()->ip();
                $where['admin_id'] = $admininfo['admin_id'];
                $admin->where($where)->setInc('hits',1);
                $admin->where($where)->update($data);
                Session::set('aid',$admininfo['admin_id']);
                Session::set('username',$admininfo['username']);
                $result['status'] = 1;
                $result['msg'] = '恭喜您，登陆成功!';
                return $result;
            }else{
                $this->error('用户名或者密码错误，重新输入');
                exit();
            }
        }
    }
    //退出登陆
    public function logout(){
        session(null);
        $this->redirect('login/login');
    }
}