<?php
namespace app\index\controller;

use think\Controller;
//use think\Input;
use Think\Exception;
use think\Validate;
use app\tools\controller\Message;
use app\tools\controller\Tools;

header('Content-Type: text/html; charset=utf8');
/**
 * 微信公众平台操作类
 */

class WeChat extends  Controller{

	private $_appid = WECHAT_GZH_APPID;
	private $_appsecret = WECHAT_GZH_APPSECRET;
	private $_token = WECHAT_TOKEN;// 公众平台请求开发者时需要标记

	//表示QRCode的类型
	const QRCODE_TYPE_TEMP = 1;
	const QRCODE_TYPE_LIMIT = 2;
	const QRCODE_TYPE_LIMIT_STR = 3;

	private $log_path = APP_PATH.'log/WeChat.log';


    /**
     * [getQRCode description]
     * @param  int|string  $content qrcode内容标识
     * @param  [type]  $file    存储为文件的地址，如果为NULL表示直接输出
     * @param  integer $type    类型
     * @param  integer $expire  如果是临时，表示其有效期
     * @return [type]           [description]
     */
    public function getQRCode($content, $file=NULL, $type=1, $expire=2592000) {
        // 获取ticket
        $ticket = $this->_getQRCodeTicket($content, $type=1, $expire=2592000);
        $url = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=$ticket";

        //	$result = $this->_requestGet($url);//此时result就是图像内容
        if ($file) {
            $command = 'curl -G  "'.$url.'"  >  '.$file;
            try{
                wlog($this->log_path,"getQRCode command is:$command");
                $res = system($command,$error);
                wlog($this->log_path,"getQRCode 下载课程二维码 $res 成功：$file");
            }catch (Exception $e){
                wlog($this->log_path,"getQRCode 下载课程二维码失败！file is:".$file);
                wlog($this->log_path,"getQRCode $e->getMessage()");
            }
            //      file_put_contents("$file", $result);
            //检查图片大小
            $size = filesize($file);
            return $size;
        } else {
            header('Content-Type: image/jpeg');
            echo $this->_requestGet($url);//此时result就是图像内容;
        }
    }

    /**
     * [getQRCodeTicket description]
     * @param $content 内容
     * @param $type qr码类型
     * @param $expire 有效期，如果是临时的类型则需要该参数
     * @return string ticket
     */
    private function _getQRCodeTicket($content, $type=1, $expire=2592000) {
        $access_token = $this->_getAccessToken();
        $data_arr = [];
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=$access_token";
        $type_list = array(
            self::QRCODE_TYPE_TEMP => 'QR_SCENE',
            self::QRCODE_TYPE_LIMIT => 'QR_LIMIT_SCENE',
            self::QRCODE_TYPE_LIMIT_STR => 'QR_LIMIT_STR_SCENE',
        );
        $action_name = $type_list[$type];
        switch ($type) {
            case self::QRCODE_TYPE_TEMP:
                // {"expire_seconds": 604800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": 123}}}
                $data_arr['expire_seconds'] = $expire;
                $data_arr['action_name'] = $action_name;
                $data_arr['action_info']['scene']['scene_id'] = $content;
                break;
            case self::QRCODE_TYPE_LIMIT:
            case self::QRCODE_TYPE_LIMIT_STR:
                $data_arr['action_name'] = $action_name;
                $data_arr['action_info']['scene']['scene_id'] = $content;
                break;
        }
        $data = json_encode($data_arr);
        $result = $this->_requestPost($url, $data);
        if (!$result) {
            return false;
        }
        //处理响应数据
        $result_obj = json_decode($result);
        if(empty($result_obj->ticket)){
            return false;
        }
        return $result_obj->ticket;
    }

