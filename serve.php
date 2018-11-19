<?php

$ip = '127.0.0.1';
$port = 8080;

if(($sock = socket_create(AF_INET,SOCK_STREAM,SOL_TCP)) < 0) {
    echo "socket_create() 失败的原因是:".socket_strerror($sock)."\n";
}

if(($ret = socket_bind($sock,$ip,$port)) < 0) {
    echo "socket_bind() 失败的原因是:".socket_strerror($ret)."\n";
}

if(($ret = socket_listen($sock,4)) < 0) {
    echo "socket_listen() 失败的原因是:".socket_strerror($ret)."\n";
}


while(true) {

    if (($msgsock = socket_accept($sock)) < 0) {
        echo "socket_accept() 失败的原因是：" . socket_strerror($msgsock) . "\n";
        break;
    } else {

        $buf = socket_read($msgsock,8192);
        $REQUEST_URI = explode(" ",explode("\r\n", $buf)[0])[1];
        
        if($REQUEST_URI == "/"){
            $SCRIPT_NAME = "/index.php";
        } else {
            $requestStringTmp = explode("?",$REQUEST_URI);
            $SCRIPT_NAME = $requestStringTmp[0];
            $QUERY_STRING = count($requestStringTmp) > 1 ? $requestStringTmp[1] : '';
        }

        if(file_exists('./htdocs/'.$SCRIPT_NAME)){

            if (strpos($SCRIPT_NAME, ".php")>0) {

                putenv("SERVER_PROTOCOL=HTTP/1.1");
                putenv("GATEWAY_INTERFACE=CGI/1.1");
                putenv("REQUEST_URI=$REQUEST_URI");
                putenv("SCRIPT_NAME=$SCRIPT_NAME");
                putenv("SCRIPT_FILENAME=./htdocs/$SCRIPT_NAME");
                putenv("QUERY_STRING=$QUERY_STRING");

                exec("php-cgi",$content);
                $content = implode("\n", $content);
                $msg = "HTTP/1.1 200 OK\r\nConnection: Keep-Alive\r\n".$content;
            } else {
                $content = file_get_contents('./htdocs/'.$SCRIPT_NAME);
                $msg = "HTTP/1.1 200 OK\r\nContent-type: ".mime_content_type('./htdocs/'.$REQUEST_URI)."; charset=UTF-8\r\nConnection: Keep-Alive\r\nServer: phpHttpd/1.0.0\r\n\r\n".$content;
            }

            socket_write($msgsock, $msg, strlen($msg));

        } else {
            $msg = "HTTP/1.1 404 Not Found\r\nContent-type: text/html; charset=UTF-8\r\nConnection: Keep-Alive\r\nServer: phpHttpd/1.0.0\r\n\r\n Not Found: ".$SCRIPT_NAME;

            socket_write($msgsock, $msg, strlen($msg));
        }  
        
    }

}

socket_close($sock);