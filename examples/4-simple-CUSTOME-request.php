<?php
include '../cURL.class.php';

$curl = new cURL('http://echo.opera.com/');
$curl->setPostContent('{"name":"POST contents"}');
$curl->setHeader('Content-Type: application/json;charset=utf-8');
$curl->REQUEST('PUT');//PATCH, DELETE, OPTION,..

echo '<pre>',htmlspecialchars($curl),'</pre>';
echo "<br/><br/>\r\n\r\nRequested HTTP headers:<br/>\r\n";
var_dump($curl->getRequestHeaders());
