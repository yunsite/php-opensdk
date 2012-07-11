<?php

require_once 'OpenSDK/OAuth2/Client.php';
require_once 'OpenSDK/OAuth/Interface.php';

/**
 * TOP 开放平台（http://open.taobao.com） SDK OAuth2.0
 *
 * 依赖：
 * 1、PECL json >= 1.2.0    (no need now)
 * 2、PHP >= 5.2.0 because json_decode (no need now)
 * 3、$_SESSION
 * 4、PECL hash >= 1.1 (no need now)
 *
 * only need PHP >= 5.0
 *
 * 如何使用：
 * 1、将OpenSDK文件夹放入include_path
 * 2、require_once 'OpenSDK/Taobao/Open.php';
 * 3、OpenSDK_Taobao_Open::init($appkey,$appsecret);
 * 4、OpenSDK_Taobao_Open::getAuthorizeURL(); 获得跳转授权URL
 * 5、OpenSDK_Taobao_Open::getAccessToken() 获得access token
 * 6、OpenSDK_Taobao_Open::call();调用API接口
 *
 * 建议：
 * 1、PHP5.2 以下版本，可以使用Pear库中的 Service_JSON 来兼容json_decode
 * 2、使用 session_set_save_handler 来重写SESSION。调用API接口前需要主动session_start
 * 3、OpenSDK的文件和类名的命名规则符合Pear 和 Zend 规则
 *    如果你的代码也符合这样的标准 可以方便的加入到__autoload规则中
 *
 * @author icehu@vip.qq.com
 */

class OpenSDK_Taobao_Open extends OpenSDK_OAuth_Interface
{

    /**
     * app key
     * @var string
     */
    protected static $client_id = '';
    /**
     * app secret
     * @var string
     */
    protected static $client_secret = '';

    /**
     * 初始化
     * @param string $appkey
     * @param string $appsecret
     */
    public static function init($appkey,$appsecret)
    {
        self::$client_id = $appkey;
        self::$client_secret = $appsecret;
    }
    
    /**
     * OAuth 对象
     * @var OpenSDK_OAuth_Client
     */
    private static $oauth = null;

    private static $accessTokenURL = 'https://oauth.taobao.com/token';

    private static $authorizeURL = 'https://oauth.taobao.com/authorize';

    private static $logoutURL = 'https://oauth.taobao.com/logoff';

    /**
     * OAuth 版本
     * @var string
     */
    protected static $version = '2.0';

    /**
     * authorize接口
     *
     * 对应API：{@link http://open.taobao.com/doc/detail.htm?id=118 Oauth2/authorize}
     *
     * @param string $url 授权后的回调地址,站外应用需与回调地址一致,站内应用需要填写canvas page的地址
     * @param string $response_type 支持的值包括 code 和token 默认值为code
     * @param string $scope 参见scope的定义
     * @param string $state 用于保持请求和回调的状态。在回调时,会在Query Parameter中回传该参数
     * @param string $view 默认为web 可选值web,tmall,wap
     * @return string
     */
    public static function getAuthorizeURL($url,$response_type='code',$scope='',$state='state',$view='web')
    {
        $params = array();
        $params['client_id'] = self::$client_id;
        $params['redirect_uri'] = $url;
        $params['response_type'] = $response_type;
        $params['state'] = $state;
        $params['view'] = $view;
        $scope && $params['scope'] = $scope;
        return self::$authorizeURL . '?' . http_build_query($params);
    }

