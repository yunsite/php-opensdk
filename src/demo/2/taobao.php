<?php
/**
 * just a demo
 *
 * 仅仅是个demo，未有严格考虑，请不要使用这个简单逻辑到生产环境。
 *
 */
//设置include_path 到 OpenSDK目录
set_include_path(dirname(dirname(dirname(__FILE__))) . '/lib/');
require_once 'OpenSDK/Taobao/Open.php';

include '../taobaoappkey.php';

OpenSDK_Taobao_Open::debug(false);
OpenSDK_Taobao_Open::init($appkey, $appsecret);

//打开session
session_start();
header('Content-Type: text/html; charset=utf-8');
$exit = false;
//对TOP协议的解析
if(isset($_GET['top_parameters']))
{
    if(OpenSDK_Taobao_Open::parseTopParameters())
    {
        $uinfo = OpenSDK_Taobao_Open::call('taobao.user.get',array(
            'fields' => 'user_id,nick,seller_credit',
        ));
        echo '你已经获得授权。你的授权信息:<br />';
        echo 'Access token: ', OpenSDK_Taobao_Open::getParam(OpenSDK_Taobao_Open::ACCESS_TOKEN), '<br />';
        echo 'Expire in：', OpenSDK_Taobao_Open::getParam(OpenSDK_Taobao_Open::EXPIRES_IN), '<br />';
        echo 'Refresh token: ', OpenSDK_Taobao_Open::getParam(OpenSDK_Taobao_Open::REFRESH_TOKEN), '<br />';
        echo 'RE Expire in：', OpenSDK_Taobao_Open::getParam(OpenSDK_Taobao_Open::RE_EXPIRES_IN), '<br />';
        echo 'R1 Expire in：', OpenSDK_Taobao_Open::getParam(OpenSDK_Taobao_Open::R1_EXPIRES_IN), '<br />';
        echo 'R2 Expire in：', OpenSDK_Taobao_Open::getParam(OpenSDK_Taobao_Open::R2_EXPIRES_IN), '<br />';
        echo 'W1 Expire in：', OpenSDK_Taobao_Open::getParam(OpenSDK_Taobao_Open::W1_EXPIRES_IN), '<br />';
        echo 'W2 Expire in：', OpenSDK_Taobao_Open::getParam(OpenSDK_Taobao_Open::W2_EXPIRES_IN), '<br />';
        echo 'user_id:', OpenSDK_Taobao_Open::getParam(OpenSDK_Taobao_Open::OAUTH_USER_ID), '<br />';
        echo 'user_nick:' , OpenSDK_Taobao_Open::getParam(OpenSDK_Taobao_Open::OAUTH_USER_NICK) , '<br />';
        echo '你的淘宝帐号信息为:<br /><pre>';
        var_dump($uinfo);
    }
    else
    {
        exit('参数校验失败');
    }
    $exit = true;
}
elseif(isset($_GET['exit']))
{
    OpenSDK_Taobao_Open::setParam(OpenSDK_Taobao_Open::ACCESS_TOKEN, null);
    OpenSDK_Taobao_Open::setParam(OpenSDK_Taobao_Open::REFRESH_TOKEN, null);
    echo '<a href="?go_oauth">点击去授权</a>';
}
else if(
        OpenSDK_Taobao_Open::getParam (OpenSDK_Taobao_Open::ACCESS_TOKEN)
        )
{
    //已经取得授权
    $uinfo = OpenSDK_Taobao_Open::call('taobao.user.get',array(
        'fields' => 'user_id,nick,seller_credit',
    ));
    echo '你已经获得授权。你的授权信息:<br />';
    echo 'Access token: ', OpenSDK_Taobao_Open::getParam(OpenSDK_Taobao_Open::ACCESS_TOKEN), '<br />';
    echo 'Expire in：', OpenSDK_Taobao_Open::getParam(OpenSDK_Taobao_Open::EXPIRES_IN), '<br />';
    echo 'Refresh token: ', OpenSDK_Taobao_Open::getParam(OpenSDK_Taobao_Open::REFRESH_TOKEN), '<br />';
    echo 'RE Expire in：', OpenSDK_Taobao_Open::getParam(OpenSDK_Taobao_Open::RE_EXPIRES_IN), '<br />';
    echo 'R1 Expire in：', OpenSDK_Taobao_Open::getParam(OpenSDK_Taobao_Open::R1_EXPIRES_IN), '<br />';
    echo 'R2 Expire in：', OpenSDK_Taobao_Open::getParam(OpenSDK_Taobao_Open::R2_EXPIRES_IN), '<br />';
    echo 'W1 Expire in：', OpenSDK_Taobao_Open::getParam(OpenSDK_Taobao_Open::W1_EXPIRES_IN), '<br />';
    echo 'W2 Expire in：', OpenSDK_Taobao_Open::getParam(OpenSDK_Taobao_Open::W2_EXPIRES_IN), '<br />';
    echo 'user_id:', OpenSDK_Taobao_Open::getParam(OpenSDK_Taobao_Open::OAUTH_USER_ID), '<br />';
    echo 'user_nick:' , OpenSDK_Taobao_Open::getParam(OpenSDK_Taobao_Open::OAUTH_USER_NICK) , '<br />';
    echo '你的淘宝帐号信息为:<br /><pre>';
    var_dump($uinfo);

    var_dump(OpenSDK_Taobao_Open::call('taobao.item.get', array(
        'num_iid' => '12743463759',
        'fields' => 'num_iid,title,price',
    )));
    var_dump(OpenSDK_Taobao_Open::call('taobao.taobaoke.items.convert', array(
        'num_iids' => '12743463759',
        'fields' => 'num_iid,title,nick,pic_url,price,click_url,commission,commission_rate,commission_num,commission_volume,shop_click_url,seller_credit_score,item_location,volume',
        'nick' => 'huangrongccnu',
    )));


//    var_dump(OpenSDK_Taobao_Open::call('taobao.itemcats.get', array(
//        'fields' => 'cid,parent_cid,name,is_parent',
//        'parent_cid' => '0',
//    )));

//    var_dump(OpenSDK_Taobao_Open::call('taobao.itempropvalues.get ', array(
//        'cid' => '1512',
//        'fields' => 'cid,pid,prop_name,vid,name,name_alias,status',
//        'pvs' => '20000:30111',
//    )));

//    var_dump(OpenSDK_Taobao_Open::call('taobao.item.add', array(
//        'num' => '1',
//        'price' => '10000',
//        'type' => 'fixed',
//        'stuff_status' => 'unused',
//        'title' => 'IPhone4 白色 32G',
//        'desc' => '几乎全新的Iphone',
//        'location.state' => '北京',
//        'location.city' => '北京',
//        'cid' => '1512',
//        'props' => '20000:30111;10004:3231342;10000:10000;10002:27325;20879:32558;20930:33000',
//    )));
    $exit = true;
}
else if( isset($_GET['code']))
{
    //从Callback返回时
    if(OpenSDK_Taobao_Open::getAccessToken('code',array(
            'code'=>$_GET['code'],
            'redirect_uri'=>'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']
        )))
    {
        $uinfo = OpenSDK_Taobao_Open::call('taobao.user.get',array(
            'fields' => 'user_id,nick,seller_credit',
        ));
        echo '从Opent返回并获得授权。你的淘宝帐号信息为：<br />';
        echo 'Access token: ' , OpenSDK_Taobao_Open::getParam (OpenSDK_Taobao_Open::ACCESS_TOKEN) , '<br />';
        echo 'Expire in：' , OpenSDK_Taobao_Open::getParam(OpenSDK_Taobao_Open::EXPIRES_IN) , '<br />';
        echo 'Refresh token: ' , OpenSDK_Taobao_Open::getParam (OpenSDK_Taobao_Open::REFRESH_TOKEN) , '<br />';
        echo 'RE Expire in：' , OpenSDK_Taobao_Open::getParam(OpenSDK_Taobao_Open::RE_EXPIRES_IN) , '<br />';
        echo 'R1 Expire in：' , OpenSDK_Taobao_Open::getParam(OpenSDK_Taobao_Open::R1_EXPIRES_IN) , '<br />';
        echo 'R2 Expire in：' , OpenSDK_Taobao_Open::getParam(OpenSDK_Taobao_Open::R2_EXPIRES_IN) , '<br />';
        echo 'W1 Expire in：' , OpenSDK_Taobao_Open::getParam(OpenSDK_Taobao_Open::W1_EXPIRES_IN) , '<br />';
        echo 'W2 Expire in：' , OpenSDK_Taobao_Open::getParam(OpenSDK_Taobao_Open::W2_EXPIRES_IN) , '<br />';
        echo 'user_id:' , OpenSDK_Taobao_Open::getParam(OpenSDK_Taobao_Open::OAUTH_USER_ID) , '<br />';
        echo 'user_nick:' , OpenSDK_Taobao_Open::getParam(OpenSDK_Taobao_Open::OAUTH_USER_NICK) , '<br />';
        echo '你的淘宝帐号信息为:<br /><pre>';
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
    $url = OpenSDK_Taobao_Open::getAuthorizeURL($callback, 'code','');
    header('Location: ' . $url);
}
else
{
    echo '淘宝 OAuth2.0演示<a href="?go_oauth">点击去授权</a>';
}

if($exit)
{
    echo '<a href="?exit">退出再来一次</a>','<br />';
//    $callback = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
//    echo "<a href='".OpenSDK_Taobao_Open::getlogoffURL($callback)."'>退出</a>";
}
