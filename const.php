<?php
const MY_CHANNEL_NAME = "TD_CHANNEL_ID";
const FING_TAG = 0;//发现fragment控制标示


/**
 * 网络请求端口
 */
const SERVER_URL = "http://111.230.238.183:9527";
//const SERVER_URL = "https://api.tianyan199.com";
const SERVER_URL_BACK = "api.tianyan199.com";// 备用
const SERVER_PATH = '/data/wwwroot/TianYanApi';

const BANZHUREN = 294;//天雁商学院班主任

/**
 * 极光推送
 */
const JIGUANG_APPKEY = '390a59bdf6f756dd22a6d4f1';
const JIGUANG_MASTER_SECRET = 'b28ac12b9cc2eed27738b5f9';
const JIGUANG_REGISTRATION_ID = '1a1018970a8d274291f';

/**
 * 微信支付
 */
const WECHATPAY_URL = "https://api.mch.weixin.qq.com/pay/unifiedorder";
const WECHATPAY_APPID = 'wxa674ca5ea6141e79';
const WECHATPAY_MCHID = '1516590501';
const WECHATPAY_DEVICE_INFO = 'WEB';
//const WECHATPAY_KEY = '12e68fa788b610d3b511cec9ccdd35b1';
const WECHATPAY_KEY = '21e55fa787b612d5b511cec2ccdd25b8';
//const WECHATPAY_KEY = 'f47616e8c43a26329c7aae556de75580';

/**
 * 微信相关
 */
//公众号
const WECHAT_GZH_APPID = 'wx8f291dccc9e8f39b';
const WECHAT_GZH_APPSECRET = '331cf131b3b025fdc71ac7712de86fdd';
//APP
const WECHAT_APPID = 'wx7b4b44dce0248205';
//const WECHAT_APPID = 'wxa674ca5ea6141e79';
const WECHAT_APPSECRET = '4508898569cdc065b6c102bc9f6d697a';
//const WECHAT_APPSECRET = '12e68fa788b610d3b511cec9ccdd35b1';
const WECHAT_SOURCECODE = 'gh_2f514810fe61';
const WECHAT_TOKEN ='tianyanlivehome2017';
const WECHAT_USER_URL = 'https://api.weixin.qq.com/sns/userinfo';
const WECHAT_OAUTH_URL = 'https://api.weixin.qq.com/sns/oauth2/access_token';

/**
 * 阿里云直播相关
 */
//const LIVE_URL = 'rtmp://video-center-sg.alivecdn.com/';
const LIVE_URL = 'rtmp://video-center.alivecdn.com/';
const LIVE_APPNAME = 'tianyansxy';
const LIVE_STREAMNAME_LEFT = 'ty_stream';
const LIVE_VHOST = 'live.tianyan199.com';
const LIVE_AUTH_KEY = 'Q52CFgY3rN';

const LIVEROOM_QRCODE_URL = "/public/images/qrcode_ty.jpg"; //直播间默认二维码
/**
 * workerman
 */
const WORKERMAN_PUBLISH_URL = 'http://tianyan199.com:2121/';
//const WORKERMAN_PUBLISH_URL = 'http://119.29.136.97:2121/';
//const WORKERMAN_PUBLISH_URL = 'http://www.webmsg.com:2121/';//本地测试

/**
 * 阿里云短信 相关
 */
const ALIYUN_ACCESS_KEY_ID = 'UCguBiEKTCTelyZp';
const ALIYUN_ACCESS_KEY_SECRET = 'TbNwocSPammIWDfTzV0Pd61xtHu3t5';
const ALIYUN_TEMP_CODE = 'SMS_14111501';
const ALIYUN_TEMP_CODE1 = 'SMS_141115015';//身份验证验证码
const ALIYUN_TEMP_CODE2 = 'SMS_141115014';//登录确认验证码
const ALIYUN_TEMP_CODE3 = 'SMS_141115013';//登录异常验证码
const ALIYUN_TEMP_CODE4 = 'SMS_141115012';//用户注册验证码
const ALIYUN_TEMP_CODE5 = 'SMS_141115011';//修改密码验证码
const ALIYUN_TEMP_CODE6 = 'SMS_141115010';//信息变更验证码
const ALIYUN_SIGN_TEST = '阿里云短信测试专用';
const ALIYUN_SIGN = '天雁商学院';
const ALIYUN_PRODUCT = '456';
/**
 * 阿里云OSS 相关
 */