    /**
	 * access_token接口
	 *
	 * 对应API：{@link 参见scope的定义 OAuth2/access_token}
	 *
	 * @param string $type 请求的类型,可以为:code, password, token
	 * @param array $keys 其他参数：
	 *  - 当$type为code时： array('code'=>..., 'redirect_uri'=>...)
	 *  - 当$type为password时： array('username'=>..., 'password'=>...)
	 *  - 当$type为token时： array('refresh_token'=>...)
	 * @return array
	 */
    public static function getAccessToken( $type , $keys )
    {
        $params = array();
        $params['client_id'] = self::$client_id;
        $params['client_secret'] = self::$client_secret;
        if ( $type === 'token' ) {
            $params['grant_type'] = 'refresh_token';
            $params['refresh_token'] = $keys['refresh_token'];
        } elseif ( $type === 'code' ) {
            $params['grant_type'] = 'authorization_code';
            $params['code'] = $keys['code'];
            $params['redirect_uri'] = $keys['redirect_uri'];
        } elseif ( $type === 'password' ) { //not support!
            $params['grant_type'] = 'password';
            $params['username'] = $keys['username'];
            $params['password'] = $keys['password'];
        } else {
            exit("wrong auth type");
        }

        $response = self::request(self::$accessTokenURL , 'POST', $params);
        $token = OpenSDK_Util::json_decode($response, true);
        if ( is_array($token) && !isset($token['error']) ) 
        {
            self::setParam(self::OAUTH_TIME, time());
            self::setParam(self::ACCESS_TOKEN, $token['access_token']);
            self::setParam(self::EXPIRES_IN, $token['expires_in']);
            self::setParam(self::REFRESH_TOKEN, $token['refresh_token']);
            self::setParam(self::RE_EXPIRES_IN, $token['re_expires_in']);
            self::setParam(self::R1_EXPIRES_IN, $token['r1_expires_in']);
            self::setParam(self::R2_EXPIRES_IN, $token['r2_expires_in']);
            self::setParam(self::W1_EXPIRES_IN, $token['w1_expires_in']);
            self::setParam(self::W2_EXPIRES_IN, $token['w2_expires_in']);
            self::setParam(self::OAUTH_USER_ID, $token['taobao_user_id']);
            self::setParam(self::OAUTH_USER_NICK, urldecode($token['taobao_user_nick']));
        } 
        else
        {
            exit("get access token failed." . $token['error']);
        }
        return $token;
    }

    /**
     * 存储access_token的session key
     */
    const ACCESS_TOKEN = 'top_access_token';

    /**
     * 存储refresh_token的session key
     */
    const REFRESH_TOKEN = 'top_refresh_token';

    /**
     * 存储expires_in的sieesion key
     */
    const EXPIRES_IN = 'top_expires_in';

    /**
     * 存储expires_in的sieesion key
     */
    const RE_EXPIRES_IN = 'top_re_expires_in';

    /**
     * 存储expires_in的sieesion key
     */
    const R1_EXPIRES_IN = 'top_r1_expires_in';

    /**
     * 存储expires_in的sieesion key
     */
    const R2_EXPIRES_IN = 'top_r2_expires_in';

    /**
     * 存储expires_in的sieesion key
     */
    const W1_EXPIRES_IN = 'top_w1_expires_in';

    /**
     * 存储expires_in的sieesion key
     */
    const W2_EXPIRES_IN = 'top_w2_expires_in';

    /**
     * 授权时间
     */
    const OAUTH_TIME = 'top_time';

    /**
     * 淘宝帐号id
     */
    const OAUTH_USER_ID = 'top_user_id';
    /**
     * 淘宝帐号昵称
     */
    const OAUTH_USER_NICK = 'top_user_nick';
    
    /**
     *
     * 文档缺失 不清楚这个logoff的地址是用户访问还是接口调用
     * 假设是用户访问，返回url
     *
     * @param string $url
     * @param string $view
     */
    public static function getlogoffURL($url,$view='web')
    {
        $params = array();
        $params['client_id'] = self::$client_id;
        $params['redirect_uri'] = $url;
        $params['view'] = $view;
        //这里是什么意思，后台退出还是用户访问？
        return self::$logoutURL . '?' . http_build_query($params);
    }

