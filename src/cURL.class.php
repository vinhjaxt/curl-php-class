<?php
# class cURL
# by VinhNoName
# follow me: https://github.com/vinhjaxt/
if(!class_exists('cURLResponse')){
	class cURLResponse{

		# Response Body
		public $Body='';

		# Response Headers
		public $Headers='';

		# Other data
		private $data=array();

		public function __construct($Body='',$Headers=''){
			$this->Body=$Body;
			$this->Headers=$Headers;
		}

		public function __toString(){
			return $this->Body;
		}

		public function __call($name, $arguments=array()){
			$methodName=strtolower(trim($name));
			if(in_array($methodName,array('body','getbody'))){
				return $this->Body;
			}
			if(in_array($methodName,array('header','getheader','headers','getheaders'))){
				return $this->Headers;
			}
			if(in_array($methodName,array('setbody'))){
				$this->Body=$arguments[0];
				return true;
			}
			if(in_array($methodName,array('setheader','setheaders'))){
				$this->Headers=$arguments[0];
				return true;
			}
			if(isset($this->$name)){
				return $this->$name;
			}
			if(array_key_exists($name, $this->data)){
				return $this->data[$name];
			}
			return null;
		}

		public function __set($name,$mixed){
			$this->data[$name]=$mixed;
		}

		public function __get($name){
			if(array_key_exists($name, $this->data)){
				return $this->data[$name];
			}
			return null;
		}
	}//define class cURLResponse
}//class cURLResponse exist

