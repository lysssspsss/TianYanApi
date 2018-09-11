<?php
namespace app\tools\controller;
require EXTEND_PATH . 'oss/autoload.php';
use think\Controller;
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
            wlog($log_path,"推送数据给前台用户参数不完整！");
            return;
        }
        $post_data = array(
            //'type' => 'publish', //本地测试
            'type' => 'ty_publish',
            'content' => $content,
            'to' => $to_uid,
            'fromUserId'=>$publisher_id //本地测试时注释
        );
        $return = self::curlPost($push_api_url,[],$post_data);
        wlog($log_path,"推送数据给前台用户res：".json_encode($return));
        wlog($log_path,"推送数据给前台用户push_api_url：".$push_api_url);
        wlog($log_path,"推送数据给前台用户to_uid：".$to_uid);
        wlog($log_path,"推送数据给前台用户content：".$content."\n");
        //return $return;
        //var_export($return);
    }


    public static function curlPost($url,$header,$data){
        try{
            $ch = curl_init();
            if (substr($url, 0, 5) == 'https') {
                // 跳过证书检查
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                // 从证书中检查SSL加密算法是否存在
                // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $url);// 设置请求的url
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);// 设置请求的HTTP Header
            // 设置允许查看请求头信息
            // curl_setopt($ch,CURLINFO_HEADER_OUT,true);
            curl_setopt($ch, CURLOPT_POST, true);// 请求方式是POST
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));// 设置发送的data
            $response = curl_exec($ch);
            // 查看请求头信息
            // dump(curl_getinfo($ch,CURLINFO_HEADER_OUT));
            if ($error = curl_error($ch)) {
                // 如果发生错误返回错误信息
                curl_close($ch);
                $ret=['status'=>false,'msg'=>$error];
                return $ret;
            } else {
                // 如果发生正确则返回response
                curl_close($ch);
                $ret=['status'=>true,'msg'=>$response];
                return $ret;
            }
        }catch (\Exception $exception){
            $ret=['status'=>false,'msg'=>$exception->getMessage()];
            return $ret;
        }
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


    public static function isout($addtime,$h){
        if($addtime){
            $filetime = strtotime($addtime);
            $now = time();
            //计算天数
            $timediff = $now-$filetime;
            $days = intval($timediff/86400);
            /*LogController::W_H_Log("days is :".$days);
            LogController::W_H_Log("h is :".$h);*/
            if($days > $h){
                return false;
            }else{
                return true;
            }
        }
    }

    /**
     * 上传文件到OSS
     * @param $object 远程文件名
     * @param $content 本地文件
     * @return int
     * @throws \OSS\Core\OssException
     */
    public static function UploadFile_OSS($object,$content){
        $accessKeyId = OSS_ACCESS_KEY_ID;
        $accessKeySecret = OSS_ACCESS_KEY_SECRET;
        $endpoint = OSS_END_POINT;
        $bucket = OSS_BUCKET;
        $log_path = APP_PATH.'log/uploadFile.log';
        if (!isset($object)){
            wlog($log_path,"上传OSS,缺少参数Object!");
            return false;
        }
        if (!isset($content)){
            wlog($log_path,"上传OSS,缺少参数content!");
            return false;
        }
        try {
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $ossClient->uploadFile($bucket, $object, $content);
            wlog($log_path,"上传成功OSS,$object->$content");
            return true;
        } catch (OssException $e) {
            wlog($log_path,"上传失败OSS:$object->$content");
            wlog($log_path,"uploadfile exception:".$e->getTraceAsString());
            return false;
        }catch (Exception $e){
            wlog($log_path,"上传失败OSS:".$e->getTraceAsString());
            return false;
        }
    }

    public static function  getVideoCover($file,$time=1,$name) {
        $log_path = APP_PATH.'log/ffmpeg.log';
        if(empty($time))$time = '1';//默认截取第一秒第一帧
        //$str = "ffmpeg -i ".$file." -y -f mjpeg -ss 3 -t ".$time." -s 320x240 ".$name;
        $str = "ffmpeg -i ".$file." -y -f mjpeg -ss 1 -t ".$time." -s 320x240 ".$name;
        try{
            $result = system($str);
        }catch (Exception $e){
            wlog($log_path,"视频截图失败！filename is:".$file.' || '.$e->getMessage());
            return false;
        }
        return true;
    }

    public static function  get_mp3($p_amr,$p_mp3) {
        $log_path = APP_PATH.'log/ffmpeg.log';
        //使用ffmpeg 将amr转成mp3
        $command = "ffmpeg -i  $p_amr -ac 2 -ar 48000 -ab 128 -vol 300 $p_mp3";
        try{
            $res = system($command,$error);
        }catch (Exception $e){
            wlog($log_path,"音频格式转换失败！filename is:".$p_amr.' || '.$e->getMessage());
            return false;
        }
        return true;
    }

    public static function  get_mp4($p_amr,$p_mp4) {
        $log_path = APP_PATH.'log/ffmpeg.log';
        $command = "ffmpeg -i $p_amr -codec copy $p_mp4";
        try{
            $res = system($command,$error);
        }catch (Exception $e){
            wlog($log_path,"视频格式转换失败！filename is:".$p_amr.' || '.$e->getMessage());
            return false;
        }
        return true;
    }




}




