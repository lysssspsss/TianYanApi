<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use think\Db;
use think\Session;
use think\Validate;


class Cash extends Base
{
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * @param $memberid
     * @return array
     * 返回数组
     */
    public static function memberEarnings_array($memberid){
        $result = array();
        if ($memberid){
            //先查该用户拥有的专栏
            $channel_list = db('channel')->where("(memberid=".$memberid." and (lecturer='' or lecturer is null)) or (lecturer <> '' and lecturer=".$memberid.") and category='channel'")->select();
            $course_pay = 0;
            $course_play = 0;
            $channel_pay = 0;
            foreach ($channel_list as $k=>$v){
                $tid[] = $v['id'];
                //1、专栏课程收益
                $course_sql = "select round(sum(n.fee),2) as sums from live_coursepay p inner join live_earns n on p.out_trade_no=n.out_trade_no and p.courseid in (select c.id from live_course c where c.channel_id=".$v['id'].") and n.remarks is null and p.status='finish'";
                $course_pay_t = db()->query($course_sql);
                $course_pay += $course_pay_t[0]['sums'];

                //2、专栏课程打赏收益
                $course_pay_sql = "select round(sum(n.fee),2) as sums from live_earns n where n.lectureid in (select c.id from live_course c where c.channel_id=".$v['id'].") and n.type='play' and n.status='finish' and n.memberid=".$memberid;
                $course_play_t = db()->query($course_pay_sql);
                $course_play += $course_play_t[0]['sums'];

                //3、专栏收益
                $channel_pay_sql = "select round(sum(p.fee/100),2) as sums from live_channelpay p inner join live_earns n on p.out_trade_no=n.out_trade_no and p.channelid=".$v['id']." and n.remarks is null and p.status='finish'";
                $channel_pay_t = db()->query($channel_pay_sql);
                $channel_pay += $channel_pay_t[0]['sums'];
            }


            //4、推广收益及未加入专栏的课程收益
            $course_sql_1 = "select round(sum(n.fee),2) as sums from live_coursepay p inner join live_earns n on p.out_trade_no=n.out_trade_no and p.courseid in (select c.id from live_course c where c.channel_id=0 and c.memberid=".$memberid.") and n.remarks is null and p.status='finish'";
            $course_pay1 = db()->query($course_sql_1);
            $course_pay1 = $course_pay1[0]['sums'];
            $course_pay_sql_1 = "select round(sum(n.fee),2) as sums from live_earns n where n.lectureid in (select c.id from live_course c where c.channel_id=0 and c.memberid=".$memberid.") and n.type='play' and n.status='finish'and n.memberid=".$memberid;
            $course_play1 = db()->query($course_pay_sql_1);
            $course_play1 = $course_play1[0]['sums'];
            $popular_sql = " select round(sum(n.fee),2) as sums from live_earns n where memberid =".$memberid." and remarks='分销推广'";
            $popular = db()->query($popular_sql);
            $popular = $popular[0]['sums'];
            //5、作为嘉宾获得的打赏
            if (isset($tid) && !empty($tid)){
                $invited_sql = "select round(sum(n.fee),2) as sums from live_earns n where n.lectureid in (select i.courseid from live_invete i inner join live_course c on i.courseid=c.id and c.channel_id not in (".implode(',',$tid).") and i.beinviteid=".$memberid." ) and n.type='play' and n.status='finish' and n.memberid=".$memberid;
                /*                $invited_sql = "select round(sum(n.fee),2) as sums from live_earns n where n.lectureid in (select i.courseid from live_invete i inner join live_course c on i.courseid=c.id and c.channel_id not in (".implode(',',$tid).") and i.beinviteid=".$memberid." and i.invitetype='嘉宾') and n.type='play' and n.status='finish' and n.memberid=".$memberid;*/
            }else{
                $invited_sql = "select round(sum(n.fee),2) as sums from live_earns n where n.lectureid in (select i.courseid from live_invete i inner join live_course c on i.courseid=c.id  and i.beinviteid=".$memberid.") and n.type='play' and n.status='finish' and n.memberid=".$memberid;
            }
            $course_invited_sql = db()->query($invited_sql);
            $course_invited_sql = $course_invited_sql[0]['sums'];

            //6、用户充值的金额
            $recharge_sql = " select round(sum(fee),2) as sums from live_earns where memberid =" . $memberid . " and type='recharge' and status='finish'";
            $recharge = db()->query($recharge_sql);
            $recharge = $recharge[0]['sums'];

            $result["popular"] = $popular;
            $result["course_play1"] = $course_play1;
            $result["course_play"] = $course_play+$course_invited_sql;
            $result["course_pay1"] = $course_pay1;
            $result["channel_pay"] = $channel_pay;
            $result["course_pay"] = $course_pay;
            $result["recharge"] = $recharge;
            return $result;
        }else{
            return $result;
        }
    }


