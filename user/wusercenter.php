<?php
session_start ();
include dirname('__FILE__').'./Wish/WishClient.php';
include dirname('__FILE__').'./mysql/dbhelper.php';
use Wish\WishClient;
use mysql\dbhelper;
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
			$_SESSION ['email'] = $username;
			$_SESSION['userid'] = $row['userid'];
		}else{
			header("Location:./wlogin.php?errorMsg=登录失败");
			exit;
		}
	}
}

$add = $_GET['add'];
$postlabels = array();
$lc = 0;
if(strcmp($add,"1") == 0){
	$x = 3;
	$y = 5;
	for($z =0;$z<$x;$z ++){
		for($lz=0;$lz<$y;$lz ++){
			$postlabels[$lc] = $_POST['label_'.$z.$lz];
			echo "label:".$z.$lz.$postlabels[$lc]."</br>";
			$labelCNEN = explode ( "|", $postlabels[$lc]);
			print_r("label:".$labelCNEN);
			echo "insert lable id: ".$dbhelper->insertLabel($labelCNEN[0], $labelCNEN[1]);
			$lc++;
		}
	}
	
	print_r($postlabels);	
}

$labels = array();
$labelResult = $dbhelper->getUserLabels($_SESSION['userid']);
while ($label = mysql_fetch_array ( $labelResult )) {
	$labels[$label['id']] = $label['cn_name']."|".$label['en_name'];
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
	
	$client = new WishClient ( $rows ['token'], 'prod' );
	$unfulfilled_orders = $client->getAllUnfulfilledOrdersSince ( '2010-01-20' );
	$accounts ['order' . $i] = $unfulfilled_orders;
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
<link href="../css/bootstrap.min.css" rel="stylesheet">  
<script src="../js/jquery-2.2.0.min.js"></script>  
<script src="../js/bootstrap.min.js"></script>  
</head>
<script type="text/javascript">
	function processorders(){
		alert("processorders");	
		var a=$('input[name^="label"]').map(function(){return {value:this.value,name:this.name}}).get();
		//for(var i=0;i<a.length;i++)alert(a[i].name+'='+a[i].value);
		var form = document.getElementById("processorders");
		form.submit();
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
<div id="page-content" class="dashboard-wrapper">
<form class="form-horizontal" id="processorders" action="./wusercenter.php?add=1" method="post">
<li>已绑定的wish账号:
<?php  for($count = 0; $count < $i; $count ++) {
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$accounts ['accountid' . $count];
}?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="wbindwish.php">绑定wish账号</a></li>
<ul align="center"><button class="btn btn-info" type="button" onclick="processorders()">处理订单</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button class="btn btn-info" type="button" onclick="processorders()">下载标签</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button class="btn btn-info" type="button" onclick="processorders()">上传单号</button></ul>

<?php
for($count1 = 0; $count1 < $i; $count1 ++) {
	$orders = $accounts ['order' . $count1];
	echo "<div class=\"row-fluid\"><div class=\"span12\"><div class=\"widget\"><div class=\"widget-header\"><div class=\"title\">账号".$accounts ['accountid' . $count1].":&nbsp;&nbsp;".count ( $orders )."个未处理订单";
	echo "</div><span class=\"tools\"><a class=\"fs1\" aria-hidden=\"true\" data-icon=\"&#xe090;\"></a></span></div>";
	echo "<div class=\"widget-body\"><table class=\"table table-condensed table-striped table-bordered table-hover no-margin\"><thead><tr><th style=\"width:5%\"><input type=\"checkbox\" class=\"no-margin\" /></th>";
	echo "<th style=\"width:10%\">日期</th><th style=\"width:10%\" class=\"hidden-phone\">履行的天数</th><th style=\"width:25%\" class=\"hidden-phone\">产品 (SKU)参数|数量</th>";
	echo "<th style=\"width:20%\" class=\"hidden-phone\">总成本(成本+运费)($)</th><th style=\"width:20%\" class=\"hidden-phone\">客户名称|国家</th><th style=\"width:10%\" class=\"hidden-phone\">中英文品名</th></tr></thead>";
	echo "<tbody>";
	$orderCount = 0;
	foreach ( $orders as $cur_order ) {
		$shippingdetail = $cur_order->ShippingDetail;
		if($orderCount % 2 == 0){
			echo "<tr>";
		}else{
			echo "<tr class=\"gradeA success\">";
		}
		echo "<td style=\"width:5%;vertical-align:middle;\"><input type=\"checkbox\" class=\"no-margin\" /></td><td style=\"width:10%;vertical-align:middle;\">".substr($cur_order->order_time,0,10)."</td>";
		echo "<td style=\"width:10%;vertical-align:middle;\" class=\"hidden-phone\">".$cur_order->days_to_fulfill."天</td>";
		echo "<td style=\"width:25%;vertical-align:middle;\" class=\"hidden-phone\"><ul><li><img width=50 height=50 style=\"vertical-align:middle;\" src=\"".$cur_order->product_image_url."\">".$cur_order->sku.":(".$cur_order->color." - ".$cur_order->size." * ".$cur_order->quantity.")</li><ul></td>";
		echo "<td style=\"width:20%;vertical-align:middle;\" class=\"hidden-phone\">".$cur_order->cost." + ".$cur_order->shipping_cost."=".$cur_order->order_total."</td>";
		echo "<td style=\"width:20%;vertical-align:middle;\" class=\"hidden-phone\">".$shippingdetail->name."&nbsp;|&nbsp;".$shippingdetail->country."</td>";
		echo "<td style=\"width:10%;vertical-align:middle;\" class=\"hidden-phone\"><div class=\"input-group\"><input type=\"text\" name=\"label_".$count1.$orderCount."\" placeholder=\"中文|英文\">";
      	echo "<div class=\"input-group-btn\"><button type=\"button\" class=\"btn btn-default dropdown-toggle\" data-toggle=\"dropdown\">选择 <span class=\"caret\"></span></button>";
        echo "<ul class=\"dropdown-menu dropdown-menu-right\" role=\"menu\">";
        echo "<li>按钮式下拉菜单</li><li><a href=\"#\">输入框组带有下拉菜单的按钮</a></li><li>输入框组带有下拉菜单的按钮</li></ul></div></div></td><tr>";
		$orderCount ++;
	}
	echo "</tbody></table></div></div></div></div>";
	
}?>
</form>
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