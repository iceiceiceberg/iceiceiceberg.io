<?php
header("Content-type: text/html; charset=utf-8");
error_reporting(0);
include 'conn.php';
include 'libs/mysql.class.php';
include 'libs/AipFace.php';
include 'libs/distance.function.php';
include 'libs/PHPExcel.class.php';

$M = new mysql();
$M->connect(DB_HOST,DB_USER,DB_PASS,DB_NAME);

switch (isset($_REQUEST['action'])?$_REQUEST['action']:null) {
    //设置签到
    case 'setSignin':
		echo $keyword;
        /*$room = isset($_POST['room'])?$_POST['room']:null; //教室号
        //$lat = isset($_POST['lat'])?$_POST['lat']:null; //纬度
        //$long = isset($_POST['long'])?$_POST['long']:null; //经度
        $keyword = isset($_POST['keyword']) ? $_POST['keyword']:null;
        $class = isset($_POST['class'])?$_POST['class']:null; //班级
        if($M->getRoom($room)){
            echo '{"status":"1"}';
        }else{
            $result = $M->setSignin($room,$lat,$long,$keyword,$class);
            echo $result ? '{"status":"0"}':'{"status":"1"}';
        }*/
        break;
?>