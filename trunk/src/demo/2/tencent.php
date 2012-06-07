<?php
/**
 * just a demo
 *
 * 仅仅是个demo，未有严格考虑，请不要使用这个简单逻辑到生产环境。
 *
 */
//设置include_path 到 OpenSDK目录
set_include_path(dirname(dirname(dirname(__FILE__))) . '/lib/');
require_once 'OpenSDK/Tencent/Weibo2.php';

include '../tencentappkey.php';

OpenSDK_Tencent_Weibo2::init($appkey, $appsecret);

//打开session
session_start();
header('Content-Type: text/html; charset=utf-8');
$exit = false;
if(isset($_GET['exit']))
{
	OpenSDK_Tencent_Weibo2::setParam(OpenSDK_Tencent_Weibo2::ACCESS_TOKEN, null);
        OpenSDK_Tencent_Weibo2::setParam(OpenSDK_Tencent_Weibo2::REFRESH_TOKEN, null);
	OpenSDK_Tencent_Weibo2::setParam(OpenSDK_Tencent_Weibo2::OAUTH_OPENID, null);
	OpenSDK_Tencent_Weibo2::setParam(OpenSDK_Tencent_Weibo2::OAUTH_OPENKEY, null);
	OpenSDK_Tencent_Weibo2::setParam(OpenSDK_Tencent_Weibo2::OAUTH_NAME, null);
    echo '<a href="?go_oauth">点击去授权</a>';
}
else if(
        OpenSDK_Tencent_Weibo2::getParam (OpenSDK_Tencent_Weibo2::ACCESS_TOKEN)
        )
{
    //已经取得授权
    $uinfo = OpenSDK_Tencent_Weibo2::call('user/info');
	echo '从Opent返回并获得授权。你的微博帐号信息为：<br />';
	echo 'Access token: ' , OpenSDK_Tencent_Weibo2::getParam (OpenSDK_Tencent_Weibo2::ACCESS_TOKEN) , '<br />';
	echo 'Refresh token: 腾讯微博2.a暂不提供刷新令牌<br />';
	echo 'Expire in：' , OpenSDK_Tencent_Weibo2::getParam(OpenSDK_Tencent_Weibo2::EXPIRES_IN) , '<br />';
	echo 'Name:' , OpenSDK_Tencent_Weibo2::getParam(OpenSDK_Tencent_Weibo2::OAUTH_NAME) , '<br />';
	echo '你的微博帐号信息为:<br /><pre>';
	var_dump($uinfo);
    /**
     * 上传一张图片，并发微博
     */
    var_dump(
		OpenSDK_Tencent_Weibo2::call('t/add_pic', array(
				'content' => 'test pic',
				'clientip' => '210.22.88.242',
			), 'POST', array(
				'pic'=>dirname(dirname(__FILE__)) . '/0.jpg'
			)
		)
	);
    $exit = true;
}
else if( isset($_GET['code']))
{
    //从Callback返回时
    if(OpenSDK_Tencent_Weibo2::getAccessToken('code',array('code'=>$_GET['code'],'redirect_uri'=>'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'], 'openid'=>$_GET['openid'], 'openkey'=>$_GET['openkey'])))
    {
		$uinfo = OpenSDK_Tencent_Weibo2::call('user/info');
		echo '从Opent返回并获得授权。你的微博帐号信息为：<br />';
		echo 'Access token: ' , OpenSDK_Tencent_Weibo2::getParam (OpenSDK_Tencent_Weibo2::ACCESS_TOKEN) , '<br />';
		echo 'Refresh token: 腾讯微博2.a暂不提供刷新令牌<br />';
		echo 'Expire in：' , OpenSDK_Tencent_Weibo2::getParam(OpenSDK_Tencent_Weibo2::EXPIRES_IN) , '<br />';
		echo 'Name:' , OpenSDK_Tencent_Weibo2::getParam(OpenSDK_Tencent_Weibo2::OAUTH_NAME) , '<br />';
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
    $url = OpenSDK_Tencent_Weibo2::getAuthorizeURL($callback);
    header('Location: ' . $url);
}
else
{
    echo '腾讯微博OAuth2.0演示<a href="?go_oauth">点击去授权</a>';
}
if($exit)
{
    echo '<a href="?exit">退出再来一次</a>';
}