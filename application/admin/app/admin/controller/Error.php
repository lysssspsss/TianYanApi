<?php
namespace app\admin\controller;
use think\Db;
use think\Request;
use clt\Form;
class Error extends Common{
    protected  $dao,$fields;
    public function _initialize()
    {
        parent::_initialize();
        $this->dao = db(MODULE_NAME);
        $fields = F($this->moduleid.'_Field');

        foreach($fields as $key => $res){
            $res['setup']=string2array($res['setup']);
            $this->fields[$key]=$res;
        }
        //print_r($fields);
        unset($fields);
        unset($res);
        $this->assign ('fields',$this->fields);
    }
    public function index(){
        $request = Request::instance();
        $controllerName = $request->controller();
        $this->_list(strtolower($controllerName));
        $category = db('category')->select();
        foreach($category as $key => $res){
            $categorys[$res['id']]=$res;
        }
        $this->assign('categorys', $categorys);
        return $this->fetch ('content/index');
    }
    protected function _list($modelname, $map = '', $sortBy = '', $asc = false ,$listRows = 15) {
        $model = db($modelname);
        $data = input();
        $id=$model->getPk();
        $this->assign ( 'pkid', $id );
        $order = "listorder asc,id desc";

        $keyword = isset($data['val'])?$data['val']:'';
        if (isset($data['catid'])) {
            $catids = db('category')->where('parentid',$data['catid'])->column('id');
            if($catids){
                $catid = $data['catid'].','.implode(',',$catids);
            }else{
                $catid = $data['catid'];
            }
        }
        if(!empty($keyword) ){
            $map['title']=array('like','%'.$keyword.'%');
        }

        $prefix=config('database.prefix');
        $Fields=Db::getFields($prefix.$modelname);

        foreach ($Fields as $k=>$v){
            $field[$k] = $k;
        }
        if(in_array('catid',$field)){
            if(isset($catid)){
                $map['catid']=array('in',$catid);
            }
        }
        $list = $model->where($map)->order($order)->paginate($listRows);
        //echo $model->getLastSql();
        // 获取分页显示
        $page = $list->render();
        // 模板变量赋值
        $this->assign('list', $list);
        $this->assign('keyword', $keyword);
        $this->assign('page', $page);
        return;
    }
    public function edit(){
        $id = input('id');
        $request = Request::instance();
        $controllerName = $request->controller();
        if($controllerName=='Page'){
            $p = $this->dao->where('id',$id)->find();
            if(empty($p)){
                $data['id']=$id;
                $data['title'] = $this->categorys[$id]['catname'];
                $data['keywords'] = $this->categorys[$id]['keywords'];
                $this->dao->insert($data);
            }
        }
        $info = $this->dao->where('id',$id)->find();

        $form=new Form($info);
        $returnData['vo'] = $info;
        $returnData['form'] = $form;
        $this->assign ('info', $info );
        $this->assign ( 'form', $form );
        return $this->fetch('content/edit');
    }
    function update(){
        $request = Request::instance();
        $controllerName = $request->controller();
        $model = $this->dao;
        $fields = $this->fields;


        $data = $this->checkfield($fields,input('post.'));
        if(isset($fields['updatetime'])) {
            $data['userid'] = session('aid');
        }

        if(isset($fields['updatetime'])) {
            $data['updatetime'] = time();
        }

        $title_style ='';
        if (isset($data['style_color'])) {
            $title_style .= 'color:' . $data['style_color'];
            unset($data['style_color']);
        }
        if (isset($data['style_bold'])) {
            $title_style .= ';font-weight:' . $data['style_bold'];
            unset($data['style_bold']);
        }
        if($fields['title']['setup']['style']==1) {
            $data['title_style'] = $title_style;
        }
        if($controllerName!='Page') {
            $data['updatetime'] = time();
        }
        $aid = $data['aid'];
        unset($data['aid']);
        unset($data['pics_name']);
        //编辑多图和多文件
        foreach ($fields as $k=>$v){
            if($v['type']=='files' or $v['type']=='images'){
                if(!$data[$k]){
                    $data[$k]='';
                }
            }
        }
        $list=$model->update($data);
        if (false !== $list) {
            $id= $data['id'];
            $catid = $controllerName =='Page' ? $id : $data['catid'];
            if(isset($aid)) {
                $Attachment =db('Attachment');
                $aids =  implode(',',$aid);
                $data2['id']= $id;
                $data2['catid']= $catid;
                $data2['status']= '1';
                $Attachment->where("aid in (".$aids.")")->update($data2);
            }
            if($controllerName=='Page'){
                $result['url'] = url("admin/category/index");
            }else{
                $result['url'] = url("admin/".$controllerName."/index",array('catid'=>$data['catid']));
            }
            $result['info'] = '修改成功!';
            $result['status'] = 1;
            return $result;
        } else {
            $result['info'] = '修改失败!';

            $result['status'] = 0;
            return $result;
        }
    }
    public function set_categorys($categorys = array()) {
        if (is_array($categorys) && !empty($categorys)) {
            foreach ($categorys as $id => $c) {
                $this->categorys[$c['id']] = $c;
                $r = db('category')->where("parentid = $c[id]")->order('listorder ASC,id ASC')->select();
                $this->set_categorys($r);
            }
        }
        return true;
    }
    function checkfield($fields,$post){

        foreach ( $post as $key => $val ) {
            if(isset($fields[$key])){
                $setup=$fields[$key]['setup'];
                if(!empty($fields[$key]['required']) && empty($post[$key])){
                    $result['info'] = '缺少必要的参数!';
                    $result['status'] = 0;
                    return $result;
                }
                if(isset($setup['multiple'])){
                    $post[$key] = implode(',',$post[$key]);
                }
                if(isset($setup['inputtype'])){
                    if($setup['inputtype']=='checkbox'){
                        $post[$key] = implode(',',$post[$key]);
                    }
                }
                if(isset($setup['type'])){
                    if($fields[$key]['type']=='checkbox'){
                        $post[$key] = implode(',',$post[$key]);
                    }
                }
                if($fields[$key]['type']=='datetime'){
                    $post[$key] =strtotime($post[$key]);
                }elseif($fields[$key]['type']=='textarea'){
                    $post[$key]=addslashes($post[$key]);
                }elseif($fields[$key]['type']=='images' || $fields[$key]['type']=='files'){
                    $name = $key.'_name';
                    $text = $key.'_text';
                    $arrdata =array();
                    foreach($post[$key] as $k=>$res){
                        $arrdata[]=$post[$key][$k].'|'.$post[$name][$k].'|'.$post[$text][$k];
                    }
                    $post[$key]=implode(':::',$arrdata);
                }elseif($fields[$key]['type']=='editor'){
                    if(isset($post['add_description']) && $post['description'] == '' && isset($post['content'])) {
                        $content = stripslashes($post['content']);
                        $description_length = intval($post['description_length']);
                        $post['description'] = str_cut(str_replace(array("\r\n","\t",'[page]','[/page]','&ldquo;','&rdquo;'), '', strip_tags($content)),$description_length);
                        $post['description'] = addslashes($post['description']);
                    }
                    if(isset($post['auto_thumb']) && $post['thumb'] == '' && isset($post['content'])) {
                        $content = $content ? $content : stripslashes($post['content']);
                        $auto_thumb_no = intval($post['auto_thumb_no']) * 3;
                        if(preg_match_all("/(src)=([\"|']?)([^ \"'>]+\.(gif|jpg|jpeg|bmp|png))\\2/i", $content, $matches)) {
                            $post['thumb'] = $matches[$auto_thumb_no][0];
                        }
                    }
                }
            }
        }
        return $post;
    }

