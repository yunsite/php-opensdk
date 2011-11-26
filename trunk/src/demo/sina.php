<?php
/**
 * just a demo
 *
 * 仅仅是个demo，未有严格考虑，请不要使用这个简单逻辑到生产环境。
 *
 */
//设置include_path 到 OpenSDK目录
set_include_path(dirname(dirname(__FILE__)) . '/lib/');
require_once 'OpenSDK/Sina/Weibo.php';

include 'sinaappkey.php';

OpenSDK_Sina_Weibo::init($appkey, $appsecret);

//打开session
session_start();
header('Content-Type: text/html; charset=utf-8');
$exit = false;
if(isset($_GET['exit']))
{
    OpenSDK_Sina_Weibo::setParam(OpenSDK_Sina_Weibo::OAUTH_TOKEN, null);
    OpenSDK_Sina_Weibo::setParam(OpenSDK_Sina_Weibo::ACCESS_TOKEN, null);
    OpenSDK_Sina_Weibo::setParam(OpenSDK_Sina_Weibo::OAUTH_TOKEN_SECRET, null);
	echo '<a href="?go_oauth">点击去授权</a>';
}
else if(
        OpenSDK_Sina_Weibo::getParam (OpenSDK_Sina_Weibo::ACCESS_TOKEN) && 
        OpenSDK_Sina_Weibo::getParam (OpenSDK_Sina_Weibo::OAUTH_TOKEN_SECRET)
        )
{
	//已经取得授权
//	$uinfo = OpenSDK_Sina_Weibo::call('users/show/'.OpenSDK_Sina_Weibo::getParam(OpenSDK_Sina_Weibo::OAUTH_USER_ID));
	echo '你已经获得授权。你的授权信息:<br />';
	echo 'Access token: ' , OpenSDK_Sina_Weibo::getParam (OpenSDK_Sina_Weibo::ACCESS_TOKEN) , '<br />';
	echo 'oauth_token_secret: ' , OpenSDK_Sina_Weibo::getParam (OpenSDK_Sina_Weibo::OAUTH_TOKEN_SECRET) , '<br />';
	echo '你的微博帐号信息为:<br /><pre>';
//	var_dump($uinfo);
	/**
	 * 上传一张图片，并发微博
	 */
	var_dump(
	OpenSDK_Sina_Weibo::call('statuses/upload', array(
		'status' => 'test pic',
	), 'POST', array(
		'pic'=>dirname(__FILE__) . '/0.jpg'
		)
	)
			);
	$exit = true;
}
else if( isset($_GET['oauth_token']) && isset($_GET['oauth_verifier']))
{
	//从Callback返回时
	if(OpenSDK_Sina_Weibo::getAccessToken($_GET['oauth_verifier']))
	{
		$uinfo = OpenSDK_Sina_Weibo::call('users/show/'.OpenSDK_Sina_Weibo::getParam(OpenSDK_Sina_Weibo::OAUTH_USER_ID));
		echo '从Opent返回并获得授权。你的微博帐号信息为：<br />';
		echo 'Access token: ' , OpenSDK_Sina_Weibo::getParam (OpenSDK_Sina_Weibo::ACCESS_TOKEN) , '<br />';
		echo 'oauth_token_secret: ' , OpenSDK_Sina_Weibo::getParam (OpenSDK_Sina_Weibo::OAUTH_TOKEN_SECRET) , '<br />';
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
	$request_token = OpenSDK_Sina_Weibo::getRequestToken($callback);
	$url = OpenSDK_Sina_Weibo::getAuthorizeURL($request_token);
	header('Location: ' . $url);
}
else
{
	echo '<a href="?go_oauth">点击去授权</a>';
}

if($exit)
{
	echo '<a href="?exit">退出再来一次</a>';
}