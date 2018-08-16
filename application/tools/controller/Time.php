<?php
namespace app\tools\controller;
use think\Controller;
use think\Input;


class Time extends Controller
{
    public  static  function msecdate($tag, $time)
    {
        $a = substr($time,0,10);
        $b = substr($time,10);
        $date = date($tag,$a);
        return $date;
    }

    public static  function timediff( $begin_time, $end_time ,$lecmins)
    {
        if ( $begin_time > $end_time ) {
            $starttime = $begin_time;
            $endtime = $end_time;
        } else {
            //计算天数
            $timediff = $end_time-$begin_time;
            $days = intval($timediff/86400);
            //计算小时数
            $remain = $timediff%86400;
            $hours = intval($remain/3600);
            //计算分钟数
            $remain = $remain%3600;
            $mins = intval($remain/60);

            $sum = $days*24*60 + $hours*60 + $mins;
            if($lecmins > $sum){
                return "进行中";
            }else{
                return null;
            }
        }
        $timediff =  $starttime - $endtime;
        $days = intval( $timediff / 86400 );
        if($days > 0){
            if ($days == 1){
                return "明天";
            }else{
                    return $days."天后";
            }
        }
        $remain = $timediff % 86400;
        $hours = intval( $remain / 3600 );
        if($hours > 0){
                return $hours."小时后";
        }
        $remain = $remain % 3600;
        $mins = intval( $remain / 60 );
        if($mins > 0){
            return $mins."分钟后";
        }
        $secs = $remain % 60;
        if($secs > 0){
            return $secs."秒后";
        }
    }
    public static  function getdistancetime( $begin_time, $end_time ,$lecmins)
    {
        if ( $begin_time > $end_time ) {
            $starttime = $begin_time;
            $endtime = $end_time;
        } else {
            //计算天数
            $timediff = $end_time-$begin_time;
            $days = intval($timediff/86400);
            //计算小时数
            $remain = $timediff%86400;
            $hours = intval($remain/3600);
            //计算分钟数
            $remain = $remain%3600;
            $mins = intval($remain/60);

            $sum = $days*24*60 + $hours*60 + $mins;
            if($lecmins > $sum){
                return "进行中";
            }else{
                return null;
            }
        }
        $timediff =  $starttime - $endtime;
        $days = intval( $timediff / 86400 );
        if($days > 0){
            if ($days == 1){
                return "1天";
            }else{
                    return $days."天";
            }
        }
        $remain = $timediff % 86400;
        $hours = intval( $remain / 3600 );
        if($hours > 0){
                return $hours."小时";
        }
        $remain = $remain % 3600;
        $mins = intval( $remain / 60 );
        if($mins > 0){
            return $mins."分钟";
        }
        $secs = $remain % 60;
        if($secs > 0){
            return $secs."秒";
        }
    }


    /**
     * @param $timestr
     * 返回距离当前时间的小时数
     */
    public static function getdiffdays($timestr){
        if ($timestr){
            $timediff =  time() - strtotime($timestr);
            $hours = intval( $timediff / 3600 );
            return $hours;
        }
    }

}




