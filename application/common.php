<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

if (!function_exists('encode_sign')) {
    /**
     * 公钥加密
     * @param $values
     * @return string
     */
    function encode_public_sign($values)
    {
        $pub_key = RSA_PUBLIC_KEY;
        //签名步骤一：按字典序排序参数
        if(is_array($values)){
            ksort($values);
            $string = to_url_params($values);
        }else{
            $string = (string)$values;
        }
        //签名步骤二：MD5加密
        $string = md5($string);
        //签名步骤三：所有字符转为大写
        $result = strtoupper($string);
        //签名步骤四：用公钥对result签名
        //echo "The result after MD5 is:".$result."</br>";
        $pubkey = openssl_pkey_get_public($pub_key);
        //echo "pubkey is:".$pubkey."</br>";
        $encrypted = ''; //用来存放加密后的内容
        openssl_public_encrypt($result, $encrypted, $pubkey);
        $encrypted = base64_encode($encrypted);
        return $encrypted;
    }

}

if (!function_exists('encode_private_sign')) {
    /**
     * 私钥加密
     * @param $values
     * @return string
     */
    function encode_private_sign($values)
    {
        $pub_key = RSA_PRIVATE_KEY;
        //签名步骤一：按字典序排序参数
        if(is_array($values)){
            ksort($values);
            $string = to_url_params($values);
        }else{
            $string = (string)$values;
        }
        //签名步骤二：MD5加密
        $string = md5($string);
        //签名步骤三：所有字符转为大写
        $result = strtoupper($string);
        //签名步骤四：用公钥对result签名
        //echo "The result after MD5 is:".$result."</br>";
        $pubkey = openssl_pkey_get_private($pub_key);
        //echo "pubkey is:".$pubkey."</br>";
        $encrypted = ''; //用来存放加密后的内容
        openssl_private_encrypt($result, $encrypted, $pubkey);
        $encrypted = base64_encode($encrypted);
        return $encrypted;
    }

}

if (!function_exists('decode_sign')) {
    /**
     * 私钥解密
     * @param $sign
     * @return string
     */
    function decode_sign($sign)
    {
        $private_key = RSA_PRIVATE_KEY;
        //解密结果对比
        $pi_key = openssl_pkey_get_private($private_key);
        //echo "pikey is:".$pi_key."</br>";
        $decrypted = '';
        $encryResult2 = base64_decode($sign);
        openssl_private_decrypt($encryResult2, $decrypted, $pi_key);
        //var_dump($decrypted);exit;
        return $decrypted;
    }
}

