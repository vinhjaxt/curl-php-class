<?php
include '../cURL.class.php';

$curl = new cURL('http://echo.opera.com/');
$curl->setProxy(array('server'=>'123.12.13.23'));
$curl->exec();

echo '<pre>',htmlspecialchars($curl),'</pre>';
echo "<br/><br/>\r\n\r\nRequested HTTP headers:<br/>\r\n";
var_dump($curl->getRequestHeaders());
