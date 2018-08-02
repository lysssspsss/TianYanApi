<?php
namespace app\admin\controller;
use think\Db;
use think\Request;
use clt\Leftnav;
use app\admin\model\Sys as SysModel;
class Sys extends Common
{
    /********************************数据库还原/备份*******************************/
    protected $db = '', $datadir =  './public/Data/';
    function _initialize(){
        parent::_initialize();
        $db=db('');
        $this->db =   db::connect();
    }

    public function database(){
        $dbtables = $this->db->query("SHOW TABLE STATUS LIKE '".config('prefix')."%'");
        $total = 0;
        foreach ($dbtables as $k => $v) {
            $dbtables[$k]['size'] = format_bytes($v['Data_length'] + $v['Index_length']);
            $total += $v['Data_length'] + $v['Index_length'];
        }
        $this->assign('dataList', $dbtables);
        $this->assign('total', format_bytes($total));
        $this->assign('tableNum', count($dbtables));
        return $this->fetch();
    }
    //优化
    public function optimize() {
        $batchFlag = input('param.batchFlag', 0, 'intval');
        //批量删除
        if ($batchFlag) {
            $table = input('key', array());
        }else {
            $table[] = input('tablename' , '');
        }

        if (empty($table)) {
            $result['msg'] = '请选择要优化的表!';
            $result['status'] = 0;
            return $result;
        }

        $strTable = implode(',', $table);
        if (!DB::query("OPTIMIZE TABLE {$strTable} ")) {
            $strTable = '';
        }
        $result['msg'] = '优化表成功!';
        $result['url'] = url('database');
        $result['status'] = 1;
        return $result;
    }
    //修复
    public function repair() {
        $batchFlag = input('param.batchFlag', 0, 'intval');
        //批量删除
        if ($batchFlag) {
            $table = input('key', array());
        }else {
            $table[] = input('tablename' , '');
        }

        if (empty($table)) {
            $result['msg'] = '请选择要修复的表!';
            $result['status'] = 0;
            return $result;
        }

        $strTable = implode(',', $table);
        if (!DB::query("REPAIR TABLE {$strTable} ")) {
            $strTable = '';
        }
        $result['msg'] = '修复表成功!';
        $result['url'] = url('database');
        $result['status'] = 1;
        return $result;
    }
    public function backup(){
        $puttables = input('post.tables/a');
        if(empty($puttables)) {
            $dataList = $this->db->query("SHOW TABLE STATUS LIKE '".config('prefix')."%'");
            foreach ($dataList as $row){
                $table[]= $row['Name'];
            }
        }else{
            $table=input('tables/a');
        }
        $sql = "-- CLTPHP SQL Backup\n-- Time:".toDate(time())."\n-- http://www.cltphp.com \n\n";
        foreach($table as $key=>$table) {
            $sql .= "--\n-- 表的结构 `$table`\n-- \n";
            $sql .= "DROP TABLE IF EXISTS `$table`;\n";
            $info = $this->db->query("SHOW CREATE TABLE  $table");
            $sql .= str_replace(array('USING BTREE','ROW_FORMAT=DYNAMIC'),'',$info[0]['Create Table']).";\n";
            $result = $this->db->query("SELECT * FROM $table");
            if($result)$sql .= "\n-- \n-- 导出`$table`表中的数据 `$table`\n--\n";
            foreach($result as $key=>$val) {
                foreach ($val as $k=>$field){
                    if(is_string($field)) {
                        $val[$k] = '\''. $this->db->escape_string($field).'\'';
                    }elseif($field==0){
                        $val[$k] = 0;
                    } elseif(empty($field)){
                        $val[$k] = 'NULL';
                    }
                }
                $sql .= "INSERT INTO `$table` VALUES (".implode(',', $val).");\n";
            }
        }

        $filename = empty($fileName)? date('YmdH').'_'.rand_string(10) : $fileName;
        $r= file_put_contents($this->datadir . $filename.'.sql', trim($sql));
        exit(json_encode(array('status'=>1,'msg'=>"成功备份数据库",'url'=>url('restore'))));
    }


