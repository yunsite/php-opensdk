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
	unset($_SESSION[OpenSDK_Sina_Weibo::OAUTH_TOKEN]);
	unset($_SESSION[OpenSDK_Sina_Weibo::ACCESS_TOKEN]);
	unset($_SESSION[OpenSDK_Sina_Weibo::OAUTH_TOKEN_SECRET]);
	echo '<a href="?go_oauth">点击去授权</a>';
}
else if(isset($_SESSION[OpenSDK_Sina_Weibo::ACCESS_TOKEN]) && isset($_SESSION[OpenSDK_Sina_Weibo::OAUTH_TOKEN_SECRET]))
{
	//已经取得授权
	$uinfo = OpenSDK_Sina_Weibo::call('users/show/'.$_SESSION['user_id']);
	echo '你已经获得授权。你的授权信息:<br />';
	echo 'Access token: ' , $_SESSION[OpenSDK_Sina_Weibo::ACCESS_TOKEN] , '<br />';
	echo 'oauth_token_secret: ' , $_SESSION[OpenSDK_Sina_Weibo::OAUTH_TOKEN_SECRET] , '<br />';
	echo '你的微博帐号信息为:<br /><pre>';
	var_dump($uinfo);
	$exit = true;
}
else if( isset($_GET['oauth_token']) && isset($_GET['oauth_verifier']))
{
	//从Callback返回时
	if(OpenSDK_Sina_Weibo::getAccessToken($_GET['oauth_verifier']))
	{
		$uinfo = OpenSDK_Sina_Weibo::call('users/show/'.$_SESSION['user_id']);
		echo '从Opent返回并获得授权。你的微博帐号信息为：<br />';
		echo 'Access token: ' , $_SESSION[OpenSDK_Sina_Weibo::ACCESS_TOKEN] , '<br />';
		echo 'oauth_token_secret: ' , $_SESSION[OpenSDK_Sina_Weibo::OAUTH_TOKEN_SECRET] , '<br />';
		echo '你的微博帐号信息为:<br /><pre>';
		var_dump($uinfo);
	}
	else
	{
		var_dump($_SESSION);
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