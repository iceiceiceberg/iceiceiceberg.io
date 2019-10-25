<?php
//$type = $_POST['type'];
$password = trim($_POST['password']);
$number = trim($_POST['number']);
//$password = trim($_POST['password']);
$name = trim($_POST['name']);
$class = trim($_POST['class']);
//$college = $_POST['college'];

/*echo "<br />".$type;
echo "<br />".$ID;
echo "<br />".$password;
echo "<br />".$name;
echo "<br />".$sex;
echo "<br />".$college;*/
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>注册结果</title>
	</head>
	<body>
		<h1>注册结果</h1>
		<?php
		
		/*过滤输入数据*/
		if(!$password || !$number || !$name || !$class){
			echo '<p>未输入完整	信息<br />
				请重新输入</p>';
				exit;			
		}
		
		/*连接数据库*/
		$db = new mysqli('localhost', 'root', '~lc1117~', 'signin');
		if(mysqli_connect_errno()){
				echo '<p>Error: Could not connect to database.<br />
				Please try again later.</ p>';
				exit;
			}
		//else echo'<p>connecttion success.<br /></p>';
		
		/*插入操作*/
		/*if($type == 'student'){
			$query = "INSERT INTO Students VALUE(?, ?, ?, ?, ?)";
		}
		else if ($type == 'teacher'){
			$query = "INSERT INTO Teachers VALUE(?, ?, ?, ?, ?)";
		}
		*/
		$query = "INSERT INTO sign_class(number, name, class, password) VALUE(?, ?, ?, ?)";
		
		$stmt = $db->prepare($query);
		$stmt->bind_param('isss', $number, $name, $class, $password);
		$stmt->execute();
		
		if($stmt->affected_rows > 0){
			echo "<p>注册成功</p>";			
		}
		else {
			echo "<p>注册发生错误</p>";
		}
		?>
	</body>
</html>