    public function restore(){
        $size = 0;
        $pattern = "*.sql";
        $filelist = glob($this->datadir.$pattern);
        $fileArray = array();
        foreach ($filelist  as $i => $file) {
            //只读取文件
            if (is_file($file)) {
                $_size = filesize($file);
                $size += $_size;
                $name = basename($file);
                $pre = substr($name, 0, strrpos($name, '_'));
                $number = str_replace(array($pre. '_', '.sql'), array('', ''), $name);
                $fileArray[] = array(
                    'name' => $name,
                    'pre' => $pre,
                    'time' => filemtime($file),
                    'size' => $_size,
                    'number' => $number,
                );
            }
        }

        if(empty($fileArray)) $fileArray = array();
        krsort($fileArray); //按备份时间倒序排列
        $this->assign('vlist', $fileArray);
        $this->assign('total', format_bytes($size));
        $this->assign('filenum', count($fileArray));
        return $this->fetch();
    }
    //执行还原数据库操作
    public function restoreData() {

        header('Content-Type: text/html; charset=UTF-8');
        $filename = input('sqlfilepre');
        $file = $this->datadir.$filename;

        //读取数据文件
        $sqldata = file_get_contents($file);
        $sqlFormat = $this->sql_split($sqldata,config('prefix'));

        foreach ((array)$sqlFormat as $sql){
            $sql = trim($sql);
            if (strstr($sql, 'CREATE TABLE')){
                preg_match('/CREATE TABLE `([^ ]*)`/', $sql, $matches);
                $ret = $this->excuteQuery($sql);
            }else{
                $ret =$this->excuteQuery($sql);
            }
        }
        $result['msg'] = '数据库还原成功!';
        $result['url'] = url('sys/database');
        $result['status'] = 1;
        return $result;
    }
    public function excuteQuery($sql='')
    {
        if(empty($sql)) {$this->error('空表');}
        $queryType = 'INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|LOAD DATA|SELECT .* INTO|COPY|ALTER|GRANT|TRUNCATE|REVOKE|LOCK|UNLOCK';
        if (preg_match('/^\s*"?(' . $queryType . ')\s+/i', $sql)) {
            $data['result'] = $this->db->execute($sql);
            $data['type'] = 'execute';
        }else {
            $data['result'] = $this->db->query($sql);
            $data['type'] = 'query';
        }
        $data['dberror'] = $this->db->getError();
        return $data;
    }
    function  sql_split($sql,$tablepre) {
        if($tablepre != "cltphp_") $sql = str_replace("cltphp_", $tablepre, $sql);
        //$sql = preg_replace("/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/", "ENGINE=\\1 DEFAULT CHARSET=utf8",$sql);

        if($r_tablepre != $s_tablepre) $sql = str_replace($s_tablepre, $r_tablepre, $sql);
        $sql = str_replace("\r", "\n", $sql);
        $ret = array();
        $num = 0;
        $queriesarray = explode(";\n", trim($sql));
        unset($sql);
        foreach($queriesarray as $query)
        {
            $ret[$num] = '';
            $queries = explode("\n", trim($query));
            $queries = array_filter($queries);
            foreach($queries as $query)
            {
                $str1 = substr($query, 0, 1);
                if($str1 != '#' && $str1 != '-') $ret[$num] .= $query;
            }
            $num++;
        }
        return $ret;
    }
    //下载
    public function downFile() {
        $file = $this->request->param('file');
        $type = $this->request->param('type');
        if (empty($file) || empty($type) || !in_array($type, array("zip", "sql"))) {
            $this->error("下载地址不存在");
        }
        $path = array("zip" => $this->datadir."zipdata/", "sql" => $this->datadir);
        $filePath = $path[$type] . $file;
        if (!file_exists($filePath)) {
            $this->error("该文件不存在，可能是被删除");
        }
        $filename = basename($filePath);
        header("Content-type: application/octet-stream");
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header("Content-Length: " . filesize($filePath));
        readfile($filePath);
    }
    //删除sql文件
    public function delSqlFiles() {
        $batchFlag = input('param.batchFlag', 0, 'intval');
        //批量删除
        if ($batchFlag) {
            $files = input('key', array());
        }else {
            $files[] = input('sqlfilename' , '');
        }
        if (empty($files)) {
            $result['msg'] = '请选择要删除的sql文件!';
            $result['status'] = 0;
            return $result;
        }

        foreach ($files as $file) {
            $a = unlink($this->datadir.'/' . $file);
        }
        if($a){
            $result['msg'] = '删除成功!';
            $result['url'] = url('sys/restore');
            $result['status'] = 1;
            return $result;
        }else{
            $result['msg'] = '删除失败!';
            $result['status'] = 0;
            return $result;
        }
    }
    /********************************站点管理*******************************/
    //站点设置
    public function sys($sys_id=1){
		$sys = SysModel::get($sys_id);
        $this->assign('sys',$sys);
        return $this->fetch();
    }
    //保存站点设置
    public function runsys($sys_id=1){
		$sys = SysModel::get($sys_id);
		$datas = input('post.');
		if($sys->allowField(true)->validate(true)->save($datas)) {
			$result['info'] = '站点设置保存成功!';
			$result['url'] = url('sys');
			$result['status'] = 1;
		} else {
			$result['info'] = $sys->getError();
			$result['status'] = 0;
		}
		return $result;
    }
    //微信设置
    public function wesys(){
        $sys=db('sys')->where('sys_id=1')->find();
        $this->assign('sys',$sys);
        return $this->fetch();
    }
    //保存微信设置
    public function addwei(){
        $sys=db('sys');
        $sl_data=array(
            'sys_id'=>input('post.sys_id'),
            'wesys_name'=>input('post.wesys_name'),
            'wesys_id'=>input('post.wesys_id'),
            'wesys_number'=>input('post.wesys_number'),
            'wesys_appid'=>input('post.wesys_appid'),
            'wesys_appsecret'=>input('post.wesys_appsecret'),
            'wesys_type'=>input('post.wesys_type'),
        );
        $sys->update($sl_data);
        $result['status'] = 1;
        $result['info'] = '微信设置保存成功!';
        $result['url'] = url('wesys');
        return $result;

    }
    /*-----------------------管理员管理----------------------*/
    //管理员列表
    public function adminList(){
        $val=input('val');
        $url['val'] = $val;
        $this->assign('testval',$val);
        $map='';
        if($val){
            $map['a.username|a.email|a.tel']= array('like',"%".$val."%");
        }
        if (session('aid')!=1){
            $map['a.admin_id']=session('aid');
        }

        $adminList=Db::table('clt_admin')->alias('a')
            ->join('clt_auth_group ag','a.group_id = ag.group_id','left')
            ->field('a.*,ag.title')
            ->where($map)
            ->paginate(config('pageSize'));
        $adminList->appends($url);
        // 模板变量赋值
        $page = $adminList->render();
        $this->assign('page', $page);
        $this->assign('admin_list',$adminList);
        return $this->fetch();
    }
    public function adminAdd(){
        $auth_group=db('auth_group')->select();
        $this->assign('auth_group',$auth_group);
        return $this->fetch();
    }
    public function adminInsert(){
        $admin = db('admin');
        $username = input('post.username');
        $check_user = $admin->where(array('username'=>$username))->find();
        if ($check_user) {
            $result['status'] = 0;
            $result['info'] = '用户已存在，请重新输入用户名!';
            return $result;
            exit;
        }
        $request = Request::instance();
        $data = array(
            'username' => $username,
            'pwd' => input('post.pwd', '', 'md5'),
            'email' => input('post.email'),
            'tel' => input('post.tel'),
            'open' => input('post.open'),
            'ip' => $request->ip(),
            'addtime' => time(),
            'group_id' => input('post.group_id')
        );
        $admin->insert($data);
        $result['status'] = 1;
        $result['info'] = '管理员添加成功!';
        $result['url'] = url('adminList');
        return $result;
    }
    //删除管理员
    public function adminDel(){
        $admin_id=input('get.admin_id');
        if (session('aid')==1){
            if (empty($admin_id)){
                $result['status'] = 0;
                $result['info'] = '用户ID不存在!';
                $result['url'] = url('adminList');
                exit;
            }
            db('admin')->where('admin_id='.$admin_id)->delete();
            $this->redirect('adminList');
        }
    }
    //修改管理员状态
    public function adminState(){
        $id=input('post.id');
        if (empty($id)){
            $result['status'] = 0;
            $result['info'] = '用户ID不存在!';
            $result['url'] = url('adminList');
            exit;
        }
        $status=db('admin')->where('admin_id='.$id)->value('is_open');//判断当前状态情况
        if($status==1){
            $data['is_open'] = 0;
            db('admin')->where('admin_id='.$id)->update($data);
            $result['status'] = 1;
            $result['info'] = '状态禁止';
        }else{
            $data['is_open'] = 1;
            db('admin')->where('admin_id='.$id)->update($data);
            $result['status'] = 1;
            $result['info'] = '状态开启';
        }
        return $result;
    }
    //更新管理员信息
    public function adminEdit(){
        $auth_group = db('auth_group')->select();
        echo input('get.admin_id');
        $info = db('admin')->where('admin_id='.input('admin_id'))->find();
        $this->assign('info', $info);
        $this->assign('auth_group', $auth_group);
        return $this->fetch();
    }
    public function adminUpdate(){
        $admin=db('admin');
        $pwd=input('post.pwd');
        $map['admin_id'] = array('neq',input('post.admin_id'));
        $where['admin_id'] = input('post.admin_id');
        if(input('post.username')){
            $map['username'] = input('post.username');
            $check_user = $admin->where($map)->find();
            if ($check_user) {
                $result['status'] = 0;
                $result['info'] = '用户已存在，请重新输入用户名!';
                exit;
            }
        }

        if ($pwd){
            $admindata['pwd']=input('post.pwd','','md5');
        }
        if(input('post.username')){
            $admindata['username']=input('post.username');
            $admindata['group_id']=input('post.group_id');
        }
        $admindata['email']=input('post.email');
        $admindata['tel']=input('post.tel');

        $admindata['open']=input('post.open');
        $admin->where($where)->update($admindata);
        $result['status'] = 1;
        $result['info'] = '管理员修改成功!';
        $result['url'] = url('adminList');
        return $result;
    }
    /*-----------------------用户组管理----------------------*/
    //用户组管理
    public function adminGroup(){
        $list=db('auth_group')->select();
        $this->assign('list',$list);
        return $this->fetch();
    }
    //删除管理员分组
    public function groupDel(){
        db('auth_group')->where('group_id='.input('id'))->delete();
        $this->redirect('adminGroup');
    }
    //修改分组状态
    public function groupState(){
        $map['group_id']=input('post.id');
        $status=db('auth_group')->where($map)->value('status');//判断当前状态情况
        if($status==1){
            db('auth_group')->where($map)->setField('status',0);
            $result['info'] = '状态禁止';
        }else{
            db('auth_group')->where($map)->setField('status',1);
            $result['info'] = '状态开启';
        }
        $result['status'] = 1;
        return $result;
    }
    //添加分组
    public function groupAdd(){
        return $this->fetch();
    }
    public function groupInsert(){
        $auth_group=db('auth_group');
        $data=array(
            'title'=>input('post.title'),
            'status'=>input('post.status'),
            'addtime'=>time(),
        );
        $auth_group->insert($data);
        $result['info'] = '用户组添加成功!';
        $result['url'] = url('adminGroup');
        $result['status'] = 1;
        return $result;

    }
    //修改分组
    public function groupEdit(){
        $id = input('id');
        $group=db('auth_group')->where(array('group_id'=>$id))->find();
        $this->assign('group',$group);
        return $this->fetch();
    }
    public function groupUpdate(){
        $auth_group=db('auth_group');
        $data=array(
            'title'=>input('post.title'),
            'status'=>input('post.status')
        );
        $map['group_id'] = input('post.id');
        $auth_group->where($map)->update($data);
        $result['info'] = '用户组修改成功!';
        $result['url'] = url('adminGroup');
        $result['status'] = 1;
        return $result;
    }
    //分组配置规则
    public function groupAccess(){
        $admin_group=db('auth_group')->where(array('group_id'=>input('id')))->find();
        $authRule = db('auth_rule');
        $data = $authRule->field('id,name,title')->where(array('pid'=>0,'authopen'=>0))->order('sort')->select();
        foreach ($data as $k=>$v){
            $data[$k]['sub'] = $authRule->field('id,name,title')->where(array('pid'=>$v['id'],'authopen'=>0))->order('sort')->select();
            foreach ($data[$k]['sub'] as $kk=>$vv){
                $data[$k]['sub'][$kk]['sub'] = $authRule->field('id,name,title')->where(array('pid'=>$vv['id'],'authopen'=>0))->order('sort')->select();
                foreach ($data[$k]['sub'][$kk]['sub'] as $kkk=>$vvv){
                    $data[$k]['sub'][$kk]['sub'][$kkk]['sub'] =$authRule->field('id,name,title')->where(array('pid'=>$vvv['id'],'authopen'=>0))->order('sort')->select();
                }
            }
        }
        $this->assign('admin_group',$admin_group);	// 顶级
        $this->assign('datab',$data);	// 顶级
        return $this->fetch();
    }
    public function groupSetaccess(){
        $authGroup = db('auth_group');
        if(empty($_POST['new_rules'])){
            $this->error('请选择权限！',0,0);
        }
        $new_rules = $_POST['new_rules'];
        $imp_rules = implode(',', $new_rules).',';
        $map['group_id'] = input('id');
        $sldata=array(
            'rules'=>$imp_rules
        );
        if($authGroup->where($map)->update($sldata)){
            $result['info'] = '权限配置成功!';
            $result['url'] = url('groupAccess',array('id'=>input('id')));
            $result['status'] = 1;
            return $result;
        }else{
            $this->error('配置没有改变，无需保存',url('groupAccess',array('id'=>input('id'))),0);
        }
    }

