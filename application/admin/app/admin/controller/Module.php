<?php
namespace app\admin\controller;
use think\Db;
use think\Request;
class Module extends Common
{
    protected $dao;
    function _initialize()
    {
        parent::_initialize();
        $this->dao=db('module');
        $field_pattern = array(
            '0' => "请选择",
            'email' => "电子邮件",
            'url' => "网址 ",
            'date' => "日期 ",
            'number' => "有效的数值 ",
            'digits' => "数字",
            'creditcard' => "信用卡号码",
            'equalTo' => "再次输入相同的值.",
            'ip4' => "IP",
            'mobile' => "手机号码",
            'zipcode' => "邮编 ",
            'qq' => "qq",
            'idcard' => "身份证号",
            'chinese' => "中文字符 ",
            'cn_username' => "中文英文和数字和下划线",
            'tel' => " 电话号码",
            'english' => "英文",
            'en_num' => "英文和数字和下划线",
        );
        $this->assign('pattern', $field_pattern);
    }
    public function index(){
        $list = $this->dao->where(array('type'=>1))->select();
        $this->assign('list', $list);
        return $this->fetch();
    }
    public function edit(){
        $map['id'] = input('param.id');
        $info = $this->dao->where($map)->find();
        $this->assign('info', $info);
        return $this->fetch();
    }
    public function moduleUpdate(){
        $data = input('post.');
        if($this->dao->update($data)!==false){
            savecache('Module');
            $result['status'] = 1;
            $result['url'] = url('index');
            $result['info'] = '修改成功！';
        }else{
            $result['status'] = 0;
            $result['info'] = '修改失败！';
        }
        return $result;
    }

