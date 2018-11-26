<?php
namespace app\index\controller;
use app\tools\controller\Time;
use app\tools\controller\Tools;
use think\Controller;
use think\Request;
use think\Db;
use think\Session;
use think\Validate;
use Qiniu\Auth;


class Index extends Base
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        //var_dump(time());exit;
        /*$namelist = db('role')->select();
        $a = 1988;
        $module = ['后台管理','课程管理','专栏管理','人员管理','收益管理','公众号管理','素材管理','活动管理','权限管理'];
        $do = ['查询','查询','查询','修改','修改','修改','删除','添加','添加','添加'];
        for($i=0;$i<$a;$i++){
            $time = time()-412620+($i*223);
            $rand = mt_rand(0,20);
            $rand2 = mt_rand(0,8);
            $rand3 = mt_rand(0,9);
            $data[$i]['addtime'] = date('Y-m-d H:i:s',$time);
            $data[$i]['name'] = $namelist[$rand]['name'];
            $data[$i]['ip'] = '183.238.1.246';
            $data[$i]['module'] = $module[$rand2];
            $data[$i]['do'] = $do[$rand3];
            $data[$i]['result'] = '成功';

            $b = db('shenji')->insert($data[$i]);
        }
        //var_dump($data);
        */
        echo 'index';exit;
    }

    public function stock()
    {
        /*$appkey ='';
        $url = "http://web.juhe.cn:8080/finance/stock/hs";
        $params = array(
            "gid" => "",//股票编号，上海股市以sh开头，深圳股市以sz开头如：sh601009
            "key" => $appkey,//APP Key
        );
        $paramstring = http_build_query($params);
        $content = juhecurl($url,$paramstring);
        $result = json_decode($content,true);
        if($result){
            if($result['error_code']=='0'){
                print_r($result);
            }else{
                echo $result['error_code'].":".$result['reason'];
            }
        }else{
            echo "请求失败";
        }*/
    }


    /**
     * 获取首页
     */
    public function main()
    {
        if(empty($this->user['nickname'])){
            $this->user['nickname'] = '游客';
        }
        $name = empty($this->user['name'])?$this->user['nickname']:$this->user['name'];
        $data['title'] = $this->get_wenhou().','.$name;
        //var_dump($data);exit;
        $lunbo = db('banner')->field('id,image,url,orderby')->where(['isShow'=>1,'type'=>1])->order('orderby')->select();
        $data['lunbo'] = [];
        if(!empty($lunbo)){
            foreach($lunbo as $key => $value){
                if(strpos($value['url'],'channel_detail')){
                    $urlarr = parse_url($value['url']);
                    $urlarr = $this->convertUrlQuery($urlarr['query']);
                    $lunbo[$key]['type'] = 'channel';
                    $lunbo[$key]['id'] = $urlarr['channel_id'];
                    $lunbo[$key]['url'] = '';
                    $lunbo[$key]['remark'] = '根据id跳转到对应专栏';
                }elseif (strpos($value['url'],'Lecture/index') || strpos($value['url'],'lecture/index')){
                    $urlarr = parse_url($value['url']);
                    $urlarr = $this->convertUrlQuery($urlarr['query']);
                    $id = strpos($urlarr['id'],'P')?explode('P',$urlarr['id'])[0]:$urlarr['id'];
                    $lunbo[$key]['type'] = 'lecture';
                    $lunbo[$key]['id'] = $id;
                    $lunbo[$key]['url'] = '';
                    $lunbo[$key]['remark'] = '根据id跳转到对应课程';
                }elseif(strpos($value['url'],'eqxiu.com')){
                    $lunbo[$key]['type'] = 'url';
                    $lunbo[$key]['id'] = '0';
                    $lunbo[$key]['remark'] = '跳转到一个网页地址';
                }elseif(strpos($value['url'],'morningRegister')){
                    $lunbo[$key]['type'] = 'reg';
                    $lunbo[$key]['id'] = '0';
                    $lunbo[$key]['remark'] = '跳转到注册界面';
                }else{
                    unset($lunbo[$key]);
                }
            }
            $data['lunbo'] = array_values($lunbo);
        }
        $data['toutiao'] = $this->toutiao();
        $data['jingxuan'] = db('course')->field('id,name,clicknum,coverimg,mode,type,memberid,channel_id')->where(['isshow'=>'show','show_on_page'=>1])->order('clicknum','desc')->limit(4)->select();
        $data['jingxuan'] = $this->check_js_member_id($data['jingxuan']);
        $data['todaylive'] = db('course')->field('id,name,sub_title,coverimg,mode,type,starttime,memberid,channel_id')
            ->where(['isshow'=>'show','show_on_page'=>1])
            ->where('UNIX_TIMESTAMP(starttime) > '.strtotime(date('Ymd')))
            ->order('starttime','desc')->limit(4)->select();
        $data['todaylive'] = $this->check_js_member_id($data['todaylive']);
        /*$ranksql = "select * from live_teacherrank t inner join live_member m on t.memberid=m.id and  t.isshow=1 order by t.rank limit 8";
        $ranklist =  db()->query($ranksql);*/
        $data['daka'] = $this->get_main_daka();
        $data['mingshi'] = $this->get_mingshi();
        $yuedu = $this->get_yuedu();
        $data['mtts'] = $yuedu[0];
        $data['zblj'] = $yuedu[1];
        $data['fufei'] = db('course')->field('id,name,clicknum,coverimg,mode,cost')->where(['isshow'=>'show','show_on_page'=>1,'type'=>'pay_lecture'])->order('clicknum','desc')->limit(4)->select();
        $this->return_json(OK,$data);
    }

    /**
     * 根据时间获取问候语
     * @return string
     */
    private function get_wenhou()
    {
        $h = date('G');
        if ($h<11)      return '早上好';
        elseif ($h<13) return '中午好';
        elseif ($h<17) return '下午好';
        else           return '晚上好';
    }

    /**
     * 拆分URL后面的参数
     * @param $query
     * @return array
     */
    public function convertUrlQuery($query)
    {
        $queryParts = explode('&', $query);
        $params = array();
        foreach ($queryParts as $param) {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }
        return $params;
    }

    /**
     * 全部课程
     */
    public function all_lecture()
    {
        $limit = input('post.limit');//页码
        $type = input('post.type');//类型
        //数据验证
        $result = $this->validate(
            [
                'limit' => $limit,
                'type' => $type,
            ],
            [
                'limit' =>  'require|number|min:1',
                'type' =>  'require|in:open_lecture,pay_lecture',
            ]
        );
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }
        $leng = 20;
        $course = db('course');
        $course2 = db('course');
        $course->field('id,name,sub_title,coverimg,mode,type,cost,clicknum,starttime');
        $course->where(['isshow'=>'show','show_on_page'=>1]);
        $course2->where(['isshow'=>'show','show_on_page'=>1]);
        if($type=='open_lecture'){
            $course->where('type!="pay_lecture"');
            $course2->where('type!="pay_lecture"');
        }else{
            $course->where('type="pay_lecture"');
            $course2->where('type="pay_lecture"');
        }
        $data = $course->order('clicknum','desc')->limit($limit-1,$leng)->select();
        $count = $course2->count();
        if(empty($data)) {
            $this->return_json(E_OP_FAIL,'查询失败请重试');
        }
        $res['limit'] = $limit;
        $res['count'] = $count;
        $res['list'] = $data;
        $this->return_json(OK,$res);
    }

    /**
     * 获取名师专题
     */
    public function get_mszt()
    {
        $data = db('famous')->field('channel_id,memberid,room_id,name,img,cost,intro,intro1,intro2,js_memberid')->where('ms_order <> 0')->order('ms_order','desc')->select();
        foreach($data as $key => $value){
            $course = db('course')->field('sum(clicknum) as clicknum,count(id) as count')->where(['channel_id'=>$value['channel_id']])->find();
            $top1 = db('course')->field('name as tuijian')->where(['channel_id'=>$value['channel_id']])->order('clicknum','desc')->find();
            $data[$key]['clicknum'] = $course['clicknum'];
            $data[$key]['count'] = $course['count'];
            $data[$key]['tuijian'] = $top1['tuijian'];
        }
        $this->return_json(OK,$data);
    }

    /**
     * 获取全部行业大咖
     */
    public function get_hydk()
    {
        $data = db('famous')->field('channel_id,memberid,room_id,name,img,cost,intro,intro1,intro2,fake_clicknum as clicknum,js_memberid')->where('dk_order <> 0')->order('dk_order','desc')->select();
        $this->return_json(OK,$data);
    }


    /**
     * 获取搜索页面信息
     */
    public function get_search_info()
    {
        $data['hot'] = ['增员','高绩效团队','天雁论坛','保险','名师','营销策略'];
        $data['search_history'] =db('searchhistory')->field('id,content,time')->where(['memberid'=>$this->user['id'],'isshow'=>'yes'])->order('time','desc')->limit(10)->select();
        $this->return_json(OK,$data);
    }


    /**
     * 搜索
     */
    public function search()
    {
        $input = input('post.input');//搜索内容
        $limit = input('post.limit');//页码
        //数据验证
        $result = $this->validate(
            [
                'input' => $input,
                'limit' => $limit,
            ],
            [
                'input'  => 'require|chsAlphaNum',
                'limit' =>  'require|number|min:1',
            ]
        );
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }
        $limit = !empty($limit) ? $limit : 1;
        $length = 20;
        $list = db('course')->where("(name like '%$input%' or labels like '%$input%') and isshow='show'")->limit($limit-1, $length)->select();
        $data = [];
        if(empty($list)) {
            //$this->return_json(OK,[]);
            $res['code'] = OK;
            $res['data'] = [];
            exit(json_encode($res));
        }
        foreach ($list as $k => $v) {
            $member = db('member')->find($v['memberid']);
            $data[$k]['headimgurl'] = $member['headimg'];
            $data[$k]['img'] = $member['img'];
            $data[$k]['nickname'] = $member['name'];
            $data[$k]['cover'] = $v['coverimg'];
            $data[$k]['has_redpack'] = false;
            $data[$k]['lecture_id'] = $v['id'];
            $data[$k]['name'] = $v['name'];
            $data[$k]['cost'] = $v['cost'];
            $data[$k]['pass'] = $v['pass'];
            $data[$k]['starttime'] = $v['starttime'];
            $status = Time::timediff(strtotime($v['starttime']), time(), $v['mins']);
            if ($status != '进行中') {
                $data[$k]['current_status'] = $status ? 'ready' : 'closed';
            } else {
                $data[$k]['current_status'] = 'started';
            }
            $data[$k]['current_status_display'] = $status ? $status : '已结束';
        }
        if(empty($this->user['id'])){
            $this->user['id'] = 0;
        }
        $a = db('searchhistory')->field('id')->where(['memberid'=>$this->user['id'],'content'=>$input])->find();
        if(empty($a['id'])){
            $sdata['content'] = $input;
            $sdata['memberid'] = $this->user['id'];
            $sdata['time'] = date('Y-m-d H:i');
            db('searchhistory')->insertGetId($sdata);
        }else{
            $sdata['time'] = date('Y-m-d H:i');
            db('searchhistory')->where(['id'=>$a['id']])->update($sdata);
        }
        $this->return_json(OK,$data);
    }

    /**
     * 删除一个搜索历史记录
     */
    public function clear_search_history(){
        $id = input('post.id');
        db('searchhistory')->where(['id'=>$id])->setField('isshow','no');
        $this->get_search_info();
    }

    /**
     * 首页知识头条
     */
    public function toutiao()
    {
        $frontpage = db('frontpage')->field('id,title,url')->where(['isshow'=>'show'])->order('orderby','desc')->limit(4)->select();
        return $frontpage;
        //$this->return_json(OK,$frontpage);
    }

    /**
     * 知识头条列表页
     * @return array
     */
    public function get_toutiao_list(){
        $arr = db('frontpage')->field('id,title,descip,news_date,url')->where("isshow='show' and title != ''")->order('orderby','desc')->limit(200)->select();
        if(empty($arr)){
            $this->returns('为空');
        }
        $list = self::build_toutiao_list($arr);
        $this->return_json(OK,$list);
    }

    /**
     * 组装知识头条列表数据
     * @param $arr
     * @return array
     */
    public static function build_toutiao_list($arr)
    {
        $list = array();
        foreach ($arr as $k=>$v){
            if(strtotime($v['news_date']) == strtotime(date('Y-m-d'.'00:00:00',time()))){
                $list['today']['day'] = '今天';
                $list['today']['list'][$k+1] = $v;
            }elseif(strtotime($v['news_date']) == strtotime(date('Y-m-d'.'00:00:00',time()-3600*24))){
                $list['yesterday']['day']  = '昨天';
                $list['yesterday']['list'][$k+1] = $v;
            } else{
                $list[$v['news_date']]['day'] = $v['news_date'];
                $list[$v['news_date']]['list'][$k+1] = $v;
            }
        }
        $list =  array_values($list);
        foreach($list as $key =>$value){
            $list[$key]['list'] = array_values($value['list']);
        }
        return $list;
    }

    /**
     * 知识头条详情页
     * @return array
     */
    public function get_toutiao_detail($id = ''){

        $id = empty($id)?(int)input('get.id'):$id;
        $detail = db('frontpage')->where(['id'=>$id])->find();
        if($detail['manuscript']){
            $this->assign("manuscript", 1);
        }else{
            $this->assign("manuscript", 0);
        }
        //是否点赞
        if(!empty($this->user['id'])){
            $table = db('ask_comments');
            $upvote = $table->where("acitivity = 2 and action = 2 and memberid=".$this->user['id']. " and questionid=".$id)->find();
            $detail['upvote'] = $upvote ? 'true' : 'false';
            //是否收藏
            $collect = $table->where("acitivity = 2 and action = 3 and memberid=".$this->user['id']. " and questionid=".$id)->find();
            $detail['collect'] = $collect ? 'true' : 'false';
        }else{
            $detail['upvote'] ='false';
            $detail['collect'] ='false';
        }

        $this->return_json(OK,$detail);
    }


    /**
     * 知识头条收藏
     * @return mixed
     */
    public function toutiao_collect(){
        $id = input('post.id');
        $type = input('post.type')?input('post.type'):1;//1收藏，2取消收藏
        //数据验证
        $result = $this->validate(
            [
                'input' => $id,
                'limit' => $type,
            ],
            [
                'input'  => 'require|number',
                'limit' =>  'require|in:1,2',
            ]
        );
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }
        $action = 3;
        $table = db('ask_comments');
        $where = "acitivity = 2 and action = ".$action." and memberid=".$this->user['id']. " and questionid=".$id;
        $already = $table->where($where)->find();
        $res = [];
        if(!empty($already)){
            //$res['collect'] = 'success';
            if($type == 2){//取消收藏
                $table->where($where)->delete();
                $res['collect'] = 'cancel success';
            }else{
                $this->return_json(E_OP_FAIL,'请不要重复收藏');
            }
        }else{
           //收藏
            $already = db('frontpage')->field('id,title,descip')->where(['id'=>$id])->find();
            if(empty($already)){
                $this->returns('没有该头条');
            }
            $data = array(
                "questionid" => $id,
                "memberid" => $this->user['id'],
                "acitivity" => 2,
                "action" => $action,
                "addtime" => date("Y-m-d H:i:s",time())
            );
            $count = $table->insertGetId($data);
            if($count){
                $res['collect'] = 'success';
            }else{
                $res['collect'] = 'fail';
            }
        }
        $this->return_json(OK,$res);
        //$this->ajaxReturn($res,"JSON");
    }

    /**
     * 知识头条分享
     * @return mixed
     */
    public function toutiao_share(){
        $id = (int)input('get.id');
        $table = db('frontpage');
        $already = $table->field('id,title,descip')->where(['id'=>$id])->find();
        if(empty($already)){
            $this->return_json(E_OP_FAIL,'该头条已删除');
        }
        $already['url'] = FENXIANG_TT_URL.$id;
        $already['img'] = OSS_URL.'/Public/img/132.jpg';
        $this->return_json(OK,$already);
    }



    /**
     * 获取上一条或下一条知识头条
     */
    public function get_toutiao_next()
    {
        $id = (int)input('get.id');
        $type = (int)input('get.type');
        $arr = db('frontpage')->field('id,title,descip,news_date')->where("isshow='show' and title != ''")->order('orderby','desc')->limit(200)->select();
        if(empty($arr)){
            $this->returns('结果为空');
        }
        $resid = 0;
        if($type == 1){
            //上一个
            $reset = reset($arr);
            if($reset['id'] == $id){
                $this->return_json(E_OP_FAIL,'当前已经是第一条');
            }
            foreach($arr as $key => $value){
                if($value['id'] == $id){
                    $resid = $arr[$key-1]['id'];
                }
            }
        }else{
            //下一个
            $end = end($arr);
            if($end['id'] == $id){
                $this->return_json(E_OP_FAIL,'当前已经是最后一条');
            }
            foreach($arr as $key => $value){
                if($value['id'] == $id){
                    $resid = $arr[$key+1]['id'];
                }
            }
        }
        $this->get_toutiao_detail($resid);
    }

    /**
     * 主页的行业大咖
     * @return mixed
     */
    private function get_main_daka()
    {
        $data = db('famous')->field('memberid,channel_id,name,intro as nick,intro1,intro2,img,js_memberid')->where('dk_order <> 0 and is_main <> 0')->order('is_main','desc')->select();
        return $data;
    }

    /**
     * 主页的名师专题
     * @return mixed
     */
    private function get_mingshi()
    {
        $data = db('famous')->field('memberid,channel_id,name,intro,intro1,intro2,img,js_memberid,fake_clicknum as clicknum')->where('ms_order <> 0 and is_main <> 0')->order('is_main','desc')->select();
        return $data;
    }


    /**
     * 主页的悦读时光
     * @return array
     */
    public function get_yuedu($field = '')
    {
        if(empty($field)){
            $field = 'id,name,cover,iscopyright,orders';
        }
        $onlineBooksList = db('onlinebooks')->field($field)->where(['isshow'=>'show'])->order('orders asc,id desc')->select();
        $copyrightList = [];
        $noCopyrightList = [];
        if(empty($onlineBooksList)){
            return [0=>[],1=>[]];
        }
        foreach ($onlineBooksList as $key => $value){
            $value['iscopyright'] == 'yes' ? $copyrightList[] = $value : $noCopyrightList[] = $value;
        }
        return [$copyrightList,$noCopyrightList];
    }

    /**
     * 获取阅读悦读时光列表
     * @return array
     */
    public function get_yuedu_list()
    {
        $type = (int)input('get.type');
        $page = abs((int)input('get.page'));
        /*if(empty($page)){
            $page = 1;
        }*/
        //->page($page,10)
        $onlineBooksList = db('onlinebooks')->field('id,name,cover,iscopyright,orders,intro,listentimes')->where(['isshow'=>'show'])->order('orders asc,id desc')->select();
        $count = db('onlinebooks')->where(['isshow'=>'show'])->count('id');
        $copyrightList = [];
        $noCopyrightList = [];
        if(empty($onlineBooksList)){
            return [0=>[],1=>[]];
        }
        foreach ($onlineBooksList as $key => $value){
            $value['jie'] = $this->redis->hget('yuedu',$value['id']);
            if(empty($value['jie'])){
                $value['jie'] = db('Bookchapter')->field('count(id) as jie')->where(['bookid'=>$value['id']])->select();
                if(empty($value['jie'][0]['jie'])){
                    $value['jie'] = '10+';
                }else{
                    $value['jie'] = $value['jie'][0]['jie'];
                }
                $this->redis->hset('yuedu',$value['id'],$value['jie']);
            }
            $value['iscopyright'] == 'yes' ? $copyrightList[] = $value : $noCopyrightList[] = $value;
        }

        if($type == 1){
            $this->return_json(OK,$copyrightList,$count);
        }else{
            $this->return_json(OK,$noCopyrightList,$count);
        }
    }

    /**
     * 获取阅读悦读时光详情页
     * @return array
     */
    public function get_yuedu_detail()
    {
        $onlineBookes_model = db('Onlinebooks');
        $where = array(
            'id' => (int)input('get.id')
        );
        $field = 'id,name,intro,detail,cover,type,isshow,truncate(fee/100,2) as fee';
        $status = 0;// 辨别金钱与命运
        $info = $onlineBookes_model->where($where)->field($field)->find();

        if ($info['isshow'] == 'hide' || empty($info)) {
            $this->return_json(E_OP_FAIL,'书籍已下架!');
        }

        if($info['id'] != 9){
            $groom = $onlineBookes_model
                ->where(array('id' => array('neq', $where['id']),'isshow' => 'show'))
                ->field('id,name,intro,cover,listentimes')
                ->limit(2)
                ->order('Rand()')
                ->select();
        }else{
            $groom = $this->get_yuedu_die_data();
        }
        foreach ($groom as $key => $value){
            $groom[$key]['jie'] = $this->redis->hget('yuedu',$value['id']);
            if(empty($groom[$key]['jie'])){
                $groom[$key]['jie'] = db('Bookchapter')->field('count(id) as jie')->where(['bookid'=>$value['id']])->select();
                if(empty($groom[$key]['jie'][0]['jie'])){
                    $groom[$key]['jie'] = '10+';
                }else{
                    $groom[$key]['jie'] = $groom[$key]['jie'][0]['jie'];
                }
                $this->redis->hset('yuedu',$value['id'],$groom[$key]['jie']);
            }
        }
        $list = $this->get_book_list($info['id']);
        $this->return_json(OK,['info'=>$info,'groom'=>$groom,'list'=>$list]);
    }


    /**
     * 获取书籍章节列表
     * @param $id
     * @return mixed
     */
    public function get_book_list($id)
    {
        $where = array(
            'bc.isshow' => 'show',
            'bc.bookid' => $id,
            'bc.isdelete' => 0
        );
        $field = 'bc.id,bc.bookid,bc.name,bc.intro,bc.cover,bc.length,bc.discusstimes,bc.listentimes,bc.url,ob.type,1 as is_pay';
        /*$length = 5;
        $page = I('page') ?: 1;*/
        $list = db('Bookchapter')
            ->alias('bc')
            ->join('live_onlinebooks ob','ob.id = bc.bookid')
            ->where($where)
            ->field($field)
            ->order('bc.orderby asc,bc.id desc')
            //->page($page, $length)
            ->select();

        // 查询是否支付，前两个章节数据免费
        if ($list && $list[0]['type'] == 'pay') {
            //$_idArr = $this->_first_two_id($id);
            foreach ($list as $key => $value) {
                $list[$key]['is_pay'] = $this->_is_pay($value['bookid'], $value['id']);
            }
            $list[0]['is_pay'] = 1;
            $list[1]['is_pay'] = 1;
        }
        return $list;
    }

    /**
     * 获取前二章节ID
     * @param $bookid
     * @return mixed
     */
   /* public function _first_two_id($bookid)
    {

        $where = array(
            'bookid' => $bookid
        );
        $field = 'bc.id';

        $idArr = db('Bookchapter')
            ->alias('bc')
            ->join('onlinebooks ob','ob.id = bc.bookid')
            ->where($where)
            ->field($field)
            ->limit(2)
            ->order('bc.id asc')
            ->select();

        $result = array_column($idArr, 'id');
        return $result;
    }*/

    /**
     * 判断书籍章节是否需要支付
     * @param $arr
     * @param $bookid
     * @param $chapterid
     * @return int
     */
    protected function _is_pay($bookid, $chapterid)
    {
        $result = 0;//0支付，1否之
        /*
         * 注册用户id为2的听书直接免费
         * */

        if($bookid==2){
            return 1;
        }
        $result = db('Onlinebookpay')
            ->field('id')
            ->where(array('bookid' => $bookid, 'memberid' => $this->user['id'], 'status' => 'finish'))
            ->find();

        // 查询支付情况
        if (!empty($result)) {
            $result = 1;//已支付
        } else {
            //$result = in_array($chapterid, $arr) ? 1 : 0;
            $result = 0;//未支付
        }
        return $result;
    }

    private function get_yuedu_die_data()
    {
        $groom = array(
            '0' => array(
                'name' => '李善民教授',
                'cover' => 'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/mao_8.png',
                'audio'=> 'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/active/lishanmin.mp3',
                'intro'=> '中山大学副校长、博士生导师'
            ),
            '1' => array(
                'name' => '于绍文先生',
                'cover' => 'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/mao_6.png',
                'audio'=> 'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/active/yushaowen.mp3',
                'intro'=> '《经理人》杂志社社长、经理人传媒有限公司董事长'
            ),
            '2' => array(
                'name' => '赵福俊先生',
                'cover' => 'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/mao_3.png',
                'audio'=> 'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/active/zhaofujun.mp3',
                'intro'=> '中国平安人寿保险副总经理'
            ),
            '3' => array(
                'name' => '林力博先生',
                'cover' => 'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/mao_2.png',
                'audio'=> 'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/active/linlibo.mp3',
                'intro'=> '英国金融时报中文版《FT睿》前主编、法国《葡萄酒评论》中文版主编'
            ),
            '4' => array(
                'name' => '李宏先生',
                'cover' => 'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/mao_1.png',
                'audio'=> 'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/active/lihong.mp3',
                'intro'=> '思高方达基金服务中国公司董事长总经理'
            ),
            '5' => array(
                'name' => '秦朔博士',
                'cover' => 'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/mao_4.png',
                'audio'=> 'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/active/qinsuo.mp3',
                'intro'=> '《南风窗》前主编、《第一财经》媒体集团主编'
            ),
            '6' => array(
                'name' => '周斌博士',
                'cover' => 'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/mao_5.png',
                'audio'=> 'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/active/zhoubin.mp3',
                'intro'=> '华东师范大学风险管理与保险系系主任'
            ),
            '7' => array(
                'name' => '汪静波博士',
                'cover' => 'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/mao_7.png',
                'audio'=> 'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/active/wangjingbo.mp3',
                'intro'=> '纽交所上市的中国第一家IFA、诺亚财富CEO'
            )
        );
        return $groom;
    }

    /**
     * 关于我们
     */
    public function about()
    {
        if($this->source == 'ANDROID'){
            $banben = '1.0.4';
        }else{
            $banben = '1.0.5';
        }
        $content = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>关于我们</title></head><body style="background-color:#fa4649 ;position:relative;"><div style="width: 100%; height: auto;"><img src="'.SERVER_URL.'/public/images/about.png" style="width: 100%;"></div><div style="width: 100%; height: 5%;position:fixed;left:0;padding-bottom:10px;bottom:26%;"><p style="float: left; margin-left: 5%;margin-bottom: 1%; color: white;font-family:\'黑体\';font-size:1.2rem;">当前版本 '.$banben.'<br/></p></div><div style="background-color:white;width:100%; height: 22%; position:fixed; left:0; bottom:0;color: #fa4649; "><p style="margin: 15% auto auto 5%;font-size: 1rem;">天雁商学院会员协议 </p><p style="margin: auto auto auto 5%;font-size: 1rem;">Copyright © 2017 www.tianyan.cn ALL Rights Reserved.</p></div></body></html>';
        //echo $content;
        $result['code'] = OK;
        $result['data'] = $content;
        exit(json_encode($result));
    }

    public function xieyi()
    {
        $content = '天雁商学院会员协议

一、注册协议条款的确认和接受
为获得网络服务，申请人应当认真阅读、充分理解本《协议》中各条款，包括免除或者限制本公司责任的免责条款及对用户
的权利限制条款。请用户审慎阅读本《协议》(未成年人应在法定监护人陪同下阅读)。
同意接受本协议的全部条款,申请人应当按照应用程序页面上的提示完成全部的注册程序，否则申请人应当终止并退出申请。
本《协议》可由本公司随时更新，更新后的协议条款一旦公布即代替原来的协议条款，恕不再另行通知，用户可在本公司官
微上查阅最新版协议条款。在修改《协议》条款后，如果用户不接受修改后的条款，请立即停止使用本公司提供的网络服务
，继续使用的用户将被视为已接受了修改后的协议。

二、服务内容
1.网络服务的内容包括但不限于“天雁商学院”微信平台中定期发布的图文、音频和视频等。
2.在天雁商学院平台成功注册，即可成为天雁商学院普通会员。会员权益包括——
●随时学习天雁商学院所有的免费课程。
●免费使用天雁商学院的早安海报、节日海报和天雁头条等内容。
●免费获得天雁商学院为学员提供的营销干货内容。
●每月的天雁会员日，享受会员5折购相关产品。
3.订阅须知
●天雁商学院为会员提供的付费课程为视频或”音频+图文”形式，全年（从购买日起12个月）有效；
●天雁商学院名师栏和名师专题（即付费199元/年的订阅产品，订阅成功后一年内可使用该专栏出品的所有内容）； 
●天雁商学院所提供的是虚拟内容服务，一经订阅成功概不退款，请您理解。
4.用户理解，天雁商学院仅提供相关的网络服务，除此之外与相关网络服务有关的设备（如手机或其他与接入互联网或移动
网有关的装置）及所需的费用（如为接入互联网而支付的上网费、为使用移动网而支付的手机费）均应由用户自行负担。
5、您不得干扰我们正常地提供产品和服务，包括但不限于：
●攻击、侵入我们的服务器或使服务器过载；
●破解、修改我们提供的客户端程序；
●利用程序的漏洞和错误（Bug）破坏服务的正常进行；
●不合理地干扰或阻碍他人使用我们所提供的产品和服务等。

三、用户账号
1.经天雁商学院微信平台的注册系统完成注册程序的用户即为天雁商学院普通会员。
2.用户账号的所有权归本公司，用户完成申请注册后，用户享有使用权。
3.用户有义务保证自身账号的安全，用户利用该账号所进行的一切活动引起的任何损失或损害，由用户自行承担全部责任，
本公司不承担任何责任。如用户发现账号遭到未授权的使用或发生其他任何安全问题，应立即修改账号密码并妥善保管。因
黑客行为或用户的保管疏忽导致账号非法使用，本公司不承担任何责任。

四、隐私保护
保护用户隐私是本公司的一项基本政策，本公司保证不对外公开或向第三方提供用户的注册资料，但下列情况除外：
1.事先获得用户的书面明确授权；
2.根据有关的法律法规要求；
3.按照相关政府主管部门的要求；
4.为维护社会公众的利益；
5.为维护本公司的合法权益。

五、声明
任何单位或个人未经天雁商学院授权许可，不得以天雁商学院提供的产品和服务内容，及天雁商学院商标、文字、形象、
标识等进行营利活动，天雁商学院将保留对前述行为人追究法律责任的权利。';
        $result['code'] = OK;
        $result['data'] = $content;
        exit(json_encode($result,JSON_UNESCAPED_UNICODE));
    }
}
