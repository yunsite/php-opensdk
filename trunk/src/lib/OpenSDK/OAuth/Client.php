<?php

/**
 * OAuth协议接口
 *
 * 依赖：
 * PHP 5 >= 5.1.2, PECL hash >= 1.1
 * 
 * @ignore
 * @author icehu@vip.qq.com
 *
 */

class OpenSDK_OAuth_Client
{
	/**
	 * 签名的url标签
	 * @var string
	 */
	public $oauth_signature_key = 'oauth_signature';
	
	/**
	 * 不加入签名的请求字段
	 * @var array
	 */
	public $not_signed = array('pic','image');

	/**
	 * app secret
	 * @var string
	 */
	private $_app_secret = '';

	/**
	 * token secret
	 * @var string
	 */
	private $_token_secret = '';

	/**
	 * 设置App secret
	 * @param string $appsecret
	 */
	public function setAppSecret($appsecret)
	{
		$this->_app_secret = $appsecret;
	}

	/**
	 * 设置token secret
	 * @param string $tokensecret
	 */
	public function setTokenSecret($tokensecret)
	{
		$this->_token_secret = $tokensecret;
	}

	/**
	 * 组装参数签名并请求接口
	 *
	 * @param string $url
	 * @param array $params
	 * @param string $method
	 * @param false|array $multi false:普通post array: array ( array('type'=>'mine','name'=>'filename','data'=>'filedata') ) 文件上传
	 * @return string
	 */
	public function request( $url, $method, $params, $multi = false )
	{
		$oauth_signature = $this->sign($url, $method, $params);
		$params[$this->oauth_signature_key] = $oauth_signature;
		return $this->http($url, $params, $method, $multi);
	}

	/**
	 * OAuth 协议的签名
	 *
	 * @param string $url
	 * @param string $method
	 * @param array $params
	 * @return string
	 */
	private function sign( $url , $method, $params )
	{
		foreach( $this->not_signed as $notkey)
		{
			unset($params[$notkey]);
		}
		uksort($params, 'strcmp');
		$pairs = array();
        foreach($params as $key => $value)
        {
			$key = self::urlencode_rfc3986($key);
            if(is_array($value))
            {
                // If two or more parameters share the same name, they are sorted by their value
                // Ref: Spec: 9.1.1 (1)
                natsort($value);
                foreach($value as $duplicate_value)
                {
					$duplicate_value = self::urlencode_rfc3986($duplicate_value);
                    $pairs[] = $key . '=' . $duplicate_value;
                }
            }
            else
            {
				$value = self::urlencode_rfc3986($value);
                $pairs[] = $key . '=' . $value;
            }
        }
		
        $sign_parts = self::urlencode_rfc3986(implode('&', $pairs));
		
		$base_string = implode('&', array( strtoupper($method) , self::urlencode_rfc3986($url) , $sign_parts ));

        $key_parts = array($this->_app_secret, self::urlencode_rfc3986($this->_token_secret));

        $key = implode('&', $key_parts);
        return base64_encode(hash_hmac('sha1', $base_string, $key, true));
	}

	/**
	 * Http请求接口
	 *
	 * @param string $url
	 * @param array $params
	 * @param string $method
	 * @param false|array $multi false:普通post array: array ( array('type'=>'mine','name'=>'filename','data'=>'filedata') ) 文件上传
	 * @return string
	 */
	private function http( $url , $params , $method='GET' , $multi=false )
	{
		$method = strtoupper($method);
		if( !$multi )
		{
			$parts = array();
			foreach ($params as $key => $val)
			{
				$parts[] = urlencode($key) . '=' . urlencode($val);
			}
			if ($parts)
			{
				$postdata = strpos($url, '?') ? '&' : '?' . implode('&', $parts);
				$httpurl = $url . $postdata;
			}
			else
			{
				$postdata = '';
				$httpurl = $url;
			}
		}
		$urls = @parse_url($url);
		$host = $urls['host'];
		$port = $urls['port'] ? $urls['port'] : 80;
		$version = '1.1';
		if($urls['scheme'] === 'https')
        {
            $port = 443;
        }
		$headers = array();
		if($method == 'GET')
		{
			$headers[] = "GET $httpurl HTTP/$version";
		}
		else
		{
			$headers[] = "POST $url HTTP/$version";
		}
		$headers[] = 'Host: ' . $host;
		$headers[] = 'Connection: Close';

		if($method != 'GET')
		{
			if($multi)
			{
				$boundary = uniqid('------------------');
				$MPboundary = '--' . $boundary;
				$endMPboundary = $MPboundary . '--';
				$multipartbody = '';
				$headers[]= 'Content-Type: multipart/form-data; boundary=' . $boundary;
				foreach($params as $key => $val)
				{
					$multipartbody .= $MPboundary . "\r\n";
					$multipartbody .= 'Content-Disposition: form-data; name="' . $key . "\"\r\n\r\n";
					$multipartbody .= $val . "\r\n";
				}
				foreach($multi as $key => $data)
				{
					$multipartbody .= $MPboundary . "\r\n";
					$multipartbody .= 'Content-Disposition: form-data; name="' . $key . '"; filename="' . $data['name'] . '"' . "\r\n";
					$multipartbody .= 'Content-Type: ' . $data['type'] . "\r\n\r\n";
					$multipartbody .= $data['data'] . "\r\n";
				}
				$multipartbody .= $endMPboundary . "\r\n";
				$postdata = $multipartbody;
			}
			else
			{
				$headers[]= 'Content-Type: application/x-www-form-urlencoded';
			}
		}
        $ret = '';
        $fp = fsockopen($host, $port, $errno, $errstr, 10);

        if(! $fp)
        {
            $error = 'Open Socket Error';
			return '';
        }
        else
        {
            fwrite($fp, implode("\r\n", $headers));
			fwrite($fp, "\r\n\r\n");
			if($method != 'GET' && $postdata)
			{
				fwrite($fp, $postdata);
			}
			//skip headers
            while(! feof($fp))
            {
                $ret .= fgets($fp, 1024);
            }
			fclose($fp);
			$pos = strpos($ret, "\r\n\r\n");
			if($pos)
			{
				$rt = trim(substr($ret , $pos+1));
				if(strpos( substr($ret , 0 , $pos), 'Transfer-Encoding: chunked'))
				{
					$response = explode("\r\n", $rt);
					$t = array_slice($response, 1, - 1);

					return implode('', $t);
				}
				return $rt;
			}
			return '';
        }
	}

	/**
	 * rfc3986 encode
	 * why not encode ~ 
	 *
	 * @param string|mix $input
	 * @return string
	 */
	public static function urlencode_rfc3986($input)
    {
        if(is_array($input))
        {
            return array_map( array( __CLASS__ , 'urlencode_rfc3986') , $input);
        }
        else if(is_scalar($input))
		{
			return str_replace('%7E', '~', rawurlencode($input));
		}
		else
		{
			return '';
		}
    }

}
