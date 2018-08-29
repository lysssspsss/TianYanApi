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
        $data['daka'] = $this->get_main_daka();
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
        $course->field('id,name,sub_title,coverimg,mode,type,cost,clicknum');
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
     * 获取名师专题
     */
    public function get_mszt()
    {
        /*$data = array(
            array('channel_id'=>438,
                'name'=>'邓万勤',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_dwq.jpg',
                'cost'=>'199',
                'intro'=>'中国平安厦门分公司高级处经理',
                'intro1'=>'五星级杰出导师',
                'intro2'=>'《做一个成功的保险企业家》',
                'clicknum'=>'9863'
            ),
            array('channel_id'=>418,
                'name'=>'李彦涛',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_lyt.jpg',
                'cost'=>'199',
                'intro'=>'太平洋寿险第一总监',
                'intro1'=>'全国组织发展峰会会长',
                'intro2'=>'《让生命绽放华彩》',
                'clicknum'=>'11471'
            ),
            array('channel_id'=>413,
                'name'=>'刘影',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_ly.jpg',
                'cost'=>'199',
                'intro'=>'华夏人寿内蒙古分公司营销总监',
                'intro1'=>'内蒙古女企业家商会副会长',
                'intro2'=>'《新生代团队的基因裂变》',
                'clicknum'=>'13422'
            ),
            array('channel_id'=>415,
                'name'=>'饶志明',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_rzm.jpg',
                'cost'=>'199',
                'intro'=>'保寿险第一团队长',
                'intro1'=>'中国人民人寿四川分公司高级总监',
                'intro2'=>'《新兴团队的增员模式》',
                'clicknum'=>'13312'
            ),
//            array('id'=>412,
//                'name'=>'冯靖贻',
//                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_fjy.jpg',
//                'cost'=>'￥199/年',
//                'intro'=>'中国平安人寿东莞支公司资深业务总监',
//                'intro2'=>'全球MDRT年会中国区旗手',
//                'intro1'=>'《建双优团队 筑长青基业》',
//                'clicknum'=>'12477人关注'
//            ),
            array('channel_id'=>410,
                'name'=>'曾黎明',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_zlm.jpg',
                'cost'=>'199',
                'intro'=>'中国平安东莞分公司资深业务总监',
                'intro1'=>'阳光系列创始人',
                'intro2'=>'《如何让团队既大又强》',
                'clicknum'=>'12486'
            ),
            array('channel_id'=>404,
                'name'=>'王琨',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_wk.jpg',
                'cost'=>'199',
                'intro'=>'太平洋人寿深圳分公司总监',
                'intro1'=>'连续六年MDRT会员',
                'intro2'=>'《特色文化引领新生代团队成长》',
                'clicknum'=>'11945'
            ),
            array('channel_id'=>399,
                'name'=>'韩纲',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_hg.jpg',
                'cost'=>'199',
                'intro'=>'中国人寿浙江区域总监',
                'intro1'=>'《险商思维”做大团队》',
                'intro2'=>'《险商“生意经”》',
                'clicknum'=>'11231'
            ),
            array('channel_id'=>391,
                'name'=>'董博',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_dby.jpg',
                'cost'=>'199',
                'intro'=>'解放军管理专家',
                'intro1'=>'国际军商研究院院长',
                'intro2'=>'《向解放军学执行力》',
                'clicknum'=>'10423'
            ),
            array('channel_id'=>388,
                'name'=>'吴洪',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_wh.jpg',
                'cost'=>'199',
                'intro'=>'太平人寿四川分公司个险业务总监',
                'intro1'=>'太平人寿成都市高新支公司创始人',
                'intro2'=>'《营业部管理逻辑》',
                'clicknum'=>'11469'
            ),
            array('channel_id'=>361,
                'name'=>'陈军',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/index/e_cj.jpg',
                'cost'=>'199',
                'intro'=>'中国保险界运用国学智慧第一人',
                'intro1'=>'《用传统文化引领高绩效团队》',
                'intro2'=>'《善文化和保险营销 》',
                'clicknum'=>'13435'
            ),
            array('channel_id'=>352,
                'name'=>'操浩天',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/index/e_cht.jpg',
                'cost'=>'199',
                'intro'=>'佛山安徽商会副秘书长',
                'intro1'=>'《高端客户经营》',
                'intro2'=>'《“大保单之王”的成功奥秘》',
                'clicknum'=>'13035'
            ),
            array('channel_id'=>338,
                'name'=>'秦晶',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/index/e_qj.jpg',
                'cost'=>'199',
                'intro'=>'天一集团创始人',
                'intro1'=>'《从增员“难”说起》',
                'intro2'=>'《高端客户经营》',
                'clicknum'=>'15535'
            ),
            array('channel_id'=>328,
                'name'=>'杨小红',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_yxh2.jpg',
                'cost'=>'199',
                'intro'=>'佛山寿险名人会主席',
                'intro2'=>'',
                'intro1'=>'《直击人心的销售话术～为什么要买保险》',
                'clicknum'=>'13435'
            ),
            array('channel_id'=>381,
                'name'=>'王辰',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_wc.jpg',
                'cost'=>'199',
                'intro'=>'最具影响力的保险教育家',
                'intro1'=>'保险专业培训师',
                'intro2'=>'《如何建立高绩效团队》',
                'clicknum'=>'10862'
            ),
            array('channel_id'=>322,
                'name'=>'陈立松',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_cls.jpg',
                'cost'=>'199',
                'intro'=>'2016年中国保险十大影响力总监',
                'intro1'=>'',
                'intro2'=>'《平台出众  合伙圆梦》',
                'clicknum'=>'10735'
            ),
            array('channel_id'=>303,
                'name'=>'张玉梅',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_zym.jpg',
                'cost'=>'199',
                'intro'=>'广西新华第一总监',
                'intro1'=>'',
                'intro2'=>'《有效增员五步曲》',
                'clicknum'=>'10230'
            ),
            array('channel_id'=>295,
                'name'=>'文军',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_wj.jpg',
                'cost'=>'199',
                'intro'=>'中国共产党蓝天团队支部委员会书记 ',
                'intro1'=>'《打造卓越保险企业家》',
                'intro2'=>'《黄金习惯成就卓越人生》',
                'clicknum'=>'11050'
            ),
            array('channel_id'=>174,
                'name'=>'王博雯',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_wbw.jpg',
                'cost'=>'199',
                'intro'=>'《生命密码》保险营销学创始人',
                'intro1'=>'《开启生命密码之门》',
                'intro2'=>'《生命数字的计算方法》',
                'clicknum'=>'12251'
            ),
            array('channel_id'=>175,
                'name'=>'严冬香',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_ydx.jpg',
                'cost'=>'199',
                'intro'=>'太平洋江西卓越体系创始人',
                'intro1'=>'《如何有效进行主顾开拓》',
                'intro2'=>'《卓越体系实现组织快速裂变》',
                'clicknum'=>'11203'
            ),
            array('channel_id'=>155,
                'name'=>'安建平',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_ajp.jpg',
                'cost'=>'199',
                'intro'=>'三线城市建10000人团队的超级总监',
                'intro1'=>'《营业部的四层管理角色》',
                'intro2'=>'《如何打造一支营销铁军？》',
                'clicknum'=>'10745'
            ),
            array('channel_id'=>119,
                'name'=>'魏建宏',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_wjh.jpg',
                'cost'=>'199',
                'intro'=>'平安荆楚第一总监',
                'intro1'=>'《宏哥在美国MDRT做创说会》',
                'intro2'=>'《如何快速做大团队》',
                'clicknum'=>'10522'
            ),
            array('channel_id'=>56,
                'name'=>'夏根娣',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_xgd.jpg',
                'cost'=>'199',
                'intro'=>'太平全国高峰精英会会长',
                'intro1'=>'《主顾开拓之一招致胜》',
                'intro2'=>'《百万销售系统》',
                'clicknum'=>'8838'
            ),

            array('channel_id'=>80,
                'name'=>'林佳政',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_ljz.jpg',
                'cost'=>'199',
                'intro'=>'保险业文化行销领军人物',
                'intro1'=>'《独门销售秘籍》',
                'intro2'=>'《催眠销售法》',
                'clicknum'=>'8826'
            ),
            array('channel_id'=>19,
                'name'=>'吴稼羚',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_wjl.jpg',
                'cost'=>'199',
                'intro'=>'台湾寿险界感动力行销创始人',
                'intro1'=>'《反对问题的处理》',
                'intro2'=>'《到哪里找客户》',
                'clicknum'=>'7510'
            ),
            array('channel_id'=>103,
                'name'=>'吴晋江',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_wjj.png',
                'cost'=>'199',
                'intro'=>'第一代最具影响力的保险代理人',
                'intro1'=>'《从日本艺术慈善谈增员与绩效》',
                'intro2'=>'《如何招募培养高绩效人才？》',
                'clicknum'=>'7263'
            ),
            array('channel_id'=>154,
                'name'=>'肖珊',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_xs.jpg',
                'cost'=>'199',
                'intro'=>'CMF副主席、全球CIAM寿险博士',
                'intro1'=>'《如何一天做到MDRT的TOT》',
                'intro2'=>'《如何做大保单？》',
                'clicknum'=>'5862'
            ),
            array('channel_id'=>18,
                'name'=>'杨响华',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_yxh.jpg',
                'cost'=>'199',
                'intro'=>'保险营销畅销书第一人',
                'intro1'=>'《销售的套路》',
                'intro2'=>'《增员就是这么简单》',
                'clicknum'=>'5722'
            ),
            array('channel_id'=>118,
                'name'=>'朱旭东',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_zxd.jpg',
                'cost'=>'199',
                'intro'=>'陕西金融十大杰出青年',
                'intro1'=>'《寿险新生代增员及育成系统》',
                'intro2'=>'《西安小朱看MDRT年会》',
                'clicknum'=>'5589'
            ),
            array('channel_id'=>156,
                'name'=>'文菊田',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_wjt.jpg',
                'cost'=>'199',
                'intro'=>'中国保险年度人物、十大保险明星',
                'intro1'=>'《营业部自主经营之业务推动》',
                'intro2'=>'《营业部经理的基本职责》',
                'clicknum'=>'5263'
            ),
            array('channel_id'=>120,
                'name'=>'吕启彪',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_lqb.jpg',
                'cost'=>'199',
                'intro'=>'776天连续签单洲际纪录创造者',
                'intro1'=>'《20年个人冠军转型组织发展之路》',
                'intro2'=>'《做MDRT创造不平凡的人生》',
                'clicknum'=>'5034'
            ),
            array('channel_id'=>131,
                'name'=>'杨寅',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_yy.jpg',
                'cost'=>'199',
                'intro'=>'美国NLP大学授证高级导师',
                'intro1'=>'《NLP神奇沟通术（一）》',
                'intro2'=>'《NLP神奇沟通术（二）》',
                'clicknum'=>'4572'
            ),
            array('channel_id'=>102,
                'name'=>'王萍',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_wp.png',
                'cost'=>'199',
                'intro'=>'深圳MDRT精英会会长',
                'intro1'=>'《“3到”助你高效签单》',
                'intro2'=>'《目标市场开发》',
                'clicknum'=>'4367'
            ),
            array('channel_id'=>82,
                'name'=>'毛丹平',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_mdp.jpg',
                'cost'=>'199',
                'intro'=>'理财教母、中山大学博士',
                'intro1'=>'《保险代理人的未来》',
                'intro2'=>'《理财方程式》',
                'clicknum'=>'3862'
            ),
            array('channel_id'=>32,
                'name'=>'张迎宾',
                'img'=>'http://livehomefile.oss-cn-shenzhen.aliyuncs.com/Public/img/e_zyb.jpg',
                'cost'=>'199',
                'intro'=>'中国保险"性格行销"创始人',
                'intro1'=>'《找准性格卖保险》',
                'intro2'=>'《理财方程式》',
                'clicknum'=>'8695 '
            )

        );*/
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
        //$member = $_SESSION['CurrenMember'];
        $id = input('post.id');
        db('searchhistory')->where(['id'=>$id])->setField('isshow','no');
        $this->get_search_info();
    }

    private function get_main_daka()
    {
       /* $data = [
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
        ];*/
        $data = db('famous')->field('memberid,channel_id,name,intro as nick,img,js_memberid')->where(['is_main'=>1])->where('dk_order <> 0')->order('dk_order')->select();
        return $data;
    }


    private function get_mingshi()
    {
        $data = array(
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