    public function add(){
        $form=new Form();
        $this->assign ( 'form', $form );
        return $this->fetch('content/edit');
    }
    public function insert(){
        $request = Request::instance();
        $controllerName = $request->controller();
        $model = $this->dao;
        $fields = $this->fields;
        $data = $this->checkfield($fields,input('post.'));

        if($fields['createtime']  && empty($data['createtime']) ){
            $data['createtime'] = time();
        }
        if($fields['updatetime']  && empty($data['updatetime']) ) {
            $data['updatetime'] = time();
        }
        if($controllerName!='Page') {
            if ($fields['updatetime']){
                $data['updatetime'] = $data['createtime'];
            }
        }
        $data['userid'] = session('aid');
        $data['username'] = session('username');
        if($data['style_color']){
            $data['style_color'] = 'color:'.$data['style_color'];
        }
        if($data['style_bold']){
            $data['style_bold'] =  ';font-weight:'.$data['style_bold'];
        }
        if($data['style_color'] || $data['style_bold'] ){
            $data['title_style'] = $data['style_color'].$data['style_bold'];
        }
        $aid = $data['aid'];
        unset($data['style_color']);
        unset($data['style_bold']);
        unset($data['aid']);
        unset($data['pics_name']);
        $id= $model->insertGetId($data);
        if ($id !==false) {
            $catid = $controllerName =='Page' ? $id : $data['catid'];

            if($aid) {
                $Attachment =db('Attachment');
                $aids =  implode(',',$aid);
                $data2['id']=$id;
                $data2['catid']= $catid;
                $data2['status']= '1';
                $Attachment->where("aid in (".$aids.")")->update($data2);
            }
            if($controllerName=='Page'){
                $result['url'] = url("admin/news/category");
            }else{
                $result['url'] = url("admin/".$controllerName."/index",array('catid'=>$data['catid']));
            }
            $result['info'] = '添加成功!';
            $result['status'] = 1;
            return $result;
        } else {
            $result['info'] = '添加失败!';
            $result['status'] = 0;
            return $result;
        }

    }
    public function listDel(){
        $id = input('id');
        $model = $this->dao;
        $model->where(array('id'=>$id))->delete();//转入回收站
        $this->redirect('index',array('catid'=>input('catid')));
    }
    public function delAll(){

        $map['id'] =array('in',input('param.ids'));
        $model = $this->dao;
        $model->where($map)->delete();
        $result['msg'] = '删除成功！';
        $result['url'] = url('index',array('catid'=>input('param.catid')));
        return $result;
    }

    public function listorder(){
        $model = $this->dao;
        $ids = $_POST['listorders'];
        $catid = input('catid');
        foreach($ids as $key=>$r) {
            $data['listorder']=$r;
            $model->where('id='.$key)->update($data);
        }
        $result = ['info' => '排序成功！','url'=>url('index',array('catid'=>$catid)), 'status' => '1'];
        return $result;
    }
}