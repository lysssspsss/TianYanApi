<?php
namespace app\index\controller;
use app\tools\controller\Time;
use app\tools\controller\Tools;
use think\Controller;
use think\Request;
//use think\Input;
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
        echo 'index';exit;
    }


    /**
     * 获取首页
     */
    public function main()
    {
        $name = empty($this->user['name'])?$this->user['nickname']:$this->user['name'];
        $data['title'] = '早上好,'.$name;
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
        $data['fufei'] = db('course')->field('id,name,clicknum,coverimg,mode,cost')->where(['isshow'=>'show','show_on_page'=>1,'type'=>'pay_lecture'])->order('clicknum','desc')->limit(4)->select();
        $this->return_json(OK,$data);
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
            $res['data'] = '';
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
     * 主页的行业大咖
     * @return mixed
     */
    private function get_main_daka()
    {
        $data = db('famous')->field('memberid,channel_id,name,intro as nick,img,js_memberid')->where('dk_order <> 0 and is_main <> 0')->order('is_main','desc')->select();
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

    public function about()
    {
        $content = '<!DOCTYPE html>
                    <html>
                        <head>
                            <meta charset="UTF-8">
                            <title>关于我们</title>
                        </head>
                        <!--fa4649-->
                        <body style="background-color:#fa4649 ;position:relative;">
                            <div style="width: 100%; height: auto;">
                                <img src="'.SERVER_URL_HTTPS.'/public/images/about.png" style="width: 100%;">
                            </div>
                            <div style="width: 100%; height: 5%;position:fixed;left:0;bottom:24%;">
                                <p style="float: left; margin-left: 5%;margin-bottom: 1%; color: white;font-family:\'黑体\';font-size:2.5rem;">当前版本 1.0.0</p>
                            </div>
                            <div style="background-color:white;width:100%; height: 22%; position:fixed; left:0; bottom:0;color: #fa4649; ">
                                <p style="margin: 15% auto auto 5%;font-size: 1.8rem;">天雁商学院会员协议 </p>
                                <p style="margin: auto auto auto 5%;font-size: 1.8rem;">Copyright © 2017 www.tianyan.cn ALL Rights Reserved.</p>
                            </div>
                        </body>
                    </html>';
        echo $content;
    }
}
