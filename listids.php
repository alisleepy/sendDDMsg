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
    if ($res->errcode == 0) {
        $list_ids_arr = object2array($list_ids);
        var_dump($list_ids_arr);exit;
    } else {
        Log::e('获取部门ids失败，'.$res->errmsg);
    }
}
//对象转数组
function object2array($obj) {
    $obj = (array)$obj;
    foreach ($obj as $k => $v) {
        if (gettype($v) == 'resource') {
            return;
        }
        if (gettype($v) == 'object' || gettype($v) == 'array') {
            $obj[$k] = (array)object_to_array($v);
        }
    }
    return $obj;
}



