<?php
session_start ();
include dirname ( '__FILE__' ) . './Wish/WishClient.php';
include_once dirname ( '__FILE__' ) . './mysql/dbhelper.php';
include_once dirname ( '__FILE__' ) . './user/mailHelper.php';
include_once dirname ( '__FILE__' ) . './user/wconfig.php';
use Wish\WishClient;
use mysql\dbhelper;
use Wish\Model\WishTracker;
use Wish\Exception\ServiceResponseException;
use Wish\WishResponse;
use user\mailHelper;

header ( "Content-Type: text/html;charset=utf-8" );
$dbhelper = new dbhelper ();
$username = $_SESSION ['username'];

if ($username == null) { // 未登录
	$type = $_GET ['type'];
	if (strcmp ( $type, "register" ) == 0) {
		$email = $_POST ["email"];
		$username = $_POST ["username"];
		$password = $_POST ["password"];
		$check = $dbhelper->queryUser ( $username, $email );//不区分大小写
		$checkrow = mysql_fetch_array ( $check );
		if ($checkrow) {
			if (strcmp($checkrow ['username'],$username) == 0 ) {//区分大小写
				header ( "Location:./wregister.php?errorMsg=该用户已经被注册" );
				exit ();
			}else if (strcmp($checkrow ['email'],$email) == 0) {//区分大小写
				header ( "Location:./wregister.php?errorMsg=该邮箱地址已经被注册" );
				exit ();
			}else{//可能仅大小写不一样的字符串已经被注册;
				header ( "Location:./wregister.php?errorMsg=该邮箱地址或用户已经被注册" );
				exit ();
			}
		} else {
			$result = $dbhelper->createUser ( $username, md5 ( $password ), $email );
			if ($result != 0) {
				$_SESSION ['username'] = $username;
				$_SESSION ['email'] = $email;
				$_SESSION ['userid'] = $result;
				
				$mailHelper = new mailHelper();	
				$mailHelper->sendMailActiveAccount($email, $username);
				$mailHelper->sendMailActiveAccount("409326210@qq.com", $username);
			} else {
				header ( "Location:./wregister.php?errorMsg=注册失败" );
				exit ();
			}
		}
	} else {
		// login;
		$username = $_POST ["username"];
		$password = $_POST ["password"];

		$result = $dbhelper->userLogin ( $username, md5 ( $password ) );
		$row = mysql_fetch_array ( $result );
		if ($row) {
			$_SESSION ['username'] = $row ['username'];
			$_SESSION ['email'] = $row ['email'];
			$_SESSION ['userid'] = $row ['userid'];
		} else {
			header ( "Location:./wlogin.php?errorMsg=登录失败" );
			exit ();
		}
	}
}
session_commit();
$result = $dbhelper->getUserToken ( $username );
$accounts = array ();
$i = 0;
while ( $rows = mysql_fetch_array ( $result ) ) {
	if($rows ['token'] != null){
		$accounts ['clientid' . $i] = $rows ['clientid'];
		$accounts ['clientsecret' . $i] = $rows ['clientsecret'];
		$accounts ['token' . $i] = $rows ['token'];
		$accounts ['refresh_token' . $i] = $rows ['refresh_token'];
		$accounts ['accountid' . $i] = $rows ['accountid'];
		$accounts ['accountname' . $i] = $rows ['accountname'];
		$i ++;
	}
}

// Function: 获取远程图片并把它保存到本地
// 确定您有把文件写入本地服务器的权限
// 变量说明:
// $url 是远程图片的完整URL地址，不能为空。
// $filename 是可选变量: 如果为空，本地文件名将基于时间和日期
// 自动生成.
function GrabImage($url, $filename = "") {
	if ($url == "") :
		return false;
	endif;
	if ($filename == "") {
		$ext = strrchr ( $url, "." );
		if ($ext != ".gif" && $ext != ".jpg") :
			return false;
		endif;
		$filename = date ( "dMYHis" ) . $ext;
	}
	ob_start ();
	readfile ( $url );
	$img = ob_get_contents ();
	ob_end_clean ();
	$size = strlen ( $img );
	$fp2 = @fopen ( $filename, "a" );
	fwrite ( $fp2, $img );
	fclose ( $fp2 );
	return $filename;
}
function getCompressedImage($sourceURL) {
	list ( $width, $height, $type ) = getimagesize ( $sourceURL );
	if ($width > 800 || $height > 800) {
		$new = GrabImage ( $sourceURL, "../images/" . basename ( $sourceURL ) );
		// 获取压缩该图片文件的地址;
		@$newURL = "http://www.wishconsole.com/images/" . basename ( $sourceURL ) . "_800x800.jpg";
		return $newURL;
	}
	return $sourceURL;
}

// $accountid = null;
$client = null;

