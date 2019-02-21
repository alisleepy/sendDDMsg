<?php
require_once(__DIR__ . "/config.php");
class db{
    public $servername = DB_SERVERNAME;
    public $username   = DB_USERNAME;
    public $password   = DB_PASSWORD;
    public $dbname     = DB_NAME;

    //连接mysql
    public function mysqlConnect(){
        //创建连接
        $conn = new mysqli($this->servername, $this->username, $this->password);
        if($conn->connect_error){
            return false;
        }
        var_dump($conn);exit;
        return $conn;
    }
    /*
     * 执行sql语句
     * @param $conn obj 连接对象
     * @param $sql  string sql语句
     */
    public function query($conn, $sql){
        mysqli_select_db($conn,$this->dbname);  //选择库
        mysql_query("set names 'utf8'");        //设置字符集
        $result = mysqli_query($conn,$sql);//设置指定编码格式
        return $result;
    }
    //关闭mysql连接
    public function mysqlClose($conn){
        mysqli_close($conn);
    }
}
