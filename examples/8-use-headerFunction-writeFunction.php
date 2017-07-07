<?php
include '../src/cURL.class.php';

$curl = new cURL('http://echo.opera.com/');
$curl->setWriteFunction(function($handle,$data){
	echo "\r\nResponse body: ",$data;
});
$curl->setHeaderFunction(function($handle,$data){
	echo "\r\nResponse header: ",$data;
});
$curl->exec();

echo "<br/><br/>\r\n\r\nRequested HTTP headers:<br/>\r\n";
var_dump($curl->getRequestHeaders());
