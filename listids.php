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
                        $userInfo = getUserInfo($access_token, $userid);
                        var_dump($userInfo);exit;
                        //发送工作通知
                        sendWordMessage();
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
    }
    $res = Http::get("/user/getDeptMember",
    array(
        "access_token" => $access_token,
        "deptId" => $deptid, //部门id
    ));
    if($res->errcode != 0){
        Log::e('获取部门用户ids失败，'.$res->errmsg);
    }
    $resArr = object2array($res);
    $userIdsArr = $resArr['userIds'];
    return $userIdsArr ? $userIdsArr : [];
}
//获取用户信息
function getUserInfo($access_token, $userid){
    if(!$access_token || !$userid){
        return [];
    }
    $res = Http::get("/user/get",
    array(
        "access_token" => $access_token,
        "userid" => $userid, //用户id
    ));
    var_dump($res);exit;
}