if(!class_exists('cURL')){
	class cURL extends cURLResponse {
		# @curl cURL handle
		public $ch;

		# @array cURL options
		private $options=array();

		# @array || @string error string
		public $errors=array();

		# @string url
		public $url;

		# @boolean this $url page have body or not
		public $haveBody=false;

		# @array requestHeaders
		public $requestHeaders=array();

		# @boolean is executed?
		private $executed=false;

		# post contents
		public $posts=array();

		# if convert this object to string
		public function __toString(){
			if(!$this->executed) $this->exec();
			return $this->Body;
		}

		# if init this classs
		public function __construct($url=''){
			$this->ch=curl_init();
			$options = array(
				CURLINFO_HEADER_OUT    => true,
//				CURLOPT_VERBOSE		   => true,
//				CURLOPT_HEADER		   => true,
				CURLOPT_RETURNTRANSFER => true,	 // return web page
				CURLOPT_ENCODING	   => '',	   // handle all encodings
				CURLOPT_CONNECTTIMEOUT => 10,	  // timeout on connect (s)
				CURLOPT_TIMEOUT 	   => 120,	  // timeout on connect && response (s)
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_MAXREDIRS	   => 5,
				CURLOPT_AUTOREFERER    => true,
//				CURLOPT_FRESH_CONNECT  => true,
				CURLOPT_BINARYTRANSFER => true,
				CURLOPT_CUSTOMREQUEST  => 'GET',
			);
			$this->options=$options;
			$this->options[CURLOPT_HEADERFUNCTION] = array(&$this, 'readHeader');
			$this->options[CURLOPT_WRITEFUNCTION] = array(&$this, 'readBody');
			$this->options[CURLOPT_DNS_CACHE_TIMEOUT] = 600;
			$this->setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win32; x32; rv:45.0) Gecko/20100101 Firefox/45.0');
			$this->setHeader(array('Accept-Language: en-US,en;q=0.5','Expect:','Pragma: no-cache','Cache-Control: no-cache'));
			if($url && is_array($url)){
				# $url is options
				$this->setOptions($url);
				if(isset($url['url']))
					$url=$url['url'];
				else
					$url=null;
			}
			if($url){
				$this->setURL($url);
				$this->setReferer($url);
			}
		}

		# curl proxy
		public static function defineProxyConstants(){
			defined('CURLOPT_PROXYTYPE') || define('CURLOPT_PROXYTYPE',101);
			defined('CURLOPT_CONNECT_ONLY') || define('CURLOPT_CONNECT_ONLY',141);
			defined('CURLOPT_HTTPPROXYTUNNEL') || define('CURLOPT_HTTPPROXYTUNNEL',61);
			defined('CURLOPT_PROXYAUTH') || define('CURLOPT_PROXYAUTH',111);
			defined('CURLAUTH_BASIC') || define('CURLAUTH_BASIC',1);
			defined('CURLAUTH_NTLM') || define('CURLAUTH_NTLM',8);
			defined('CURLOPT_PROXYPORT') || define('CURLOPT_PROXYPORT',59);
			defined('CURLOPT_PROXY') || define('CURLOPT_PROXY',10004);
			defined('CURLPROXY_HTTP') || define('CURLPROXY_HTTP',0);
			defined('CURLPROXY_SOCKS4') || define('CURLPROXY_SOCKS4',4);
			defined('CURLPROXY_SOCKS5') || define('CURLPROXY_SOCKS5',5);
			defined('CURLPROXY_SOCKS4A') || define('CURLPROXY_SOCKS4A',6);
			defined('CURLPROXY_SOCKS5_HOSTNAME') || define('CURLPROXY_SOCKS5_HOSTNAME',7);
			defined('CURLOPT_PROXYUSERPWD') || define('CURLOPT_PROXYUSERPWD',10006);
		}
		private $proxyTypes=array(
			'http'=>CURLPROXY_HTTP,
			'socks4'=>CURLPROXY_SOCKS4,
			'socks4a'=>CURLPROXY_SOCKS4A,
			'socks5'=>CURLPROXY_SOCKS5,
			'socks5hostname'=>CURLPROXY_SOCKS5_HOSTNAME
			);
		public function setProxy($options=array('auth'=>'user:pass','server'=>'','port'=>80,'type'=>'http','user'=>'','password'=>'')){
			if(!isset($options['server']) || !$options['server']){
				$this->appendError('setProxy() needed a server to connect.');
				return false;
			}
			$this->options[CURLOPT_HTTPPROXYTUNNEL]=true;
			$this->options[CURLOPT_PROXY]=$options['server'];
			if(isset($options['type']))
				$this->options[CURLOPT_PROXYTYPE]=(is_string($options['type']) && isset($this->proxyTypes[$options['type']])) ? $this->proxyTypes[$options['type']] : $options['type'];
			if(isset($options['auth'])){
				if(strpos($options['auth'],'\\')===false)
					$this->options[CURLOPT_PROXYAUTH]=CURLAUTH_BASIC;
				else
					$this->options[CURLOPT_PROXYAUTH]=CURLAUTH_NTLM; #CURLAUTH_NTLM => Microsoft NT
				$this->options[CURLOPT_PROXYUSERPWD]=$options['auth'];
			}
			if(isset($options['port']))
				$this->options[CURLOPT_PROXYPORT]=$options['port'];
			if(isset($options['user'])){
				$this->options[CURLOPT_PROXYAUTH]=CURLAUTH_BASIC;
				$this->options[CURLOPT_PROXYUSERPWD]=$options['user'].':'.$options['password'];
			}
		}

		# HTTP Authenticate
		public function httpAuth($user,$pass=false){
			if(!$user) return false;
			$this->options[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
			$this->options[CURLOPT_USERPWD] = $pass===false?$user:$user.':'.$pass;
		}

		# set max redirect location times
		public function setRedirects($maxRedirects=5){
			$this->setRedirect($maxRedirects);
		}
		public function setRedirect($maxRedirects=5){
			if($maxRedirects){
				$this->options[CURLOPT_FOLLOWLOCATION]=true;
				$this->options[CURLOPT_MAXREDIRS]=$maxRedirects;
			}else{
				$this->options[CURLOPT_FOLLOWLOCATION]=false;
			}
		}

		# force lookup domain with ipv6
		public function forceIpv6($on=true){
			if($on){
				# Use IPv6 for DNS resolution
				if(defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V6')){
					$this->options[CURLOPT_IPRESOLVE] = CURL_IPRESOLVE_V6;
				}
			}else{
				if(defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_WHATEVER')){
					$this->options[CURLOPT_IPRESOLVE] = CURL_IPRESOLVE_WHATEVER;
				}
			}
		}
		# force lookup domain with ipv4
		public function forceIpv4($on=true){
			if($on){
				# Use IPv4 for DNS resolution
				if(defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')){
					$this->options[CURLOPT_IPRESOLVE] = CURL_IPRESOLVE_V4;
				}
			}else{
				if(defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_WHATEVER')){
					$this->options[CURLOPT_IPRESOLVE] = CURL_IPRESOLVE_WHATEVER;
				}
			}
		}

		# part-content, resume download
		public function setRange($range=null){
			if(!$range) $range=substr($_SERVER['HTTP_RANGE'], 6);
			$this->options[CURLOPT_RANGE]=$range;
		}

		# set curl Options
		public function setOption($options=array(),$param2=false){
			return $this->setOptions($options,$param2);
		}
		public function setOptions($options=array(),$param2=false){
			if(empty($options)) return false;
			if(!is_array($options)){
				$key=$options;
				$options=array($key=>$param2);
			}
			if(isset($options['headerFunction']))
				$this->setHeaderFunction($options['headerFunction']);
			if(isset($options['writeFunction']))
				$this->setWriteFunction($options['writeFunction']);
			if(isset($options['referer'])) $this->setReferer($options['referer']);
			if(isset($options['cookie'])) $this->setCookie($options['cookie']);
			if(isset($options['userAgent'])) $this->setUserAgent($options['userAgent']);
			if(isset($options['httpHeader'])) $this->setHeader($options['httpHeader']);
			if(isset($options['writeToFile'])) $this->setWriteToFile($options['writeToFile']);
			if(isset($options['sslVerify']) && $options['sslVerify'])
				$this->setSSL_CAINFO(__DIR__.DIRECTORY_SEPARATOR.'cacert.pem');
			if(isset($options['cookieFile']))
				$this->setCookieFile($url['cookieFile']);
			if(isset($options['url'])) $this->setURL($options['url']);
			if(isset($options['post']))	$this->setPostContent($options['post']);
			if(isset($options['file'])) $this->setWriteToFile($options['file']);
			if(isset($options['uploadFile']) && is_array($options['uploadFile'])){
				foreach($options['uploadFile'] as $k=>$v){
					if(is_numeric($k) && is_array($v)){
						$this->uploadFile($v[0],$v[1]);
					}
					if(is_string($k) && is_string($v)){
						$this->uploadFile($k,$v);
					}
				}
			}
			if(isset($options['customRequest'])) $this->setCustomRequestMethod($options['customRequest']);
			if(isset($options['httpAuth'])) $this->httpAuth($options['httpAuth']);
			if(isset($options['proxy'])){
				if(!is_array($options['proxy'])) $options['proxy']=array($options['proxy']);
				$this->setProxy(isset($options['proxy']['server'])?$options['proxy']['server']:$options['proxy'][0]);
			}
			if(isset($options['forceIpv4'])) $this->forceIpv4($options['forceIpv4']);
			if(isset($options['forceIpv6'])) $this->forceIpv6($options['forceIpv6']);
		}

		# set url to execute
		public function setURL($url){
			if(!$url){
				$this->appendError('setURL($url): $url can not be empty.');
				return false;
			}
			$this->url=$url;
			$this->options[CURLOPT_URL]=$url;
			$this->executed=false;
		}

		# set ssl cainfo file
		public function setSSL_CAINFO($file=''){
			/*
			Goto http://curl.haxx.se/docs/caextract.html and download cacert.pem file
				and put it in this directory
			*/
			if(!is_file($file)) $file=__DIR__.DIRECTORY_SEPARATOR.'cacert.pem';
			$this->options[CURLOPT_SSL_VERIFYPEER]=true;
			$this->options[CURLOPT_SSL_VERIFYHOST]=2;
			$this->options[CURLOPT_CAINFO]=$file;
		}

		# custom write body function
		public function setWriteFunction($func){
			if(!$func) return false;
			$this->writeFunction=$func;
		}
		# cancel custom write body function
		public function unsetWriteFunction($func){
			$this->writeFunction=false;
		}

		# private function to read the body of the page
		private function readBody($handle,$data){
			$len=strlen($data);
			if($this->haveBody==false)
				$this->haveBody=true;
			if($this->writeToFile){
				$fp=fopen($this->writeToFile,'a+');
				if($fp){
					fwrite($fp,$data);
					fclose($fp);
				}else{
					static $runOnce;
					if(!$runOnce){
						$this->appendError('File: "'.$this->writeToFile.'" cloud not be opened.');
						$runOnce=true;
					}
				}
			}else if($this->writeFunction && is_callable($this->writeFunction)){
				call_user_func($this->writeFunction,$handle,$data);
			}else{
				$this->Body .=$data;
			}
			return $len;
		}

		# set file to be written body into
		public function setWriteToFile($file){
			if(!$file) return false;
			if(is_file($file)) unlink($file);
			$this->writeToFile=$file;
		}

		# unset writeToFile, return the webpage
		public function unsetWriteToFile(){
			$this->writeToFile=false;
		}

		# custom read response headers of the page
		public function setHeaderFunction($func){
			if(!$func) return false;
			$this->headerFunction=$func;
		}

		# cancel custom read response headers of the page
		public function unsetHeaderFunction(){
			$this->headerFunction=false;
		}

		# private function to read the headers
		private function readHeader($handle,$data){
			$len=strlen($data);
			if($this->headerFunction && is_callable($this->headerFunction)){
				call_user_func($this->headerFunction,$handle,$data);
			}
			$this->Headers.=$data;
			return $len;
		}

		# set file to upload to url
		public function uploadFile($field,$file){
			if(!$field){
				$this->appendError('uploadFile($field,$file): $field can not be empty.');
				return false;
			}
			if(is_array($this->posts)){
				$cURLFile = curl_file_create($file);
				$this->setPostField($field,$cURLFile);
//				$this->options[CURLOPT_UPLOAD]=true;
			}else{
				$this->appendError('setPostContent was called. Can not uploadFile.');
			}
		}

		# resume from downloaded bytes
		public function resumeFrom($bytes){
			$this->options[CURLOPT_RESUME_FROM]=$bytes;
		}
		# set request header
		public function setHeader($header,$value=NULL){
			if(!$header){
				$this->appendError('setHeader($header,$value=NULL): $header can not be empty.');
				return false;
			}
			$headers=array();
			if(is_array($header)){
				foreach($header as $k=>$v){
					if(is_numeric($k))
						$headers[]=$v;
					else
						$headers[]=$k.': '.$v;
				}
			}else if(is_string($header)){
				if($value!==NULL){
					$headers[]=$header.': '.$value;
				}else{
					$headers[]=$header;
				}
			}

			$this->requestHeaders=array_merge($this->requestHeaders,$headers);
			$this->options[CURLOPT_HTTPHEADER]=$this->requestHeaders;
		}

		# set request userAgent
		public function setUserAgent($userAgent){
			$this->options[CURLOPT_USERAGENT]=$userAgent;
		}

		# set request referer
		public function setReferer($referer){
			$this->options[CURLOPT_REFERER]=$referer;
		}

		# set request cookie
		public function setCookie($cookie,$value=NULL){
			if(!$cookie){
				$this->appendError('setCookie($cookie,$value=NULL): $cookie can not be empty.');
				return false;
			}
			if($value!==NULL) $cookie.='='.$value;
			$cookieContent=$cookie;
			if(is_array($cookieContent)){
				$cookieContents=array();
				foreach($cookieContent as $k => $v) $cookieContents[]=$k.'='.$v;
				$cookie=implode('; ',$cookieContents);
				unset($cookieContent,$cookieContents);
			}
			$this->options[CURLOPT_COOKIE]=$cookie;
		}

		# cookies read & store in a file
		public function setCookieFile($file){
			if(!$file){
				$this->appendError('setCookieFile($file): $file can not be empty.');
				return false;
			}
			$this->options[CURLOPT_COOKIEJAR]=$this->options[CURLOPT_COOKIEFILE]=$file;
		}

		# get all the cookies
		public function getCookies(){
			$cookies=curl_getinfo($this->ch, CURLINFO_COOKIELIST);
			return implode("\r\n",$cookies);
		}

		# get Content-Type header of the page
		public function getContentType(){
			if(!$this->contentType)
				$this->contentType=curl_getinfo($this->ch,CURLINFO_CONTENT_TYPE);
			return $this->contentType;
		}

		# get runtime errors
		public function getErrors(){
			return $this->errors;
		}

		# set custom post content
		public function setPostContent($content){
			$this->options[CURLOPT_CUSTOMREQUEST]='POST';
			$this->options[CURLOPT_POST]=true;
			$this->options[CURLOPT_SAFE_UPLOAD]=true;
			$this->posts=$content;
		}

		# set post field
		public function setPostField($field,$value=''){
			if(!$field){
				$this->appendError('setPostField($field,$value): $field and $value can not be empty.');
				return false;
			}
			if(is_array($this->posts)){
				$this->options[CURLOPT_CUSTOMREQUEST]='POST';
				$this->options[CURLOPT_POST]=true;
				$this->options[CURLOPT_SAFE_UPLOAD]=true;
				$fields=$field;
				if(!is_array($fields)) $fields=array($field => $value);
				foreach($fields as $field => $value){
					$this->posts[$field]=$value;
				}
			}
			else
				$this->appendError('setPostContent was called. Can not setPostField');
		}

		# execute post request
		public function POST($url='',$post=NULL){
			if($post===NULL && $url){
				$url='';
				$post=$url;
			}
			if($url)
				$this->setURL($url);
			if(!empty($post)){
				/*
				$post=array('name1'=>'value1','name2'=>array('name'=>'file_name.txt','content'=>'Simple text file'));
				*/
				$upload=false;
				if(is_array($post))
				foreach($post as $v) if(is_array($v)){ $upload=true; continue; }
				if($upload){
					$delimiter = '-------------'.uniqid();
					$data = '';
					foreach ($post as $k => $v) {
						$k=addslashes($k);
						if(is_array($v)){
							#source
							if(empty($v['type'])) $v['type']='application/octet-stream';
							if(empty($v['name'])) $v['name']=rand(1000,9999);
							$data .= '--'.$delimiter."\r\n";
							$data .= 'Content-Disposition: form-data; name="='.$k.'"; filename="'.$v['name'].'"'."\r\n";
							$data .= 'Content-Type: '.$v['type']."\r\n\r\n";
							unset($v['type']);
							unset($v['name']);
							$v=(isset($v['content']) ? $v['content'] : end($v));
							$data .= $v."\r\n";
							# /source
							continue;
						}//is array
						$data .= '--'.$delimiter."\r\n";
						$data .= 'Content-Disposition: form-data; name="'.$k.'"'."\r\n\r\n";
						$data .= $v."\r\n";
					}//foreach

					$data .= '--'.$delimiter . "--\r\n";
					$delimiter=array(
						'Content-Type: multipart/form-data; boundary=' . $delimiter,
						'Content-Length: '.strlen($data));
					//if($cookie) $delimiter[]='Cookie: '.$cookie;
					$this->setHeader($delimiter);
					$this->posts=$data;
					$data='';
				}else{
					//httpHeader: Content-Type: application/x-www-form-urlencoded
					$this->posts=$post;
				}//if upload
			}
			$this->options[CURLOPT_CUSTOMREQUEST]='POST';
			$this->options[CURLOPT_POST]=true;
			$this->options[CURLOPT_SAFE_UPLOAD]=true;
			return $this->exec();
		}

		# execute get request
		public function GET($url=''){
			$this->options[CURLOPT_CUSTOMREQUEST]='GET';
			$this->options[CURLOPT_POST]=false;
			$this->options[CURLOPT_HTTPGET]=true;
			if($url)
				$this->setURL($url);
			return $this->exec();
		}

		# set custom request method
		public function setCustomRequestMethod($method){
			if(!$method){
				$this->appendError('setCustomRequestMethod($method): $method can not be empty.');
				return false;
			}
			$this->options[CURLOPT_CUSTOMREQUEST]=$method;
		}

		# execute custom request
		public function REQUEST($method,$url=''){
			# $method can be: array('method'=>'DELETE','url'=>'http://')
			if(is_array($method)) $method=isset($method['method'])?$method['method']:$method[0];
			if(!$url) $url=is_array($method)?(isset($method['url'])?$method['url']:end($method)):'';
			$this->options[CURLOPT_CUSTOMREQUEST]=$method;
			$this->options[CURLOPT_POST]=false;
			if($url)
				$this->setURL($url);
			return $this->exec();
		}

		# execute handle
		public function exec($url=''){
			if($url) $this->setURL($url);

			$this->Body='';
			$this->haveBody=false;
			$this->Headers='';
			if(!empty($this->posts)) $this->options[CURLOPT_POSTFIELDS]=$this->posts;
			curl_setopt_array($this->ch,$this->options);
			$this->options=array();
			$this->posts=array();
			$this->requestHeaders=array();
			$this->options[CURLOPT_POST]=false;
			$this->options[CURLOPT_CUSTOMREQUEST]='GET';

			$this->executed=true;
			$response=curl_exec($this->ch);
			$noError=true;
			if($response === false){
				$noError=false;
				$this->appendError('cURL Error: '.curl_error($this->ch));
			}
			if(curl_errno($this->ch)){
				if($noError)
					$this->appendError('cURL Error: '.curl_error($this->ch));
				$noError=false;
			}else{
				/*
				// if CURLOPT_HEADER == true
				$header_size=curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
				$header=substr($response, 0, $header_size);
				$body=substr($response, $header_size);
				*/
				$redirectCount=curl_getinfo($this->ch,CURLINFO_REDIRECT_COUNT);
				$httpCode=curl_getinfo($this->ch,CURLINFO_HTTP_CODE);
				$headerSent=curl_getinfo($this->ch,CURLINFO_HEADER_OUT);
				$this->httpCode=$httpCode;
				$this->redirectCount=$redirectCount;
				$this->headerSent=$headerSent;
				$this->thisURL=curl_getinfo($this->ch,CURLINFO_EFFECTIVE_URL);
				$this->redirectTo=curl_getinfo($this->ch,CURLINFO_REDIRECT_URL);
				$this->curlInfo=curl_getinfo($this->ch);
				$response=true;
				switch($httpCode){
					case 200:  # OK
					case 206:  # Part-content
					case 301:  # Moved Permanently
					case 302:  # Found
						break;
					default:
						$response=false;
						$this->appendError("Unexpected HTTP code: $httpCode<br/>\r\ncURL error: ".curl_error($this->ch));
				}
			}
			return $response;
		}

		# get request headers
		public function getRequestHeaders(){
			return $this->headerSent;
		}
		public function getRequestHeader(){
			return $this->getRequestHeaders();
		}

		# set a runtime error
		public function appendError($str_error){
			if(is_array($this->errors))
				$this->errors[]=$str_error;
			else
				$this->errors .= $str_error."<br/>\r\n";
		}

		# close handle
		public function __destruct(){
			curl_close($this->ch);
		}
		/*
		Params:
		browser_cookie a string like c_name1=value_1; c_name_2=val2 this is cookie set in header when you browse a website
		domain : The domain that created AND that can read the variable. 
		flag - A TRUE/FALSE value indicating if all machines within a given domain can access the variable. This value is set automatically by the browser, depending on the value you set for domain. 
		path - The path within the domain that the variable is valid for.

		secure - A TRUE/FALSE value indicating if a secure connection with the domain is needed to access the variable.

		http_only to protect cookie from javascript

		expiration - The UNIX time that the variable will expire on. UNIX time is defined as the number of seconds since Jan 1, 1970 00:00:00 GMT. 
		Return content of  CURL's cookie file
		*/
		public static function to_curl_cookie($browser_cookie,$domain='.facebook.com', $flag=true,$path='/',$secure=false, $http_only=true,$life_time=86400*365 /* one year */ ){
			if(empty($browser_cookie)) return '';
			$arr=explode(';',$browser_cookie);
			$life_time=$_SERVER['REQUEST_TIME']+$life_time;
			$c="# Netscape HTTP Cookie File\r\n		# http://curl.haxx.se/docs/http-cookies.html\r\n		# This file was generated by libcurl! Edit at your own risk.\r\n		# Raw cookie: ".preg_replace('#(\r?\n)+#',' ',$browser_cookie)."\r\n\r\n";
			foreach($arr as $ar){
				if(!$ar || strpos($ar,'=')===false) continue;
				$ar=explode('=',trim($ar));
				$c.=($http_only ? '#HttpOnly_' : '').$domain.'	'.($flag ? 'TRUE' : 'FALSE').'	/	'.($secure ? 'TRUE' : 'FALSE').'	'.$life_time.'	'.$ar[0].'	'.$ar[1]."\n";
			}//foreach
			return $c;
		}//to_curl_cookie

		/*
		Convert curl's cookie to http header cookie
		return string that sent by browser every request with "Cookie:" header
		*/
		public static function to_browser_cookie($curl_cookie){
			$ar=explode("\n",str_replace("\r",'',$curl_cookie));
			$c='';
			foreach($ar as $v){
				$v=trim($v);
				if(empty($v) || ($v{0}=='#' && substr($v,0,10)!='#HttpOnly_')) continue; //Comment
				$v=explode('	',$v);
				$value=array_pop($v);
				$name=array_pop($v);
				$c.='; '.$name.'='.$value;
			}
			$c=substr($c,2);
			return $c;
		}//to_browser_cookie

		/*
		  Function complete_url 
		   To process href,src properties of HTML tag like browser do 
		   Return a url
		*/
		public static function complete_url($base_href='https://m.facebook.com/a/',$href='?action=like'){
			$href=trim($href);
			if($href=='') return '';
			switch($href{0}){
				case '#':
					return $href;
				case 'm':
					if(substr($href, 0, 7)=='mailto:'){
						return $href;
					}
				case 'j':
					if(substr($href, 0, 11)=='javascript:'){
						return $href;
					}
				case 'a':
					if(substr($href, 0, 11)=='about:blank'){
						return $href;
					}
				case 'd':
					if(substr($href, 0, 5)=='data:'){
						return $href;
					}
			}//switch
			$hash_pos=strpos($base_href,'?');
			if($hash_pos!=0)
				$base_href=substr($base_href,0,$hash_pos);
			$hash_pos=strpos($base_href,'#');
			if($hash_pos!=0)
				$base_href=substr($base_href,0,$hash_pos);
			$hash_pos=strrpos($href, '#');
			$fragment=$hash_pos===false ? '': substr($href, $hash_pos);
			$sep_pos=strpos($href, '://');
			$protocol=$sep_pos===false ? '' : strtolower(substr($href,0,$sep_pos));
			if(in_array($protocol, array('http','https','ftp'))) return $href;
			$sep_pos=strpos($base_href, '://');
			$protocol=$sep_pos===false ? '' : strtolower(substr($base_href,0,$sep_pos));
			if(!in_array($protocol, array('http','https','ftp'))){
				trigger_error('complete_url($base_href,$href); failed path is not completed url. Please pass the $base_href like this: http://domain.com:98/path', E_USER_NOTICE);
				return $href;
			}
			$base_href_pos=strpos($base_href,'/',$sep_pos+3);
			$base_href_noshl=false;
			if($base_href_pos===false){
				$host=substr($base_href,$sep_pos+3);
				$base_href_noshl=true;
			}else{
				$host=substr($base_href,$sep_pos+3,$base_href_pos-$sep_pos-3);
			}

			$hash_pos=strrpos($base_href, '#');
			$shl_pos=strpos($base_href,'?');
			if($shl_pos===false)
				$shl_pos=$hash_pos;
			else if($hash_pos!==false)
				$shl_pos=min($shl_pos,$hash_pos);
			if($shl_pos===false)
				$shl_pos=0;
			else
				$shl_pos=$shl_pos-strlen($base_href);
			$real_path=( $base_href_pos===false ? $base_href.'/' : substr($base_href, 0, strrpos($base_href,'/',$shl_pos)).'/' );
			switch($href{0}){
				case '/':
					$href=substr($href, 0, 2)=='//' ? $protocol.':'.$href : $protocol.'://'.$host.$href;
					break;
				case '?':
					$href=$base_href.($base_href_noshl ? '/' : '').$href;
					break;
				case '.':
					if(substr($href, 0, 2)=='./'||substr($href, 0, 3)=='../'){
						$href=$real_path.$href;
						break;
					}
				default:
					$href=$real_path.$href;
					break;
			}//switch
			return $href;
		}//complete_url
	}//define class cURL
}//class cURL exist

if(!function_exists('_curl')){
	function _curl($url,$options=array()){
		$protocol=substr($url,0,7);
		if($protocol!='http://' && $protocol!='https:/'){
			if(is_file($url)){
				return file_get_contents($url);
			}else{
				trigger_error('file_get_contents("'.$url.'"): file not found', E_USER_NOTICE);
				return 0;
			}
		}
		global $cURL;
		if(!isset($cURL)) $cURL=new cURL();
		$cURL->setOption($options);
		$cURL->setURL($url);
		$cURL->exec();
		return $cURL;
	}//define function _curl
}// function _curl exist