const OSS_ACCESS_KEY_ID = 'UCguBiEKTCTelyZp';
const OSS_ACCESS_KEY_SECRET = 'TbNwocSPammIWDfTzV0Pd61xtHu3t5';
const OSS_END_POINT_SZ = 'http://oss-cn-shenzhen.aliyuncs.com';
const OSS_END_POINT_SH = 'http://oss-cn-shanghai.aliyuncs.com';//用于直播录制存储
const OSS_BUCKET = 'livehomefile';
const OSS_ZHIBO_BUCKET = 'tianyanzhibo';
const OSS_REMOTE_PATH = 'http://livehomefile.oss-cn-shenzhen.aliyuncs.com';
const OSS_LUZHI_TIMEOUT = 315360000;//生成的录制视频url过期时间：10年

/**
 * 短信宝
 */

const DXB_ACCOUNT = 'bx5000';//短信宝账户
const DXB_PASSWORD = '19891125';//密码
const DXB_SENDSMSURL = "http://api.smsbao.com/sms";
const DXB_QUERYURL = "http://api.smsbao.com/query";

/**
 * Redis
 */
const REDIS_HOST = '111.230.238.183';
const REDIS_PORT = 6379;
const REDIS_TIMEOUT = 20;
const REDIS_AUTH = '83ss90km612apoWBY2S';
const REDIS_PUBLIC_KEY = 1;//公库
const REDIS_PRIVATE_KEY = 2;//私库
const REDIS_EXPIRE_5M = 300;          // 过期时间 5分钟
const REDIS_EXPIRE_1H = 3600;          // 过期时间 1 小时
const REDIS_EXPIRE_1D = 86400;        // 过期时间 1 天
const REDIS_EXPIRE_2D = 172800;       // 过期时间 2 天
const REDIS_YZM_KEY = 'verify_code'; //验证码键值



/**
 * 课程分享公众号链接
 */
const FENXIANG_URL = 'https://tianyan199.com/index.php/Home/Lecture/index?id=';

/**
 * 七牛云 相关
 */
const QINIU_ACCESS_KEY = 'WdoTQvcgbolHkm-W_EfXM4I0wTBrHWhbYfuNu3ZLC';
const QINIU_SECRET_KEY = 'kZku0Xn3tLRXa0q8kJhJ8IwHdJ2e7DDKqFrDfEwQD';
/**
 * 七牛上传类型
 */
const QINIU_TYPE_PIC = 1;  // 上传相册
const QINIU_TYPE_VIDEO = 2;  // 上传视频
const QINIU_TYPE_FACE = 3;  // 上传头像
const QINIU_TYPE_AUTH_PIC_POSITIVE = 4;  // 上传认证图片正面
const QINIU_TYPE_AUTH_PIC_NEGATIVE = 5;  // 上传认证图片反面
const RESULT_CODE_200 = 200;  // 返回码

/**
 *  接口請求 token密钥
 */
const USER_TOKEN_KEY = 'KqHDDFrdJ2e7DfEwQIw';

const RSA_PUBLIC_KEY = "-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDu0FN8odEtZ0aAY5cLHugCW5AQ
Ba5Uy/fVZiehl4sDuQ7Jzn0v5OL4bsl0UKGJ6oKUJ+QJByLOXy55B41LTVQ2MKNT
cyi9PhLBhkhS/9uNopT1i1wjcu/wymjCeKVTXLlkJTCSZMySTsR63fuh9FZMaFaC
3utnr2gytVE7M6tP1wIDAQAB
-----END PUBLIC KEY-----";

