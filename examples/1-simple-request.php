<?php
include '../cURL.class.php';

# Fetch google.com

$curl = new cURL('http://google.com/');
echo $curl;

echo "<br/><br/>\r\n\r\nResponse HTTP headers:<br/>\r\n";
var_dump($curl->getHeaders());
