<?php
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/util/Http.php");
require_once(__DIR__ . "/util/Log.php");
error_reporting(false); //


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
$access_token = getAccessToken(APP_KEY, APP_SECRET);
if ($access_token) {
    //获取部门列表
    $res = Http::get("/department/list_ids",
    array(
        "access_token" => $access_token,
        "id" => 1, //默认根部门
    ));
    if ($res->errcode != 0) {
        Log::e('获取部门ids失败，'.$res->errmsg);
    } else {
        $resArr = object2array($res);
        $sub_dept_id_list = $resArr['sub_dept_id_list']; //部门id列表
        if($sub_dept_id_list){
            foreach($sub_dept_id_list as $deptid){
                //获取部门用户ids
                $userIdsArr = getDeptMember($access_token, $deptid);
                //获取用户的信息
                if($userIdsArr){
                    //获取用户信息
                    foreach($userIdsArr as $userid){
                        if($userid == 'manager232'){
                            $userInfo = getUserInfo($access_token, $userid);
                            //var_dump($userInfo);exit;
                            //把用户userid和mobile保存起来，下次使用时直接获取
                            //saveUseridAndMobile($userInfo);
                            //发送工作通知
                            sendWordMessage($access_token, $userInfo);
                        }
                    }
                }
            }
        }
    }
}
//对象转数组
function object2array($object) {
    if (is_object($object)) {
        foreach ($object as $key => $value) {
            $array[$key] = $value;
        }
    }
    else {
        $array = $object;
    }
    return $array;
}
//获取部门用户ids列表
function getDeptMember($access_token, $deptid){
    if(!$access_token || !$deptid){
        Log::e('access_token或deptid缺失');
        return '';
    }
    $res = Http::get("/user/getDeptMember",
    array(
        "access_token" => $access_token,
        "deptId" => $deptid, //部门id
    ));
    if($res->errcode != 0){
        Log::e('获取部门用户ids失败，'.$res->errmsg);
        return '';
    }
    $resArr = object2array($res);
    $userIdsArr = $resArr['userIds'];
    return $userIdsArr ? $userIdsArr : [];
}
//获取用户信息
function getUserInfo($access_token, $userid){
    if(!$access_token || !$userid){
        Log::e('access_token或userid缺失');
        return '';
    }
    $res = Http::get("/user/get",
    array(
        "access_token" => $access_token,
        "userid" => $userid, //用户id
    ));
    if($res->errcode != 0){
        Log::e('获取用户信息失败，'.$res->errmsg);
        return '';
    }
    $resArr = object2array($res);
    return $resArr;
}
//保存用户信息，下次使用
function saveUseridAndMobile($userInfo){
    if(empty($userInfo)){
        return '';
    }
    //连接mysql
    $conn = mysqlConnect();
    if($conn == false){
        Log::e('数据库连接失败');
        return '';
    }
    //执行sql语句
    query($conn, $sql);
}
//连接mysql
function mysqlConnect(){
    $servername = DB_SERVERNAME;
    $username   = DB_USERNAME;
    $password   = DB_PASSWORD;
    //创建连接
    $conn = new mysqli($servername, $username, $password);
    if($conn->connect_error){
        return false;
    }
    return $conn;
}
//执行sql语句
function query($conn, $sql){

}
//发送通知
function sendWordMessage($access_token, $userInfo){
    if(!$userInfo){
        return '';
    }
    $agent_id = AGENT_ID;
    $userid_list = $userInfo['userid'];
    $msg = '{"msgtype":"text","text":{"content":"【想帮帮】您好，王凯凯提交了新订单，请及时处理'.date('Y-m-d H:i').'"}}';

    $url = 'https://oapi.dingtalk.com/topapi/message/corpconversation/asyncsend_v2?access_token='.$access_token;
    $post_data['agent_id'] = $agent_id;
    $post_data['userid_list'] = $userid_list;
    $post_data['msg'] = $msg;

    $res = request_post($url, $post_data);
    var_dump($res);exit;

    $resArr = object2array($res);
    echo '<pre>';
    print_r($resArr);exit;
    if($res->errcode != 0){
        Log::e('工作通知发送失败，'.$res->errmsg);
        return '';
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
