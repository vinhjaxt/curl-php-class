<?php
include '../cURL.class.php';

$curl = new cURL('http://fb.com/');
$curl->setCookie('a=b');
# Overwrite
$curl->setCookie(array('b'=>'c'));
$curl->setCookieFile(__DIR__.'/9-cookieFile.txt');
$curl->exec();

echo "<br/><br/>\r\n\r\nRequested HTTP headers:<br/>\r\n";
var_dump($curl->getRequestHeaders());
