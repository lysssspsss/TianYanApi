<?php
namespace app\index\controller;
use app\tools\controller\Tools;
use think\Controller;
use think\Request;
use think\Input;
use think\Db;
use think\Session;
use think\Validate;
use app\tools\controller\Time;

class Upload extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 上传文件
     * @return mixed
     */
    public function uploadfile()
    {
        return parent::upload_file();
    }


    public function www()
    {
        //header( "Content-type: image/jpeg");
        $PSize = filesize(FILE_PATH.'1.png');
        $picturedata = fread(fopen(FILE_PATH.'1.png', "r"), $PSize);
        echo $picturedata;
    }

    /**
     * 获取二进制文件流保存到本地
     */
    public function upload_binary()
    {
        /*$PSize = filesize(FILE_PATH.'1.mp3');
        $picturedata = fread(fopen(FILE_PATH.'1.mp3', "r"), $PSize);
        $content = $picturedata;*/
        $content = file_get_contents('php://input');    // 不需要php.ini设置，内存压力小
        //wlog(APP_PATH.'log/upload_binary.log',json_encode($content,JSON_UNESCAPED_UNICODE));
        $name = time().mt_rand(1000,9999);
        $path = FILE_PATH.'temp/'.$name;
        $is = file_put_contents($path, $content, true);
        if ($is) {
            $this->redis->hset('file',$name,$path);
            $this->return_json(OK, ['fid'=>$name]);
        } else {
            $this->return_json(E_OP_FAIL, '操作失败！');
        }
    }

    public function upload_oss()
    {
        $name = $_POST['fid'];
        $houzui = $_POST['houzui'];
        if(empty($name)){
            $this->return_json(E_ARGS, '参数错误f1');
        }
        $path = $this->redis->hget('file',$name);
        if(empty($path)){
            $this->return_json(E_ARGS, '参数错误f2');
        }
        $oss_path = 'Public/Uploads/Chat/app/'.$name.$houzui;
        $is = Tools::UploadFile_OSS($oss_path,$path);
        if ($is) {
            $apath = OSS_REMOTE_PATH.'/'.$oss_path;
            //$this->redis->hset('file',$name,$apath);
            $this->return_json(OK, ['path'=>$apath]);
        } else {
            $this->return_json(E_OP_FAIL, '操作失败！');
        }
    }


    /**
     * 获取二进制文件流并上传到OSS-图片
     */
    public function upload_binary_photo()
    {
        $content = file_get_contents('php://input');    // 不需要php.ini设置，内存压力小
        //header( "Content-type: image/jpeg");
        /*$PSize = filesize(FILE_PATH.'2.png');
        $picturedata = fread(fopen(FILE_PATH.'2.png', "r"), $PSize);
        $content = $picturedata;*/
        $name = time().mt_rand(1000,9999);
        $path = FILE_PATH.'temp/'.$name;
        $handle=finfo_open(FILEINFO_MIME_TYPE);//This function opens a magic database and returns its resource.
        file_put_contents($path, $content, true);
        $fileInfo=finfo_file($handle,$path);// Return information about a file
        finfo_close($handle);
        switch ($fileInfo){
            case 'image/jpeg':$houzui = '.jpg';break;
            case 'image/png':$houzui = '.png';break;
            case 'image/bmp':$houzui = '.bmp';break;
            default:$houzui = '.jpg';
        }
        $oss_path = 'Public/Uploads/Chat/app/'.$name.$houzui;
        $is = Tools::UploadFile_OSS($oss_path,$path);
        if ($is) {
            $this->return_json(OK, ['path'=>OSS_REMOTE_PATH.'/'.$oss_path]);
        } else {
            $this->return_json(E_OP_FAIL, '操作失败！');
        }
    }


    /**
     * 获取二进制文件流并上传到OSS-视频
     */
    public function upload_binary_video()
    {
        $content = file_get_contents('php://input');    // 不需要php.ini设置，内存压力小
        $name = time().mt_rand(1000,9999).'.mp4';
        $path = FILE_PATH.'temp/'.$name;
        file_put_contents($path, $content, true);
        $oss_path = 'Public/Uploads/Chat/app/'.$name;
        $is = Tools::UploadFile_OSS($oss_path,$path);
        if ($is) {
            $this->return_json(OK, ['path'=>OSS_REMOTE_PATH.'/'.$oss_path]);
        } else {
            $this->return_json(E_OP_FAIL, '操作失败！');
        }
    }

    /**
     * 获取二进制文件流并上传到OSS-语音
     */
    public function upload_binary_voice()
    {
        $PSize = filesize(FILE_PATH.'1.mp3');
        $picturedata = fread(fopen(FILE_PATH.'1.mp3', "r"), $PSize);
        $content = $picturedata;

        //$content = file_get_contents('php://input');    // 不需要php.ini设置，内存压力小
        $name = time().mt_rand(1000,9999).'.mp3';
        $path = FILE_PATH.'temp/'.$name;
        file_put_contents($path, $content, true);
        $oss_path = 'Public/Uploads/Chat/app/'.$name;
        $is = Tools::UploadFile_OSS($oss_path,$path);
        if ($is) {
            $this->return_json(OK, ['path'=>OSS_REMOTE_PATH.'/'.$oss_path]);
        } else {
            $this->return_json(E_OP_FAIL, '操作失败！');
        }
    }
}