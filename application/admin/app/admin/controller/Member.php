<?php
namespace app\admin\controller;
use think\Db;
use think\Request;
use think\Controller;
use app\admin\model\Member as MemberModel;
class Member extends Common{
    //会员列表
    public function memberList(){
        $key=input('post.val');
        $url['key'] = $key;
        $this->assign('testkey',$key);
        $memberList=db('member')->alias('m')
            ->join('clt_member_group mg','m.group_id = mg.group_id','left')
            ->field('m.*,mg.name')
            ->where('username|email|petname','like',"%".$key."%")
            ->paginate(config('pageSize'));
        //dump($memberList);
        $memberList->appends($url);

        $page = $memberList->render();
        $this->assign('page', $page);
        $this->assign('memberList',$memberList);
        return $this->fetch();
    }
    public function memberGroup(){
        $member_group=db('member_group');
        $member_group_list=$member_group->order('sort')->select();
        $this->assign('member_group_list',$member_group_list);
        return $this->fetch();
    }
    public function groupAdd(){
        $open = input('post.open') ? input('post.open') : 0;
        $data= array(
          'name'=>input('post.name'),
          'bomlimit'=>input('post.bomlimit'),
          'toplimit'=>input('post.toplimit'),
          'sort'=>input('post.sort'),
          'open'=>$open
        );
        db('member_group')->insert($data);
        $result['info'] = '会员组添加成功!';
        $result['url'] = url('memberGroup');
        $result['status'] = 1;
        return $result;
    }
    public function groupState(){
        $group_id=input('post.id');
        if (!$group_id){
            $this->error('请正确操作',url('memberGroup'),0);
        }
        $open=db('member_group')->where(array('group_id'=>$group_id))->value('open');//判断当前状态情况
        if($open==1){
            $data['open'] = 0;
            db('member_group')->where(array('group_id'=>$group_id))->setField($data);
            $result['info'] = '状态禁止';
            $result['status'] = 1;
        }else{
            $data['open'] = 1;
            db('member_group')->where(array('group_id'=>$group_id))->setField($data);
            $result['info'] = '状态开启';
            $result['status'] = 1;
        }
        return $result;
    }
    public function groupEdit(){
        $group_id=input('post.group_id');
        $member_group=db('member_group')->where(array('group_id'=>$group_id))->find();
        $member_group['status']=1;
        return $member_group;
    }
    public function groupUpdate(){
        $data=array(
            'group_id'=>input('post.group_id'),
            'name'=>input('post.name'),
            'toplimit'=>input('post.toplimit'),
            'bomlimit'=>input('post.bomlimit'),
            'sort'=>input('post.sort')
        );
        db('member_group')->update($data);
        $result['info'] = '用户组修改成功!';
        $result['url'] = url('memberGroup');
        $result['status'] = 1;
        return $result;
    }
    public function groupDel(){
        $group_id=input('group_id');
        if (empty($group_id)){
            $this->error('会员组ID不存在',url('memberGroup'),0);
        }
        db('member_group')->where(array('group_id'=>$group_id))->delete();
        $this->redirect('memberGroup');
    }
    public function groupOrder(){
        $member_group=db('member_group');
        foreach ($_POST as $id => $sort){
            $member_group->where(array('group_id' => $id ))->setField('sort' , $sort);
        }
        $result['info'] = '排序更新成功!';
        $result['url'] = url('memberGroup');
        $result['status'] = 1;
        return $result;
    }
    public function memberState(){
        $id=input('post.id');
        $status=db('member')->where(array('member_id'=>$id))->value('open');//判断当前状态情况
        if($status==1){
            $data['open'] = 0;
            db('member')->where(array('member_id'=>$id))->setField($data);
            $result['info'] = '状态禁止';
            $result['status'] = 1;
        }else{
            $data['open'] = 1;
            db('member')->where(array('member_id'=>$id))->setField($data);
            $result['info'] = '状态开启';
            $result['status'] = 1;
        }
        return $result;
    }
    public function memberAdd(){
        $province = db('Region')->where ( array('pid'=>1) )->select ();
        $this->assign('province',$province);
        $member_group=db('member_group')->order('sort')->select();
        $this->assign('member_group',$member_group);
        return $this->fetch();
    }
    public function memberInsert(){
		$member = input('post.');
		$user = new MemberModel;
		if ($user->allowField(true)->validate(true)->save($member)) {
			$result['info'] = '会员添加成功!';
			$result['url'] = url('memberList');
			$result['status'] = 1;
		} else {
			$result['info'] = $user->getError();
			$result['status'] = 0;
		}
		return $result;
    }
    public function memberEdit($member_id=''){
        $province = db('Region')->where ( array('pid'=>1) )->select ();
        $member_group=db('member_group')->order('sort')->select();
		$memberList = MemberModel::get($member_id);
        $this->assign('memberList',$memberList);
        $this->assign('province',$province);
        $this->assign('member_group',$member_group);
        return $this->fetch();
    }
    public function memberUpdate($member_id=''){
		$user = MemberModel::get($member_id);
		$member = input('post.');
		if ($user->allowField(true)->validate(true)->save($member)) {
			$result['info'] = '会员修改成功!';
			$result['url'] = url('memberList');
			$result['status'] = 1;
		} else {
			$result['info'] = $user->getError();
			$result['status'] = 0;
		}
		return $result;
    }
    public function memberDel($member_id=''){
		MemberModel::destroy($member_id);
		$this->redirect('memberList');
    }

}