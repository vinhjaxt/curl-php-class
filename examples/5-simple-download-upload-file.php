<?php
include '../src/cURL.class.php';

$curl = new cURL('http://echo.opera.com/');
$curl->setWriteToFile(__DIR__.'/5-testWriteToFile.txt');
$curl->GET();

echo '<pre>',htmlspecialchars($curl),'</pre>';
echo "<br/><br/>\r\n\r\Requested HTTP headers:<br/>\r\n";
var_dump($curl->getRequestHeaders());

echo "\r\n<br/>\r\n";

$curl->unsetWriteToFile();
$curl->uploadFile('input_file',__DIR__.'/5-testWriteToFile.txt');
$curl->POST();

echo '<pre>',htmlspecialchars($curl),'</pre>';
echo "<br/><br/>\r\n\r\nRequested HTTP headers:<br/>\r\n";
var_dump($curl->getRequestHeaders());
