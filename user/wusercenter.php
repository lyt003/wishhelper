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
			header("Location:./wlogin.php?errorMsg=登录失败");
			exit;
		}
	}
}


$result = $dbhelper->getUserToken ( $username );
$accounts = array ();
$i = 0;
while ( $rows = mysql_fetch_array ( $result ) ) {
	$accounts ['clientid' . $i] = $rows ['clientid'];
	$accounts ['clientsecret' . $i] = $rows ['clientsecret'];
	$accounts ['token' . $i] = $rows ['token'];
	$accounts ['refresh_token' . $i] = $rows ['refresh_token'];
	$accounts ['accountid' . $i] = $rows ['accountid'];
	$i ++;
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
<a href="http://wishconsole.com/" class="brand">
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

<div id="page-content" class="container-fluid  user">
<li>已绑定的wish账号:
<?php  for($count = 0; $count < $i; $count ++) {
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$accounts ['accountid' . $count];
}?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="wbindwish.php">绑定wish账号</a></li>
<ul align="center"><a href="../orders.php" style="font-size: 56px; color: #000000">处理订单</a></ul>

<!-- table 1: -->
          <div class="row-fluid">
              
              <div class="span12">
                <div class="widget">
                  <div class="widget-header">
                    <div class="title">
                      账号1 未处理订单
                    </div>
                    <span class="tools">
                      <a class="fs1" aria-hidden="true" data-icon="&#xe090;"></a>
                    </span>
                  </div>
                  <div class="widget-body">
                    <table class="table table-condensed table-striped table-bordered table-hover no-margin">
                      <thead>
                        <tr>
                          <th style="width:5%">
                            <input type="checkbox" class="no-margin" />
                          </th>
                          <th style="width:40%">
                            Name
                          </th>
                          <th style="width:20%" class="hidden-phone">
                            Product
                          </th>
                          <th style="width:10%" class="hidden-phone">
                            Status
                          </th>
                          <th style="width:15%" class="hidden-phone">
                            Date
                          </th>
                          <th style="width:10%" class="hidden-phone">
                            Actions
                          </th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>
                            <input type="checkbox" class="no-margin" />
                          </td>
                          <td>
                            <span class="name">
                              Mahendra Singh Dhoni
                            </span>
                          </td>
                          <td class="hidden-phone">
                            Baswa #567
                          </td>
                          <td class="hidden-phone">
                            <span class="label label label-info">
                              New
                            </span>
                          </td>
                          <td class="hidden-phone">
                            15 - 02 - 2013
                          </td>
                          <td class="hidden-phone">
                            
                            <div class="btn-group">
                              <button data-toggle="dropdown" class="btn btn-mini dropdown-toggle">
                                Action 
                                <span class="caret">
                                </span>
                              </button>
                              <ul class="dropdown-menu">
                                <li>
                                  <a href="#">
                                    Edit
                                  </a>
                                </li>
                                <li>
                                  <a href="#">
                                    Delete
                                  </a>
                                </li>
                              </ul>
                            </div>
                          </td>
                          
                        </tr>
                        <tr>
                          <td>
                            <input type="checkbox" class="no-margin" />
                          </td>
                          <td>
                            <span class="name">
                              Michel Clark
                            </span>
                          </td>
                          <td class="hidden-phone">
                            Baswa #224
                          </td>
                          <td class="hidden-phone">
                            <span class="label label label-success">
                              New
                            </span>
                          </td>
                          <td class="hidden-phone">
                            10 - 02 - 2013
                          </td>
                          <td class="hidden-phone">
                            
                            <div class="btn-group">
                              <button data-toggle="dropdown" class="btn btn-mini dropdown-toggle">
                                Action 
                                <span class="caret">
                                </span>
                              </button>
                              <ul class="dropdown-menu">
                                <li>
                                  <a href="#">
                                    Edit
                                  </a>
                                </li>
                                <li>
                                  <a href="#">
                                    Delete
                                  </a>
                                </li>
                              </ul>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td>
                            <input type="checkbox" class="no-margin" />
                          </td>
                          <td>
                            <span class="name">
                              Rahul Dravid
                            </span>
                          </td>
                          <td class="hidden-phone">
                            Baswa #342
                          </td>
                          <td class="hidden-phone">
                            <span class="label label label-important">
                              New
                            </span>
                          </td>
                          <td class="hidden-phone">
                            14 - 02 - 2013
                          </td>
                          <td class="hidden-phone">
                            
                            <div class="btn-group">
                              <button data-toggle="dropdown" class="btn btn-mini dropdown-toggle">
                                Action 
                                <span class="caret">
                                </span>
                              </button>
                              <ul class="dropdown-menu">
                                <li>
                                  <a href="#">
                                    Edit
                                  </a>
                                </li>
                                <li>
                                  <a href="#">
                                    Delete
                                  </a>
                                </li>
                              </ul>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td>
                            <input type="checkbox" class="no-margin" />
                          </td>
                          <td>
                            <span class="name">
                              Anthony Michell
                            </span>
                          </td>
                          <td class="hidden-phone">
                            Baswa #3021
                          </td>
                          <td class="hidden-phone">
                            <span class="label label label-info">
                              New
                            </span>
                          </td>
                          <td class="hidden-phone">
                            19 - 02 - 2013
                          </td>
                          <td class="hidden-phone">
                            
                            <div class="btn-group">
                              <button data-toggle="dropdown" class="btn btn-mini dropdown-toggle">
                                Action 
                                <span class="caret">
                                </span>
                              </button>
                              <ul class="dropdown-menu">
                                <li>
                                  <a href="#">
                                    Edit
                                  </a>
                                </li>
                                <li>
                                  <a href="#">
                                    Delete
                                  </a>
                                </li>
                              </ul>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td>
                            <input type="checkbox" class="no-margin" />
                          </td>
                          <td>
                            <span class="name">
                              Srinu Baswa
                            </span>
                          </td>
                          <td class="hidden-phone">
                            Baswa #771
                          </td>
                          <td class="hidden-phone">
                            <span class="label label label-success">
                              New
                            </span>
                          </td>
                          <td class="hidden-phone">
                            12 - 02 - 2013
                          </td>
                          <td class="hidden-phone">
                            
                            <div class="btn-group">
                              <button data-toggle="dropdown" class="btn btn-mini dropdown-toggle">
                                Action 
                                <span class="caret">
                                </span>
                              </button>
                              <ul class="dropdown-menu">
                                <li>
                                  <a href="#">
                                    Edit
                                  </a>
                                </li>
                                <li>
                                  <a href="#">
                                    Delete
                                  </a>
                                </li>
                              </ul>
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
              
            </div>
    
<!-- table 2 -->    
          <div class="row-fluid">
              
              <div class="span12">
                <div class="widget">
                  <div class="widget-header">
                    <div class="title">
                       账号2 未处理订单
                    </div>
                    <span class="tools">
                      <a class="fs1" aria-hidden="true" data-icon="&#xe090;"></a>
                    </span>
                  </div>
                  <div class="widget-body">
                    <table class="table table-condensed table-striped table-bordered table-hover no-margin">
                      <thead>
                        <tr>
                          <th style="width:5%">
                            <input type="checkbox" class="no-margin" />
                          </th>
                          <th style="width:40%">
                            Name
                          </th>
                          <th style="width:20%" class="hidden-phone">
                            Product
                          </th>
                          <th style="width:10%" class="hidden-phone">
                            Status
                          </th>
                          <th style="width:15%" class="hidden-phone">
                            Date
                          </th>
                          <th style="width:10%" class="hidden-phone">
                            Actions
                          </th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>
                            <input type="checkbox" class="no-margin" />
                          </td>
                          <td>
                            <span class="name">
                              Mahendra Singh Dhoni
                            </span>
                          </td>
                          <td class="hidden-phone">
                            Baswa #567
                          </td>
                          <td class="hidden-phone">
                            <span class="label label label-info">
                              New
                            </span>
                          </td>
                          <td class="hidden-phone">
                            15 - 02 - 2013
                          </td>
                          <td class="hidden-phone">
                            
                            <div class="btn-group">
                              <button data-toggle="dropdown" class="btn btn-mini dropdown-toggle">
                                Action 
                                <span class="caret">
                                </span>
                              </button>
                              <ul class="dropdown-menu">
                                <li>
                                  <a href="#">
                                    Edit
                                  </a>
                                </li>
                                <li>
                                  <a href="#">
                                    Delete
                                  </a>
                                </li>
                              </ul>
                            </div>
                          </td>
                          
                        </tr>
                        <tr>
                          <td>
                            <input type="checkbox" class="no-margin" />
                          </td>
                          <td>
                            <span class="name">
                              Michel Clark
                            </span>
                          </td>
                          <td class="hidden-phone">
                            Baswa #224
                          </td>
                          <td class="hidden-phone">
                            <span class="label label label-success">
                              New
                            </span>
                          </td>
                          <td class="hidden-phone">
                            10 - 02 - 2013
                          </td>
                          <td class="hidden-phone">
                            
                            <div class="btn-group">
                              <button data-toggle="dropdown" class="btn btn-mini dropdown-toggle">
                                Action 
                                <span class="caret">
                                </span>
                              </button>
                              <ul class="dropdown-menu">
                                <li>
                                  <a href="#">
                                    Edit
                                  </a>
                                </li>
                                <li>
                                  <a href="#">
                                    Delete
                                  </a>
                                </li>
                              </ul>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td>
                            <input type="checkbox" class="no-margin" />
                          </td>
                          <td>
                            <span class="name">
                              Rahul Dravid
                            </span>
                          </td>
                          <td class="hidden-phone">
                            Baswa #342
                          </td>
                          <td class="hidden-phone">
                            <span class="label label label-important">
                              New
                            </span>
                          </td>
                          <td class="hidden-phone">
                            14 - 02 - 2013
                          </td>
                          <td class="hidden-phone">
                            
                            <div class="btn-group">
                              <button data-toggle="dropdown" class="btn btn-mini dropdown-toggle">
                                Action 
                                <span class="caret">
                                </span>
                              </button>
                              <ul class="dropdown-menu">
                                <li>
                                  <a href="#">
                                    Edit
                                  </a>
                                </li>
                                <li>
                                  <a href="#">
                                    Delete
                                  </a>
                                </li>
                              </ul>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td>
                            <input type="checkbox" class="no-margin" />
                          </td>
                          <td>
                            <span class="name">
                              Anthony Michell
                            </span>
                          </td>
                          <td class="hidden-phone">
                            Baswa #3021
                          </td>
                          <td class="hidden-phone">
                            <span class="label label label-info">
                              New
                            </span>
                          </td>
                          <td class="hidden-phone">
                            19 - 02 - 2013
                          </td>
                          <td class="hidden-phone">
                            
                            <div class="btn-group">
                              <button data-toggle="dropdown" class="btn btn-mini dropdown-toggle">
                                Action 
                                <span class="caret">
                                </span>
                              </button>
                              <ul class="dropdown-menu">
                                <li>
                                  <a href="#">
                                    Edit
                                  </a>
                                </li>
                                <li>
                                  <a href="#">
                                    Delete
                                  </a>
                                </li>
                              </ul>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td>
                            <input type="checkbox" class="no-margin" />
                          </td>
                          <td>
                            <span class="name">
                              Srinu Baswa
                            </span>
                          </td>
                          <td class="hidden-phone">
                            Baswa #771
                          </td>
                          <td class="hidden-phone">
                            <span class="label label label-success">
                              New
                            </span>
                          </td>
                          <td class="hidden-phone">
                            12 - 02 - 2013
                          </td>
                          <td class="hidden-phone">
                            
                            <div class="btn-group">
                              <button data-toggle="dropdown" class="btn btn-mini dropdown-toggle">
                                Action 
                                <span class="caret">
                                </span>
                              </button>
                              <ul class="dropdown-menu">
                                <li>
                                  <a href="#">
                                    Edit
                                  </a>
                                </li>
                                <li>
                                  <a href="#">
                                    Delete
                                  </a>
                                </li>
                              </ul>
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
              
            </div>
            
      <!-- table 3 --> 
                      <div class="row-fluid">
              
              <div class="span12">
                <div class="widget">
                  <div class="widget-header">
                    <div class="title">
                       账号3 未处理订单
                    </div>
                    <span class="tools">
                      <a class="fs1" aria-hidden="true" data-icon="&#xe090;"></a>
                    </span>
                  </div>
                  <div class="widget-body">
                    <table class="table table-condensed table-striped table-bordered table-hover no-margin">
                      <thead>
                        <tr>
                          <th style="width:5%">
                            <input type="checkbox" class="no-margin" />
                          </th>
                          <th style="width:40%">
                            Name
                          </th>
                          <th style="width:20%" class="hidden-phone">
                            Product
                          </th>
                          <th style="width:10%" class="hidden-phone">
                            Status
                          </th>
                          <th style="width:15%" class="hidden-phone">
                            Date
                          </th>
                          <th style="width:10%" class="hidden-phone">
                            Actions
                          </th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>
                            <input type="checkbox" class="no-margin" />
                          </td>
                          <td>
                            <span class="name">
                              Mahendra Singh Dhoni
                            </span>
                          </td>
                          <td class="hidden-phone">
                            Baswa #567
                          </td>
                          <td class="hidden-phone">
                            <span class="label label label-info">
                              New
                            </span>
                          </td>
                          <td class="hidden-phone">
                            15 - 02 - 2013
                          </td>
                          <td class="hidden-phone">
                            
                            <div class="btn-group">
                              <button data-toggle="dropdown" class="btn btn-mini dropdown-toggle">
                                Action 
                                <span class="caret">
                                </span>
                              </button>
                              <ul class="dropdown-menu">
                                <li>
                                  <a href="#">
                                    Edit
                                  </a>
                                </li>
                                <li>
                                  <a href="#">
                                    Delete
                                  </a>
                                </li>
                              </ul>
                            </div>
                          </td>
                          
                        </tr>
                        <tr>
                          <td>
                            <input type="checkbox" class="no-margin" />
                          </td>
                          <td>
                            <span class="name">
                              Michel Clark
                            </span>
                          </td>
                          <td class="hidden-phone">
                            Baswa #224
                          </td>
                          <td class="hidden-phone">
                            <span class="label label label-success">
                              New
                            </span>
                          </td>
                          <td class="hidden-phone">
                            10 - 02 - 2013
                          </td>
                          <td class="hidden-phone">
                            
                            <div class="btn-group">
                              <button data-toggle="dropdown" class="btn btn-mini dropdown-toggle">
                                Action 
                                <span class="caret">
                                </span>
                              </button>
                              <ul class="dropdown-menu">
                                <li>
                                  <a href="#">
                                    Edit
                                  </a>
                                </li>
                                <li>
                                  <a href="#">
                                    Delete
                                  </a>
                                </li>
                              </ul>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td>
                            <input type="checkbox" class="no-margin" />
                          </td>
                          <td>
                            <span class="name">
                              Rahul Dravid
                            </span>
                          </td>
                          <td class="hidden-phone">
                            Baswa #342
                          </td>
                          <td class="hidden-phone">
                            <span class="label label label-important">
                              New
                            </span>
                          </td>
                          <td class="hidden-phone">
                            14 - 02 - 2013
                          </td>
                          <td class="hidden-phone">
                            
                            <div class="btn-group">
                              <button data-toggle="dropdown" class="btn btn-mini dropdown-toggle">
                                Action 
                                <span class="caret">
                                </span>
                              </button>
                              <ul class="dropdown-menu">
                                <li>
                                  <a href="#">
                                    Edit
                                  </a>
                                </li>
                                <li>
                                  <a href="#">
                                    Delete
                                  </a>
                                </li>
                              </ul>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td>
                            <input type="checkbox" class="no-margin" />
                          </td>
                          <td>
                            <span class="name">
                              Anthony Michell
                            </span>
                          </td>
                          <td class="hidden-phone">
                            Baswa #3021
                          </td>
                          <td class="hidden-phone">
                            <span class="label label label-info">
                              New
                            </span>
                          </td>
                          <td class="hidden-phone">
                            19 - 02 - 2013
                          </td>
                          <td class="hidden-phone">
                            
                            <div class="btn-group">
                              <button data-toggle="dropdown" class="btn btn-mini dropdown-toggle">
                                Action 
                                <span class="caret">
                                </span>
                              </button>
                              <ul class="dropdown-menu">
                                <li>
                                  <a href="#">
                                    Edit
                                  </a>
                                </li>
                                <li>
                                  <a href="#">
                                    Delete
                                  </a>
                                </li>
                              </ul>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td>
                            <input type="checkbox" class="no-margin" />
                          </td>
                          <td>
                            <span class="name">
                              Srinu Baswa
                            </span>
                          </td>
                          <td class="hidden-phone">
                            Baswa #771
                          </td>
                          <td class="hidden-phone">
                            <span class="label label label-success">
                              New
                            </span>
                          </td>
                          <td class="hidden-phone">
                            12 - 02 - 2013
                          </td>
                          <td class="hidden-phone">
                            
                            <div class="btn-group">
                              <button data-toggle="dropdown" class="btn btn-mini dropdown-toggle">
                                Action 
                                <span class="caret">
                                </span>
                              </button>
                              <ul class="dropdown-menu">
                                <li>
                                  <a href="#">
                                    Edit
                                  </a>
                                </li>
                                <li>
                                  <a href="#">
                                    Delete
                                  </a>
                                </li>
                              </ul>
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
              
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