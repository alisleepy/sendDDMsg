<?php
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/util/Http.php");
require_once(__DIR__ . "/util/Log.php");
require_once(__DIR__ . "/db.php");
error_reporting(false); //禁止错误信息输出

$userid     = $_GET['userid'];     //员工的钉钉userid
$msgConetnt = $_GET['msgConetnt']; //消息内容（每一个用户每天相同内容只能收到一次，所以内容不能每次相同）
if(!$userid){
    Log::e('userid不能为空');
    echoJson(-1, 'userid不能为空');
}
if(!$msgConetnt){
    Log::e('msgConetnt不能为空');
    echoJson(-1, 'msgConetnt不能为空');
}
$access_token = getAccessToken(APP_KEY, APP_SECRET);
if(!$access_token){
    Log::e('access_token获取失败');
    echoJson(-1, 'access_token获取失败');
}else{
    //发送钉钉消息
    sendWordMessage($access_token, $userid, $msgConetnt);
}
//获取access_token
function getAccessToken($appkey, $appsecret) {
    $ret = Http::get("/gettoken",
    array(
        "appkey" => $appkey,
        "appsecret" => $appsecret,
    ));
    if ($ret->errcode != 0) {
        Log::e('获取access_token错误，'.$ret->errmsg);
        return '';
    }
    return $ret->access_token;
}
//发送通知
function sendWordMessage($access_token, $userid, $msgConetnt){
    $agent_id = AGENT_ID;
    $userid_list = $userid;
    $msg = '{"msgtype":"text","text":{"content":"'.$msgConetnt.'"}}';

    $url = 'https://oapi.dingtalk.com/topapi/message/corpconversation/asyncsend_v2?access_token='.$access_token;
    $post_data['agent_id'] = $agent_id;
    $post_data['userid_list'] = $userid_list;
    $post_data['msg'] = $msg;

    $res = request_post($url, $post_data);
    $resArr = object2array($res);
    if($res->errcode != 0){
        Log::e('工作通知发送失败，'.$res->errmsg);
        echoJson(-1, '工作通知发送失败，');
    }else{
        echoJson(0, '工作通知发送成功，');
    }
}
/**
 * 模拟post进行url请求
 * @param string $url
 * @param array $post_data
 */
function request_post($url = '', $post_data = array()) {
    if (empty($url) || empty($post_data)) {
        return false;
    }
    
    $o = "";
    foreach ( $post_data as $k => $v ) 
    { 
        $o.= "$k=" . urlencode( $v ). "&" ;
    }
    $post_data = substr($o,0,-1);

    $postUrl = $url;
    $curlPost = $post_data;
    $ch = curl_init();//初始化curl
    curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
    curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
    curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
    curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
    $data = curl_exec($ch);//运行curl
    curl_close($ch);
    
    return $data;
}

//返回信息
function echoJson($code, $msg, $data = array()){
    if(!$data){
        echo json_encode(array('code'=>$code, 'msg'=>$msg));
        exit;
    }
    echo json_encode(array('code'=>$code, 'msg'=>$msg, 'data'=>$data));
    exit;
}
