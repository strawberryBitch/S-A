<?php
//ini_set("display_errors", "On");
//error_reporting(E_ALL | E_STRICT);
//useage
//echo get_access_token();
function get_access_token(){
	//get access token from local reddis server or from wechat server
	$configs = require('config.php');
	/**
	 * try get access token from local
	 */
	//connect server
	$redis = new Redis();
	$redis->connect($configs['redis_server'], $configs['redis_port']);
	$redis->select($configs['redis_db']);
	//get token
	$cur_token = $redis->get('access_token');
	//if hit, return token
	if($cur_token){
		return $cur_token;
	}
	/**
	 * if token miss, get token from wechat server
	 */
	//load settings
	$appid=$configs['wechat_appid'];
	$appsecret=$configs['wechat_appsecret'];
	//magical url settings written in code
	$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";
	//curl init& settings, using get, no post info
	//, not using wechat_curl_post.php to prevent circular include
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    
    $res = curl_exec($ch);

    if(!$res){ die('connection failed');}
    //use simple json decode
    $json_obj = json_decode($res, true);
    //test if has errcode
    if(array_key_exists('errcode',$json_obj)){
    	die('errcode: $json_obj["errcode"],errmsg: $json_obj["errmsg"]');
    }
    //return valid token, set into redis
    $redis->set('access_token',$json_obj['access_token']);
    //set timeout
    $redis->setTimeout('access_token',$configs['wechat_acces_token_expire']);
    return $json_obj['access_token'];
}


?>