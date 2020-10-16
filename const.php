<?php
const MY_CHANNEL_NAME = "TD_CHANNEL_ID";
const FING_TAG = 0;//发现fragment控制标示

const TOKEN_USER_LIVE_TIME  =  1;        //会员toekn超时时间，单位：小时
const USER_BANK_PWD_ERROR   = 5;        //取款密码错误次数


/**
 * 网络请求端口
 */
const SERVER_URL = "";//http://abc.52zhibow.com:8182
const SERVER_URL_BACK = "";// 备用

/**
 * 阿里云短信 相关
 */
const ALIYUN_ACCESS_KEY_ID = '';
const ALIYUN_ACCESS_KEY_SECRET = '';

const ALIYUN_TEMP_CODE1 = 'SMS_141115015';//身份验证验证码
const ALIYUN_TEMP_CODE2 = 'SMS_141115014';//登录确认验证码
const ALIYUN_TEMP_CODE3 = 'SMS_141115013';//登录异常验证码
const ALIYUN_TEMP_CODE4 = 'SMS_141115012';//用户注册验证码
const ALIYUN_TEMP_CODE5 = 'SMS_141115011';//修改密码验证码
const ALIYUN_TEMP_CODE6 = 'SMS_141115010';//信息变更验证码
const ALIYUN_SIGN = '阿里云短信测试专用';//
const ALIYUN_PRODUCT = '456';


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
const TOKEN_KEY = 'KqHDDFrdJ2e7DfEwQIw';

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
const E_OP_FAIL     = 423;      // 操作失败!

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
    const REQUEST_ACTION_LOGIN = 10001;// 登录
    const REQUEST_ACTION_TAG = 10002;// 获取TAG标签
    const REQUEST_ACTION_REGISTER = 10003;// 注册
    const REQUEST_ACTION_AUTHPHONE = 10004;// 绑定手机
    const REQUEST_ACTION_AUTOLOGIN = 10005;// 自动登录
    const REQUEST_ACTION_HEARTBEAT = 10006;// 在线心跳
    const REQUEST_ACTION_FIND = 10007;// 发现
    const REQUEST_ACTION_SP = 10008;// 私拍
    const REQUEST_ACTION_MESSAGE = 10009;// 消息
    const REQUEST_ACTION_FIND_PASSWORD = 10010;// 消息
    const REQUEST_ACTION_CALL_LOG = 10011;// 通话记录
    const REQUEST_ACTION_QINIU_TOKEN = 10012;// 获取七牛上传凭证
    const REQUEST_ACTION_UPDATE_USER = 10013;// 更新个人信息
    const REQUEST_ACTION_REG_BASE = 10014;// 注册基本信息
    const REQUEST_ACTION_TOKEN_LOG = 10015;// 私拍记录
    const REQUEST_ACTION_MY_TOKEN = 10016;// 我的私拍
    const REQUEST_ACTION_MY_TOKEN_DETAIL = 10017;// 私拍详细
    const REQUEST_ACTION_OTHER_INFO = 10018;// 私拍详细
    const REQUEST_ACTION_GET_PIC = 10019;// 获取图片和视频
    const REQUEST_ACTION_GZ = 10020;// 关注
    const REQUEST_ACTION_SETTING_PRICE_LIST = 10021;// 收费设置价格列表
    const REQUEST_ACTION_PAY_LIST = 10022;// 获取商品列表
    const REQUEST_ACTION_BINDING_BANK = 10023;// 用户绑定银行卡
    const REQUEST_ACTION_FEEDBACK = 10024;// 意见反馈
    const REQUEST_ACTION_MY_INCOME = 10025;// 我的收益
    const REQUEST_ACTION_ALL_EVALUATE = 10026;// 所有评价
    const REQUEST_ACTION_CHECK_UPDATE = 10027;// 检查更新
    const REQUEST_ACTION_PAY = 10028;// 请求支付
    const REQUEST_ACTION_QUREY_ORDER = 10029;// 查询支付订单
    const REQUEST_ACTION_LH = 10030;// 拉黑
    const REQUEST_ACTION_RECOMMEND = 10031;// 推荐
    const REQUEST_ACTION_GIFT_LIST = 10032;// 礼物列表
    const REQUEST_ACTION_TIP = 10033;// 拉黑并举报
    const REQUEST_ACTION_LOGIN_AUTH = 10034;//第三方登录认证
    const REQUEST_ACTION_VIDEO_PAY = 10035;//视频支付
    const REQUEST_ACTION_GIFT_BUY = 10036;//购买礼物并赠送
    const REQUEST_ACTION_WE_VIDEO_PAY = 10037;//判断是否需要支付
    const REQUEST_ACTION_DELETE_RESOURCE = 10038;//删除上传到服务器上的资源
    const REQUEST_ACTION_IM_USERINFO = 10039;//根据环信id获得用户信息
    const REQUEST_ACTION_UPLOAD_ZHIBO_STATE = 10040;//上传直播连接状态
    const REQUEST_ACTION_INIT_STATE = 10041;//初始化请求
    const REQUEST_ACTION_GET_VIDEO_COMMENT = 10042;//获取视频评论列表
    const REQUEST_ACTION_VIDEO_COMMENT = 10043;//评论视频
    const REQUEST_ACTION_PAY_WEIXIN = 10044;//是否已付费看微信
    const REQUEST_ACTION_PAY_WEIXIN_RESULT = 10045;//看微信支付
    const REQUEST_ACTION_VIDEO_ID_INFOR = 10046;//根据ID获取私拍信息
    const REQUEST_ACTION_EVALUATE = 10047;//上报用户评价
    const REQUEST_ACTION_USER_TAG_LIST = 10048;//获取用户标签列表
    const REQUEST_ACTION_VIDEO_DESCRIP = 10049;//视频描述
    const REQUEST_ACTION_CHECK_SENSITIVEDATABASE = 10050;//敏感詞
    const REQUEST_ACTION_INVITE_FRIEND = 10051;//邀请好友
    const REQUEST_ACTION_SEARCH_INFO = 10052;//搜索
    const REQUEST_ACTION_GET_FANS = 10053;//获取粉丝
    const REQUEST_ACTION_GET_ATTANTION = 10054;//获取关注
    const REQUEST_ACTION_VERIFY_BANK = 10055;//验证用户输入的银行卡号对应的银行
    const REQUEST_ACTION_ABOUT_US = 10056;//关于我们
    const REQUEST_ACTION_AUTH_STATE = 10057;//获取认证状态
    const REQUEST_ACTION_SENTWARN = 10058;//异常信息上报
    const REQUEST_ACTION_CALLCUSTOMER = 10059;//呼叫客服
    const REQUEST_ACTION_TOURISRLOGIN=10060;//游客登录
    /**
     * 请求协议
     */
    const LONGIN_URL = "0/api/user/login";//登录
    const TAG_URL = "/api/user/showTag";//获取TAG标签
    const REGISTER_URL = "/api/user/reg";//注册
    const AUTHPHONE_URL = "/api/user/authPhone";//绑定手机
    const AUTOLOGIN_URL = "/api/user/autoLogin";//自动登录
    const HEARTBEAT_URL = "/api/user/online/heartbeat";//在线心跳
    const FIND_URL = "/api/user/find";//发现页面的标签
    const SP_URL = "/api/video/list";//私拍
    const MESSAGE_URL = "/api/sns/chatList";//消息
    const FIND_PASSWORD = "/api/user/updatePwd";//找回密码
    const CALL_LOG_URL = "/api/sns/callLog";//通话记录
    const GET_QINIU_TOKEN = "/api/sys/upload/token";//获取七牛Token
    const GET_UPDATE_USER = "/api/user/update";//更新个人信息
    const REG_BASE = "/api/user/regBase";//注册基本信息
    const CALL_TOKEN_URL = "/api/video/playLog";//通话记录
    const MY_TOKEN_URL = "/api/video/person";//我的私拍
    const TOKEN_DETAIL_URL = "/api/video/person/details";//我的私拍
    const OTHER_INFORMATION_URL = "/api/user/index";//获取他人信息
    const GRT_PIC_URL = "/api/user/getPhotoVideoList";//拉取图片和视频
    const GRT_GZ_URL = "/api/user/follow";//关注
    const SETTING_PRICE_LIST = "/api/setting/price/list";//收费设置价格列表
    const GET_PAY_LIST = "/api/pay/item/list";//获取商品列表
    const BINDING_BANK = "/api/user/exchangeCash";//用户绑定银行卡
    const FEEDBACK = "/api/user/feedback/add";//意见反馈
    const MY_INCOME = "/api/user/myIncome";//我的收益
    const ALL_EVALUATE = "/api/user/userComments";//所有评价
    const CHECK_UPDATE = "/api/sys/android/update";//检查更新
    const PAY = "/pay/order";//支付接口
    const QUREY_ORDER = "/pay/payResult";//查询订单支付状态
    const LH_URL = "/api/user/blacklist";//拉黑
    const RECOMMEND_URL = "/api/user/recommend";//推荐
    const GET_GIFT_LIST = "/api/gift/list";//礼物列表
    const TIP_URL = "/api/user/complaint";//举报
    const LOGIN_AUTH = "/api/user/auth/login";//第三方登录认证
    const VIDEO_PAY_URL = "/api/video/deductBalance";//视频支付
    const GIFT_BUY = "/api/gift/buy";//购买礼物并赠送
    const VIDEO_WE_PAY_URL = "/api/video/payState";//判断视频是否支付
    const DELETE_RESOURCE = "/api/user/delPhotoVideo";//删除上传到服务器的资源
    const IM_USERINFO_URL = "/api/user/imInfo";//根据imid获得用户信息
    const UPLOAD_ZHIBO_STATE = "/api/liveVideo/upload";//上传直播连接状态
    const VIDEO_COMMENT = "/api/video/listComment";//视频评论列表
    const DO_VIDEO_COMMENT = "/api/video/comment";//评论视频
    const WEHT_WEIXIN_PAY = "/api/user/check/wx/state";//是否已支付微信费用
    const WEHT_WEIXIN_PAY_RESULT = "/api/user/check/wx/deduct";//支付微信
    const DO_VIDEO_ID_INFOR = "/api/video/get";//根据videoid获取数据
    const DO_VIDEO_DES = "/api/video/update";//根据描述视频
    const INIT_URL = "/api/init";//初始化请求
    const EVALUATE = "/api/user/report/evaluate";//上报用户评价
    const USER_TAG_LIST = "/api/user/tag/list";//获取用户标签列表
    const CHECK_SENSITIVEDATABASE_URL = "/keyWord/update";//更新敏感词数据库
    const INVITE_FRIEND = "/api/sys/invite";//邀请好友
    const SEARCH_URL = "/api/user/search";//搜索
    const GET_FANS = "/api/user/fans/list";//获取粉丝列表
    const GET_ATTANTION = "/api/user/attention/list";//获取关注列表
    const GET_VERIFY_BANK_URL = "https://ccdcapi.alipay.com/validateAndCacheCardInfo.json";//验证用户输入的银行卡号对应的银行
    const GET_ABOUT_US = "/api/sys/aboutUs";//获取粉丝列表
    const GET_AUTH_STATE = "/api/user/auth/state";//获取认证状态
    const SENT_WARN = "/api/monitor/sentWarn";//异常信息上报
    const CALL_CUSTOMER = "/api/user/askCustomer";//呼叫客服
    const TOURISR_LOGIN="/api/user/visitorLogin";//游客登录

    /**
     * OK标识
     */
    const OK = "ok";

    /**
     * header名称
     */
    const HEADER_MOBILE_MODEL = "mobile_model";
    const HEADER_OS_VERSION = "os_version";
    const HEADER_NET_MODE = "net_mode";
    const HEADER_PACKAGE = "package";
    const HEADER_APPNAME = "appname";
    const HEADER_IMSI = "imsi";
    const HEADER_IMEI = "uuid";
    const HEADER_SCREEN = "screen";
    const HEADER_ICCID = "iccid";
    const HEADER_LAC = "lac";
    const HEADER_MAC = "mac";
    const HEADER_MIP = "mip";
    const HEADER_UA = "ua";
    const HEADER_SIGNATURE = "signature";
    const HEADER_TIMESTAMP = "timeStamp";
    const HEADER_RANDOM = "random";
    const HEADER_APP_NAME = "app_name";
    const HEADER_PACKAGENAME = "app_package";
    const HEADER_APPVERSION = "app_version";
    const HEADER_MOBILE_APN = "mobile_apn";
    const HEADER_MOBILE_BRAND = "mobile_brand";
    const HEADER_NEW_IMEI = "imei";
    const HEADER_NET = "net";
    const HEADER_OS = "os";
    const HEADER_DEVICE_TOKEN = "device_token";
    const HEADER_TOKEN = "Token";
    const HEADER_USER_AGENT = "User-Agent";
    const HEADER_OBAND = "oband";
    /**
     * 请求参数名称
     */
    const REQUEST_USERID = "userId";//用户ID
    const REQUEST_QUERYID_USERID = "queryId";//用户ID
    const REQUEST_OTHER_USERID = "tuserId";//用户ID
    const REQUEST_CHANNEL = "channel";
    const REQUEST_MVERSION = "mversion";
    const REQUEST_PHONE = "phone";//手机号
    const REQUEST_PWD = "pwd";//密码
    const REQUEST_NEWPWD = "newPwd";//新密码
    const REQUEST_MTYPE = "mType";//类型
    const REQUEST_TOKEN = "token";//用户令牌
    const REQUEST_BALANCE = "balance";//用户余额
    const REQUEST_TYPE = "type";//类型
    const REQUEST_PAGE = "page";//分页
    const REQUEST_SAERCHID = "searchId";//每页数量
    const REQUEST_PAGESIZE = "pageSize";//每页数量
    const REQUEST_FACE = "face";//头像
    const REQUEST_NICK = "nick";//昵称
    const REQUEST_SEX = "sex";//昵称
    const REQUEST_BIRTHDAY = "birthday";//年龄
    const REQUEST_HEIGHT = "height";//身高
    const REQUEST_SIGN = "sign";//个性签名
    const REQUEST_PRICE = "price";//价格
    const REQUEST_INVITE = "invite";//是否接受视频邀请
    const REQUEST_INVITECODE = "inviteCode";//邀请码
    const REQUEST_VIDEO_ID = "videoId";//视频ID
    const REQUEST_VIDEO_DESCRIP = "description";//视频描述
    const REQUEST_VIDEO_URL = "videoUrl";//视频url
    const REQUEST_GZ_TYPE = "cType";//0取消1关注
    const REQUEST_ACCOUNT_NAME = "account_name";//开户名
    const REQUEST_BANK_ACCOUNT = "bank_account";//开户账号
    const REQUEST_BANK_NAME = "bank_name";//开户行
    const REQUEST_CONTACT_WAY = "contact_way";//联系方式
    const REQUEST_CASH_MONEY = "cash_money";//提现金额
    const REQUEST_CONTENT = "content";//意见反馈
    const REQUEST_VERSION = "version";//版本号 reason
    const REQUEST_REASON = "reason";//举报原因
    const REQUEST_REMARKS = "remarks";//举报信息
    const REQUEST_GIFTID = "giftId";//礼物id
    const REQUEST_COUNT = "count";//数量
    const REQUEST_HXUSERID = "imId";//环信用户id
    const REQUEST_UID = "uid";//唯一标识
    const REQUEST_CONVER = "cover";//设置封面
    const REQUEST_COMMENT = "comment";//评论语
    const REQUEST_STAR = "star";//星级
    const REQUEST_TAGS = "tags";//用户标签评价
    const REQUEST_WXID = "wxId";//微信号
    const REQUEST_TAG = "tag";//用户类型
    const REQUEST_SEARCH_MSG = "keyword";//搜索的内容
    const REQUEST_INPUT_CHARSET = "_input_charset";//验证银行卡参数
    const REQUEST_CARDNO = "cardNo";//验证银行卡参数
    const REQUEST_CARDBINCHECK = "cardBinCheck";//验证银行卡参数
    const REQUEST_PHONE_VERSION = "phone_version";//手机版本
    const REQUEST_PHONE_MODEL = "phone_model";//手机型号
    const REQUEST_PLATFORM = "platform";//手机平台
    const REQUEST_WARNINTTIME = "warnintTime";//异常警告时间
    const USER_SEX_TYPE = "sex_type";  // 用户性别

    //------------第三方登录字段-----------
    const REQUEST_LOGIN_USERID = "userId";
    const REQUEST_LOGIN_NICK = "nick";
    const REQUEST_LOGIN_TYPE = "type";
    const REQUEST_LOGIN_AGE = "age";
    //------------第三方登录字段-----------

    const REQUEST_RESOURCE_ID = "id";
    const REQUEST_RESOURCE_TYPE = "type";

    /**
     * 解析参数
     */
    const PARESE_RESULT = "result";
    const PARESE_TOPTAG = "topTag";
    const PARESE_BOTTOMTAG = "bottomTag";

    const PARESE_TYPE = "type";
    const PARESE_TEXT = "text";
    const PARESE_ICONURL = "iconUrl";
    const PARESE_ISVISIBLE = "isVisible";

    const INTENT_ADV_URL = "adv_url";

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



