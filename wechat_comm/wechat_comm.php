<?php
/**
  *require files
  */
require_once('xmlhandler.php');
require_once('wechat_curl_post.php');

/** 
  * handle received XML data
  */

$xml=file_get_contents("php://input");//获取xml
//init curl_post object
$curl_post = new Curl_post();
//check if receive xml, pass value to XMLhandler object,refer to xmlhandler.php
if($xml){
    //initiate xmlhandler
    $xmlhandler = new XMLhandler($xml);
    //get data in $data->value form
    $data = $xmlhandler->getData();
    $user = strval($data->FromUserName);

    //$file = fopen("newfile.txt", "w");
    //fwrite($file,$xml);
    //fclose($file);

    if($data->Event=="subscribe"){
        $post = array(
            "touser" => $user,
            "msgtype" => "text",
            "text" => array("content" => "感谢关注！Welcome to Stone&Associates!")
        );
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=";
        $curl_post->wechat_post($url,$post);
    }

    //case qrscene_feedback : 用户未关注微信号，用户扫上门服务反馈二维码并关注微信号
    //case feedback : 用户已关注微信号，扫上门服务反馈二维码
    if($data->EventKey=="qrscene_feedback" || $data->EventKey=="feedback"){
        $post = array(
            "touser" => $user,
            "msgtype" => "text",
            "text" => array("content" => "没时间解释了，快上车 www.stoneandassociates.cn")
        );
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=";
        $curl_post->wechat_post($url,$post);
    }
    $curl_post->all_post_exec();
}

/** 
  * if not xml, handle in the next block
  */

/** 
  * wechat comm test
  */
//define your token
//define("TOKEN", "stoneandassoc");
//$wechatObj = new wechatCallbackapiTest();
//$wechatObj->valid();

class wechatCallbackapiTest
{
    public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }

    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        //extract post data
        if (!empty($postStr)){
                /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
                   the best way is to check the validity of xml by yourself */
                libxml_disable_entity_loader(true);
                $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $fromUsername = $postObj->FromUserName;
                $toUsername = $postObj->ToUserName;
                $keyword = trim($postObj->Content);
                $time = time();
                $textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            <FuncFlag>0</FuncFlag>
                            </xml>";             
                if(!empty( $keyword ))
                {
                    $msgType = "text";
                    $contentStr = "Welcome to wechat world!";
                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
                }else{
                    echo "Input something...";
                }

        }else {
            echo "";
            exit;
        }
    }
        
    private function checkSignature()
    {
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }
        
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
                
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
}

?>