const RSA_PRIVATE_KEY = "-----BEGIN PRIVATE KEY-----
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



/**
 * 超时时间
 */
const TIME_OUT = 15000;

/**
 * 网络请求成功失败标识
 */
const JSON_SUCESS = true;// 网络请求成功
const JSON_FAIL = false;// 网络请求失败
const REQUEST_FAIL = -1;// 网络请求失败
const REQUEST_REPEAT = 304;// 数据重复提交


const OSS_URL = 'http://livehomefile.oss-cn-shenzhen.aliyuncs.com';
const DEFAULT_IMG = OSS_URL.'/Public/img/center/xiu.png';


/* 接口返回状态码 */
const OK            = '200';      // 请求成功！
const OK_OLINE      = '201';      // 充值成功标识！
const OK_RED        = '202';      // 红包已抢完
const OK_OLINE_MAX  = '205';      // 支付方式限额上限！
const E_DATA_REPEAT = '304';      // 数据重复提交!
const E_SIGN       = '400';      //  接口请求 验签失败!
const E_TOKEN       = '401';      // 校验错误, 未受权调用!
const E_POWER       = '402';      // 校验错误, 越权调用!
const E_DENY        = '403';      // 拒绝访问!
const E_API_NO_EXIST = '404';     // 接口不存在!
const E_METHOD      = '405';      // 请求方法不支持!
const E_DATA_INVALID = '420';     // 无效数据!
const E_DATA_EMPTY  = '421';      // 无数据!
const E_ARGS        = '422';      // 参数错误!
const E_OP_FAIL     = '423';      // 操作失败!
const E_YZM_CHECK   = '425';      // 需要验证码
const E_NOOP        = '429';      // 无操作!
const E_SYS         = '503';      // 系统错误, 请联系管理员!
const E_SYS_1       = '504';      // 系统错误1!
const E_UNKNOW      = '999';      // 未知错误!

const TOKEN_TIME_OUT = 600;     // TOKEN超时
const TOKEN_BE_OUTED = 601;     // token被踢出，也就是用户被踢出
const BLACK_IP = 602;           // IP被列入黑名单
const ADMIN_ACCESS_IP = 603;    // IP无法访问管理员后台
const LOGOUT = 604;             // 退出



/* 用户信息长度限制 */
const USER_USERNAME_MIN_LENGTH = 4;     //用户最小长度
const USER_USERNAME_MAX_LENGTH = 14;    //用户最大长度
const USER_PWD_MIN_LENGTH = 6;          //用户密码最小长度
const USER_PWD_MAX_LENGTH = 18;         //用户密码最大长度
const USER_PWD_ERROR_AND_LOCK = 5;      // 用户输入错多少次，锁定用户
const EXTENSION = 'intr';               // 代理推广url传递参数keys

/* token的后台，前台区分 */
const TOKEN_PRIVATE_KEY_TIME = 3600;    //获取token的键值的生存时间
const TOKEN_PRIVATE_KEY_CHECK_MIN_TIME = 3;//获取token的键值的生存时间
//const TOKEN_CODE_AUTH = 'AuthGC';       //后台
const TOKEN_CODE_ADMIN = 'Admin';       //后台
const TOKEN_CODE_USER = 'User';         //会员
//const TOKEN_CODE_AGENT = 'Agent';       //代理
const TOKEN_ADMIN_LIVE_TIME = 24;       //管理员超时时间，单位：小时
const TOKEN_USER_LIVE_TIME  =  1;        //会员toekn超时时间，单位：小时
const TOKEN_USER_LIVE_15DAY  =  360;        //会员toekn超时时间：半个月
const TOKEN_USER_LIVE_1MOON  =  720;        //会员toekn超时时间：1个月
const TOKEN_USER_LIVE_2MOON  =  1440;        //会员toekn超时时间：2个月
const TOKEN_USER_OFF_LINE   = 15;       //会员自动离线时间，单位：分钟
const USER_BANK_PWD_ERROR   = 5;        //取款密码错误次数
/*密钥*/
const TOKEN_PRIVATE_ADMIN_KEY = 123456; // 管理员token密钥
const TOKEN_PRIVATE_USER_KEY = 234567;  // 会员token密钥

