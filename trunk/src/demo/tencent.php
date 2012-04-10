<?php
/**
 * just a demo
 *
 * 仅仅是个demo，未有严格考虑，请不要使用这个简单逻辑到生产环境。
 *
 */
//设置include_path 到 OpenSDK目录
set_include_path(dirname(dirname(__FILE__)) . '/lib/');
require_once 'OpenSDK/Tencent/Weibo.php';

include 'tencentappkey.php';

OpenSDK_Tencent_Weibo::init($appkey, $appsecret);

//打开session
session_start();
header('Content-Type: text/html; charset=utf-8');
$exit = false;
if(isset($_GET['exit']))
{
    OpenSDK_Tencent_Weibo::setParam(OpenSDK_Tencent_Weibo::OAUTH_TOKEN, null);
    OpenSDK_Tencent_Weibo::setParam(OpenSDK_Tencent_Weibo::ACCESS_TOKEN, null);
    OpenSDK_Tencent_Weibo::setParam(OpenSDK_Tencent_Weibo::OAUTH_TOKEN_SECRET, null);
	echo '<a href="?go_oauth">点击去授权</a>';
}
else if( OpenSDK_Tencent_Weibo::getParam (OpenSDK_Tencent_Weibo::ACCESS_TOKEN) &&
         OpenSDK_Tencent_Weibo::getParam (OpenSDK_Tencent_Weibo::OAUTH_TOKEN_SECRET)
        )
{
	//已经取得授权
	$uinfo = OpenSDK_Tencent_Weibo::call('user/info');
	echo '你已经获得授权。你的授权信息:<br />';
	echo 'Access token: ' , OpenSDK_Tencent_Weibo::getParam(OpenSDK_Tencent_Weibo::ACCESS_TOKEN) , '<br />';
	echo 'oauth_token_secret: ' , OpenSDK_Tencent_Weibo::getParam(OpenSDK_Tencent_Weibo::OAUTH_TOKEN_SECRET) , '<br />';
	echo '你的微博帐号信息为:<br /><pre>';
	var_dump($uinfo);
	/**
	 * 上传一张图片并发一条微博
	 */
	var_dump(

            OpenSDK_Tencent_Weibo::call(
                't/add_pic',
                array(
                    'content' => 'test pic' . time(),
                    'clientip' => '123.119.32.253',
                ),
                'POST',
                array('pic' => dirname(__FILE__) . '/0.jpg',)
            )
    );
	$exit = true;
}
else if( isset($_GET['oauth_token']) && isset($_GET['oauth_verifier']))
{
	//从Callback返回时
	if(OpenSDK_Tencent_Weibo::getAccessToken($_GET['oauth_verifier']))
	{
		$uinfo = OpenSDK_Tencent_Weibo::call('user/info');
		echo '从Opent返回并获得授权。你的微博帐号信息为：<br />';
		echo 'Access token: ' , OpenSDK_Tencent_Weibo::getParam(OpenSDK_Tencent_Weibo::ACCESS_TOKEN) , '<br />';
		echo 'oauth_token_secret: ' , OpenSDK_Tencent_Weibo::getParam(OpenSDK_Tencent_Weibo::OAUTH_TOKEN_SECRET) , '<br />';
		echo '你的微博帐号信息为:<br /><pre>';
		var_dump($uinfo);
	}
	else
	{
		echo '获得Access Tokn 失败';
	}
	$exit = true;
}
else if(isset($_GET['go_oauth']))
{
	$callback = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
	$request_token = OpenSDK_Tencent_Weibo::getRequestToken($callback);
        !$request_token && exit('获取request_token失败，请检查网络或者appkey和appsecret是否正确');
	$url = OpenSDK_Tencent_Weibo::getAuthorizeURL($request_token);
	header('Location: ' . $url);
}
else
{
	echo '腾讯微博OAuth1.0接口演示<a href="?go_oauth">点击去授权</a>';
}

if($exit)
{
	echo '<a href="?exit">退出再来一次</a>';
}