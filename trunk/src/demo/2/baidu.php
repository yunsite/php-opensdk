<?php
/**
 * just a demo
 *
 * 仅仅是个demo，未有严格考虑，请不要使用这个简单逻辑到生产环境。
 *
 */
//设置include_path 到 OpenSDK目录
set_include_path(dirname(dirname(dirname(__FILE__))) . '/lib/');
require_once 'OpenSDK/Baidu/Open.php';

include '../baiduappkey.php';

OpenSDK_Baidu_Open::init($appkey, $appsecret);

//打开session
session_start();
header('Content-Type: text/html; charset=utf-8');
$exit = false;
if(isset($_GET['exit']))
{
    OpenSDK_Baidu_Open::setParam(OpenSDK_Baidu_Open::ACCESS_TOKEN, null);
    OpenSDK_Baidu_Open::setParam(OpenSDK_Baidu_Open::REFRESH_TOKEN, null);
    echo '<a href="?go_oauth">点击去授权</a>';
}
else if(
        OpenSDK_Baidu_Open::getParam (OpenSDK_Baidu_Open::ACCESS_TOKEN)
        )
{
    //已经取得授权
    $uinfo = OpenSDK_Baidu_Open::call('passport/users/getLoggedInUser',array());
    echo '你已经获得授权。你的授权信息:<br />';
    echo 'Access token: ' , OpenSDK_Baidu_Open::getParam (OpenSDK_Baidu_Open::ACCESS_TOKEN) , '<br />';
    echo 'Refresh token: ' , OpenSDK_Baidu_Open::getParam (OpenSDK_Baidu_Open::REFRESH_TOKEN) , '<br />';
    echo 'Session key: ' , OpenSDK_Baidu_Open::getParam (OpenSDK_Baidu_Open::SESSION_KEY) , '<br />';
    echo 'Session secret: ' , OpenSDK_Baidu_Open::getParam (OpenSDK_Baidu_Open::SESSION_SECRET) , '<br />';
    echo 'Expire in：' , OpenSDK_Baidu_Open::getParam(OpenSDK_Baidu_Open::EXPIRES_IN) , '<br />';
    echo '你的个人信息:<br /><pre>';
    var_dump($uinfo);
    
    $exit = true;
}
else if( isset($_GET['code']))
{
    //从Callback返回时
    if(OpenSDK_Baidu_Open::getAccessToken('code',array('code'=>$_GET['code'],'redirect_uri'=>'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'])))
    {
        $uinfo = OpenSDK_Baidu_Open::call('passport/users/getLoggedInUser');
        echo '从Opent返回并获得授权。你的人人帐号信息为：<br />';
        echo 'Access token: ' , OpenSDK_Baidu_Open::getParam (OpenSDK_Baidu_Open::ACCESS_TOKEN) , '<br />';
        echo 'Refresh token: ' , OpenSDK_Baidu_Open::getParam (OpenSDK_Baidu_Open::REFRESH_TOKEN) , '<br />';
        echo 'Session key: ' , OpenSDK_Baidu_Open::getParam (OpenSDK_Baidu_Open::SESSION_KEY) , '<br />';
        echo 'Session secret: ' , OpenSDK_Baidu_Open::getParam (OpenSDK_Baidu_Open::SESSION_SECRET) , '<br />';
        echo 'Expire in：' , OpenSDK_Baidu_Open::getParam(OpenSDK_Baidu_Open::EXPIRES_IN) , '<br />';
        echo '你的个人信息:<br /><pre>';
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
    $url = OpenSDK_Baidu_Open::getAuthorizeURL($callback, 'code', 'state');
    header('Location: ' . $url);
}
else
{
    echo '百度开放平台OAuth2.0演示<a href="?go_oauth">点击去授权</a>';
}
if($exit)
{
    echo '<a href="?exit">退出再来一次</a>';
}