    /********************************权限管理*******************************/
    public function adminRule(){
        $nav = new Leftnav();
        $admin_rule=db('auth_rule')->order('sort asc')->select();
        $arr = $nav->rule($admin_rule);
        $this->assign('admin_rule',$arr);//权限列表
        //dump($arr);
        return $this->fetch();
    }
    public function ruleAdd(){
        $admin_rule=db('auth_rule');
        $data=array(
            'name'=>input('post.name'),
            'title'=>input('post.title'),
            'status'=>1,
            'menustatus'=>input('post.status'),
            'sort'=>input('post.sort'),
            'addtime'=>time(),
            'pid'=>input('post.pid'),
            'css'=>input('post.css'),
        );
        $admin_rule->insert($data);
        $result['info'] = '权限添加成功!';
        $result['url'] = url('adminRule');
        $result['status'] = 1;
        return $result;
    }
    public function ruleorder(){
        $auth_rule=db('auth_rule');
        foreach ($_POST as $id => $sort){
            $auth_rule->where(array('id' => $id ))->setField('sort' , $sort);
        }
        $result['info'] = '排序更新成功!';
        $result['url'] = url('adminRule');
        $result['status'] = 1;
        return $result;
    }
    public function ruleState(){
        $id=input('post.id');
        $statusone=db('auth_rule')->where(array('id'=>$id))->value('menustatus');//判断当前状态情况
        if($statusone==1){
            $statedata = array('menustatus'=>0);
            db('auth_rule')->where(array('id'=>$id))->setField($statedata);
            $result['info'] = '状态禁止';
            $result['status'] = 1;
        }else{
            $statedata = array('menustatus'=>1);
            db('auth_rule')->where(array('id'=>$id))->setField($statedata);
            $result['info'] = '状态开启';
            $result['status'] = 1;
        }
        return $result;

    }
    public function ruleTz(){
        $id=input('post.id');
        $statusone=db('auth_rule')->where(array('id'=>$id))->value('authopen');//判断当前状态情况
        if($statusone==1){
            $statedata = array('authopen'=>0);
            db('auth_rule')->where(array('id'=>$id))->setField($statedata);
            $result['info'] = '需要验证';
            $result['status'] = 1;
        }else{
            $statedata = array('authopen'=>1);
            db('auth_rule')->where(array('id'=>$id))->setField($statedata);
            $result['info'] = '无需验证';
            $result['status'] = 1;
        }
        return $result;
    }