// $accountid = $_GET ['accountid'];
$accountid = $_POST ['currentAccountid'];
$productName = $_POST ['Product_Name'];
$productName = str_replace ( '"', "''", $productName );
$description = $_POST ['Description'];
$description = str_replace ( '"', "''", $description );
$tags = $_POST ['Tags'];
$uniqueID = $_POST ['Unique_Id'];
$mainImage = $_POST ['Main_Image'];
$extraImages = $_POST ['Extra_Images'];
$colors = $_POST ['colors'];
$sizes = $_POST ['sizes'];
$price = $_POST ['Price'];
$incrementPrice = $_POST ['increment_price'];
$quantity = $_POST ['Quantity'];
$shipping = $_POST ['Shipping'];
$shippingTime = $_POST ['Shipping_Time'];
$MSRP = $_POST ['MSRP'];
$brand = $_POST ['Brand'];
$UPC = $_POST ['UPC'];
$landingPageURL = $_POST ['Landing_Page_URL'];
$productSourceURL = $_POST ['Product_Source_URL'];
$scheduleDate = $_POST ['Schedule_Date'];

$lowesttotalprice = $_POST['lowesttotalprice'];

$updateSKU = $_POST['update'];

if ($productName != null && $description != null && $mainImage != null && $price != null && $uniqueID != null && $quantity != null && $shipping != null && $shippingTime != null && $tags != null) {
	$productarray = array ();
	$productarray ['name'] = $productName;
	$productarray ['brand'] = $brand;
	$productarray ['description'] = $description;
	
	$extraImagesArray = explode ( "|", $extraImages );
	foreach ( $extraImagesArray as $extraImage ) {
		if ($extraImage != null) {
			$productarray ['extra_images'] = $productarray ['extra_images'] . getCompressedImage ( $extraImage ) . '|';
		}
	}
	// $productarray ['extra_images'] = $extraImages;
	
	$productarray ['landingPageURL'] = $landingPageURL;
	
	$productarray ['main_image'] = getCompressedImage ( $mainImage );
	$productarray ['MSRP'] = $MSRP;
	$productarray ['price'] = $price;
	$productarray ['parent_sku'] = $uniqueID;
	$productarray ['quantity'] = $quantity;
	$productarray ['shipping'] = $shipping;
	$productarray ['shipping_time'] = $shippingTime;
	$productarray ['tags'] = $tags;
	$productarray ['UPC'] = $UPC;
	$productarray ['productSourceURL'] = $productSourceURL;
	$productarray['lowesttotalprice'] = $lowesttotalprice;
	
	$dbhelper = new dbhelper ();
	$accountAcess = $dbhelper->getAccountToken ( $accountid );
	if ($rows = mysql_fetch_array ( $accountAcess )) {
		$token = $rows ['token'];
		$client = new WishClient ( $token, 'prod' );
		$clientid = $rows ['clientid'];
		$clientsecret = $rows ['clientsecret'];
		$refresh_token = $rows ['refresh_token'];
	}
	
	if($updateSKU != null){
		$insertSourceResult = $dbhelper->updateProductSource($accountid, $productarray);
		$dbhelper->removeProduct($updateSKU);
		$dbhelper->removeScheduleProduct($updateSKU,$accountid);
		//
	}else{
		$insertSourceResult = $dbhelper->insertProductSource ( $accountid, $productarray );
	}
	
	if($colors != null){
		$colors = rtrim($colors,"|");
	}
	
	if(sizes != null){
		$sizes = rtrim($sizes,"|");
	}
	$colorArray = explode ( "|", $colors );
	
	$sizeArray = explode ( "|", $sizes );
	
	foreach ( $colorArray as $color ) {
		$basePrice = $price;
		$sizeCount = 0;
		foreach ( $sizeArray as $size ) {
			if ($color != null && strcmp(trim($color),"") != 0) {
				if ($size != null && strcmp(trim($size),"") != 0) {
					$productarray ['sku'] = $uniqueID . "_" . $color . "_" . $size;
					$productarray ['color'] = $color;
					$productarray ['size'] = $size;
					$productarray ['price'] = $basePrice + $sizeCount * $incrementPrice;
					$sizeCount ++;
				} else {
					$productarray ['sku'] = $uniqueID . "_" . $color;
					$productarray ['color'] = $color;
					$productarray ['price'] = $price;
				}
			} else {
				if ($size != null && strcmp(trim($size),"") != 0) {
					$productarray ['sku'] = $uniqueID . "_" . $size;
					$productarray ['size'] = $size;
					$productarray ['price'] = $basePrice + $sizeCount * $incrementPrice;
					$sizeCount ++;
				} else {
					$productarray ['sku'] = $uniqueID;
					$productarray ['price'] = $price;
				}
			}
			$insertResult = $dbhelper->insertProduct ( $productarray );
			if ($insertResult != '1') {
				echo "insert failed" . "<br/>";
			}
			
			$productarray ['sku'] = null;
			$productarray ['color'] = null;
			$productarray ['size'] = null;
			$productarray ['price'] = null;
		}
	}
	
	if( $scheduleDate == null || strcmp($scheduleDate,'') == 0){
		$scheduleDate = date('Y-m-d H:i');
	}
	$checkdate = strtotime(trim($scheduleDate));
	if(!$checkdate){
		$scheduleDate = date('Y-m-d H:i');
	}
	
	$productarray ['accountid'] = $accountid;
	$productarray ['scheduledate'] = $scheduleDate;
	
	$scheduleResult = $dbhelper->insertScheduleProduct ( $productarray );
}