    public function add(){
        return $this->fetch();
    }
    public function moduleState(){
        $id=input('post.id');
        $status=$this->dao->where(array('id'=>$id))->value('status');//判断当前状态情况
        if($status==1){
            $data['status'] = 0;
            $this->dao->where(array('id'=>$id))->setField($data);
            $result['info'] = '状态禁止';
            $result['status'] = 1;
        }else{
            $data['status'] = 1;
            $this->dao->where(array('id'=>$id))->setField($data);
            $result['info'] = '状态开启';
            $result['status'] = 1;
        }
        return $result;
    }
    public function moduleInsert(){
        //获取数据库所有表名
        $tables = Db::getTables();
        //组装表名
        $prefix = config('database.prefix');
        $tablename = $prefix.input('post.name');
        //判断表名是否已经存在
        if(in_array($tablename,$tables)){
            $result['status'] = 0;
            $result['info'] = '该表已经存在！';
            return $result;
        }
        $name = ucfirst(input('post.name'));

        $data = input('post.');
        $data['type'] = 1;
        $moduleid = $this->dao->insertGetId($data);
        if(empty($moduleid)){
            $result['status'] = 0;
            $result['info'] = '添加模型失败！';
            return $result;
        }

        $emptytable =input('post.emptytable');
        if($emptytable=='0'){
            Db::execute("CREATE TABLE `".$tablename."` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `catid` smallint(5) unsigned NOT NULL DEFAULT '0',
			  `userid` int(8) unsigned NOT NULL DEFAULT '0',
			  `username` varchar(40) NOT NULL DEFAULT '',
			  `title` varchar(120) NOT NULL DEFAULT '',
			  `title_style` varchar(40) NOT NULL DEFAULT '',
			  `thumb` varchar(225) NOT NULL DEFAULT '',
			  `keywords` varchar(120) NOT NULL DEFAULT '',
			  `description` mediumtext NOT NULL,
			  `content` mediumtext NOT NULL,
			  `url` varchar(60) NOT NULL DEFAULT '',
			  `template` varchar(40) NOT NULL DEFAULT '', 
			  `posid` tinyint(2) unsigned NOT NULL DEFAULT '0',
			  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
			  `recommend` tinyint(1) unsigned NOT NULL DEFAULT '0',
			  `readgroup` varchar(100) NOT NULL DEFAULT '',
			  `readpoint` smallint(5) NOT NULL DEFAULT '0',
			  `listorder` int(10) unsigned NOT NULL DEFAULT '0',
			  `hits` int(11) unsigned NOT NULL DEFAULT '0',
			  `createtime` int(11) unsigned NOT NULL DEFAULT '0',
			  `updatetime` int(11) unsigned NOT NULL DEFAULT '0',
			  PRIMARY KEY (`id`),
			  KEY `status` (`id`,`status`,`listorder`),
			  KEY `catid` (`id`,`catid`,`status`),
			  KEY `listorder` (`id`,`catid`,`status`,`listorder`)
			) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8");

            Db::execute("INSERT INTO `".$prefix."field` VALUES ('', '".$moduleid."', 'catid', '栏目', '', '1', '1', '6', '', '必须选择一个栏目', '', 'catid', '','1','', '0', '1', '1')");
            Db::execute("INSERT INTO `".$prefix."field` VALUES ('', '".$moduleid."', 'title', '标题', '', '1', '1', '80', '', '标题必须为1-80个字符', '', 'title', 'array (\n  \'thumb\' => \'1\',\n  \'style\' => \'1\',\n  \'size\' => \'55\',\n)','1','',  '0', '1', '1')");
            Db::execute("INSERT INTO `".$prefix."field` VALUES ('', '".$moduleid."', 'keywords', '关键词', '', '0', '0', '80', '', '', '', 'text', 'array (\n  \'size\' => \'55\',\n  \'default\' => \'\',\n  \'ispassword\' => \'0\',\n  \'fieldtype\' => \'varchar\',\n)','1','',  '0', '1', '1')");
            Db::execute("INSERT INTO `".$prefix."field` VALUES ('', '".$moduleid."', 'description', 'SEO简介', '', '0', '0', '0', '', '', '', 'textarea', 'array (\n  \'fieldtype\' => \'mediumtext\',\n  \'rows\' => \'4\',\n  \'cols\' => \'55\',\n  \'default\' => \'\',\n)','1','',  '0', '1', '1')");
            Db::execute("INSERT INTO `".$prefix."field` VALUES ('', '".$moduleid."', 'content', '内容', '', '0', '0', '0', '', '', '', 'editor', 'array (\n  \'toolbar\' => \'full\',\n  \'default\' => \'\',\n  \'height\' => \'\',\n  \'showpage\' => \'1\',\n  \'enablekeylink\' => \'0\',\n  \'replacenum\' => \'\',\n  \'enablesaveimage\' => \'0\',\n  \'flashupload\' => \'1\',\n  \'alowuploadexts\' => \'\',\n)','1','',  '10', '1', '1')");
            Db::execute("INSERT INTO `".$prefix."field` VALUES ('', '".$moduleid."', 'createtime', '发布时间', '', '1', '0', '0', 'date', '', '', 'datetime', '','1','',  '93', '1', '1')");
            Db::execute("INSERT INTO `".$prefix."field` VALUES ('', '".$moduleid."', 'recommend', '允许评论', '', '0', '0', '1', '', '', '', 'radio', 'array (\n  \'options\' => \'允许评论|1\r\n不允许评论|0\',\n  \'fieldtype\' => \'tinyint\',\n  \'numbertype\' => \'1\',\n  \'labelwidth\' => \'\',\n  \'default\' => \'\',\n)','1','', '0', '0', '0')");
            Db::execute("INSERT INTO `".$prefix."field` VALUES ('', '".$moduleid."', 'readpoint', '阅读收费', '', '0', '0', '5', '', '', '', 'number', 'array (\n  \'size\' => \'5\',\n  \'numbertype\' => \'1\',\n  \'decimaldigits\' => \'0\',\n  \'default\' => \'0\',\n)','1','', '0', '0', '0')");
            Db::execute("INSERT INTO `".$prefix."field` VALUES ('', '".$moduleid."', 'hits', '点击次数', '', '0', '0', '8', '', '', '', 'number', 'array (\n  \'size\' => \'10\',\n  \'numbertype\' => \'1\',\n  \'decimaldigits\' => \'0\',\n  \'default\' => \'0\',\n)','1','',  '0', '0', '0')");
            Db::execute("INSERT INTO `".$prefix."field` VALUES ('', '".$moduleid."', 'readgroup', '访问权限', '', '0', '0', '0', '', '', '', 'groupid', 'array (\n  \'inputtype\' => \'checkbox\',\n  \'fieldtype\' => \'tinyint\',\n  \'labelwidth\' => \'85\',\n  \'default\' => \'\',\n)','1','', '96', '0', '1')");
            Db::execute("INSERT INTO `".$prefix."field` VALUES ('', '".$moduleid."', 'posid', '推荐位', '', '0', '0', '0', '', '', '', 'posid', '','1','', '97', '1', '1')");
            Db::execute("INSERT INTO `".$prefix."field` VALUES ('', '".$moduleid."', 'template', '模板', '', '0', '0', '0', '', '', '', 'template', '','1','', '98', '1', '1')");
            Db::execute("INSERT INTO `".$prefix."field` VALUES ('', '".$moduleid."', 'status', '状态', '', '0', '0', '0', '', '', '', 'radio', 'array (\n  \'options\' => \'发布|1\r\n定时发布|0\',\n  \'fieldtype\' => \'tinyint\',\n  \'numbertype\' => \'1\',\n  \'labelwidth\' => \'75\',\n  \'default\' => \'1\',\n)','1','','99', '1', '1')");
        }else{
            Db::execute("CREATE TABLE `".$tablename."` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
			  `listorder` int(10) unsigned NOT NULL DEFAULT '0',
			  `template` varchar(40) NOT NULL DEFAULT '', 
			  `createtime` int(11) unsigned NOT NULL DEFAULT '0',
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8");
            Db::execute("INSERT INTO `".$prefix."field` VALUES ('', '".$moduleid."', 'createtime', '发布时间', '', '1', '0', '0', 'date', '', '', 'datetime', '','1','',  '93', '1', '1')");
            Db::execute("INSERT INTO `".$prefix."field` VALUES ('', '".$moduleid."', 'template', '模板', '', '0', '0', '0', '', '', '', 'template', '','1','', '98', '1', '1')");
            Db::execute("INSERT INTO `".$prefix."field` VALUES ('', '".$moduleid."', 'status', '状态', '', '0', '0', '0', '', '', '', 'radio', 'array (\n  \'options\' => \'发布|1\r\n定时发布|0\',\n  \'fieldtype\' => \'tinyint\',\n  \'numbertype\' => \'1\',\n  \'labelwidth\' => \'75\',\n  \'default\' => \'1\',\n)','0','', '99', '1', '1')");
        }
        if ($moduleid  !==false) {
            savecache('Module');
            $result['status'] = 1;
            $result['info'] = '添加模型成功！';
            $result['url'] = url('index');
            return $result;
        }
    }
    //删除模型
    function moduleDel() {
        $id =input('param.id');
        $r = db('module')->find($id);
        if(!empty($r)){
            $tablename = config('database.prefix').$r['name'];

            $m = db('module')->delete($id);
            if($m){
                Db::execute("DROP TABLE IF EXISTS `".$tablename."`");
                db('Field')->where(array('moduleid'=>$id))->delete();
            }
        }
        savecache('Module');
        $this->redirect('index');
    }


    public function field(){
        $this->assign('sysfield',array('catid','userid','username','title','thumb','keywords','description','posid','status','createtime','url','template'));
        $this->assign('nodostatus',array('catid','title','status','createtime'));
        $list = db('field')->where("moduleid=".input('param.id'))->order('listorder asc,id asc')->select();
        $this->assign('list', $list);
        return $this->fetch();
    }
    public function fieldEdit(){
        $model = db('Field');
        $id = input('param.id');
        if(empty($id)){
            $result['info'] = '缺少必要的参数！';
            $result['status'] = 0;
            return $result;
        }
        $fieldInfo = $model->where(array('id'=>$id))->find();
        if($fieldInfo['setup']) $fieldInfo['setup']=string2array($fieldInfo['setup']);
        $this->assign('info',$fieldInfo);
        $this->assign('moduleid', input('param.moduleid'));
        return $this->fetch();
    }
    public function listorder(){
        $model =db('field');
        $ids = input('post.listorders/a');
        foreach($ids as $key=>$r) {
            $model->where('id='.$key)->update(array('listorder'=>$r));
        }
        $result = ['info' => '排序成功！','url'=>url('field',array('id'=>input('post.id'))), 'status' => '1'];
        return $result;
    }

    public function fieldAdd(){
        $moduleid =input('moduleid');
        if(empty($moduleid)){
            $result['info'] = '缺少必要的参数！';
            $result['status'] = 0;
            return $result;
        }
        if(input('isajax')){
            $this->assign(input('get.'));
            $this->assign(input('post.'));
            $name = db('module')->where(array('id'=>input('moduleid')))->value('name');
            if(input('name')){
                $files = Db::getTableInfo("clt_".$name);
                $fieldtype = $files['type'][input('name')];
                $this->assign('fieldtype',$fieldtype);
                return view('fieldType');
            }else{
                return view('fieldAddType');
            }
        }else{
            $this->assign('moduleid',input('moduleid'));
            return $this->fetch();
        }
    }
    public function fieldStatus(){
        $map['id']=input('post.id');
        //判断当前状态情况
        $field = db('field');
        $status=$field->where($map)->value('status');
        if($status==1){
            $data['status'] = 0;
            $result['info'] = '状态禁止';
        }else{
            $data['status'] = 1;
            $result['info'] = '状态开启';
        }
        $field->where($map)->setField($data);
        $result['status'] = 1;
        return $result;
    }
    function fieldInsert() {
        $data = input('post.');
        $fieldName=$data['field'];
        $prefix=config('database.prefix');
        $name = db('module')->where(array('id'=>$data['moduleid']))->value('name');
        $tablename=$prefix.$name;
        $Fields=Db::getFields($tablename);
        foreach ( $Fields as $key =>$r){
            if($key==$fieldName){
                $ishave=1;
            }
        }
        if($ishave) {
            $result['info'] = '字段名已近存在！';
            $result['status'] = 0;
            return $result;
        }
        $addfieldsql =$this->get_tablesql($data,'add');
        if($data['setup']) {
            $data['setup'] = array2string($data['setup']);
        }
        $data['status'] =1;
        $model = db('field');
        if ($model->insert($data) !==false) {
            savecache('field',$data['moduleid']);
            if(is_array($addfieldsql)){
                foreach($addfieldsql as $sql){
                    $model->execute($sql);
                }
            }else{
                $model->execute($addfieldsql);
            }

            $result['info'] = '添加成功！';
            $result['status'] = 1;
            $result['url'] = url('field',array('id'=>input('post.moduleid')));
            return $result;
        } else {
            $result['info'] = '添加失败！';
            $result['status'] = 0;
            return $result;
        }
    }


    public function fieldUpdate(){
        $data = input('post.');

        $oldfield = $data['oldfield'];
        $fieldName=$data['field'];
        $prefix=config('database.prefix');
        $name = db('module')->where(array('id'=>$data['moduleid']))->value('name');
        $tablename=$prefix.$name;
        $Fields=Db::getFields($tablename);
        foreach ( $Fields as $key =>$r){
            if($key != $oldfield and $key==$fieldName){
                $ishave=1;
            }
        }
        if($ishave) {
            $result['info'] = '字段名重复！';
            $result['status'] = 0;
            return $result;
        }

        $editfieldsql =$this->get_tablesql($data,'edit');
        if($data['setup']){
            $data['setup']=array2string($data['setup']);
        }
        if(!empty($data['unpostgroup'])){
            $data['setup'] = implode(',',$data['unpostgroup']);
        }
        $model = db('field');
        if (false !== $model->update($data)) {
            savecache('Field',$data['moduleid']);
            if(is_array($editfieldsql)){
                foreach($editfieldsql as $sql){
                    $model->execute($sql);
                }
            }else{
                $model->execute($editfieldsql);
            }
            $result['info'] = '修改成功！';
            $result['status'] = 1;
            $result['url'] = url('field',array('id'=>input('post.moduleid')));
            return $result;
        } else {
            $result['info'] = '修改失败！';
            $result['status'] = 0;
            return $result;
        }
    }

    function fieldDel() {
        $id=input('id');
        $r = db('field')->find($id);
        db('field')->delete($id);

        $moduleid = $r['moduleid'];

        $field = $r['field'];

        $prefix=config('database.prefix');
        $name = db('module')->where(array('id'=>$moduleid))->value('name');
        $tablename=$prefix.$name;

        db('field')->execute("ALTER TABLE `$tablename` DROP `$field`");

        $this->redirect('field',array('id'=>$moduleid));
    }


    public function get_tablesql($info,$do){
        $fieldtype = $info['type'];
        if($info['setup']['fieldtype']){
            $fieldtype=$info['setup']['fieldtype'];
        }
        $moduleid = $info['moduleid'];
        $default=   $info['setup']['default'];
        $field = $info['field'];
        $prefix = config('database.prefix');
        $name = db('module')->where(array('id'=>$moduleid))->value('name');
        $tablename=$prefix.$name;
        $maxlength = intval($info['maxlength']);
        $minlength = intval($info['minlength']);
        $numbertype = $info['setup']['numbertype'];
        $oldfield = $info['oldfield'];
        if($do=='add'){
            $do = ' ADD ';
        }else{
            $do =  " CHANGE `".$oldfield."` ";
        }
        switch($fieldtype) {
            case 'varchar':
                if(!$maxlength){$maxlength = 255;}
                $maxlength = min($maxlength, 255);
                $sql = "ALTER TABLE `$tablename` $do `$field` VARCHAR( $maxlength ) NOT NULL DEFAULT '$default'";
                break;

            case 'title':
                if(!$maxlength){$maxlength = 255;}
                $maxlength = min($maxlength, 255);
                $sql[] = "ALTER TABLE `$tablename` $do `$field` VARCHAR( $maxlength ) NOT NULL DEFAULT '$default'";
                if($do=='add'){
                    $sql[] = "ALTER TABLE `$tablename` $do `title_style` VARCHAR( 40 ) NOT NULL DEFAULT ''";
                    $sql[] = "ALTER TABLE `$tablename` $do `thumb` VARCHAR( 100 ) NOT NULL DEFAULT ''";
                }
                break;
            case 'catid':
                $sql = "ALTER TABLE `$tablename` $do `$field` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0'";
                break;

            case 'number':
                $decimaldigits = $info['setup']['decimaldigits'];
                $default = $decimaldigits == 0 ? intval($default) : floatval($default);
                $sql = "ALTER TABLE `$tablename` $do `$field` ".($decimaldigits == 0 ? 'INT' : 'decimal( 10,'.$decimaldigits.' )')." ".($numbertype ==1 ? 'UNSIGNED' : '')."  NOT NULL DEFAULT '$default'";
                break;

            case 'tinyint':
                if(!$maxlength) $maxlength = 3;
                $maxlength = min($maxlength,3);
                $default = intval($default);
                $sql = "ALTER TABLE `$tablename` $do `$field` TINYINT( $maxlength ) ".($numbertype ==1 ? 'UNSIGNED' : '')." NOT NULL DEFAULT '$default'";
                break;


            case 'smallint':
                $default = intval($default);
                $sql = "ALTER TABLE `$tablename` $do `$field` SMALLINT ".($numbertype ==1 ? 'UNSIGNED' : '')." NOT NULL DEFAULT '$default'";
                break;

            case 'int':
                $default = intval($default);
                $sql = "ALTER TABLE `$tablename` $do `$field` INT ".($numbertype ==1 ? 'UNSIGNED' : '')." NOT NULL DEFAULT '$default'";
                break;

            case 'mediumint':
                $default = intval($default);
                $sql = "ALTER TABLE `$tablename` $do `$field` INT ".($numbertype ==1 ? 'UNSIGNED' : '')." NOT NULL DEFAULT '$default'";
                break;

            case 'mediumtext':
                $sql = "ALTER TABLE `$tablename` $do `$field` MEDIUMTEXT NOT NULL";
                break;

            case 'text':
                $sql = "ALTER TABLE `$tablename` $do `$field` TEXT NOT NULL";
                break;

            case 'posid':
                $sql = "ALTER TABLE `$tablename` $do `$field` TINYINT(2) UNSIGNED NOT NULL DEFAULT '0'";
                break;

            //case 'typeid':
            //$sql = "ALTER TABLE `$tablename` $do `$field` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0'";
            //break;

            case 'datetime':
                $sql = "ALTER TABLE `$tablename` $do `$field` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
                break;

            case 'editor':
                $sql = "ALTER TABLE `$tablename` $do `$field` TEXT NOT NULL";
                break;

            case 'image':
                $sql = "ALTER TABLE `$tablename` $do `$field` VARCHAR( 80 ) NOT NULL DEFAULT ''";
                break;

            case 'images':
                $sql = "ALTER TABLE `$tablename` $do `$field` MEDIUMTEXT NOT NULL";
                break;

            case 'file':
                $sql = "ALTER TABLE `$tablename` $do `$field` VARCHAR( 80 ) NOT NULL DEFAULT ''";
                break;

            case 'files':
                $sql = "ALTER TABLE `$tablename` $do `$field` MEDIUMTEXT NOT NULL";
                break;
            case 'template':
                $sql = "ALTER TABLE `$tablename` $do `$field` VARCHAR( 80 ) NOT NULL DEFAULT ''";
                break;
        }
        return $sql;
    }

}