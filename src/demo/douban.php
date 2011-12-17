<?php
/**
 * just a demo
 *
 * 仅仅是个demo，未有严格考虑，请不要使用这个简单逻辑到生产环境。
 *
 */
//设置include_path 到 OpenSDK目录
set_include_path(dirname(dirname(__FILE__)) . '/lib/');
require_once 'OpenSDK/Douban/Open.php';

include 'doubanappkey.php';

OpenSDK_Douban_Open::init($appkey, $appsecret);

//打开session
session_start();
header('Content-Type: text/html; charset=utf-8');
$exit = false;
if(isset($_GET['exit']))
{
    OpenSDK_Douban_Open::setParam(OpenSDK_Douban_Open::OAUTH_TOKEN, null);
    OpenSDK_Douban_Open::setParam(OpenSDK_Douban_Open::ACCESS_TOKEN, null);
    OpenSDK_Douban_Open::setParam(OpenSDK_Douban_Open::OAUTH_TOKEN_SECRET, null);
	echo '<a href="?go_oauth">点击去授权</a>';
}
else if( OpenSDK_Douban_Open::getParam (OpenSDK_Douban_Open::ACCESS_TOKEN) &&
         OpenSDK_Douban_Open::getParam (OpenSDK_Douban_Open::OAUTH_TOKEN_SECRET)
        )
{
	//已经取得授权
	$uinfo = OpenSDK_Douban_Open::call('people/'.OpenSDK_Douban_Open::getParam(OpenSDK_Douban_Open::OAUTH_UID));
	echo '你已经获得授权。你的授权信息:<br />';
	echo 'Access token: ' , OpenSDK_Douban_Open::getParam(OpenSDK_Douban_Open::ACCESS_TOKEN) , '<br />';
	echo 'oauth_token_secret: ' , OpenSDK_Douban_Open::getParam(OpenSDK_Douban_Open::OAUTH_TOKEN_SECRET) , '<br />';
	echo '你的豆瓣帐号信息为:<br /><pre>';
	var_dump($uinfo);
	$exit = true;
}
else if( isset($_GET['oauth_token']) )
{
	//从Callback返回时
	if(OpenSDK_Douban_Open::getAccessToken())
	{
		$uinfo = OpenSDK_Douban_Open::call('people/'.OpenSDK_Douban_Open::getParam(OpenSDK_Douban_Open::OAUTH_UID));
		echo '从Opent返回并获得授权。你的授权信息为：<br />';
		echo 'Access token: ' , OpenSDK_Douban_Open::getParam(OpenSDK_Douban_Open::ACCESS_TOKEN) , '<br />';
		echo 'oauth_token_secret: ' , OpenSDK_Douban_Open::getParam(OpenSDK_Douban_Open::OAUTH_TOKEN_SECRET) , '<br />';
		echo '你的豆瓣帐号信息为:<br /><pre>';
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
	$request_token = OpenSDK_Douban_Open::getRequestToken();
	$url = OpenSDK_Douban_Open::getAuthorizeURL($request_token,$callback);
	header('Location: ' . $url);
}
else
{
	echo '豆瓣OAuth1.0接口演示<a href="?go_oauth">点击去授权</a>';
}

if($exit)
{
	echo '<a href="?exit">退出再来一次</a>';
}