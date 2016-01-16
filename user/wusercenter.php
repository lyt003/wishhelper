<?php
use mysql\dbhelper;
include '../mysql/dbhelper.php';
header ( "Content-Type: text/html;charset=utf-8" );
$dbhelper = new dbhelper();
$type = $_GET ['type'];
if(strcmp($type,"register") == 0){
	$email = $_POST ["email"];
	$username = $_POST ["username"];
	$password = $_POST ["password"];
	echo "values:".$email.$username.$password;
	$check = $dbhelper->queryUser($username, $email);
	$checkrow=mysql_fetch_array($check);
	if($checkrow){
		if($checkrow['$username'] == $username){
			echo "该用户已经存在";
		}
		if($checkrow['$email'] == $email){
			echo "该邮箱地址已经注册";
		}	
	}else{
		$result = $dbhelper->createUser($username, md5($password), $email);
		echo "result".$result;
		if($result !== false){
			echo "register success";
		}else{
			echo "register failed";
		}	
	}
}else{
	//login;
	$username = $_POST["username"];
	$password = $_POST ["password"];
	
	$dbhelper = new dbhelper();
	$result = $dbhelper->userLogin($username, md5($password));
	$row=mysql_fetch_array($result);
	if($row){
		echo "Hello, login succeed";
	}else{
		echo "Sorry, login failed";
	}	
}
?>