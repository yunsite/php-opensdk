<?php

require_once 'OpenSDK/OAuth2/Client.php';
require_once 'OpenSDK/OAuth/Interface.php';

/**
 * 腾讯大开放平台（http://open.qq.com） SDK3.0
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
 * 2、require_once 'OpenSDK/Tencent/Open.php';
 * 3、OpenSDK_Tencent_Open::init($appkey,$appsecret);
 * 4、OpenSDK_Tencent_Open::setOpenIDandKey(); 设置openid、openkey、pf参数
 * 6、OpenSDK_Tencent_Open::call();调用API接口
 *
 * 建议：
 * 1、PHP5.2 以下版本，可以使用Pear库中的 Service_JSON 来兼容json_decode
 * 2、使用 ::session_set_save_handler 来重写SESSION。调用API接口前需要主动session_start
 * 3、OpenSDK的文件和类名的命名规则符合Pear 和 Zend 规则
 *    如果你的代码也符合这样的标准 可以方便的加入到__autoload规则中
 *
 * @author icehu@vip.qq.com
 */

class OpenSDK_Tencent_Open extends OpenSDK_OAuth_Interface
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
     * @var OpenSDK_OAuth2_Client
     */
    private static $oauth = null;

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
     * 来源
     */
    const OAUTH_PF = 'ten_pf';

    protected static $host = 'openapi.tencentyun.com';
    protected static $protocol = 'http';
    /**
     * 是否上报业务数据
     * @var bool
     */
    protected static $report = true;
    protected static $stat = 'apistat.tencentyun.com';


    public static function setOpenIDandKey($openid='',$openkey='',$pf='')
    {
        self::setParam(self::OAUTH_OPENID, $openid?$openid:$_GET['openid']);
        self::setParam(self::OAUTH_OPENKEY, $openkey?$openkey:$_GET['openkey']);
        self::setParam(self::OAUTH_PF, $pf?$pf:$_GET['pf']);
    }

    /**
     * 统一调用接口的方法
     * 照着官网的参数往里填就行了
     * http://wiki.open.qq.com/wiki/API%E5%88%97%E8%A1%A8
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
     * @param string $command 官方说明中去掉  http://[域名或IP]/ 后面剩余的部分
     * @param array $params 官方说明中接受的参数列表，一个关联数组
     * @param string $method 官方说明中的 method GET/POST
     * @param false|array $multi 是否上传文件 false:普通post array: array ( '{fieldname}'=>'/path/to/file' ) 文件上传
     * @param bool $decode 是否对返回的字符串解码成数组
     * @param OpenSDK_Tencent_Weibo::RETURN_JSON|OpenSDK_Tencent_Weibo::RETURN_XML $format 调用格式使用默认的json即可
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
        $params['appid'] = self::$client_id;
        $params['openid'] = self::getParam(self::OAUTH_OPENID);
        $params['openkey'] = self::getParam(self::OAUTH_OPENKEY);
        !isset($params['pf']) && $params['pf'] = self::getParam(self::OAUTH_PF);
        $params['userip'] = self::getRemoteIp();
        $params['format'] = $format;

        ksort($params);
        $signarr = array();
        foreach ($params as $key => $val)
        {
            if (strlen($val) == 0)
            {
                unset($params[$key]);
                continue;
            }
            $signarr[] = "$key=$val";
        }
        $signstr = strtoupper($method) . '&' . rawurlencode('/' . ltrim($command, '/')) . '&' . rawurlencode(implode('&', $signarr));
        $params['sig'] = base64_encode(OpenSDK_Util::hash_hmac('sha1', $signstr, self::$client_secret . '&', true));
        if(self::$report)
        {
            $start_time = self::microtime();
        }
        $response = self::request( self::$protocol . '://' . self::$host . '/' .ltrim($command,'/') , $method, $params, $multi);
        if($decode)
        {
            if( $format == self::RETURN_JSON )
            {
                $rt = OpenSDK_Util::json_decode($response, true);
                if(self::$report)
                {
                    self::statReport($start_time, array(
                                'appid' => self::$client_id,
                                'pf' => $params['pf'],
                                'rc' => $rt['ret'],
                                'svr_name' => self::$host,
                                'interface' => $command,
                                'protocol' => self::$protocol,
                                'method' => $method,
                            ));
                }
                return $rt;
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

    /**
     * 获取所有会话参数
     * @return array
     */
    public static function  getParams()
    {
        return array(
            self::OAUTH_OPENID => self::getParam(self::OAUTH_OPENID),
            self::OAUTH_OPENKEY => self::getParam(self::OAUTH_OPENKEY),
            self::OAUTH_PF => self::getParam(self::OAUTH_OPENID),
        );
    }

    protected static function microtime()
    {
        return microtime(true);
    }

    protected static function statReport($start_time, $params)
    {
        $end_time = self::microtime();
        $params['time'] = round($end_time - $start_time, 4);
        $params['timestamp'] = time();
        $params['collect_point'] = 'sdk-php-v3';
        $stat_str = json_encode($params);
        //发送上报信息
        $host_ip = gethostbyname(self::$stat);
        if ($host_ip != self::$stat)
        {
            $sock = socket_create(AF_INET, SOCK_DGRAM, 0);
            if (false === $sock)
            {
                return;
            }
            socket_sendto($sock, $stat_str, strlen($stat_str), 0, $host_ip, 19888);
            socket_close($sock);
        }
    }
}
