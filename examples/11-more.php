<?php
include '../cURL.class.php';

$curl = new cURL();
$curl->setURL('http://echo.opera.com/');
$curl->setRedirects(2);
$curl->exec();

echo $curl->getContentType();
var_dump($curl);
echo "\r\n<br/>";

echo cURL::to_browser_cookie(file_get_contents(__DIR__.'/9-cookieFile.txt'));
echo "\r\n<br/>";
echo cURL::to_curl_cookie('a=b;b=c','.facebook.com');
echo "\r\n<br/>";
echo cURL::complete_url('https://m.facebook.com/a/b/likes.php?id=123','/comment.php?story_id=456');