    /**
     * 获取 access_tonken
     * @param string $token_file 用来存储token的临时文件
     */
    public function _getAccessToken($token_file=ROOT_PATH.'access_token.php',$reget_Token=false) {
        //var_dump(ROOT_PATH.'/access_token.php');exit;
        $data = json_decode($this->get_php_file($token_file));
        if ($data->expire_time < time()) {
            //LogController::W_H_Log("access_token 已过期，重新获取！");
            wlog($this->log_path,"_getAccessToken access_token 已过期，重新获取！");
            // 目标URL：
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->_appid}&secret={$this->_appsecret}";
            //向该URL，发送GET请求
            $result = $this->_requestGet($url);
            if (!$result) {
                wlog($this->log_path,"_getAccessToken 获取access_token 失败！");
                return false;
            }
            // 存在返回响应结果
            $result_obj = json_decode($result);
            $access_token = $result_obj->access_token;
            if ($access_token) {
                $data->expire_time = time() + 7000;
                $data->access_token = $access_token;
                $this->set_php_file($token_file, json_encode($data));
            }
        }else{
            $access_token = $data->access_token;
        }
        return $access_token;

    }

    /**
     * 推送消息到微信
     * @param $touser
     * @param $template_id
     * @param $url
     * @param $data
     * @param string $topcolor
     */
    public function doSendTempleteMsg($touser, $template_id, $url, $data, $topcolor = '#7B68EE')
    {
        wlog($this->log_path, "doSendTempleteMsg 推送消息".$touser.'|'.$template_id.'|'.$url.'|');
        return true;
        $template = array(
            'touser' => $touser,
            'template_id' => $template_id,
            'url' => $url,
            'topcolor' => $topcolor,
            'data' => $data
        );
        $json_template = json_encode($template);
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $this->_getAccessToken();
        $dataRes = $this->_requestPost($url, urldecode($json_template));
        if(empty($dataRes)){
            wlog($this->log_path, "doSendTempleteMsg 推送模板消息失败1");
            return false;
        }
        $dataRes = json_decode($dataRes,true);
        if ($dataRes['errcode'] == 0) {
            wlog($this->log_path, "doSendTempleteMsg 推送模板消息成功！");
            return true;
        } else {
            wlog($this->log_path, "doSendTempleteMsg 推送模板消息失败2");
            return false;
        }
    }

    private function set_php_file($filename, $content) {
        $fp = fopen($filename, "w");
        fwrite($fp, "<?php exit();?>" . $content);
        fclose($fp);
    }

    private function get_php_file($filename) {
        return trim(substr(file_get_contents($filename), 15));
    }

    /**
     * 发送GET请求的方法
     * @param string $url URL
     * @param bool $ssl 是否为https协议
     * @return string 响应主体Content
     */
    public function _requestGet($url, $ssl=true) {
        // curl完成
        $curl = curl_init();
        //设置curl选项
        curl_setopt($curl, CURLOPT_URL, $url);//URL
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0 FirePHP/0.7.4';
        curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);//user_agent，请求代理信息
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);//referer头，请求来源
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);//设置超时时间

        //SSL相关
        if ($ssl) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//禁用后cURL将终止从服务端进行验证
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//检查服务器SSL证书中是否存在一个公用名(common name)。
        }
        curl_setopt($curl, CURLOPT_HEADER, false);//是否处理响应头
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);//curl_exec()是否返回响应结果

        // 发出请求
        $response = curl_exec($curl);
        if (false === $response) {
            echo '<br>', curl_error($curl), '<br>';
            return false;
        }
        curl_close($curl);
        return $response;
    }

    private function _requestPost($url, $data, $ssl=true) {
        // curl完成
        $curl = curl_init();

        //设置curl选项
        curl_setopt($curl, CURLOPT_URL, $url);//URL
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '
Mozilla/5.0 (Windows NT 6.1; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0 FirePHP/0.7.4';
        curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);//user_agent，请求代理信息
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);//referer头，请求来源
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);//设置超时时间
        //SSL相关
        if ($ssl) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//禁用后cURL将终止从服务端进行验证
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//检查服务器SSL证书中是否存在一个公用名(common name)。
        }
        // 处理post相关选项
        curl_setopt($curl, CURLOPT_POST, true);// 是否为POST请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);// 处理请求数据
        // 处理响应结果
        curl_setopt($curl, CURLOPT_HEADER, false);//是否处理响应头
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);//curl_exec()是否返回响应结果

        // 发出请求
        $response = curl_exec($curl);
        if (false === $response) {
            echo '<br>', curl_error($curl), '<br>';
            return false;
        }
        curl_close($curl);
        return $response;
    }

}