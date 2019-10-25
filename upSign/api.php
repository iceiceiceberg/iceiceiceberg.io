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
//$aipFace = new AipFace(APP_ID, API_KEY, SECRET_KEY);

switch (isset($_REQUEST['action'])?$_REQUEST['action']:null) {
    //设置签到
    case 'setSignin':
        $room = isset($_POST['room'])?$_POST['room']:null; //教室号
        $lat = isset($_POST['lat'])?$_POST['lat']:null; //纬度
        $long = isset($_POST['long'])?$_POST['long']:null; //经度
        $keyword = isset($_POST['keyword']) ? $_POST['keyword']:null;//口令
        $class = isset($_POST['class'])?$_POST['class']:null; //班级
        if($M->getRoom($room)){//查看当前教室是否被占用
            echo '{"status":"1"}';
        }else{
            $result = $M->setSignin($room,$lat,$long,$keyword,$class);
            echo $result ? '{"status":"0"}':'{"status":"1"}';
        }
        break;
    //结束签到
    case 'endSignin':
        $room = isset($_POST['room'])?$_POST['room']:null; //教室号
        if($M->getRoom($room)){
            if($M->endSignin($room)){//调用endSign来更改签教师表中的签到标志位
                $result = $M->clearSignin($room);//清楚学生签到表数据
                echo $result ? '{"status":"0"}':'{"status":"1"}';
            }else{
                echo '{"status":"1"}';
            }
        }else{
            echo '{"status":"1"}';
        }
        break;
    //获取签到信息
    case 'getSignin':
        $room = isset($_POST['room'])?$_POST['room']:null; //教室号
        if(!$M->getRoom($room)){
            echo '{"status":"1"}';
        }else{//获取学生签到表中签到信息
            $result = $M->getSignin($room);
            echo $result ? json_encode($result):'{"status":"2"}';
        }
        break;
    //获取班级列表
    case 'getClass':
        $result = $M->getClass();
        echo $result ? json_encode($result,JSON_UNESCAPED_UNICODE):'{"status":"2"}';//中文不转为unicode ，对应的数字 256
        break;
    //获取班级学生信息
    case 'getStudent':
        $class = isset($_POST['class'])?$_POST['class']:null; //教室号
        $result = $M->getStudent($class);
        echo $result ? json_encode($result):'{"status":"1"}';
        break;
    //人脸注册
    case 'addFace':
        $name = isset($_POST['name'])?$_POST['name']:null; //姓名
        $number = isset($_POST['number'])?$_POST['number']:null; //学号
        $random = time().mt_rand(1111,9999);
        $face_tmp = isset($_FILES["file"]["tmp_name"][0])?$_FILES["file"]["tmp_name"][0]:null;
        $file_name = "uploads/".$random.".jpg";
        move_uploaded_file($face_tmp,$file_name);
        $result = $aipFace->addUser($number,$name,'student',file_get_contents($file_name));
        if(array_key_exists('error_code',$result)){echo '<h2>人脸注册失败，请将信息填写完整！</h2>';header('refresh:1;url=face.html');}else{echo '<h2>人脸注册成功！</h2>';header('refresh:0.5;url=face.html');}
        break;
    //班级导入
    case 'importClass':
        $random = time().mt_rand(1111,9999);
        $face_tmp = isset($_FILES["file"]["tmp_name"][0])?$_FILES["file"]["tmp_name"][0]:null;
        $file_name = "uploads/".$random.".xls";
        move_uploaded_file($face_tmp,$file_name);
        $excelReader = PHPExcel_IOFactory::createReader('Excel5');
        try {
            $excelLoad = $excelReader->load($file_name,$encode='utf-8');
        }catch(Exception $e){
            echo '<h2>导入失败！请检查Excel格式是否正确！</h2>';
            header('refresh:1;url=teacher.html');
            exit;
        }
        $sheet = $excelLoad->getSheet(0); //取得sheet(0)表
        $highestRow = $sheet->getHighestRow(); //取得总行数
        for($i=2; $i<=$highestRow; $i++) {
            $number = $excelLoad->getActiveSheet()->getCell("A".$i)->getValue(); //学号
            $name = $excelLoad->getActiveSheet()->getCell("B".$i)->getValue(); //姓名
            $class = $excelLoad->getActiveSheet()->getCell("C".$i)->getValue(); //班级
            $result = $M->importClass(trim($number),trim($name),trim($class));
        }
        if(!$result){echo '<h2>导入完成！</h2>';header('refresh:1;url=teacher.html');exit;}else{echo '<h2>导入完成！</h2>';header('refresh:0.5;url=teacher.html');}
        break;
    //人脸上传
    case 'identifyFace':
        $random = time().mt_rand(1111,9999);
        $face_tmp = isset($_FILES["file"]["tmp_name"][0])?$_FILES["file"]["tmp_name"][0]:null;
        $file_name = "uploads/".$random.".jpg";
        $status = move_uploaded_file($face_tmp,$file_name);
        if($status){echo '{"status":"0","file_name":"'.$file_name.'"}';}else{echo '{"status":"1"}';}
        break;
    //提交口令签到
    case 'Signin':
		$keyword = isset($_POST['keyword'])?$_POST['keyword']:null;//口令
        $room = isset($_POST['room'])?$_POST['room']:null; //教室号
        $number = isset($_POST['number'])?$_POST['number']:null; //学号
        $name = isset($_POST['name'])?$_POST['name']:null; //姓名
        $slat = isset($_POST['lat'])?$_POST['lat']:null; //纬度
        $slong = isset($_POST['long'])?$_POST['long']:null; //经度
        
        if($M->getRoom($room)){
            if($M->checkSign($room,$number)){//检查学生已经签到
                echo '{"status":"3"}';
            }
            else{
                    if($M->checkKeyword($keyword, $room))//验证口令
                    {
                        $getRoom = $M->getRoom($room);
						//检查当前教室是否开启签到
                        if($getRoom)
                        {   /*                  
                            $signin = $M->Signin($room,$tlat,$tlong,$name,$number);
                            echo $signin ? '{"status":"0"}' : '{"status":"1"}';  
							  */
							$tlat = $getRoom['lat'];
                            $tlong = $getRoom['long'];
                            $distance = getDistance($slat,$slong,$tlat,$tlong);
                            if($distance < 5) {
                                $signin = $M->Signin($room,$slat,$slong,$name,$number);
                                echo $signin ? '{"status":"0","scores":"'.$result['result'][0]['scores'][0].'"}':'{"status":"1"}';
                            }else{
                                echo '{"status":"2"}';
                            }                      
                        }
                        else{
                            echo '{"status":"4"}';
                        }
                    }
					else
					{
						echo '{"status":"1"}';	
					}
            }
        }else{
            echo '{"status":"4"}';
        }
        break;
    //定位以及人脸签到
    /*case 'Signin':
        $room = isset($_POST['room'])?$_POST['room']:null; //教室号
        $number = isset($_POST['number'])?$_POST['number']:null; //学号
        $name = isset($_POST['name'])?$_POST['name']:null; //姓名
        $file_name = isset($_POST['file_name'])?$_POST['file_name']:null; //文件名
        $slat = isset($_POST['lat'])?$_POST['lat']:null; //纬度
        $slong = isset($_POST['long'])?$_POST['long']:null; //经度
        if($M->getRoom($room)){
            if($M->checkSign($room,$number)){//检查学生已经签到
                echo '{"status":"3"}';
            }else{
                $result = $aipFace->identifyUser('student',file_get_contents($file_name));
                if(array_key_exists('error_code',$result)){
                    echo '{"status":"1"}';
                }else{
                    if($result['result'][0]['uid'] == $number){
                        $getRoom = $M->getRoom($room);
                        if($getRoom){
                            $tlat = $getRoom['lat'];
                            $tlong = $getRoom['long'];
                            $distance = getDistance($slat,$slong,$tlat,$tlong);
                            if($distance < 20) {
                                $signin = $M->Signin($room,$tlat,$tlong,$name,$number);
                                echo $signin ? '{"status":"0","scores":"'.$result['result'][0]['scores'][0].'"}':'{"status":"1"}';
                            }else{
                                echo '{"status":"2"}';
                            }
                        }else{
                            echo '{"status":"4"}';
                        }
                    }else{
                        echo '{"status":"1"}';
                    }
                }
            }
        }else{
            echo '{"status":"4"}';
        }
        break;*/
    //提交请假
    case 'Leave':
        $room = isset($_POST['room'])?$_POST['room']:null; //教室号
        $number = isset($_POST['number'])?$_POST['number']:null; //学号
        $name = isset($_POST['name'])?$_POST['name']:null; //姓名
        /*$lat = isset($_POST['lat'])?$_POST['lat']:null; //纬度
        $long = isset($_POST['long'])?$_POST['long']:null; //经度*/
        $reason = isset($_POST['reason'])?$_POST['reason']:null; //请假理由
        if($M->getRoom($room)){
            if($M->checkSign($room,$number)){
                echo '{"status":"3"}';
            }else{
                $leave = $M->Signin($room,$lat,$long,$name,$number,'1',$reason);
                echo $leave ? '{"status":"0"}':'{"status":"1"}';
            }
        }else{
            echo '{"status":"4"}';
        }
        break;
    default:
        echo '{"status":"1"}';
        break;
}
