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
        $queryString = explode(" ",explode("\r\n", $buf)[0])[1];
        
        if($queryString == "/"){
            $queryString = "/index.php";
        }
        
        if(file_exists('.'.$queryString)){
            if (strpos($queryString, ".php")>0) {
                exec("php .".$queryString,$content);
                $content = implode("\n", $content);
            } else {
                $content = file_get_contents('./'.$queryString);
            }
            $msg = "HTTP/1.1 200 OK\r\nContent-type: ".mime_content_type('./'.$queryString)."; charset=UTF-8\r\nServer: phpHttpd/1.0.0\r\n\r\n".$content;
            socket_write($msgsock, $msg, strlen($msg));
        } else {
            $msg = "HTTP/1.1 404 Not Found\r\nContent-type: text/html; charset=UTF-8\r\nServer: phpHttpd/1.0.0\r\n\r\n Not Found: ".$queryString;


            $msg = "HTTP/1.1 404 Not Found\r\nContent-type: text/html; charset=UTF-8\r\nServer: phpHttpd/1.0.0\r\n\r\n".json_encode($_SERVER['REQUEST_URI']);

            socket_write($msgsock, $msg, strlen($msg));
        }  
        
    }

}

socket_close($sock);