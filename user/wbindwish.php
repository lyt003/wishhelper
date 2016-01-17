<?php
session_start ();
use mysql\dbhelper;
include '../mysql/dbhelper.php';
header ( "Content-Type: text/html;charset=utf-8" );
$dbhelper = new dbhelper();

$username = $_SESSION ['username'];
if($username == null){
	$type = $_GET ['type'];
	if(strcmp($type,"register") == 0){
		$email = $_POST ["email"];
		$username = $_POST ["username"];
		$password = $_POST ["password"];
		$check = $dbhelper->queryUser($username, $email);
		$checkrow=mysql_fetch_array($check);
		if($checkrow){
			if($checkrow['username'] == $username){
				header("Location:./wregister.php?errorMsg=该用户已经存在");
				exit;
			}
			if($checkrow['email'] == $email){
				header("Location:./wregister.php?errorMsg=该邮箱地址已经被注册");
				exit;
			}
		}else{
			$result = $dbhelper->createUser($username, md5($password), $email);
			if($result !== false){
				$_SESSION ['username'] = $username;
			}else{
				header("Location:./wregister.php?errorMsg=注册失败");
				exit;
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
			$_SESSION ['username'] = $username;
		}else{
			header("Location:http://localhost/wishhelper/user/wlogin.php?errorMsg=登录失败");
			exit;
		}
	}
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- saved from url=(0031)http://china-merchant.wish.com/ -->
<html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Wish 商户平台</title>
<meta name="keywords" content="">
<link rel="stylesheet" type="text/css" href="../css/home_page.css">
</head>
<script type="text/javascript">
	function login(){
		var clientid  = document.getElementById("clientid").value;
		if(clientid == null){
			alert("请先输入clientid");
			return;
		}
		window.open("https://merchant.wish.com/oauth/authorize?client_id=" + clientid);    
	}

	function bind(){
		var bindcode  = document.getElementById("bindcode").value;
		if(bindcode == null || bindcode.indexOf("code") == -1){
			alert("请先输入登录后页面的网址");
			return;
		}
		var position = bindcode.indexOf("code");
		code = bindcode.substring(position+5,bindcode.length);
		alert(code);
		window.open("http://localhost/wishhelper/index.php?code=" + code);  
	}
</script>
<body>
<!-- HEADER -->
<div id="header" class="navbar navbar-fixed-top 



" style="left: 0px;">
<div class="container-fluid ">
<a class="brand" href="http://wishconsole.com/">
<span
				class="merchant-header-text"> 更有效率的Wish商户实用工具 </span>
</a>

<div class="pull-right">
<ul class="nav">
<li data-mid="5416857ef8abc87989774c1b" data-uid="5413fe984ad3ab745fee8b48">
<?php echo $username?>
</li>


</ul>

</div>

</div>
</div>
<!-- END HEADER -->
<!-- SUB HEADER NAV-->
<!-- splash page subheader-->



<div id="sub-header-nav" class="navbar navbar-fixed-top sub-header" style="left: 0px;">
<div class="navbar-inner">
<div class="container-fluid">
<div class="pull-left">
<ul class="nav">


<li><a href="./wusercenter.php">
订单处理
</a></li>
<li>
<a href="./wuploadproduct.php">
产品上传
</a>
</li>
<li><a href="http://wishconsole.com/">
个人信息
</a></li>
</ul>
</div>

<div class="pull-right">
<ul class="nav">
</ul>
</div>

</div>
</div>
</div>
<!-- END SUB HEADER NAV -->
<div class="banner-container">
</div>

<div id="page-content" class="container-fluid  user">
<ul>
&nbsp;&nbsp;&nbsp;&nbsp;请输入wish账号验证信息：</ul>
<ul>&nbsp;&nbsp;&nbsp;&nbsp;Client Id:<input id="clientid" type="text" name="clientid" value=""/></ul>
<ul>&nbsp;&nbsp;&nbsp;&nbsp;Client Secret:<input id="clientsecret" type="text" name="clientsecret" value=""/></ul>
<ul>&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" onclick="login()">登录wish</button>(请在新开页面中登录wish账号，并且把之后的页面地址复制下来)</ul>
<ul></ul>
<ul></ul>
<ul>&nbsp;&nbsp;&nbsp;&nbsp;输入页面地址:<input id="bindcode" type="text" name="bindcode" value=""/></ul>
<ul><button type="button" onclick="bind()">绑定账号</button></ul>
</div>
<!-- FOOTER -->
	<div id="footer" class="navbar navbar-fixed-bottom" style="left: 0px;">
		<div class="navbar-inner">
			<div class="footer-container">
				<span><a href="http://wishconsole.com/">关于我们</a></span> <span><a>2016
						wishconsole版权所有 京ICP备16000367号</a></span>
			</div>
		</div>
	</div>
	<!-- END FOOTER -->
</body>
</html>