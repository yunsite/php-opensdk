# php-opensdk
Automatically exported from code.google.com/p/php-opensdk
 
OpenSDK是一个轻量的SDK代码包
用最少的代码做最多的事情
目前已经完成的封装有：
1、腾讯微博SDK
2、QQ登陆SDK
3、新浪微博SDK
4、开心网API
5、搜狐微博
6、网易微博
OpenSDK将首先支持oauth1.0协议的开放平台，待后续各开放平台提供完善的OAuth2.0版本后也将快速提供支持。
如何获得代码：
1、可以点击Downloads下载打包代码
2、使用SVN Check Out 代码
svn checkout http://php-opensdk.googlecode.com/svn/trunk/ php-opensdk-read-only
然后 cd src/tools/release
点击make.bat 就会在release目录下生成打包的OpenSDK代码。这往往比DownLoads下的代码更新
只有一个对外接口调用，使用
OpenSDK_Tencent_Weibo :: call()
OpenSDK_Tencent_SNS :: call()
OpenSDK_Sina_Weibo :: call()
OpenSDK_Kaixin_SNS :: call()
OpenSDK_Sohu_Weibo :: call()
OpenSDK_163_Weibo :: call()
方法。
然后对着官方文档，填写参数即可，非常简单。
just a more choice
注意：
0825版本 修改了call 方法中 multi参数的传送方法。 旧版本用户如已经调用，请注意修改。
修改后 $multi 参数的格式为
array(
'pic' => '图片的绝对地址',
) 其中 pic 是各平台文档中图片对应的字段名
 
演示地址：http://www.open-sdk.com
腾讯微博API调试工具：http://tools.open-sdk.com/tencent
QQ登陆API调试工具：http://tools.open-sdk.com/tencent/sns.php
新浪微博API调试工具：http://tools.open-sdk.com/sina (未审核)
开心网API调试工具：http://tools.open-sdk.com/kaixin
关注作者：http://t.qq.com/icehubin http://weibo.com/icehubin
