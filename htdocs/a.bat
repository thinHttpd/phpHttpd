@echo off
set REDIRECT_STATUS=true
set SCRIPT_FILENAME=./test.php
set REQUEST_METHOD=POST
set GATEWAY_INTERFACE=CGI/1.1
set CONTENT_LENGTH=16
set CONTENT_TYPE=application/x-www-form-urlencoded
set HTTP_COOKIE=PHPSESSID=vfg5csi76qpt3qlfml359ad210
set QUERY_STRING=id=123
echo test=hello world | php-cgi.exe 
pause 