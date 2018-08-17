<?php
namespace app\tools\controller;
require EXTEND_PATH . 'oss/autoload.php';
use think\Controller;
use think\Input;
use Think\Exception;
use OSS\OssClient;


class Tools extends Controller
{
    /**
     * 通过后台API推送数据给前台
     */
    public static function publish_msg($publisher_id,$to_uid,$push_api_url,$content){
        // $to_uid 指明给谁推送，为空表示向所有在线用户推送
        // $push_api_url 推送的url地址，上线时改成自己的服务器地址
        $log_path = APP_PATH.'log/publish_msg.log';
        if (!($to_uid&&$push_api_url&&$content)){
            //LogController::W_A_Log("推送数据给前台用户参数不完整！");
            wlog($log_path,"推送数据给前台用户参数不完整！");
            return;
        }
        $post_data = array(
            'type' => 'ty_publish',
            'content' => $content,
            'to' => $to_uid,
            'fromUserId'=>$publisher_id
        );
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $push_api_url );
        curl_setopt ( $ch, CURLOPT_POST, 1 );
        curl_setopt ( $ch, CURLOPT_HEADER, 0 );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        //curl_setopt ( $ch, CURLOPT_TIMEOUT, 10 );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_data );
        $return = curl_exec ( $ch );
        curl_close ( $ch );
        wlog($log_path,"推送数据给前台用户res：".$return);
        wlog($log_path,"推送数据给前台用户push_api_url：".$push_api_url);
        wlog($log_path,"推送数据给前台用户to_uid：".$to_uid);
        wlog($log_path,"推送数据给前台用户content：".$content);
        //return $return;
        //var_export($return);
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


    /**
     * 上传文件到OSS
     * @param $object 远程文件名
     * @param $content 本地文件
     * @return int
     * @throws \OSS\Core\OssException
     */
    public static function uploadFile($object,$content){
        $accessKeyId = OSS_ACCESS_KEY_ID;
        $accessKeySecret = OSS_ACCESS_KEY_SECRET;
        $endpoint = OSS_END_POINT;
        $bucket = OSS_BUCKET;
        $log_path = APP_PATH.'log/uploadFile.log';
        if (!isset($object)){
            wlog($log_path,"上传OSS,缺少参数Object!");
            return 1;
        }
        if (!isset($content)){
            wlog($log_path,"上传OSS,缺少参数content!");
            return 1;
        }
        try {
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $ossClient->uploadFile($bucket, $object, $content);
            wlog($log_path,"上传成功OSS,$object->$content");
            return 0;
        } catch (OssException $e) {
            wlog($log_path,"上传失败OSS:$object->$content");
            wlog($log_path,"uploadfile exception:".$e->getTraceAsString());
            return 1;
        }catch (Exception $e){
            wlog($log_path,"上传失败OSS:".$e->getTraceAsString());
            return 1;
        }
    }
}




