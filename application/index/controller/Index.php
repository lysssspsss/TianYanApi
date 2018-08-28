<?php
namespace app\index\controller;
use app\tools\controller\Time;
use app\tools\controller\Tools;
use think\Controller;
use think\Request;
use think\Input;
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


    /**
     * 获取首页
     */
    public function main()
    {
        $name = empty($this->user['name'])?$this->user['nickname']:$this->user['name'];
        $data['title'] = '早上好,'.$name;
        $data['lunbo'] = db('banner')->field('id,image,url,orderby')->where(['isShow'=>1,'type'=>4])->order('orderby')->select();
        $data['jingxuan'] = db('course')->field('id,name,clicknum,coverimg,mode,type')->where(['isshow'=>'show','show_on_page'=>1])->order('clicknum','desc')->limit(4)->select();
        $data['todaylive'] = db('course')->field('id,name,sub_title,coverimg,mode,type,starttime')
            ->where(['isshow'=>'show','show_on_page'=>1])
            ->where('UNIX_TIMESTAMP(starttime)'>strtotime(date('Ymd')))
            ->order('starttime','desc')->limit(4)->select();
        /*$ranksql = "select * from live_teacherrank t inner join live_member m on t.memberid=m.id and  t.isshow=1 order by t.rank limit 8";
        $ranklist =  db()->query($ranksql);*/
        $data['daka'] = $this->get_daka();
        $data['mingshi'] = $this->get_mingshi();
        $data['fufei'] = db('course')->field('id,name,clicknum,coverimg,mode,cost')->where(['isshow'=>'show','show_on_page'=>1,'type'=>'pay_lecture'])->order('clicknum','desc')->limit(4)->select();
        $this->return_json(OK,$data);
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
        $course->field('id,name,sub_title,coverimg,mode,type,cost');
        $course->where(['isshow'=>'show','show_on_page'=>1]);
        if($type=='open_lecture'){
            $course->where('type!="pay_lecture"');
        }else{
            $course->where('type="pay_lecture"');
        }
        $data = $course->order('clicknum','desc')->limit($limit-1,$leng)->select();
        if(empty($data)) {
            $this->return_json(E_OP_FAIL,'查询失败请重试');
        }
        $this->return_json(OK,$data);
    }

    /**
     * 获取行业大咖
     */
    public function get_hydk()
    {

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
            $this->return_json(OK,[]);
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

        $sdata['content'] = $input;
        $sdata['memberid'] = $this->user['id'];
        $sdata['time'] = date('Y-m-d H:i');
        db('searchhistory')->insertGetId($sdata);
        $this->return_json(OK,$data);
    }

    /**
     * 删除一个搜索历史记录
     */
    public function clear_search_history(){
        //$member = $_SESSION['CurrenMember'];
        $id = input('post.id');
        db('searchhistory')->where(['id'=>$id])->setField('isshow','no');
        $this->get_search_info();
    }

    private function get_daka()
    {
        $data = [
            array(
                'memberid'=>294,
                'channel_id'=>135,
                'name'=>'天雁CEO聂家艳',
                'nick'=>'玫瑰有约栏目创始人',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_njy.jpg',
            ),
            array(
                'memberid'=>22069,
                'channel_id'=>173,
                'name'=>'赖素免',
                'nick'=>'DLT保险创价系统创始人',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_lst.jpg',
            ),
            array(
                'memberid'=>294,
                'channel_id'=>85,
                'name'=>'游森然',
                'nick'=>'金诚同达律师事务所律师',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_ysr.jpg',
            ),
            array(
                'memberid'=>294,
                'channel_id'=>439,
                'name'=>'蒲明玉',
                'nick'=>'中国平安“钻石铁人”',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_pmy.jpg',
            ),
        ];
        return $data;
    }


    private function get_mingshi()
    {
        $data = array(
//            array('id'=>413,
//                  'name'=>'刘影专栏',
//                  'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_ly.jpg',
//                  'cost'=>'￥199/年',
//                  'intro'=>'华夏人寿内蒙古分公司营销总监',
//                  'intro2'=>'内蒙古女企业家商会副会长',
//                  'intro1'=>'《新生代团队的基因裂变》',
//                  'clicknum'=>'13422人关注'
//              ),
//            array('id'=>415,
//                'name'=>'饶志明专栏',
//                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_rzm.jpg',
//                'cost'=>'￥199/年',
//                'intro'=>'保寿险第一团队长',
//                'intro2'=>'中国人民人寿四川分公司高级总监',
//                'intro1'=>'《新兴团队的增员模式》',
//                'clicknum'=>'13312人关注'
//            ),
//            array('id'=>412,
//                'name'=>'冯靖贻专栏',
//                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_fjy.jpg',
//                'cost'=>'￥199/年',
//                'intro'=>'中国平安人寿东莞支公司资深业务总监',
//                'intro2'=>'全球MDRT年会中国区旗手',
//                'intro1'=>'《建双优团队 筑长青基业》',
//                'clicknum'=>'12477人关注'
//            ),
            array(
                'channel_id'=>155,
                'name'=>'安建平专栏',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_ajp.jpg',
                'cost'=>'￥199/年',
                'intro'=>'三线城市建10000人团队的超级总监',
                'intro2'=>'《营业部的四层管理角色》',
                'intro1'=>'《如何打造一支营销铁军？》',
                'clicknum'=>'10745人关注'
            ),
            array(
                'channel_id'=>19,
                'name'=>'吴稼羚专栏',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_wjl.jpg',
                'cost'=>'￥199/年',
                'intro'=>'台湾寿险界感动力行销创始人',
                'intro1'=>'《反对问题的处理》',
                'intro2'=>'《到哪里找客户》',
                'clicknum'=>'7510人关注'
            ),
            array(
                'channel_id'=>119,
                'name'=>'魏建宏专栏',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_wjh.jpg',
                'cost'=>'￥199/年',
                'intro'=>'平安荆楚第一总监',
                'intro1'=>'《宏哥在美国MDRT做创说会》',
                'intro2'=>'《如何快速做大团队》',
                'clicknum'=>'10522人关注'
            ),
            array(
                'channel_id'=>32,
                'name'=>'张迎宾专栏',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_zyb.jpg',
                'cost'=>'￥199/年',
                'intro'=>'中国保险"性格行销"创始人',
                'intro1'=>'《找准性格卖保险》',
                'intro2'=>'《如何快速做大团队》',
                'clicknum'=>'8695人关注'
            ),
            array(
                'channel_id'=>381,
                'name'=>'王辰专栏',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_wc.jpg',
                'cost'=>'￥199/年',
                'intro'=>'最具影响力的保险教育家',
                'intro1'=>'保险专业培训师',
                'intro2'=>'《如何建立高绩效团队》',
                'clicknum'=>'10862人关注'
            ),
//            array('id'=>154,
//                'name'=>'肖珊专栏',
//                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_xs.jpg',
//                'cost'=>'￥199/年',
//                'intro'=>'CMF副主席、全球CIAM寿险博士',
//                'intro1'=>'《如何一天做到MDRT的TOT》',
//                'intro2'=>'《如何做大保单？》',
//                'clicknum'=>'5862人关注'
//            ),
            array(
                'channel_id'=>174,
                'name'=>'王博雯专栏',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_wbw.jpg',
                'cost'=>'￥199/10节',
                'intro'=>'《生命密码》保险营销学创始人',
                'intro1'=>'《开启生命密码之门》',
                'intro2'=>'《生命数字的计算方法》',
                'clicknum'=>'12251人关注'
            ),
            array(
                'channel_id'=>103,
                'name'=>'吴晋江专栏',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_wjj.png',
                'cost'=>'￥199/年',
                'intro'=>'第一代最具影响力的保险代理人',
                'intro1'=>'《从日本艺术慈善谈增员与绩效》',
                'intro2'=>'《如何招募培养高绩效人才？》',
                'clicknum'=>'7263人关注'
            )
        );
        return $data;
    }
}
