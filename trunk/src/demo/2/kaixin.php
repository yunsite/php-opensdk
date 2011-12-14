<?php
/**
 * just a demo
 *
 * 仅仅是个demo，未有严格考虑，请不要使用这个简单逻辑到生产环境。
 *
 */
//设置include_path 到 OpenSDK目录
set_include_path(dirname(dirname(dirname(__FILE__))) . '/lib/');
require_once 'OpenSDK/Kaixin/SNS2.php';

include '../kxappkey.php';

OpenSDK_Kaixin_SNS2::init($appkey, $appsecret);

//打开session
session_start();
header('Content-Type: text/html; charset=utf-8');
$exit = false;
if(isset($_GET['exit']))
{
    OpenSDK_Kaixin_SNS2::setParam(OpenSDK_Kaixin_SNS2::ACCESS_TOKEN, null);
    OpenSDK_Kaixin_SNS2::setParam(OpenSDK_Kaixin_SNS2::REFRESH_TOKEN, null);
    echo '<a href="?go_oauth">点击去授权</a>';
}
else if(
        OpenSDK_Kaixin_SNS2::getParam (OpenSDK_Kaixin_SNS2::ACCESS_TOKEN)
        )
{
    //已经取得授权
    $uinfo = OpenSDK_Kaixin_SNS2::call('users/me',array(),'GET');
    echo '你已经获得授权。你的授权信息:<br />';
    echo 'Access token: ' , OpenSDK_Kaixin_SNS2::getParam (OpenSDK_Kaixin_SNS2::ACCESS_TOKEN) , '<br />';
    echo 'Refresh token: ' , OpenSDK_Kaixin_SNS2::getParam (OpenSDK_Kaixin_SNS2::REFRESH_TOKEN) , '<br />';
    echo 'Expire in：' , OpenSDK_Kaixin_SNS2::getParam(OpenSDK_Kaixin_SNS2::EXPIRES_IN) , '<br />';
    echo 'Scope:' , OpenSDK_Kaixin_SNS2::getParam(OpenSDK_Kaixin_SNS2::SCOPE) , '<br />';
    echo '你的开心帐号信息为:<br /><pre>';
    var_dump($uinfo);
    
    $exit = true;
}
else if( isset($_GET['code']))
{
    //从Callback返回时
    if(OpenSDK_Kaixin_SNS2::getAccessToken('code',array('code'=>$_GET['code'],'redirect_uri'=>'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'])))
    {
        $uinfo = OpenSDK_Kaixin_SNS2::call('users/me',array(),'GET');
        echo '从Opent返回并获得授权。你的微博帐号信息为：<br />';
        echo 'Access token: ' , OpenSDK_Kaixin_SNS2::getParam (OpenSDK_Kaixin_SNS2::ACCESS_TOKEN) , '<br />';
        echo 'Refresh token: ' , OpenSDK_Kaixin_SNS2::getParam (OpenSDK_Kaixin_SNS2::REFRESH_TOKEN) , '<br />';
        echo 'Expire in：' , OpenSDK_Kaixin_SNS2::getParam(OpenSDK_Kaixin_SNS2::EXPIRES_IN) , '<br />';
        echo 'Scope:' , OpenSDK_Kaixin_SNS2::getParam(OpenSDK_Kaixin_SNS2::SCOPE) , '<br />';
        echo '你的开心帐号信息为:<br /><pre>';
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
    $url = OpenSDK_Kaixin_SNS2::getAuthorizeURL($callback, 'code', 'state');
    header('Location: ' . $url);
}
else
{
    echo '开心网OAuth2.0接口演示<a href="?go_oauth">点击去授权</a>';
}
if($exit)
{
    echo '<a href="?exit">退出再来一次</a>';
}