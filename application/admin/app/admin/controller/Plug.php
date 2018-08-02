<?php
namespace app\admin\controller;
use think\Db;
use think\Request;
use think\Controller;
class Plug extends Common{
    /*****************************友情链接******************************/
    public function linkList(){
        $type=input('type');
        $val=input('post.val');
        $map = '';
        if (!empty($val)){
            $map['name|url'] = array('like',"%".$val."%");
        }
        $link=db('link')->where($map)->order('sort')->select();
        $this->assign('link',$link);
        $this->assign('val',$val);
        return $this->fetch();
    }
    //修改友情链接状态
    public function linkState(){
        $id=input('post.val');
        $open=db('link')->where(array('link_id'=>$id))->value('open');//判断当前状态情况
        if($open==1){
            $data['open'] = 0;
            db('link')->where(array('link_id'=>$id))->setField($data);
            $result['status'] = 1;
            $result['info'] = '状态禁止';
        }else{
            $data['open'] = 1;
            db('link')->where(array('link_id'=>$id))->setField($data);
            $result['status'] = 1;
            $result['info'] = '状态开启';
        }
        return $result;
    }
    //添加
    public function linkInsert(){
        $data=array(
            'name'=>input('post.name'),
            'url'=>input('post.url'),
            'qq'=>input('post.qq'),
            'sort'=>input('post.sort'),
            'addtime'=>time(),
            'open'=>input('post.open') ? input('post.open') : 0,
        );
        db('link')->insert($data);
        $result['status'] = 1;
        $result['info'] = '友情链接添加成功!';
        $result['url'] = url('linkList');
        return $result;

    }
    //修改友情链接
    public function linkEdit(){
        $link_id=input('post.link_id');
        $plug_link=db('link')->where(array('link_id'=>$link_id))->find();
        $result['status'] = 1;
        $result['info'] = $plug_link;
        return $result;
    }
    public function linkUpdate(){
        $data=array(
            'link_id'=>input('post.edit_link_id'),
            'name'=>input('post.edit_name'),
            'url'=>input('post.edit_url'),
            'qq'=>input('post.edit_qq'),
            'sort'=>input('post.edit_sort')
        );
        db('link')->update($data);
        $result['status'] = 1;
        $result['info'] = '友情链接修改成功!';
        $result['url'] = url('linkList');
        return $result;
    }
    public function linkDel(){
        db('link')->where(array('link_id'=>input('link_id')))->delete();
        $this->redirect('linkList');
    }
    /*****************************广告管理***************************/
    //广告列表
    public function adList(){
        $key=input('post.key');
        $url['key'] = $key;
        $this->assign('testkey',$key);
        $adList=Db::table('clt_ad')->alias('a')
            ->join('clt_ad_type at','a.type_id = at.type_id','left')
            ->field('a.*,at.name as typename')
            ->where('a.name','like',"%".$key."%")
            ->order('a.sort')
            ->paginate(config('pageSize'));
        $adList->appends($url);
        $page = $adList->render();
        $this->assign('page', $page);
        $adtypeList=db('ad_type')->order('sort')->select();//获取所有广告位

        $this->assign('adList',$adList);
        $this->assign('adTypeList',$adtypeList);
        return $this->fetch();
    }
    public function adState(){
        $id=input('post.id');
        $open=db('ad')->where(array('ad_id'=>$id))->value('open');//判断当前状态情况
        if($open==1){
            $data['open'] = 0;
            db('ad')->where(array('ad_id'=>$id))->setField($data);
            $result['status'] = 1;
            $result['info'] = '状态禁止';
        }else{
            $data['open'] = 1;
            db('ad')->where(array('ad_id'=>$id))->setField($data);
            $result['status'] = 1;
            $result['info'] = '状态开启';
        }
        return $result;
    }
    public function adDel(){
        $ad_id=input('ad_id');
        db('ad')->where(array('ad_id'=>$ad_id))->delete();
        $this->redirect('adList');
    }
    public function adInsert(){
        //构建数组
        $data=array(
            'type_id'=>input('post.type_id'),
            'name'=>input('post.name'),
            'pic'=>input('post.checkpic'),
            'url'=>input('post.url'),
            'open'=>input('post.open','',0),
            'sort'=>input('post.sort'),
            'addtime'=>time(),
        );
        db('ad')->insert($data);
        $result['status'] = 1;
        $result['info'] = '广告添加成功!';
        $result['url'] = url('adList');
        return $result;
    }
    public function adOrder(){
        $ad=db('ad');
        foreach (input('post.') as $id => $sort){
            $ad->where(array('ad_id' => $id ))->setField('sort' , $sort);
        }
        $result['status'] = 1;
        $result['info'] = '广告排序更新成功';
        $result['url'] = url('adList');
        return $result;
    }
    public function adEdit(){
        $adtype=db('ad_type')->select();
        $ad_id=input('ad_id');
        $adInfo=db('ad')->where(array('ad_id'=>$ad_id))->find();
        $adInfo['picurl'] = imgUrl($adInfo['pic']);
        $this->assign('adtype',$adtype);
        $this->assign('adInfo',$adInfo);
        return $this->fetch();
    }
    public function adUpdate(){
        $data=array(
            'type_id'=>input('post.type_id'),
            'name'=>input('post.name'),
            'url'=>input('post.url'),
            'sort'=>input('post.sort'),
            'pic'=>input('post.checkpic')
        );
        db('ad')->where(array('ad_id'=>input('post.ad_id')))->update($data);
        $result['status'] = 1;
        $result['info'] = '广告设置修改成功!';
        $result['url'] = url('adList');
        return $result;
    }
    /***********************************广告分类管理************************************/
    public function adTypeList(){
        $key=input('key');
        $url['key'] = $key;
        $this->assign('testkey',$key);
        $adTypeList=db('ad_type')->where('name','like',"%".$key."%")->order('sort')->paginate(config('pageSize'));
        $adTypeList->appends($url);
        $page = $adTypeList->render();
        $this->assign('page', $page);
        $this->assign('adTypeList',$adTypeList);
        return $this->fetch();
    }
    public function adTypeEdit(){
        $type_id=input('post.type_id');
        $ad_type=db('ad_type')->where(array('type_id'=>$type_id))->find();
        $ad_type['status']=1;
        return $ad_type;
    }
    public function addTypeUpdate(){
        db('ad_type')->update(input('post.'));
        $result['status'] = 1;
        $result['info'] = '广告位修改成功!';
        $result['url'] = url('adTypeList');
        return $result;
    }
    public function adTypeOrder(){
        $ad_type=db('ad_type');
        foreach (input('post.') as $id => $sort){
            $ad_type->where(array('type_id' => $id ))->setField('sort',$sort);
        }
        $result['status'] = 1;
        $result['info'] = '广告位排序更新成功!';
        $result['url'] = url('adTypeList');
        return $result;
    }
    public function addTypeInsert(){
        db('ad_type')->insert(input('post.'));
        $result['status'] = 1;
        $result['info'] = '广告位保存成功!';
        $result['url'] = url('adTypeList');
        return $result;
    }
    public function adTypeDel(){
        db('ad_type')->where(array('type_id'=>input('type_id')))->delete();//删除广告位
        db('ad')->where(array('type_id'=>input('type_id')))->delete();//删除该广告位所有广告
        $this->redirect('adTypeList');
    }
    /********************************留言******************************/
    public function message(){
        $key=input('post.key');
        $url['key'] = $key;
        $this->assign('testkey',$key);
        $messageList=db('message')->where('name|tel|content','like',"%".$key."%")->order('addtime desc')->paginate(config('pageSize'));
        $messageList->appends($url);
        $page = $messageList->render();
        $this->assign('page', $page);
        $this->assign('messageList',$messageList);
        return $this->fetch();
    }
    //删除留言
    public function messageDel(){
        $map['message_id']=input('message_id');
        db('message')->where($map)->delete();
        $this->redirect('Plug/message');
    }
}