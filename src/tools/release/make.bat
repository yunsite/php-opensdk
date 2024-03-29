@ECHO off 
cls 
color 0a 

SET SVN_URI= "../../"
SET ZIP_URL="7za.exe"

ECHO 正在从 %SVN_URI% 导出最新版本文件

svn export --force %SVN_URI%lib lib
svn export --force %SVN_URI%demo demo
svn export --force %SVN_URI%tools/readme.txt readme.txt

copy appkey.php "demo/tencentappkey.php"
copy appkey.php "demo/sinaappkey.php"
copy appkey.php "demo/kxappkey.php"
copy appkey.php "demo/sohuappkey.php"
copy appkey.php "demo/163appkey.php"
copy appkey.php "demo/tencentsnsappkey.php"
copy appkey.php "demo/renrenappkey.php"
copy appkey.php "demo/baiduappkey.php"
copy appkey.php "demo/doubanappkey.php"
copy appkey.php "demo/taobaoappkey.php"


ECHO 文件导出成功...

ECHO 开始打包
%ZIP_URL% a OpenSDK.zip lib demo readme.txt
ECHO 创建压缩包完成

ECHO 删除临时文件
ping -n 5 127.1>nul
rmdir lib /s/q
rmdir demo /s/q
del readme.txt

ECHO 打包完成！

pause