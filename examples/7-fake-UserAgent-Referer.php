<?php
include '../src/cURL.class.php';

$curl = new cURL('http://echo.opera.com/');
$curl->setUserAgent('My Agent');
$curl->setReferer('My referer');
$curl->exec();

echo '<pre>',htmlspecialchars($curl),'</pre>';
echo "<br/><br/>\r\n\r\nRequested HTTP headers:<br/>\r\n";
var_dump($curl->getRequestHeaders());
