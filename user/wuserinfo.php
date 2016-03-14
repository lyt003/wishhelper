<?php
session_start ();
include dirname('__FILE__').'./mysql/dbhelper.php';
use mysql\dbhelper;
header ( "Content-Type: text/html;charset=utf-8" );

$username = $_SESSION ['username'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- saved from url=(0031)http://china-merchant.wish.com/ -->
<html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>更有效率的Wish商户实用工具</title>
<meta name="keywords" content="">
<link rel="stylesheet" type="text/css" href="../css/home_page.css">
<link href="../css/bootstrap-editable.css" rel="stylesheet">
<link href="../css/select2.css" rel="stylesheet">
</head>
<body>
<!-- HEADER -->
<div id="header" class="navbar navbar-fixed-top 
" style="left: 0px;">
<div class="container-fluid ">
<a class="brand" href="https://wishconsole.com/">
<span
				class="merchant-header-text"> 更有效率的Wish商户实用工具 </span>
</a>

<div class="pull-right">
<ul class="nav">
<li data-mid="5416857ef8abc87989774c1b" data-uid="5413fe984ad3ab745fee8b48">
<?php echo $username?>
</li>
<li><button><a href="./wlogin.php?type=exit">注销</a></button></li>

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
                      <div class="navbar-inner">
                        <div class="container">
                          <a href="./wusercenter.php" class="brand">
订单处理
</a>
<a href="./wuploadproduct.php" class="brand">
产品上传
</a>
<a href="./wuserinfo.php" class="brand">
个人信息
</a>
						  
                        </div>
                      </div>
                      <!-- /navbar-inner -->
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

<div class="dashboard-wrapper">
          <div class="left-sidebar">
            
            <div class="row-fluid">
              <div class="span12">
                <div class="widget no-margin">
                  <div class="widget-body">
                    <div class="container-fluid">
                      
                      <div class="row-fluid">
                        <div class="span3">
                          <div class="thumbnail">
                            <img alt="300x200" src="../image/profile.png">
                            <div class="caption">
                              <a href="#" data-type="text" data-pk="1" data-original-title="Edit your Nick Name" class="editable editable-click inputText" style="margin-bottom: 10px;">
                                Pintu
                              </a>
                              <p class="no-margin">
                                <a href="#" class="btn btn-info">
                                  Save
                                </a>
                                
                                <a href="#" class="btn">
                                  Cancel
                                </a>
                              </p>
                            </div>
                          </div>
                        </div>
                        <div class="span9">
                          <form class="form-horizontal">
                            <h5>
                              Login Information
                            </h5>
                            <hr>
                            <div class="control-group">
                              <label class="control-label">
                                User Name
                              </label>
                              <div class="controls">
                                <a href="#" id="userName" data-type="text" data-pk="1" data-original-title="Click here to edit your name" class="inputText editable editable-click">
                                  Srinu Baswa
                                </a>
                              </div>
                            </div>
                            <div class="control-group">
                              <label class="control-label">
                                Email ID
                              </label>
                              <div class="controls">
                                <a href="#" data-type="email" data-pk="1" data-original-title="Click here to edit your email" class="inputText editable editable-click">
                                  abcde@youmail.com
                                </a>
                              </div>
                            </div>
                            <div class="control-group">
                              <label class="control-label">
                                Password
                              </label>
                              <div class="controls">
                                <a href="#" id="password" data-type="password" data-pk="1" data-original-title="Click here to edit your password" class="inputText editable editable-click">
                                  ******
                                </a>
                              </div>
                            </div>
                            <div class="control-group">
                              <label class="control-label">
                                Confirm Password
                              </label>
                              <div class="controls">
                                <a href="#" id="confirmPassword" data-type="password" data-pk="1" data-original-title="Click here to confirm your password" class="inputText editable editable-click">
                                  ******
                                </a>
                              </div>
                            </div>
                            <br />
                            <h5>
                              Personal Information
                            </h5>
                            <hr>
                            <div class="control-group">
                              <label class="control-label">
                                First Name
                              </label>
                              <div class="controls">
                                <a href="#" id="firstName" data-type="text" data-pk="1" data-original-title="Click here to edit your first name" class="inputText editable editable-click">
                                  Srinu
                                </a>
                              </div>
                            </div>
                            <div class="control-group">
                              <label class="control-label">
                                Last Name
                              </label>
                              <div class="controls">
                                <a href="#" id="lastName" data-type="text" data-pk="1" data-original-title="Click here to edit your first name" class="inputText editable editable-click">
                                  Baswa
                                </a>
                              </div>
                            </div>
                            <div class="control-group">
                              <label class="control-label">
                                Location
                              </label>
                              <div class="controls">
                                <a href="#" id="location" data-type="text" data-pk="1" data-original-title="Click here to edit your first name" class="inputText editable editable-click">
                                  Banglore, India.
                                </a>
                              </div>
                            </div>
                            <div class="control-group">
                              <label class="control-label">
                                Website URL
                              </label>
                              <div class="controls">
                                <a href="#" id="url" data-type="url" data-pk="1" data-original-title="Click here to edit your first name" class="inputText editable editable-click">
                                  http:www.abcxyz.com
                                </a>
                              </div>
                            </div>
                            <div class="control-group">
                              <label class="control-label">
                                Tags
                              </label>
                              <div class="controls">
                                <a href="#" id="tags" data-type="select2" data-pk="1" data-original-title="Enter tags" class="editable editable-click">
                                  Html, CSS, Javascript
                                </a>
                              </div>
                            </div>
                            
                            <div class="control-group">
                              <label class="control-label">
                                About
                              </label>
                              <div class="controls">
                                <a data-original-title="Write about your self" data-placeholder="Your comments here..." data-pk="1" data-type="textarea" id="aboutMe" href="#" class="inputTextArea editable editable-click" style="margin-bottom: 10px;">
                                  About me :)
                                </a>
                                
                              </div>
                            </div>
                            <div class="form-actions">
                              <button type="submit" class="btn btn-info">
                                Save changes
                              </button>
                              <button type="button" class="btn">
                                Cancel
                              </button>
                            </div>
                            
                            
                            
                          </form>
                          
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
            </div>
            
          </div>
        </div>
<!-- FOOTER -->
	<div id="footer" class="navbar navbar-fixed-bottom" style="left: 0px;">
		<div class="navbar-inner">
			<div class="footer-container">
				<span><a href="https://wishconsole.com/">关于我们</a></span> <span><a>2016
						wishconsole版权所有 京ICP备16000367号</a>
						<!-- 51.la 网站统计 -->
						<script language="javascript" type="text/javascript" src="http://js.users.51.la/18799105.js"></script>
						<noscript><a href="http://www.51.la/?18799105" target="_blank"><img alt="&#x6211;&#x8981;&#x5566;&#x514D;&#x8D39;&#x7EDF;&#x8BA1;" src="http://img.users.51.la/18799105.asp" style="border:none" /></a></noscript>
				</span>
			</div>
		</div>
	</div>
	<!-- END FOOTER -->
</body>
</html>