    public function ruleDel(){
        db('auth_rule')->where(array('id'=>input('id')))->delete();
        $this->redirect('adminRule');
    }
    public function ruleEdit(){
        $admin_rule=db('auth_rule')->where(array('id'=>input('id')))->find();
        $this->assign('rule',$admin_rule);
        return $this->fetch();
    }
    public function ruleUpdate(){
        $admin_rule=db('auth_rule');
        $map['id'] = input('post.id');
        $data=array(
            'name'=>input('post.name'),
            'title'=>input('post.title'),
            'status'=>1,
            'menustatus'=>input('post.status'),
            'css'=>input('post.css'),
            'sort'=>input('post.sort')
        );
        $admin_rule->where($map)->update($data);
        $result['info'] = '权限修改成功!';
        $result['url'] = url('adminRule');
        $result['status'] = 1;
        return $result;
    }

    static public function rule($cate , $lefthtml = '— ' , $pid=0 , $lvl=0, $leftpin=0 ){
        $arr=array();
        foreach ($cate as $v){
            if($v['pid']==$pid){
                $v['lvl']=$lvl + 1;
                $v['leftpin']=$leftpin + 0;//左边距
                $v['lefthtml']=str_repeat($lefthtml,$lvl);
                $arr[]=$v;
                $arr= array_merge($arr,self::rule($cate,$lefthtml,$v['id'],$lvl+1 , $leftpin+20));
            }
        }
        return $arr;
    }
}
