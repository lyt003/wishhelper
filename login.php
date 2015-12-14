<?php
session_start ();
$online = false;
$email = $_POST ['email'];
$psd = $_POST ['password'];
if (isset ( $_SESSION ['userid'] )) {
	$next_url = "orders.php";
	header ( 'Location: ' . $next_url );
} else {
	$email = $_POST ['email'];
	$psd = $_POST ['password'];
	if (! empty ( $email ) && ! empty ( $psd )) {
		
		if($online){
			$dbhost = "bdm195587474.my3w.com";
			 
			$dbuser = "bdm195587474";
			 
			$dbpsd = "yangwu19821112";
			 
			$dbname = "bdm195587474_db";
		}else{
			$dbhost = "localhost";
			$dbuser = "root";
			$dbpsd = "yangwu";
			$dbname = "wish";
		}
		
		$db = mysql_connect ( $dbhost, $dbuser, $dbpsd );
		mysql_select_db ( $dbname );
		mysql_query ( "set names 'utf-8'" );
		
		$query = "select userid from users where email = '".$email."' and psd = '".$psd."'";
		$result = mysql_query ( $query );
		if (mysql_num_rows($result) == 1) {
			$row = mysqli_fetch_array ( $result );
			$_SESSION ['userid'] = $row ['userid'];
			$_SESSION ['email'] = $email;
			$next_url = "orders.php";
			header ( 'Location: ' . $next_url );
		} else {
			echo "login failed";
		}
	} else {
		echo "please enter valid email and password";
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Wish 订单助手</title>
		<meta name="keywords" content="">
			<link rel="stylesheet" type="text/css"
				href="https://static.china-merchant.wish.com/china_css/login_page.css?v=6968179812bf" />

</head>
<body>
	<!-- HEADER -->
	<div id="header" class="navbar navbar-fixed-top 

">
		<div class="container-fluid ">
			<a class="brand" href="/"> <span class="merchant-header-text">

					wish订单助手 </span>
			</a>




		</div>
	</div>
	<!-- END HEADER -->
	<!-- SUB HEADER NAV-->
	<!-- splash page subheader-->



	<div id="login-page-content" class="center">
		<form action="login.php" method="post">
			<div class="clearfix box">
				<div id="login-form">
					<div class="header">登录</div>
					<div class="inputs">
						<div>
							<input value="ydengwu@gmail.com" id="email-box" type="text" name="email"
								class="login-input input-block-level" required="true"
								placeholder="邮箱地址" />
						</div>
						<div>
							<input value="yangwu" id="password-box" type="password" name="password"
								class="login-input input-block-level" required="true"
								placeholder="密码" />
						</div>
						<div class="clearfix control-group lst-elem">
							<div class="pull-left remember-me">
								<label class="checkbox"> <input checked id="remember-me"
									type="checkbox" /> 记住我
								</label>
							</div>
							<div class="pull-right">
								<a class="btn btn-link issue-link pull-right"
									href=https://china-merchant.wish.com/forget_password>忘记密码了？</a>
							</div>
						</div>
						<input type="submit" value="登录"
							class="btn btn-large btn-primary btn-block btn-login" />

					</div>
				</div>
				<div class="clearfix no-acct-footer">
					<div class="pull-left">还没有帐户？</div>
					<div class="pull-right">
						<a class="btn btn-link issue-link"
							href=https://china-merchant.wish.com/signup>注册</a>

					</div>
				</div>
			</div>
		</form>
	</div>



	</div>

	<!-- global js data -->
	<script type="text/javascript">
window.locale_info = {};
window.locale_info['locale'] = "zh";
window.locale_info['locale_json'] = null;
window.pageParams = {"next_url":"\/","monitor_key":"page.login","env":"fe_prod"}
</script>
	<script type="text/javascript">
var lemmings_url="http:\/\/contestimg.wish.com\/api\/webimage";
</script>

	<!-- end global js data -->
	<script type="text/javascript" defer async
		src="https://static.china-merchant.wish.com/build-js-zh-china-cdn/page/login_page.js?v=6968179812bf">
</script>
	<!-- END BOTTOM LOAD JS -->
	<!-- begin user voice code -->

	<!-- end user voice code -->
</body>
</html>
