<?php

require_once 'OpenSDK/OAuth/Interface.php';
require_once 'OpenSDK/OAuth/QQSNSClient.php';

/**
 * 腾讯社区开放平台（QQ登陆）http://opensns.qq.com OAuth1.0 SDK
 * 腾讯社区开放平台无论是OAuth1.0还是OAuth2.0都有很多地方不遵守规范，让人很蛋疼。请源代码中搜索 “囧” 查看不守规范的地方
 * 依赖：
 * 1、PECL json >= 1.2.0    no need now
 * 2、PHP >= 5.2.0 because json_decode no need now
 * 3、$_SESSION
 * 4、PECL hash >= 1.1 no need now
 *
 * only need PHP >= 5.0
 *
 * 如何使用：
 * 1、将OpenSDK文件夹放入include_path
 * 2、require_once 'OpenSDK/Tencent/SNS.php';
 * 3、OpenSDK_Tencent_SNS::init($appkey,$appsecret);
 * 4、OpenSDK_Tencent_SNS::getRequestToken(); 获得request token
 * 5、OpenSDK_Tencent_SNS::getAuthorizeURL($token,$callback); 获得跳转授权URL
 * 6、OpenSDK_Tencent_SNS::getAccessToken($oauth_verifier) 获得access token
 * 7、OpenSDK_Tencent_SNS::call();调用API接口
 *
 * 建议：
 * 1、PHP5.2 以下版本，可以使用Pear库中的 Service_JSON 来兼容json_decode
 * 2、使用 session_set_save_handler 来重写SESSION。调用API接口前需要主动session_start
 * 3、OpenSDK的文件和类名的命名规则符合Pear 和 Zend 规则
 *    如果你的代码也符合这样的标准 可以方便的加入到__autoload规则中
 *
 * @author icehu@vip.qq.com
 */

class OpenSDK_Tencent_SNS extends OpenSDK_OAuth_Interface
{

    /**
     * app key
     * @var string
     */
    protected static $_appkey = '';
    /**
     * app secret
     * @var string
     */
    protected static $_appsecret = '';

    /**
     * 初始化
     * @param string $appkey
     * @param string $appsecret
     */
    public static function init($appkey,$appsecret)
    {
        self::$_appkey = $appkey;
        self::$_appsecret = $appsecret;
    }
    
    private static $accessTokenURL = 'http://openapi.qzone.qq.com/oauth/qzoneoauth_access_token';

    private static $authorizeURL = 'http://openapi.qzone.qq.com/oauth/qzoneoauth_authorize';

    private static $requestTokenURL = 'http://openapi.qzone.qq.com/oauth/qzoneoauth_request_token';

    /**
     * OAuth 对象
     * @var OpenSDK_OAuth_Client
     */
    protected static $oauth = null;
    /**
     * OAuth 版本
     * @var string
     */
    protected static $version = '1.0';
    /**
     * 存储oauth_token的session key
     */
    const OAUTH_TOKEN = 'tensns_oauth_token';
    /**
     * 存储oauth_token_secret的session key
     */
    const OAUTH_TOKEN_SECRET = 'tensns_oauth_token_secret';
    /**
     * 存储access_token的session key
     */
    const ACCESS_TOKEN = 'tensns_access_token';

    /**
     * 存储oauth_openid的Session key
     */
    const OAUTH_OPENID = 'tensns_oauth_openid';

    /**
     * 获取requestToken
     *
     * 返回的数组包括：
     * oauth_token：返回的request_token
     * oauth_token_secret：返回的request_secret
     * oauth_callback_confirmed：回调确认
     *
     * @return array
     */
    public static function getRequestToken()
    {
        self::getOAuth()->setTokenSecret('');
        $response = self::request( self::$requestTokenURL, 'GET' , array() );
        parse_str($response , $rt);
        if($rt['oauth_token'] && $rt['oauth_token_secret'])
        {
            self::getOAuth()->setTokenSecret($rt['oauth_token_secret']);
            self::setParam(self::OAUTH_TOKEN, $rt['oauth_token']);
            self::setParam(self::OAUTH_TOKEN_SECRET, $rt['oauth_token_secret']);
            return $rt;
        }
        else
        {
            return false;
        }
    }

    /**
     *
     * 获得授权URL
     *
     * @param string|array $token
     * @param bool $callback 回调地址
     * @return string
     */
    public static function getAuthorizeURL($token , $callback)
    {
        if(is_array($token))
        {
            $token = $token['oauth_token'];
        }
        return self::$authorizeURL . '?oauth_token=' . $token . '&oauth_consumer_key=' . self::$_appkey . '&oauth_callback=' . rawurlencode($callback);
    }