/* IP */
const BLACK_IP_LIVE = 86400;            // 黑名单IP生存时间
const CODE_IP_TIMES = 3  ;              // 连续输错多少次密码加验证码
const BLACK_IP_TIMES = 20;              // 连续输错多少次密码IP列为黑色单

/*验证码*/
const VERIDY_CODE_LENGTH = 4;           // 验证码的长度
const VERIDY_CODE_RANGE = '0123456789'; // 验证码的长度
//const VERIDY_CODE_RANGE = '0123456789';// 验证码的范围
const VERIDY_CODE_LIVE_TIME = 600;      // 验证码生存时间


/** 会员中心／充值记录 选项集 */
const INCOME_OPT_ALL = 0;       // 全部
const INCOME_OPT_COMPANY = 1;   // 转账汇款
const INCOME_OPT_ONLINE = 2;    // 在线充值
const INCOME_OPT_CARD = 3;      // 彩豆充值

/** 来源 */
const FROM_IOS = 1;     // IOS
const FROM_ANDROID = 2; // 安卓
const FROM_PC = 3;      // PC
const FROM_WAP = 4;     // 手机浏览器
const FROM_UNKNOW = 5;  // 未知
const FROM_H5APP = 6;   // h5-app(ios h5 打包app)

const AUTO_INSERT_NUM = false;      //是否开启自动插入开奖号码的功能

const APP_IS_CHENK       = 0;       //app 是否开启审核 0:关闭 1 :开启
const CASH_REQUEST_TIME  = 2;       //入款提交时间间隔  s
const IN_COMPANY_COUNT  = 3;        //公司入款次数
const CASH_AUTO_EXPIRATION = 180;   //出入款自动过期时间   单位: 分钟
const IP_EXPIRE = 2592000;     //ip过期时间

const SHOW_BET_WIN_ROWS = 49;		// 显示中奖数据行数
const ADMIN_QUERY_TIME_SPAN = 5359280;	// 后端查询数据时间跨度限制62*86440
const OUT_BOUNODS_TIME = 86400;	// 出款手续费扣除手续费时间 24小时


/**
 * 标记加载或刷新
 */
const REQUEST_REFRESH = 0;// 刷新
const REQUEST_LOADING = 1;// 加载


const REQUEST_CHOOSE_MAIN_URL = 2;// 选取主url
const REQUEST_CHOOSE_OTHER_URL = 1;// 选取备用url


