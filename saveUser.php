<?php
require_once(__DIR__ . "/db.php");

class saveUser{
    /*
     * 通过用户手机号查询userid
     * @param string $mobile 手机号
     * @param string $userid 钉钉userid
     */
    public function selectUserIdByMobile($mobile){
        $dbObj = new db();
        $conn  = $dbObj->mysqlConnect();
        if(!$conn){
            return '';
        }
        //sql
        $sql = "select * from wx_admin_userid_mobile where `mobile` = ".$mobile;
        $result = 
    }
}