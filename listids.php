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
    var_dump($res);exit;
    if ($res->errcode == 0) {
        $user = Http::get("/user/get",
        array(
            "access_token" => $access_token,
            "userid" => $res->userid,
        ));
        if ($res->errcode == 0) {
            echo json_encode(array(
                "result" => array('userId' => $res->userid, 'userName' => $user->name),
            ));
        } else {
            Log::e('获取用户信息错误，'.$user->errmsg);
        }
    } else {
        Log::e('获取用户ID错误，'.$res->errmsg);
    }
}



