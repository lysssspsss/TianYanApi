<?php
namespace app\index\controller;
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
        /*$q = $_REQUEST['q'];
        LogController::W_H_Log("q is :" . $q);
        $type = $_REQUEST['type'];
        @$p = @$_REQUEST['p'];
        $count = 20;
        $p = $p ? $p : 1;
        LogController::W_H_Log("type is:$type");
        $data = array();
        if ($type == 'user') {
            $list = M('home')->where("name like '%$q%'")->limit(($p - 1) * $count, $count)->select();
            foreach ($list as $k => $v) {
                $member = M('member')->find($v['memberid']);
                $v['member'] = $member;
                $data[$k] = $v;
            }
            $display = "roomlist";
        } else{
            if ($type=='lecture'){
                $display = "lecturelist";
                $list = M('course')->where("(name like '%$q%' or labels like '%$q%') and isshow='show'")->limit(($p - 1) * $count, $count)->select();
            }else{
                $display = "lecturelistlabel";
                $list = M('course')->where("labels like '%$q%' and isshow='show'")->limit(($p - 1) * $count, $count)->select();
            }
            foreach ($list as $k => $v) {
                $member = M('member')->find($v['memberid']);
                $data[$k]['account']['headimgurl'] = $member['headimg'];
                $data[$k]['account']['img'] = $member['img'];
                $data[$k]['account']['nickname'] = $member['name'];
                $data[$k]['cover'] = $v['coverimg'];
                $data[$k]['has_redpack'] = false;
                $data[$k]['lecid'] = $v['id'];
                $data[$k]['name'] = $v['name'];
                $data[$k]['lecture_url'] = "index.php/Home/Lecture/index?id=" . $v['id'];
                $data[$k]['need_money'] = $v['cost'] ? true : false;
                $data[$k]['need_password'] = $v['pass'] ? true : false;
                $data[$k]['start_time'] = $v['starttime'];
                $status = TimeController::timediff(strtotime($v['starttime']), time(), $v['mins']);
                if ($status != '进行中') {
                    $data[$k]['current_status'] = $status ? 'ready' : 'closed';
                } else {
                    $data[$k]['current_status'] = 'started';
                }
                $data[$k]['current_status_display'] = $status ? $status : '已结束';
            }

        }
        $member = $_SESSION['CurrenMember'];
        $sdata['content'] = $q;
        $sdata['memberid'] = $member['id'];
        $sdata['time'] = date('Y-m-d H:i');
        M('searchhistory')->add($sdata);
        if (count($list, 0) > $count) {
            $this->assign("last", true);
        } else {
            $this->assign("last", false);
        }
        $this->assign("list", $data);
        $this->assign("p", $p);
        $this->assign("q", $q);
        $this->assign("type", $type);
        $this->display($display);*/
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