/**
 * 所有的action
 */

    /**
     * 注册类型
     */
    const REG_PHONE = 1;// 手机注册
    const REG_WECHAT = 2;// 微信注册
    const REG_QQ = 3;// QQ注册
    const REG_WEIBO = 4;// 微博注册
    const REG_ALIPAY = 5;// 支付宝注册
    const AUTH_SUCCESS = 1006;// 验证成功
    const AUTH_FAIL = 1007;// 验证失败
    const BIND_FAIL = 1003;// 绑定手机失败,号码已经绑定过


    const HANDLER_MSG_ERR = 1010;// 验证码有误或请求超时
    /**
     * 视频界面
     */
    const WAITING_TYPE = "waiting_type";
    const WAITING_BEAN = "waiting_bean";
    const ZHIBO_BEAN = "zhibo_bean";
    const WAITING_TYPE_0 = 0;//主动邀请界面
    const WAITING_TYPE_1 = 1;//被邀请界面
    const TOKEN_VIDEO_ID = "token_video_id";//视频ID
    const HOST_USER_ID = "host_user_id";//用户ID
    const HOST_PLAY_URL = "host_play_url";//用户ID
    const RANDOM_UID = "randomUid";//用户ID

    /**
     * 界面跳转的FLAG
     */
    const VIDEO_DESCIP = "video_descip";
    const JUMP_FLAG = "jump_flag";
    const JUMP_REGISTER = 1;  // 注册界面
    const JUMP_FIND_PASSWORD = 2;  // 找回密码界面
    const JUMP_EDIT_NICK = 3;  // 编辑昵称
    const JUMP_EDIT_SIGN = 4;  // 编辑个性签名
    const JUMP_EDIT_WXID = 5;  // 编辑微信号


    /**
     * 获取图片、视频
     */
    const TYPE_PHOTO = 1;
    const TYPE_VIDEO = 2;

    /**
     * 环信相关
     */
    const TYPE_HX_URL = 'https://a1.easemob.com/1133170512115121/chartapp/users';//环信请求URL
    const TYPE_HX_ORG_NAME = '1133170512115121';
    const TYPE_HX_APP_NAME = 'chartapp';
    const TYPE_HX_SUCCESS = 10010;
    const TYPE_HX_FAILD = 10086;
    const TYPE_HX_RECEIVE_MSG = 10011;
    //是否在删除会话的同时删除聊天记录
    const DELETE_CHATINFO = true;
    const MSG_NUM = 0;
    const TYPE_HX_NOTICE1 = 10030;
    const TYPE_HX_NOTICE2 = 10033;
    const TYPE_HX_ENDCALL = 10012;
    const TYPE_HX_CLICKMORE = 10031;
    const TYPE_HX_USERINFO = 10041;
    const TYPE_HX_ACCEPT = 10042;//关于我们
    const TYPE_HX_DONOTDELETE = 10043;//不允许删管理员账号
    /**
     * 上传直播状态
     */
    const UPLOAD_ZHIBO_STATE_HANDUP = 0;  // 挂断
    const UPLOAD_ZHIBO_STATE_CONNECT = 1;  // 接通
    const UPLOAD_ZHIBO_STATE_REQUEST = 2;  // 发起请求

    /**
     * 获取图片方式（拍照、相册、裁剪后）
     */
    const UPLOAD_TYPE_CAMARA = 100;  // 拍照
    const UPLOAD_TYPE_GALLERY = 101;  // 相册
    const UPLOAD_TYPE_CUT = 103;  // 裁剪后
    /**
     * 用户标签类型
     */
    const USER_EVALUATE_TAG_LIST = 1;  // 获取用户视频通话后评价的标签
    const USER_TYPE_TAG_LIST = 2;  // 用户自己填写类型的标签列表标签
    /**
     * 图片、视频审核状态
     */
    const AUTH_NOW = 1;  // 审核中
    const AUTH_PASS = 2;  // 已审核
    const AUTH_NO_PASS = 3;  // 审核未通过
    /**
     * 身份认证审核状态
     * 1.未认证  2.认证中 3.认证成功 4.认证失败
     */
    const NO_AUTH_ID = 1;  // 未认证
    const AUTH_ID_NOW = 2;  // 认证中
    const AUTH_ID_SUCCESS = 3;  // 认证成功
    const AUTH_ID_FAIL = 4;  // 认证失败

    const FORCE_LOGOUT = "force_logout";
    const FORCE_LOGOUT_TIP = "force_logout_tip";

    /**
     *  支付结果
     */
    const PAY_NOW = 1;  // 支付中
    const PAY_SUCCESS = 2;  // 支付成功
    const PAY_FAIL = 3;  // 支付失败

    /**
     *  最热、最新
     */
    const FIND_ZR = 0;  // 最热
    const FIND_ZX = 1;  // 最新

    /**
     *  心跳类型
     */
    const HEART_PAY = 1;  // 通话支付
    const HEART_ONLINE = 2;  // 在线心跳

    /**
     *  用户在线状态类型
     */
    const STATE_FREE = 1;  // 空闲
    const STATE_BUSY = 2;  // 聊天中
    const STATE_OFFLINE = 3;  // 离线