    /**
     * 生成签名
     * @param <type> $paramArr
     * @return <type>
     */
    private static function createSign($paramArr)
    {
        $sign = self::$client_secret;
        ksort($paramArr);
        foreach ($paramArr as $key => $val)
        {
            if ($key != '' && $val != '')
            {
                $sign .= $key . $val;
            }
        }
        $sign = strtoupper(md5($sign . self::$client_secret));
        return $sign;
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
     * @param string $method 官方说明中的method
     * @param array $params 官方说明中接受的参数列表，一个关联数组
     * @param false|array $multi 是否上传文件 false:普通post array: array ( '{fieldname}'=>'/path/to/file' ) 文件上传
     * @param bool $decode 是否对返回的字符串解码成数组
     * @param OpenSDK_Top_Open::RETURN_JSON|OpenSDK_Top_Open::RETURN_XML $format 调用格式
     */
    public static function call($method , $params=array() , $multi=false , $decode=true , $format=self::RETURN_JSON)
    {
        if($format == self::RETURN_XML)
            ;
        else
            $format == self::RETURN_JSON;
        //去掉空数据
        foreach($params as $key => $val)
        {
            if(strlen($val) == 0)
            {
                unset($params[$key]);
            }
        }
        $params['session'] = self::getParam(self::ACCESS_TOKEN);
        $params['format'] = $format;
        $params['method'] = $method;
        $params['timestamp'] = date('Y-m-d H:i:s');
        $params['app_key'] = self::$client_id;
        $params['v'] = '2.0';
        $params['sign_method'] = 'md5';
        $params['sign'] = self::createSign($params);
        //使用http方式访问，性能更高
        $response = self::request( 'http://gw.api.taobao.com/router/rest' , 'POST', $params, $multi);
        if($decode)
        {
            if( $format == self::RETURN_JSON )
            {
                return OpenSDK_Util::json_decode($response, true);
            }
            else
            {
                //todo parse xml2array later
                //没必要。用json即可!
                return $response;
            }
        }
        else
        {
            return $response;
        }
    }

    /**
     *
     * 基于TOP协议的登录返回参数校验
     * 从TOP协议返回的串中解析授权参数，成功返回解析完数据，失败返回false
     *
     * @param number $tsdelay 时间校验允许误差
     * @return array|bool
     */
    public static function parseTopParameters($tsdelay=300)
    {
        $top_appkey = $_GET['top_appkey'];
        if($top_appkey != self::$client_id)
        {
            return false;
        }
        $top_parameters = $_GET['top_parameters'];
        $top_session = $_GET['top_session'];
        $top_sign = $_GET['top_sign'];

        $md5 = md5($top_appkey . $top_parameters . $top_session . self::$client_secret, true);
        $sign = base64_encode($md5);

        if ($sign != $top_sign)
        {
            return false;
        }
        $token = array();
	parse_str(base64_decode($top_parameters), $token);
        $now = time();
        $ts = $token['ts'] / 1000;
        if ($ts > ( $now + $tsdelay ) || $now > ( $ts + $tsdelay ))
        {
            return false;
        }
        self::setParam(self::OAUTH_TIME, time());
        self::setParam(self::ACCESS_TOKEN, $_GET['top_session']);
        self::setParam(self::EXPIRES_IN, $token['expires_in']);
        self::setParam(self::REFRESH_TOKEN, $token['refresh_token']);
        self::setParam(self::RE_EXPIRES_IN, $token['re_expires_in']);
        self::setParam(self::R1_EXPIRES_IN, $token['r1_expires_in']);
        self::setParam(self::R2_EXPIRES_IN, $token['r2_expires_in']);
        self::setParam(self::W1_EXPIRES_IN, $token['w1_expires_in']);
        self::setParam(self::W2_EXPIRES_IN, $token['w2_expires_in']);
        self::setParam(self::OAUTH_USER_ID, $token['visitor_id']);
        self::setParam(self::OAUTH_USER_NICK, urldecode($token['visitor_nick']));
        return $token;
    }

    protected static $_debug = false;

    public static function debug($debug=false)
    {
        self::$_debug = $debug;
    }

    /**
     * 获得OAuth2 对象
     * @return OpenSDK_OAuth2_Client
     */
    protected static function getOAuth()
    {
        if( null === self::$oauth )
        {
            self::$oauth = new OpenSDK_OAuth2_Client(self::$_debug);
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
        if(!self::$client_id || !self::$client_secret)
        {
            exit('app key or app secret not init');
        }
        $headers = array();
        return self::getOAuth()->request($url, $method, $params, $multi ,$headers);
    }

    /**
     * 获取所有会话参数
     * @return array
     */
    public static function  getParams()
    {
        return array(
            self::ACCESS_TOKEN => self::getParam(self::ACCESS_TOKEN),
            self::OAUTH_TIME => self::getParam(self::OAUTH_TIME),
            self::EXPIRES_IN => self::getParam(self::EXPIRES_IN),
            self::REFRESH_TOKEN => self::getParam(self::REFRESH_TOKEN),
            self::RE_EXPIRES_IN => self::getParam(self::RE_EXPIRES_IN),
            self::R1_EXPIRES_IN => self::getParam(self::R1_EXPIRES_IN),
            self::R2_EXPIRES_IN => self::getParam(self::R2_EXPIRES_IN),
            self::W1_EXPIRES_IN => self::getParam(self::W1_EXPIRES_IN),
            self::W2_EXPIRES_IN => self::getParam(self::W2_EXPIRES_IN),
            self::OAUTH_USER_ID => self::getParam(self::OAUTH_USER_ID),
            self::OAUTH_USER_NICK => self::getParam(self::OAUTH_USER_NICK),
        );
    }

}
