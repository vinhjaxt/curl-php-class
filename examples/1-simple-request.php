<?php
include '../cURL.class.php';

# Fetch google.com

$curl = new cURL('http://google.com/');
$curl->exec();

echo '<pre>',htmlspecialchars($curl),'</pre>';
echo "<br/><br/>\r\n\r\nResponse HTTP headers:<br/>\r\n";
var_dump($curl->getHeaders());
