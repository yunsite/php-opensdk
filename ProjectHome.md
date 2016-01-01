OpenSDK是一个轻量的SDK代码包

用最少的代码做最多的事情

目前已经完成的封装有：

1、腾讯微博SDK OAuth1.0

2、QQ登陆SDK OAuth1.0 & OAuth2.0

3、新浪微博SDK OAuth1.0 & OAuth2.0

4、开心网API OAuth1.0 & OAuth2.0

5、搜狐微博 OAuth1.0

6、网易微博 OAuth1.0

7、人人网SDK OAuth2.0

8、百度开放平台 OAuth2.0


一库接入所有平台 ：）

如何获得代码：

1、可以点击Downloads下载打包代码

2、使用SVN Check Out 代码

svn checkout http://php-opensdk.googlecode.com/svn/trunk/ php-opensdk-read-only

然后 cd src/tools/release

点击make.bat 就会在release目录下生成打包的OpenSDK代码。这往往比DownLoads下的代码更新

只有一个对外接口调用，使用

OpenSDK\_Tencent\_Weibo :: call()

OpenSDK\_Tencent\_SNS[2](2.md) :: call()

OpenSDK\_Sina\_Weibo[2](2.md) :: call()

OpenSDK\_Kaixin\_SNS[2](2.md) :: call()

OpenSDK\_Sohu\_Weibo :: call()

OpenSDK\_163\_Weibo :: call()

OpenSDK\_Baidu\_Open :: call()

OpenSDK\_RenRen\_SNS2 :: call()

方法。

然后对着官方文档，填写参数即可，非常简单。

just a more choice

注意：

0825版本
修改了call 方法中 multi参数的传送方法。
旧版本用户如已经调用，请注意修改。

修改后 $multi 参数的格式为

array(
> 'pic' => '图片的绝对地址',
)
其中 pic 是各平台文档中图片对应的字段名



关注作者：http://t.qq.com/icehubin      http://weibo.com/icehubin