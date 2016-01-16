<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>更有效率的Wish商户实用工具</title>
			<meta name="keywords" content="">
				<link rel="stylesheet" type="text/css"
					href="../css/new_signup_page.css">

</head>
<script type="text/javascript">

	function register(){
		var email = document.getElementById("email").value;
		var username = document.getElementById("username").value;
		var password = document.getElementById("password").value;
		var confirm_password = document.getElementById("confirm_password").value;
		if(email == null || email == ''){
			alert("请输入邮箱地址");
			return;
		}
		var emailpattern = /^(\w-*\.*)+@(\w-?)+(\.\w{2,})+$/;
		if(!emailpattern.test(email)){
	    	alert("邮箱格式不正确");
	    	return;
	    }
		if(username == null || username == ''){
			alert("请输入用户昵称");
			return;
		}
		var namepattern = /^[a-zA-z]\w{3,15}$/;
		if(!namepattern.test(username)){
		    alert("用户名格式不正确，用户名由字母、数字、下划线组成，字母开头，4-16位");
			return;
		}    
		if(password == null || password == ''){
			alert("请输入密码");
			return;
		}
		if(confirm_password == null || confirm_password == ''){
			alert("请再次输入密码");
			return;
		}

		if(password != confirm_password){
			alert("两次输入的密码不一致，请重新输入");
			return;
		} 
		var registerform = document.getElementById("registerform");
		registerform.submit();
	}
</script>
<body>
	<!-- HEADER -->
	<div id="header" class="navbar navbar-fixed-top 

">
		<div class="container-fluid fixed-width">
			<a class="brand" href="http://wishconsole.com/"> <span
				class="merchant-header-text"> 更有效率的Wish商户实用工具 </span>
			</a>

		</div>
	</div>
	<!-- END HEADER -->
	<!-- SUB HEADER NAV-->
	<!-- splash page subheader-->

	<div id="page-content" class="container-fluid fixed-width ">

		<div id="signup-page-content">
			<div class="signup-page-container">
				<div class="signup-page-title">创建账号</div>
				<div class="signup-page-content">
					<form class="form form-horizontal" id="registerform" method="post"
						action="wusercenter.php?<?php echo "type=register"?>">
						<div class="control-group">
							<label class="control-label" for="email_address"> 邮箱地址</label>
							<div class="controls input-append">
								<input type="text" id="email" name="email" class="input-block-level"
									placeholder="示例：hello@example.com"> <span class="add-on"><i
										class="icon-pencil"></i></span>
							
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="username"> 用户昵称</label>
							<div class="controls input-append">
								<input type="text" id="username" name="username" class="input-block-level"
									placeholder="用户昵称"> <span class="add-on"><i class="icon-pencil"></i></span>
							
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="password"> 密码</label>
							<div class="controls input-append">
								<input type="password" id="password" name="password" class="input-block-level"
									placeholder="输入密码"> <span class="add-on"><i class="icon-pencil"></i></span>
							
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="confirm_password"> 确认密码</label>
							<div class="controls input-append">
								<input type="password" id="confirm_password" name="confirm_password"
									class="input-block-level" placeholder="请再次输入您的密码"> <span
									class="add-on"><i class="icon-pencil"></i></span>
							
							</div>
						</div>
						<div id="create-store-container">
							<input type="button" id="signup-button"
								class="input-block-level flat-signup-btn" onclick="register()"
								value="创建">
						
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="signup-page-footer">

			已经有账号了？ <a href="wlogin.php">点击这里登入</a>

		</div>



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