if (!function_exists('to_url_params')) {
    /**
     * 拼接字符串
     * @param $content
     * @return string
     */
    function to_url_params($content)
    {
        $buff = "";
        foreach ($content as $k => $v) {
            if ($k != "sign" && $v != "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }
}

if (!function_exists('vsign')) {
    /**
     * 签名验证
     * @param $sign
     * @param $content
     * @return bool
     */
    function vsign($sign, $content)
    {
        $sign = decode_sign($sign);
        ksort($content);
        $content_md5 = strtoupper(md5(to_url_params($content)));
        //var_dump($sign,$content_md5);exit;
        if ($sign == $content_md5) {
            return true;
        } else {
            return false;
        }
    }
}
if (!function_exists('esay_curl')) {
    function esay_curl($url,$jump_ssl = false)
    {
        $ch = curl_init();
        if($jump_ssl){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }
}

if (!function_exists('dump')) {
    /**
     * 浏览器友好的变量输出
     * @param mixed $var 变量
     * @param boolean $echo 是否输出 默认为True 如果为false 则返回输出字符串
     * @param string $label 标签 默认为空
     * @param boolean $strict 是否严谨 默认为true
     * return void|string
     */
    function dump($var, $echo=true, $label=null, $strict=true)
    {
        $label = ($label === null) ? '' : rtrim($label) . ' ';
        if (!$strict) {
            if (ini_get('html_errors')) {
                $output = print_r($var, true);
                $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
            } else {
                $output = $label . print_r($var, true);
            }
        } else {
            ob_start();
            var_dump($var);
            $output = ob_get_clean();
            if (!extension_loaded('xdebug')) {
                $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
                $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
            }
        }
        if ($echo) {
            echo($output);
            return null;
        }else
            return $output;
    }
}


if (!function_exists('wlog')) {
    /**
     * @fn
     * @brief 日志记录函数
     * @param $log_file    日志文件名
     * @param $log_str    日志内容
     * @param $show        日志内容是否show出
     * @param $log_size    日志文件最大大小，默认20M
     * @return void
     */
    function wlog($log_file, $log_str, $show = false, $log_size = 20971520) /* {{{ */
    {
        ignore_user_abort(TRUE);//忽略与用户的断开,继续执行下面的程序

        $time = '['.date('Y-m-d H:i:s').'] ';
        if ( $show ) {
            echo $time.$log_str.((PHP_SAPI == "cli") ? "\r\n" : "<br>\r\n");
        }
        if ( empty($log_file) ) {
            $log_file = 'wlog.txt';
        }
        if ( defined('APP_LOG_PATH') ) {
            $log_file = APP_LOG_PATH.$log_file;
        }

        if ( !file_exists($log_file) ) {
            $fp = fopen($log_file, 'a');
        } else if ( filesize($log_file) > $log_size ) {
            $fp = fopen($log_file, 'w');
        } else {
            $fp = fopen($log_file, 'a');
        }

        if ( flock($fp, LOCK_EX) ) {
            $cip    = defined('CLIENT_IP') ? '['.CLIENT_IP.'] ' : '['.getenv('REMOTE_ADDR').'] ';
            $log_str = $time.$cip.$log_str."\r\n";
            fwrite($fp, $log_str);
            flock($fp, LOCK_UN);
        }
        fclose($fp);

        ignore_user_abort(FALSE);//停止忽略
    } /* }}} */
}


if (!function_exists('get_city')) {
    //根据ip获取所在城市
    function get_city($getIp)
    {
        // 获取当前位置所在城市
        $content = file_get_contents("http://api.map.baidu.com/location/ip?ak=2TGbi6zzFm5rjYKqPPomh9GBwcgLW5sS&ip={$getIp}&coor=bd09ll");
        $json = json_decode($content);
        if(empty($json->{'address'})){
            return false;
        }
        $address = $json->{'address'};//按层级关系提取address数据
        $address = explode('|',$address);
        return $address;
    }
}

if (!function_exists('random')) {
    /**
     * 生成随机字符数据（数字、大小写字母混合）
     * @param int $length
     * @param string $chars
     * @return string
     */
    function random($length = 8, $chars = '123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ')
    {
        $hash = '';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }
        return $hash;
    }
}

if (!function_exists('get_top_domain')) {
    /**
     * 获取顶级域名, 简洁快速版
     * 不处理 com.cn com.cc 等二级后缀的国家域名
     * 如果需要处理 2级的国家域名, 请打开代码注释
     */
    function get_top_domain($domain = 'x.com') /* {{{ */
    {
        $data = explode('.', $domain);
        $count_dot = count($data);
        // 判断是否是双后缀国家域名
        /*
        $is_2 = false;
        $domain_2 = ['com.cc','net.cc','org.cc','com.cn','net.cn','org.cn'];
        foreach ($domain_2 as $d) {
            if (strpos($domain, $d)) {
                $is_2 = true;
                break;
            }
        }
        */
        // 如果是双后缀
        // if ($is_2 == true) {
        $top_domain = $data[$count_dot - 2].'.'.$data[$count_dot - 1];
        // } else {
        //    $top_domain = $data[$count_dot - 3].'.'.$data[$count_dot - 2].'.'.$data[$count_dot - 1];
        // }
        return $top_domain;
    } /* }}} */
}


if(!function_exists('pay_curl')){

    /**
     * 第三方支付curlpost方式发送数据返回最原始的数据
     * @param $url  发送到的地址
     * @param $data 发送的数据
     * @param  $method 请求的方法
     * @param $pay_a   伪造的支付域名
     * @return  string 网站返回数据
     */
    function pay_curl($url,$data,$method,$pay_a=null)
    {
        $ch = curl_init();
        if (strtolower($method) === 'get') {
            $url .="?".http_build_query($data);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        if ($pay_a) {
            curl_setopt($ch, CURLOPT_REFERER, $pay_a);
        }
        //重定向使用
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        if (strtolower($method) == 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,$data);

        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $tmpInfo = curl_exec($ch);
        if (curl_errno($ch)) {
            $error[OK] = false;
            $error['errorMsg']  = curl_error($ch);
            echo json_decode($error);die;
        }
        return $tmpInfo;
    }

}


if (!function_exists('array_make_key')) {
    /**
     * @将数据以一个值作用key。
     * @param array $arr 数据
     * @param string $key 数据的键值，数据必需要有这个键值。
     * @return array
     */
    function array_make_key($arr=array(), $key='id')
    {
        if(empty($arr) || !is_array($arr) ) {
            return $arr;
        }
        $res = array();
        foreach ($arr as $temp) {
            $res[$temp[$key]] = $temp;
        }
        return $res;
    }
}

if (!function_exists('get_ip')) {
    /**
     * 获取IP
     * @return $string
     */
    function get_ip()
    {
        $arr_ip_header = array(
            'HTTP_CDN_SRC_IP',
            'HTTP_PROXY_CLIENT_IP',
            'HTTP_WL_PROXY_CLIENT_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'REMOTE_ADDR',
        );
        $client_ip = 'unknown';
        foreach ($arr_ip_header as $key)
        {
            if (!empty($_SERVER[$key]) && strtolower($_SERVER[$key]) != 'unknown')
            {
                $client_ip = $_SERVER[$key];
                break;
            }
        }
        //去除伪造IP
        $ss = trim($client_ip);
        $arr = explode(',', $ss);
        $count = count($arr);
        if($count<=3) {
            $ip = $arr[0];
        }else if($count>=4) {
            $ip = $arr[1];
        }
        if($ip=='::1'){
            $ip = '127.0.0.1';
        }
        $ip = preg_match("/[\d\.]{7,15}/", $ip, $matches) ? $matches[0] : $unknown;
        return $ip;
    }
}


if (!function_exists('token_encrypt')) {
    /**
     * 加密
     * @param string $token 需要被加密的数据
     * @param string $private_key 密钥
     * @return string
     */
    function token_encrypt($token='',$private_key='')
    {
        return base64_encode(openssl_encrypt($token, 'BF-CBC', md5($private_key), null, substr(md5($private_key), 0, 8)));
        //return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($private_key), $token, MCRYPT_MODE_CBC, md5(md5($private_key))));
    }
}

if (!function_exists('token_decrypt')) {
    /**
     * 解密
     * @param string $en_token 加密数据
     * @param string $private_key 密钥
     * @return string
     */
    function token_decrypt($en_token='',$private_key='')
    {
        return rtrim(openssl_decrypt(base64_decode($en_token), 'BF-CBC', md5($private_key), 0, substr(md5($private_key), 0, 8)));
        //return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($private_key), base64_decode($en_token), MCRYPT_MODE_CBC, md5(md5($private_key))), "\0");
    }
}





if (!function_exists('get_auth_headers')) {
    /**
     * 获取请求头
     * @fn
     * @brief get http headers
     * @return
     */
    function get_auth_headers($header_key = null)
    {
        if (function_exists('apache_request_headers')) {
            /* Authorization: header */
            $headers = apache_request_headers();
            $out = array();
            foreach ($headers AS $key => $value) {
                $key = str_replace(" ", "-", ucwords(strtolower(str_replace("-", " ", $key))));
                $out[$key] = $value;
            }
        } else {
            $out = array();
            if (isset($_SERVER['CONTENT_TYPE'])) {
                $out['Content-Type'] = $_SERVER['CONTENT_TYPE'];
            }
            if (isset($_ENV['CONTENT_TYPE'])) {
                $out['Content-Type'] = $_ENV['CONTENT_TYPE'];
            }
            foreach ($_SERVER as $key => $value) {
                if (substr($key, 0, 5) == "HTTP_") {
                    $key = str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($key, 5)))));
                    $out[$key] = $value;
                }
            }
        }
        if ($header_key != null) {
            $header_key = ucfirst(strtolower($header_key));
            if (isset($out[$header_key])) {
                return $out[$header_key];
            } else {
                return false;
            }
        }
        return $out;
    }


}

