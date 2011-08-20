<?php
/**
 * just a demo
 *
 * 仅仅是个demo，未有严格考虑，请不要使用这个简单逻辑到生产环境。
 *
 */
//设置include_path 到 OpenSDK目录
set_include_path(dirname(dirname(__FILE__)) . '/lib/');
require_once 'OpenSDK/Tencent/SNS.php';

include 'tencentsnsappkey.php';

OpenSDK_Tencent_SNS::init($appkey, $appsecret);

//打开session
session_start();
header('Content-Type: text/html; charset=utf-8');
$exit = false;
if(isset($_GET['exit']))
{
	unset($_SESSION[OpenSDK_Tencent_SNS::OAUTH_TOKEN]);
	unset($_SESSION[OpenSDK_Tencent_SNS::ACCESS_TOKEN]);
	unset($_SESSION[OpenSDK_Tencent_SNS::OAUTH_TOKEN_SECRET]);
	echo '<a href="?go_oauth">点击去授权</a>';
}
else if(isset($_SESSION[OpenSDK_Tencent_SNS::ACCESS_TOKEN]) && isset($_SESSION[OpenSDK_Tencent_SNS::OAUTH_TOKEN_SECRET]))
{
	//已经取得授权
	$uinfo = OpenSDK_Tencent_SNS::call('user/get_user_info');
	echo '你已经获得授权。你的授权信息:<br />';
	echo 'Access token: ' , $_SESSION[OpenSDK_Tencent_SNS::ACCESS_TOKEN] , '<br />';
	echo 'oauth_token_secret: ' , $_SESSION[OpenSDK_Tencent_SNS::OAUTH_TOKEN_SECRET] , '<br />';
	echo 'openid: ' , $_SESSION[OpenSDK_Tencent_SNS::OAUTH_OPENID] , '<br />';
	echo '你的QQ空间帐号信息为:<br /><pre>';
	var_dump($uinfo);
	/**
	 * 上传一张图片
	 */
	echo '发表一条心情' , '<br />';
	var_dump( OpenSDK_Tencent_SNS::call('shuoshuo/add_topic', array('con'=>'一条来自OpenSDK的心情'), 'POST') );
	echo '上传一张图片' , '<br />';
	var_dump( OpenSDK_Tencent_SNS::call('photo/upload_pic', array(
		'title' => 'test_pic',
		'photodesc' => '来自OpenSDK的照片上传',
	), 'POST', array(
		'picture' => array(
			'type' => 'image/jpg',
			'name' => '0.jpg',
			'data' => file_get_contents('0.jpg'),
		),
	)) );
	$exit = true;
}
else if( isset($_GET['oauth_token']) && isset($_GET['oauth_vericode']))
{
	//从Callback返回时
	if(OpenSDK_Tencent_SNS::getAccessToken($_GET['oauth_vericode']))
	{
		$uinfo = OpenSDK_Tencent_SNS::call('user/get_user_info');
		echo '从Opent返回并获得授权。你的QQ空间帐号信息为：<br />';
		echo 'Access token: ' , $_SESSION[OpenSDK_Tencent_SNS::ACCESS_TOKEN] , '<br />';
		echo 'oauth_token_secret: ' , $_SESSION[OpenSDK_Tencent_SNS::OAUTH_TOKEN_SECRET] , '<br />';
		echo 'openid: ' , $_SESSION[OpenSDK_Tencent_SNS::OAUTH_OPENID] , '<br />';
		echo '你的QQ空间帐号信息为:<br /><pre>';
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
	$request_token = OpenSDK_Tencent_SNS::getRequestToken();
	$url = OpenSDK_Tencent_SNS::getAuthorizeURL($request_token,$callback);
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