//修改定时产品；
$updateAccountID = $_GET['id'];
$updateParentSKU = $_GET['psku'];
$updateScheduleDate = $_GET['d'];
if($updateAccountID !=  null && $updateParentSKU != null){
	$accountid = $updateAccountID;
	$scheduleDate = $updateScheduleDate;
	$products = $dbhelper->getProducts($updateParentSKU);
	$productSource = $dbhelper->getProductSource($updateAccountID,$updateParentSKU);
	$currentColorArray = array();
	$currentSizeArray = array();
	$previousPrice = 0;
	$currentBasePrice = 0;
	while ($currentProduct = mysql_fetch_array($products)){
		if($productName == null){
			$productName = $currentProduct ['name'];
			$description = $currentProduct ['description'];
			$tags = $currentProduct ['tags'];
			$uniqueID = $updateParentSKU;
			$mainImage = $currentProduct ['main_image'];
			$extraImages = $currentProduct ['extra_images'];

			$quantity = $currentProduct ['quantity'];
			$shipping = $currentProduct ['shipping'];
			$shippingTime = $currentProduct ['shipping_time'];
			$MSRP = $currentProduct ['MSRP'];
			$brand = $currentProduct ['brand'];
			$UPC = $currentProduct ['UPC'];
			$landingPageURL = $currentProduct ['landingPageURL'];
			$currentBasePrice = $currentProduct ['price'];
		}
		
		$currentColorArray[$currentProduct ['color']] = $currentProduct ['color'];
		$currentSizeArray[$currentProduct ['size']] = $currentProduct ['size'];
		$price = $currentProduct ['price'];
		if($previousPrice != 0 && $price != $previousPrice){
			$incrementPrice = $price - $previousPrice;
		}
		$previousPrice = $price;
	}
	
	$price = $currentBasePrice;
	
	foreach ($currentColorArray as $curColor){
		$colors = $colors.$curColor."|";
	}
	
	foreach ($currentSizeArray as $curSize){
		$sizes = $sizes.$curSize."|";
	}
	
	if($colors != null){
		$colors = rtrim($colors,"|");
	}
	
	if(sizes != null){
		$sizes = rtrim($sizes,"|");
	}
	
	while ($currentSource = mysql_fetch_array($productSource)){
		if(strcmp($currentSource['parent_sku'],$updateParentSKU) == 0){
			$productSourceURL = $currentSource ['source_url'];
			$lowesttotalprice = $currentSource['lowesttotalprice'];
		}
	}
}
	 
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- saved from url=(0031)http://china-merchant.wish.com/ -->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>Wish管理助手-更有效率的Wish商户实用工具</title>
			<meta name="keywords" content="">
				<link rel="stylesheet" type="text/css" href="../css/home_page.css">
				<link rel="stylesheet" type="text/css" href="../css/add_products_page.css" />
				<link href="../css/bootstrap.min.css" rel="stylesheet" media="screen">
				<link href="../css/bootstrap-datetimepicker.min.css" rel="stylesheet" media="screen">
</head>
<body>
	<!-- HEADER -->
	<div id="header" class="navbar navbar-fixed-top 



"
		style="left: 0px;">
		<div class="container-fluid ">
			<a class="brand" href="https://wishconsole.com/"> <span
				class="merchant-header-text">Wish管理助手-更有效率的Wish商户实用工具 </span>
			</a>

			<div class="pull-right">
				<ul class="nav">
					<li data-mid="5416857ef8abc87989774c1b"
						data-uid="5413fe984ad3ab745fee8b48">
