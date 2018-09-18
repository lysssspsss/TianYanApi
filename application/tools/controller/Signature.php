<?php
namespace app\tools\controller;
use think\Controller;
use Think\Exception;

/**
 * 阿里云签名类
 * Class Signature
 * @package app\tools\controller
 */
class Signature
{
    public $data;
   /* public $accessKeyId = "";
    public $accessKeySecret = "";*/
    public $url;

    public function __construct($actionArray,$url){
        //parent::__construct();
        $this->url = $url;
        date_default_timezone_set("GMT");
        $this->data = array(
            // 公共参数
            'Format' => 'json',
            'Version' => '2016-11-01',
            'AccessKeyId' => ALIYUN_ACCESS_KEY_ID,
            'SignatureVersion' => '1.0',
            'SignatureMethod' => 'HMAC-SHA1',
            'SignatureNonce'=> time().mt_rand(100,999),
            'Timestamp' => date('Y-m-d\TH:i:s\Z'),
        );
        //判断输入的参数是否为数组
        if(is_array($actionArray)){
            $this->data = array_merge($this->data,$actionArray);
        }
    }

    public function percentEncode($str)
    {
        // 使用urlencode编码后，将"+","*","%7E"做替换即满足ECS API规定的编码规范
        $res = urlencode($str);
        $res = preg_replace('/\+/', '%20', $res);
        $res = preg_replace('/\*/', '%2A', $res);
        $res = preg_replace('/%7E/', '~', $res);
        return $res;
    }

    public function computeSignature($parameters, $accessKeySecret)
    {
        // 将参数Key按字典顺序排序
        ksort($parameters);
        // 生成规范化请求字符串
        $canonicalizedQueryString = '';
        foreach($parameters as $key => $value)
        {
            $canonicalizedQueryString .= '&' . $this->percentEncode($key)
                . '=' . $this->percentEncode($value);
        }
        // 生成用于计算签名的字符串 stringToSign
        $stringToSign = 'GET&%2F&' . $this->percentencode(substr($canonicalizedQueryString, 1));
        //var_dump($stringToSign);
        // 计算签名，注意accessKeySecret后面要加上字符'&'
        $signature = base64_encode(hash_hmac('sha1', $stringToSign, $accessKeySecret . '&', true));
        return $signature;
    }

    public function get_url($url,$data)
    {
        foreach($data as $key => $value){
            $url .= $key.'='.$value.'&';
        }
        $url = rtrim($url,'&');
        return $url;
    }

    public function callInterface(){
        // 计算签名并把签名结果加入请求参数
        $this->data['Signature'] = $this->computeSignature($this->data, ALIYUN_ACCESS_KEY_SECRET);
        //$url = $this->get_url($this->url,$this->data);
       // var_dump($url);
        dump($this->url . http_build_query($this->data));
        try {
            // 发送请求
            $ch = curl_init();
            if (substr($this->url, 0, 5) == 'https') {
                // 跳过证书检查
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            }
            curl_setopt($ch, CURLOPT_URL, $this->url . http_build_query($this->data));
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            //$res = curl_exec($ch);
            $response = curl_exec($ch);
            // 查看请求头信息
            // dump(curl_getinfo($ch,CURLINFO_HEADER_OUT));
            if ($error = curl_error($ch)) {
                // 如果发生错误返回错误信息
                curl_close($ch);
                $ret = ['status' => false, 'msg' => $error];
                return $ret;
            } else {
                // 如果发生正确则返回response
                curl_close($ch);
                $ret = ['status' => true, 'msg' => $response];
                return $ret;
            }
        }catch (\Exception $exception){
            $ret=['status'=>false,'msg'=>$exception->getMessage()];
            return $ret;
        }
       /* $res = json_decode($res);
        return $res;*/
    }
}