if(!function_exists('upload_file')){
    /**
     * 上传图片到远程服务器
     * @param $url 远程服务器api
     * @param $filename $_FILES['file']['name']
     * @param $path $_FILES['file']['tmp_name']
     * @param $type $_FILES['file']['type']
     * @return mixed
     * super
     */
    function upload_file($url,$filename,$path,$type){
        if (class_exists('\CURLFile')) {
            $data = array('file'=>(new CURLFile(realpath($path),$type,$filename)),'sid'=>1);
        }else {
            $data = array(
                'file'=>('@'.realpath($path).";type=".$type.";filename=".$filename),'sid'=>1
            );
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查 url为https时使用
        //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);  // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $return_data = curl_exec($ch);
        curl_close($ch);
        return $return_data;
    }
}



if (!function_exists('sortArrByField')) {
    /**
     * 根据某字段对二维数组进行排序
     * @param $array 需要排序的二维数组
     * @param $field 二维数组中的列名
     * @param bool|false $desc
     * super
     */
    function sortArrByField(&$array, $field, $desc = false)
    {
        $fieldArr = array();
        foreach ($array as $k => $v) {
            $fieldArr[$k] = $v[$field];
        }
        $sort = $desc == false ? SORT_ASC : SORT_DESC;
        array_multisort($fieldArr, $sort, $array);
    }
}

/* 层级树 */
if ( ! function_exists('level_tree')) {
    /**
     * 层级树
     * 用法：$data = $this->level_tree($data);
    $data = array(
    array(id=1,pid=0),
    array(id=1,pid=0)
    );
    pid=0表示根节点，pid不等于0表示其他根节点的子节点，而pid都是记录id值
    所以：r_field=id， l_field=pid
     * @param array   $data     数据集
     * @param array   $root     根据节点
     * @param string  $r_field  叶子节点找到根节点字段（id）
     * @param string  $l_field  叶子节点的字段（pid）
     * @param string  $leaf     叶子集合key
     */
    function level_tree(&$data, $root=array(), $r_field='id', $l_field='pid', $leaf='child')
    {
        if(empty($root))
        {
            $root = array(array($r_field=>0));
        }

        foreach ($root as $k => $v)
        {
            foreach ($data as $kk => $vv)
            {
                if($v[$r_field] == $vv[$l_field])
                {
                    $root[$k][$leaf][] = $vv;
                    unset($data[$kk]);
                }
            }
        }
        if(isset($data))
        {
            foreach ($root as $k => $v)
            {
                if(isset($v[$leaf]))
                {
                    $root[$k][$leaf] = level_tree($data, $v[$leaf], $r_field, $l_field, $leaf);
                }
            }
        }
        return $root;
    }
}

if (!function_exists('order_num')) {
    /**
     * 生成订单号
     * @param $main 主业务编号
     * @param $main_son 子业务编号
     * @return string  订单号
     */
    function order_num($main, $main_son)
    {
        $micro = substr(microtime(), 2, 4);
        return $main.bu0($main_son).substr(date('ymdHis'), 1).$micro;
    }
}


