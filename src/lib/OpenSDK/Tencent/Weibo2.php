<?php

require_once 'OpenSDK/OAuth2/Client.php';
require_once 'OpenSDK/OAuth/Interface.php';

/**
 * Tencent 微博开放平台（http://open.t.qq.com） SDK OAuth2.0
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
 * 2、require_once 'OpenSDK/Tencent/Weibo2.php';
 * 3、OpenSDK_Tencent_Weibo2::init($appkey,$appsecret);
 * 4、OpenSDK_Tencent_Weibo2::getAuthorizeURL(); 获得跳转授权URL
 * 5、OpenSDK_Tencent_Weibo2::getAccessToken() 获得access token
 * 6、OpenSDK_Tencent_Weibo2::call();调用API接口
 *
 * 建议：
 * 1、PHP5.2 以下版本，可以使用Pear库中的 Service_JSON 来兼容json_decode
 * 2、使用 ::session_set_save_handler 来重写SESSION。调用API接口前需要主动session_start
 * 3、OpenSDK的文件和类名的命名规则符合Pear 和 Zend 规则
 *    如果你的代码也符合这样的标准 可以方便的加入到__autoload规则中
 *
 * @author putersham@gmail.com、icehu@vip.qq.com
 */

class OpenSDK_Tencent_Weibo2 extends OpenSDK_OAuth_Interface
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

    private static $accessTokenURL = 'https://open.t.qq.com/cgi-bin/oauth2/access_token';

    private static $authorizeURL = 'https://open.t.qq.com/cgi-bin/oauth2/authorize';

    /**
     * OAuth 版本
     * @var string
     */
    protected static $version = '2.a';

    /**
     * 存储access_token的session key
     */
    const ACCESS_TOKEN = 'tencent2_access_token';

    /**
     * 存储refresh_token的session key
     */
    const REFRESH_TOKEN = 'tencent2_refresh_token';

    /**
     * 存储expires_in的sieesion key
     */
    const EXPIRES_IN = 'tencent2_expires_in';

    /**
     * authorize接口
     *
     * 对应API：{@link http://wiki.open.t.qq.com/index.php/OAuth2.0%E9%89%B4%E6%9D%83#.E7.AC.AC.E4.B8.80.E6.AD.A5.EF.BC.9A.E8.AF.B7.E6.B1.82code}
     *
     * @param string $url 授权后的回调地址,站外应用需与回调地址一致,站内应用需要填写canvas page的地址
     * @param string $response_type 
     * @param string $display 授权页面类型 可选范围:
     *  - ''       留空默认PC页面
     *  - wap      手机
     * @return string
     */
    public static function getAuthorizeURL($url,$response_type='code',$display=false)
    {
        $params = array();
        $params['client_id'] = self::$client_id;
        $params['redirect_uri'] = $url;
        $params['response_type'] = $response_type;
        $display && $params['wap'] = 2;
        return self::$authorizeURL . '?' . http_build_query($params);
    }

    /**
     * 存储oauth_openid的Session key
     * 腾讯业务通用，与OpenSDK_Tencent_SNS::OAUTH_OPENID 值相同
     */
    const OAUTH_OPENID = 'tensns_oauth_openid';

    /**
     * 存储oauth_openkey的Session key
     * 与OPENID配对使用
     */
    const OAUTH_OPENKEY = 'tensns_oauth_openkey';

    /**
     * 存储oauth_name的Session key
     */
    const OAUTH_NAME = 'tencent_oauth_name';

    /**
     * access_token接口
     *
     * 对应API：{@link http://wiki.open.t.qq.com/index.php/OAuth2.0%E9%89%B4%E6%9D%83#.E7.AC.AC.E4.BA.8C.E6.AD.A5.EF.BC.9A.E8.AF.B7.E6.B1.82accesstoken}
     *
     * @param string $type 请求的类型,可以为:code, token
     * @param array $keys 其他参数：
     *  - 当$type为authorization_code时： array('code'=>..., 'redirect_uri'=>..., 'openid'=>..., 'openkey'=>... )
     *  - 当$type为refresh_token时： array('refresh_token'=>...) // 目前暂不支持
     * @return array
     */
    public static function getAccessToken( $type , $keys )
    {
        if (! in_array($type, array('code', 'token'))) exit("wrong auth type");

        $params = array();
        $params['client_id'] = self::$client_id;
        $params['client_secret'] = self::$client_secret;

        if ( $type === 'token' ) {
            $params['grant_type'] = 'refresh_token';
            $params['refresh_token'] = $keys['refresh_token'];
        }

        if ( $type === 'code' ) {
            $params['grant_type'] = 'authorization_code';
            $params['code'] = $keys['code'];
            $params['redirect_uri'] = $keys['redirect_uri'];

            self::setParam(self::OAUTH_OPENID, $keys['openid']);
            self::setParam(self::OAUTH_OPENKEY, $keys['openkey']);
        }

        $response = self::request(self::$accessTokenURL , 'POST', $params);
        //这儿QQ没返回json
        //$token = OpenSDK_Util::json_decode($response, true);
        $token = array(); // 初始化变量
        parse_str($response, $token);
        if (! is_array($token) || empty($token) || isset($token['error']) )
        {
            $error = isset($token['error']) ? $token['error'] : ''; // 防止Notice空指针异常
            exit('get access token failed.' . $error);
        }

        self::setParam(self::ACCESS_TOKEN, $token['access_token']);
        //self::setParam(self::REFRESH_TOKEN, $token['refresh_token']);
        //刷新token（目前暂不返回）
        self::setParam(self::EXPIRES_IN, $token['expires_in']);
        self::setParam(self::OAUTH_NAME, $token['name']);

        return $token;
    }

    /**
     * 统一调用接口的方法
     * 照着官网的参数往里填就行了
     * http://wiki.open.t.qq.com/index.php/API%E6%96%87%E6%A1%A3
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
     * @param string $command 官方说明中去掉  http://open.t.qq.com/api/ 后面剩余的部分
     * @param array $params 官方说明中接受的参数列表，一个关联数组
     * @param string $method 官方说明中的 method GET/POST
     * @param false|array $multi 是否上传文件 false:普通post array: array ( '{fieldname}'=>'/path/to/file' ) 文件上传
     * @param bool $decode 是否对返回的字符串解码成数组
     * @param OpenSDK_Tencent_Weibo::RETURN_JSON|OpenSDK_Tencent_Weibo::RETURN_XML $format 调用格式
     */
    public static function call($command , $params=array() , $method = 'GET' , $multi=false , $decode=true , $format='json')
    {
        if($format == self::RETURN_XML)
        {
            // DO NOTHING
        }
        else
        {
            $format == self::RETURN_JSON;
        }

        //去掉空数据
        foreach($params as $key => $val)
        {
            if(strlen($val) == 0)
            {
                unset($params[$key]);
            }
        }
        $params['oauth_consumer_key'] = self::$client_id;
        $params['access_token'] = self::getParam(self::ACCESS_TOKEN);
        $params['openid'] = self::getParam(self::OAUTH_OPENID);
        $params['oauth_version'] = self::$version;
        $params['scope'] = 'all';
        $params['appfrom'] = 'OpenSDK2.0beta'; //
        $params['seqid'] = time();
        $params['serverip'] = $_SERVER['SERVER_ADDR'];

        $params['format'] = $format;

        $response = self::request( 'https://open.t.qq.com/api/'.ltrim($command,'/') , $method, $params, $multi);
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
        return self::getOAuth()->request($url, $method, $params, $multi);
    }
}