    /**
     * @param $memberid
     * 动态计算用户收益
     */
    public static function memberEarnings($memberid){
        if ($memberid){
            //先查该用户拥有的专栏
            $channel_list = db('channel')->where("memberid=".$memberid." or lecturer =".$memberid." and category='channel'")->select();
            $course_pay = 0;
            $course_play = 0;
            $channel_pay = 0;
            foreach ($channel_list as $k => $v) {
                $tid[] = $v['id'];
                //1、专栏课程收益
                $course_sql = "select round(sum(n.fee),2) as sums from live_coursepay p inner join live_earns n on p.out_trade_no=n.out_trade_no and p.courseid in (select c.id from live_course c where c.channel_id=" . $v['id'] . ") and n.remarks is null and p.status='finish'";
                $course_pay_t = db()->query($course_sql);
                $course_pay += $course_pay_t[0]['sums'];
                //2、专栏课程打赏收益
                $course_pay_sql = "select round(sum(n.fee),2) as sums from live_earns n where n.lectureid in (select c.id from live_course c where c.channel_id=" . $v['id'] . ") and n.type='play' and n.status='finish' and n.memberid=" . $memberid;
                $course_play_t = db()->query($course_pay_sql);
                $course_play += $course_play_t[0]['sums'];
                //3、专栏收益 需要考虑集合买专栏赠送的情况
                $channel_pay_sql = "select round(sum(p.fee/100),2) as sums from live_channelpay p inner join live_earns n on p.out_trade_no=n.out_trade_no and p.channelid=" . $v['id'] . " and n.remarks is null and p.status='finish'";
                $channel_pay_t = db()->query($channel_pay_sql);
                $channel_pay += $channel_pay_t[0]['sums'];
            }
            //4、推广收益及未加入专栏的课程收益
            $course_sql_1 = "select round(sum(n.fee),2) as sums from live_coursepay p inner join live_earns n on p.out_trade_no=n.out_trade_no and p.courseid in (select c.id from live_course c where c.channel_id=0 and c.memberid=" . $memberid . ") and n.remarks is null and p.status='finish'";
            $course_pay1 = db()->query($course_sql_1);
            $course_pay1 = $course_pay1[0]['sums'];
            $course_pay_sql_1 = "select round(sum(n.fee),2) as sums from live_earns n where n.lectureid in (select c.id from live_course c where c.channel_id=0 and c.memberid=" . $memberid . ") and n.type='play' and n.status='finish'and n.memberid=" . $memberid;
            $course_play1 = db()->query($course_pay_sql_1);
            $course_play1 = $course_play1[0]['sums'];
            //5、作为嘉宾获得的打赏
            if (isset($tid) && !empty($tid)){
                $invited_sql = "select round(sum(n.fee),2) as sums from live_earns n where n.lectureid in (select i.courseid from live_invete i inner join live_course c on i.courseid=c.id and c.channel_id not in (".implode(',',$tid).") and i.beinviteid=".$memberid.") and n.type='play' and n.status='finish' and n.memberid=".$memberid;
            }else{
                $invited_sql = "select round(sum(n.fee),2) as sums from live_earns n where n.lectureid in (select i.courseid from live_invete i inner join live_course c on i.courseid=c.id  and i.beinviteid=".$memberid." ) and n.type='play' and n.status='finish' and n.memberid=".$memberid;
            }
            $course_invited_sql = db()->query($invited_sql);
            $course_invited_sql = $course_invited_sql[0]['sums'];
            $popular_sql = " select round(sum(n.fee),2) as sums from live_earns n where memberid =" . $memberid . " and remarks='分销推广'";
            $popular = db()->query($popular_sql);
            $popular = $popular[0]['sums'];

            //6、用户充值的金额
           /* $recharge_sql = " select round(sum(fee),2) as sums from live_earns where memberid =" . $memberid . " and type='recharge' and status='finish'";
            $recharge = db()->query($recharge_sql);
            $recharge = $recharge[0]['sums'];*/

            $sum = $popular + $course_play1 + $course_pay1 + ($channel_pay * 0.5) + $course_play + $course_pay + $course_invited_sql; //+ $recharge;
            if ($memberid==17176){
                $sum += 32.8;
            }
            return $sum>0?$sum:0;

        }else{
            return 0;
        }
    }
}