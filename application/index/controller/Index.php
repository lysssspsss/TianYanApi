<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use think\Input;
use think\Db;
use think\Session;
use think\Validate;
use Qiniu\Auth;


class Index extends Controller
{
    //public $values;

    public function __construct()
    {
        parent::__construct();
    }


    public function index()
    {
        $a = 'http://111.230.238.183:9527/api.php/index/user/wechat_login';
        var_dump(urlencode($a));exit;
        //$data = $this->testRsaEncrypt();
        $a = get_auth_headers();
        //dump($a);exit;
        //wlog(APP_PATH.'log/test.log',json_encode($data));//日志测试
        //$auth = new Auth(QINIU_ACCESS_KEY, QINIU_SECRET_KEY);//七牛云测试
        //$auth2 = Qiniu::getInstance(); var_dump($auth2);exit;//七牛云测试2
        //$aa = Message::sendSms('13168088229','12345'); var_dump($aa);exit;//短信测试

        /*$post['phone'] = '13168088229';
        $post['type'] = '2';
        $sign = encode_public_sign($post);
        $post['sign'] = $sign;
        $res = $this->send_post('http://111.230.238.183:9527/api.php/index/user/sms',$post);
        dump($res);exit;*/

        //decode_sign('wSUNdfRrZyxVFC8ii+Bb6bwWU1sv8Rsycva2J4D7hZavQwv2jdYKn7M6SGEZPojWJ8kHgFCyOu5rOXpQsm/oJC9aDBkhk7agB2mtCxU8sDxPDk+q87ZVIGjeYcTBpnIG3WRwZjfoqc4A7RgYjQNpdXEXywZmPmqwVkuE8AB5Lvc=');

        //$this->return_json(OK,$a);
        //exit;
    }

    //post推送
    public function send_post($url,$post_data)
    {
        $ch = curl_init();
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // https跳过证书检查
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);// 10s to timeout.
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }


    /*public function testRsaEncrypt(){
        //将参数放入数组
        $values['id'] = 1;
        $values['lectureId'] = 248;
        $values['memberId'] = 2569;
        $values['search'] = "张迎宾";

        //$A = 'id=1&lectureId=248&memberId=2569&search=张迎宾&sign=jUtNVDYwo1NzKL0+EsGGSFL/242ilPWLXCNy7/DKaMJ4pVNcuWQlMJJkzJJOxHrd';
        //对参数进行签名加密
        $encryResult = $this->EncodeSign($values);
        $values['sign'] = $encryResult;
        return $values;
        $values = json_encode($values,JSON_UNESCAPED_UNICODE);
        $values = json_decode($values,true);
        //return $values;

        //打印加密后的结果
        //echo "The result after encrypt:".$encryResult."</br>";


        $private_key = "-----BEGIN PRIVATE KEY-----
MIICeAIBADANBgkqhkiG9w0BAQEFAASCAmIwggJeAgEAAoGBAO7QU3yh0S1nRoBj
lwse6AJbkBAFrlTL99VmJ6GXiwO5DsnOfS/k4vhuyXRQoYnqgpQn5AkHIs5fLnkH
jUtNVDYwo1NzKL0+EsGGSFL/242ilPWLXCNy7/DKaMJ4pVNcuWQlMJJkzJJOxHrd
+6H0VkxoVoLe62evaDK1UTszq0/XAgMBAAECgYBqX9FQSqPqHX6B4dp90Z52rmJV
QLKOguw52e79Q4tgdSXpLlkE5GdVUcPaU7AgxpyzcbFZdBxE4JDKyFpfmGpRfMA8
RSljrwweD1MPFqjFCPNiIGhY4FmpykyD5O5gjJp8HI5nRx7EGkSybD6UQqI95pre
JupcNaVgIQLKiDlJAQJBAPmFKEqnDoWHgLrNJtULFaFYE8S9S7L/595crk217MTq
pQ5XILwwKoZ4Xmi9Mtad3tB0RElATisFxb8GDpS8zDECQQD1A/3KPLYnUoWodqBU
suaOUXsapVslbnAEVgoNcxsmIMC2FPkU8wUiOwWmIkwHAMEkVGrEdUMwmqPolmu8
J0KHAkEA3MCG1g0YVuB77khkK6Wj3FutGakTmOi4vcynVQ83yzuBDb/dsUC3zsId
XHLO0HtZTnkelOP0hDGWMptsOQETcQJBANxiw7RWWa4TD0BRu7OT28glyGpWVB7e
MInl7lLct43bJhxhzw4l7fc1ScZ+0Q33gsMv331o0I/2ePqr2qo6Uh8CQQDjy0Il
EOwmfzCwsmimuXn1xIe9gaem2Ga3pl5IKxvnGVftpdjHMrct2ORuN5SJuKygk45G
NV8c7axaWU1MxZjk
-----END PRIVATE KEY-----";
        //解密结果对比
        $pi_key = openssl_pkey_get_private($private_key);
        //echo "pikey is:".$pi_key."</br>";
        $decrypted = '';
        $encryResult2 = base64_decode($values['sign']);
        openssl_private_decrypt($encryResult2,$decrypted,$pi_key);
        var_dump($decrypted);exit;

    }*/

}