    /**
     * 获得Access Token
     * @param string $oauth_verifier
     * @return array
     */
    public static function getAccessToken( $oauth_verifier = false )
    {
        $response = self::request( self::$accessTokenURL, 'GET' , array(
            'oauth_token' => self::getParam(self::OAUTH_TOKEN),
            //囧 不合规范的参数 oauth_vericode OAuth的标准参数是 oauth_verifier
            'oauth_vericode' => $oauth_verifier,
        ));
        parse_str($response,$rt);
        if( $rt['oauth_token'] && $rt['oauth_token_secret'] )
        {
            self::getOAuth()->setTokenSecret($rt['oauth_token_secret']);
            self::setParam(self::ACCESS_TOKEN, $rt['oauth_token']);
            self::setParam(self::OAUTH_TOKEN_SECRET, $rt['oauth_token_secret']);
            self::setParam(self::OAUTH_OPENID, $rt['openid']);
            return $rt;
        }
        return false;
    }

    /**
     * 统一调用接口的方法
     * 照着官网的参数往里填就行了
     * 需要调用哪个就填哪个，如果方法调用得频繁，可以封装更方便的方法。
     *
     * 如果上传文件 $method = 'POST';
     * $multi 是一个二维数组
     *
     * array(
     *    '{fieldname}' => array(        //第一个文件
     *        'type' => 'mine 类型',
     *        'name' => 'filename',
     *        'data' => 'filedata 字节流',
     *    ),
     *    ...如果接受多个文件，可以再加
     * )
     *
     * @param string $command 官方说明中去掉 http://openapi.qzone.qq.com/ 后面剩余的部分
     * @param array $params 官方说明中接受的参数列表，一个关联数组
     * @param string $method 官方说明中的 method GET/POST
     * @param false|array $multi 是否上传文件  false:普通post array: array ( '{fieldname}'=>'/path/to/file' ) 文件上传
     * @param bool $decode 是否对返回的字符串解码成数组
     * @param OpenSDK_Tencent_Weibo::RETURN_JSON|OpenSDK_Tencent_Weibo::RETURN_XML $format 调用格式
     */
    public static function call($command , $params=array() , $method = 'GET' , $multi=false ,$decode=true , $format=self::RETURN_JSON)
    {
        if($format == self::RETURN_XML)
            ;
        else
            $format == self::RETURN_JSON;
        $params['format'] = $format;
        //去掉空数据
        foreach($params as $key => $val)
        {
            if(strlen($val) == 0)
            {
                unset($params[$key]);
            }
        }
        $params['oauth_token'] = self::getParam(self::ACCESS_TOKEN);
        $response = self::request( 'http://openapi.qzone.qq.com/'.ltrim($command,'/') , $method, $params, $multi);
        if($decode)
        {
            if($format == self::RETURN_JSON)
            {
                return OpenSDK_Util::json_decode($response, true);
            }
            else
            {
                //todo parse xml2array later
                //其实没必要。用json即可
                return $response;
            }
        }
        else
        {
            return $response;
        }
    }

    /**
     * 重置Oauth对象
     * 在批量脚本中，如果同时操作多个用户，完成一个用户的操作后，需要重置
     */
    public static function clearOauth()
    {
        self::$oauth = null;
    }

    protected static $_debug = false;

    public static function debug($debug=false)
    {
        self::$_debug = $debug;
    }

    /**
     * 获得OAuth 对象
     * @return OpenSDK_OAuth_Client
     */
    protected static function getOAuth()
    {
        if( null === self::$oauth )
        {
            self::$oauth = new OpenSDK_OAuth_QQSNSClient(self::$_appsecret,self::$_debug);
            $secret = self::getParam(self::OAUTH_TOKEN_SECRET);
            if($secret)
            {
                self::$oauth->setTokenSecret($secret);
            }
        }
        return self::$oauth;
    }

    /**
     *
     * OAuth协议请求接口
     *
     * @param string $url
     * @param string $method
     * @param array $params
     * @param array $multi
     * @return string
     * @ignore
     */
    protected static function request($url , $method , $params , $multi=false)
    {
        if(!self::$_appkey || !self::$_appsecret)
        {
            exit('app key or app secret not init');
        }
        //囧 oauth_nonce必须是数字
        $params['oauth_nonce'] = mt_rand();
        $params['oauth_consumer_key'] = self::$_appkey;
        $params['oauth_signature_method'] = 'HMAC-SHA1';
        $params['oauth_version'] = self::$version;
        $params['oauth_timestamp'] = self::getTimestamp();
        //openid
        if($openid = self::getParam(self::OAUTH_OPENID))
        {
            $params['openid'] = $openid;
        }
        return self::getOAuth()->request($url, $method, $params, $multi);
    }

    /**
     * 获取所有会话参数
     * @return array
     */
    public static function getParams()
    {
        return array(
            self::ACCESS_TOKEN => self::getParam(self::ACCESS_TOKEN),
            self::OAUTH_OPENID => self::getParam(self::OAUTH_OPENID),
            self::OAUTH_TOKEN => self::getParam(self::OAUTH_TOKEN),
            self::OAUTH_TOKEN_SECRET => self::getParam(self::OAUTH_TOKEN_SECRET),
        );
    }
}