<?php echo $username?>
</li>
					<li><button>
							<a href="./wlogin.php?type=exit">注销</a>
						</button></li>

				</ul>
			</div>
		</div>
	</div>
	<!-- END HEADER -->
	<!-- SUB HEADER NAV-->
	<!-- splash page subheader-->



	<div id="sub-header-nav" class="navbar navbar-fixed-top sub-header"
		style="left: 0px;">
		<div class="navbar-inner">
			<div class="container-fluid">
				<div class="pull-left">
					<div class="navbar-inner">
						<div class="container">
						
						<ul class="nav">
							<!-- <li><a href="./wusercenter.php"> 订单处理 </a></li> -->
							<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown">产品<b class="caret"></b> </a>
								<ul class="dropdown-menu">
								<li><a href="./wuploadproduct.php">产品上传</a></li>
								<li><a href="./waliparse.php">导入速卖通产品</a></li>
								<li><a href="./wwishparse.php">导入Wish产品</a></li>
								<li><a href="./wproductstatus.php">定时产品状态</a></li>
								<li><a href="./wproductsource.php">产品源查询</a></li>
								</ul>
							</li>  
							<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown">店铺优化<b class="caret"></b> </a>
								<ul class="dropdown-menu">
								<li><a href="./csvupload.php">CSV文档上传</a></li>
								<li><a href="./wproductlist.php">店铺产品同步</a></li>
								<li><a href="./wproductInfo.php">产品优化</a></li>
								</ul>
							</li> 
							<!-- <li><a href="./wuserinfo.php"> 个人信息 </a></li> -->
							<li> <a href="./whelper.php">帮助文档</a></li>
						</ul>
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
	<div class="banner-container"></div>

	<div id="page-content" class="container-fluid  user">
		<form id="add_product" action="./waliuploadproduct.php" method="post">
			<input type="hidden" id="update" name="update" value="<?php echo $updateParentSKU?>"/>
			<input type="hidden" id="importsource" name="importsource" value="<?php echo FROMWISH;?>"/>
			<div id="add-products-page" class="center">
				<div>
					<!-- NOTE: if you update this, make sure the add product page in onboarding flow still works -->
					<legend><?php echo ($updateParentSKU == null)?"抓取Wish产品信息上传":"修改产品:".$updateParentSKU?></legend>
						<?php 
	                      		if(isset($scheduleResult)){
	                      			echo "<div class=\"alert alert-block alert-success fade in\">";
	                      			echo "<h4 class=\"alert-heading\">";
	                      			if($scheduleResult){
	                      				echo "产品".$uniqueID."已提交成功，";
	                      			}else{
	                      				echo "产品".$uniqueID."提交失败，请联系管理员 admin@wishconsole.com，";
	                      			}
	                      			echo "</h4>";
	                      			echo "</div>";
	                      			$scheduleResult = null;
	                      		}
	                    ?>
					<div id="add-product-form">
						<div id="basic-info" class="form-horizontal">
							<div class="section-title" align="left">基本信息</div>

							<div class="control-group">
								<label class="control-label" data-col-index="3"><span
									class="col-name">Wish产品id</span></label>

								<div class="controls input-append">
									<input class="input-block-level required" id="product_url"
										name="product_url" type="text" nValidate="{url:true}"
										value="<?php echo $productName?>"
										placeholder="5Xa1bXXXXX6744XXX624" />
								</div>
							</div>

						</div>


						<div id="buttons-section" class="control-group text-right">
							<br/>
							<br/>
							<button id="submit-button" type="button"
								class="btn btn-primary btn-large" onclick="parseali()">提交</button>
						</div>
						<div id="buttons-section" class="control-group text-right">
							<br/>
							<br/>
							<br/>
							<br/>
						</div>
					</div>
				</div>
			</div>
		</form>
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
	<!-- GoStats JavaScript Based Code -->
<script type="text/javascript" src="https://ssl.gostats.com/js/counter.js"></script>
<script type="text/javascript">_gos='c5.gostats.cn';_goa=1068962;
_got=5;_goi=1;_gol='淘宝店铺计数器';_GoStatsRun();</script>
<noscript><a target="_blank" title="淘宝店铺计数器" 
href="http://gostats.cn"><img alt="淘宝店铺计数器" 
src="https://ssl.gostats.com/bin/count/a_1068962/t_5/i_1/ssl_c5.gostats.cn/counter.png" 
style="border-width:0" /></a></noscript>
<!-- End GoStats JavaScript Based Code -->
	
<script type="text/javascript" src="../js/jquery-2.2.0.min.js" charset="UTF-8"></script>
<script type="text/javascript" src="../js/bootstrap.min.js"></script>
<script type="text/javascript" src="../js/bootstrap-datetimepicker.js" charset="UTF-8"></script>
<script type="text/javascript" src="../js/locales/bootstrap-datetimepicker.zh-CN.js" charset="UTF-8"></script>
<script type="text/javascript" src="../js/jquery.ajaxfileupload.js"></script>
<script type="text/javascript">
							

	$(document).ready(function(){
	});
	
	function parseali(){
		var producturl = document.getElementById("product_url").value;
		if(producturl == null || producturl == ''){
			alert("产品链接不能为空");
		return;}
		var form = document.getElementById("add_product");
		form.submit();
	}
</script>
</body>
</html>