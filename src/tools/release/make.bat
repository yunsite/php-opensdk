@ECHO off 
cls 
color 0a 

SET SVN_URI= "../../"
SET ZIP_URL="7za.exe"

ECHO ���ڴ� %SVN_URI% �������°汾�ļ�

svn export --force %SVN_URI%lib lib
svn export --force %SVN_URI%demo demo
svn export --force %SVN_URI%tools/readme.txt readme.txt

copy appkey.php "demo/tencentappkey.php"
copy appkey.php "demo/sinaappkey.php"
copy appkey.php "demo/kxappkey.php"
copy appkey.php "demo/sohuappkey.php"
copy appkey.php "demo/163appkey.php"
copy appkey.php "demo/tencentsnsappkey.php"

ECHO �ļ������ɹ�...

ECHO ��ʼ���
%ZIP_URL% a OpenSDK.zip lib demo readme.txt
ECHO ����ѹ�������

ECHO ɾ����ʱ�ļ�
ping -n 5 127.1>nul
rmdir lib /s/q
rmdir demo /s/q
del readme.txt

ECHO �����ɣ�

pause