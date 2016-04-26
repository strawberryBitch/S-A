<?php

require_once('ch_json_encode.php');
require_once('access_token.php');

/**
  * wechat curl post handler
  */
class Curl_post {
	var $multihandle;
	var $result;
	function Curl_post(){
		$this->multihandle= curl_multi_init();
		$this->result=array();
	}

	function close(){
		curl_multi_close($this->multihandle);
	}

	//set post required data and do post action,every time use new access token,
	//access token is NOT checkeused here
	function wechat_post($url,$data){

		$ch = curl_init();
		//everytime use updated access_token'
		//calling get_access_token from access_token.php
		$fullurl = $url.get_access_token();
		curl_setopt($ch, CURLOPT_URL, $fullurl);
		//postdata stored, if next use same data, no need to change url
		curl_setopt($ch, CURLOPT_POST,1);
		//set to receive return value
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_TIMEOUT,2);
		//这里一定要记得用JSON_encode,傻逼腾讯收到不encode的内容返回的是invalid userid
	    //ch_json_encode from ch_json_encode.php
		curl_setopt($ch, CURLOPT_POSTFIELDS, ch_json_encode($data));
	    curl_multi_add_handle($this->multihandle,$ch);
	}

	function all_post_exec(){
		$running=null;
		//multiple initialize
		do {
			//sleep for 0.1sec 
		    usleep(50000);
		    array_push($this->result, curl_multi_exec($this->multihandle,$running));
		} while ($running > 0);
	}
}

?>