<?php
session_start ();
include dirname ( '__FILE__' ) . './Wish/WishClient.php';
include dirname ( '__FILE__' ) . './mysql/dbhelper.php';
use Wish\WishClient;
use mysql\dbhelper;
use Wish\Model\WishTracker;
use Wish\Exception\ServiceResponseException;
use Wish\WishResponse;

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
	
	$dbhelper = new dbhelper ();
	$accountAcess = $dbhelper->getAccountToken ( $accountid );
	if ($rows = mysql_fetch_array ( $accountAcess )) {
		$token = $rows ['token'];
		$client = new WishClient ( $token, 'prod' );
		$clientid = $rows ['clientid'];
		$clientsecret = $rows ['clientsecret'];
		$refresh_token = $rows ['refresh_token'];
	}
	
	$insertSourceResult = $dbhelper->insertProductSource ( $accountid, $productarray );
	
	$colorArray = explode ( "|", $colors );
	
	$sizeArray = explode ( "|", $sizes );
	
	foreach ( $colorArray as $color ) {
		$basePrice = $price;
		$sizeCount = 0;
		foreach ( $sizeArray as $size ) {
			if ($color != null) {
				if ($size != null) {
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
				if ($size != null) {
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

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- saved from url=(0031)http://china-merchant.wish.com/ -->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>更有效率的Wish商户实用工具</title>
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
				class="merchant-header-text"> 更有效率的Wish商户实用工具 </span>
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
								<li><a href="./wproductstatus.php">定时产品状态</a></li>
								<li><a href="./wproductsource.php">产品源查询</a></li>
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
		<form id="add_product" action="./wuploadproduct.php" method="post">
			<div id="add-products-page" class="center">
				<div>
					<!-- NOTE: if you update this, make sure the add product page in onboarding flow still works -->
					<legend>添加产品</legend>
						<?php 
	                      		if(isset($scheduleResult)){
	                      			echo "<div class=\"alert alert-block alert-success fade in\">";
	                      			echo "<h4 class=\"alert-heading\">";
	                      			if($scheduleResult){
	                      				echo "产品".$uniqueID."已提交成功，";
	                      			}else{
	                      				echo "产品".$uniqueID."提交失败，请联系，";
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
									class="col-name">请选择wish账号</span></label>

								<div class="controls input-append">
									<label>
							<?php
							if ($i>0){
								for($count = 0; $count < $i; $count ++) {
									if($count != 0 && $count%3 == 0)
										echo "<br/>";
									echo "<input type=\"radio\" name=\"currentAccountid\" value=\"" . $accounts ['accountid' . $count] . "\"" . ($accountid == null ? ($count == 0 ? "checked" : "") : ((strcmp ( $accounts ['accountid' . $count], $accountid ) == 0) ? "checked" : "")) . ">";
									echo "&nbsp;&nbsp;" . $accounts ['accountname'.$count];
									echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
								}	
							}else{
								echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;您暂时没有绑定任何wish账号，请先&nbsp;&nbsp;&nbsp;&nbsp;";
							}
							 echo "<a href=\"./wbindwish.php\">绑定wish账号</a>";
							
							?></label>
								</div>
							</div>

							<div class="control-group">
								<label class="control-label" data-col-index="3"><span
									class="col-name">产品名称</span></label>

								<div class="controls input-append">
									<input class="input-block-level required" id="product_name"
										name="Product_Name" type="text"
										value="<?php echo $productName?>"
										placeholder="可接受：Men&#39;s Dress Casual Shirt Navy" />
								</div>
							</div>

							<div class="control-group">
								<label class="control-label" data-col-index="8"><span
									class="col-name">描述</span></label>

								<div class="controls input-append">
									<textarea rows="5" class = "form-control"
										name="Description" id="description" type="text"
										placeholder="可接受：This dress shirt is 100% cotton and fits true to size."><?php echo $description?>
								</textarea>
								</div>
							</div>

							<div class="control-group">
								<label class="control-label" data-col-index="7"><span
									class="col-name">Tags</span></label>
								<span id="tag_left_counts" style="color:red">10</span>
								<div class="controls input-append">
									<textarea rows="3" class="form-control"
										type="text" id="tags" name="Tags"
										placeholder="可接受：Shirt, Men&#39;s Fashion, Navy, Blue, Casual, Apparel"><?php echo $tags?></textarea>
								</div>
								
							</div>

							<div class="control-group" style="display: block;">
								<label class="control-label" data-col-index="1"><span
									class="col-name">父SKU</span></label>

								<div class="controls input-append">
									<input class="input-block-level required" name="Unique_Id"
										value="<?php echo $uniqueID?>" id="unique_id" type="text"
										value="" placeholder="可接受：HSC0424PP" />
								</div>
							</div>

							<div class="control-group" style="display: block;">
								<label class="control-label" data-col-index="1"><span
									class="col-name">主图片</span></label>

								<div class="controls input-append">
									<input class="input-block-level required" name="Main_Image"
										id="main_image" type="text" value="<?php echo $mainImage?>"
										placeholder="可接受：image url" />
									<input type="file" name="file1" id="local_main_image" />
								</div>
							</div>

							<div class="control-group" style="display: none;" id="main_image_view">
								<label class="control-label" data-col-index="1"><span
									class="col-name">预览</span></label>
								<div class="controls input-append">
									<img id="main_img_view" width=100 height=100 class="img-thumbnail"src="" alt="photos" />
								</div>
							</div>
							
							<div class="control-group" style="display: block;">
								<label class="control-label" data-col-index="1"><span
									class="col-name">额外图片</span></label>

								<div class="controls input-append">
									<textarea rows="5" class="form-control" id="extra_images"
										name="Extra_Images" id="extra_images" type="text"
										placeholder="可接受：imageurl|imageurl|imageurl"><?php echo $extraImages?></textarea>
									<input type="file" name="file2" id="local_extra_image" />
								</div>
							</div>
							
							<div class="control-group" style="display: none;" id="extra_images_view">
								<label class="control-label" data-col-index="1"><span
									class="col-name">预览</span></label>
								<div class="controls input-append">
									<img id="extra_img_view0" style="display: none;" width=100 height=100 class="img-thumbnail"src="" alt="photos" />
									<img id="extra_img_view1" style="display: none;" width=100 height=100 class="img-thumbnail"src="" alt="photos" />
									<img id="extra_img_view2" style="display: none;" width=100 height=100 class="img-thumbnail"src="" alt="photos" />
									<img id="extra_img_view3" style="display: none;" width=100 height=100 class="img-thumbnail"src="" alt="photos" />
									<img id="extra_img_view4" style="display: none;" width=100 height=100 class="img-thumbnail"src="" alt="photos" />
									<img id="extra_img_view5" style="display: none;" width=100 height=100 class="img-thumbnail"src="" alt="photos" />
								</div>
							</div>

							<div class="control-group" style="display: block;">
								<label class="control-label" data-col-index="1"><span
									class="col-name">颜色</span></label>

								<div class="controls input-append">
									<input class="input-block-level required" name="colors"
										id="colors" type="text" value="<?php echo $colors?>"
										placeholder="可接受：color|color|color" /><br/>
									<label>请用"|"分割多个颜色值，颜色取值可参考：<a href="./showwishcolors.php" target="_blank">wish颜色列表</a></label>
								</div>
							</div>

							<div class="control-group" style="display: block;">
								<label class="control-label" data-col-index="1"><span
									class="col-name">尺码</span></label>

								<div class="controls input-append">
									<input class="input-block-level required" name="sizes"
										onchange="showIncrementPrice(this.value)" id="sizes"
										type="text" value="<?php echo $sizes?>"
										placeholder="可接受：size|size|size" /><br/>
									<label>请用"|"分割多个尺码值，尺码取值可参考：<a href="http://www.merchant.wish.com/documentation/size-charts" target="_blank">wish尺码说明</a></label>
								</div>
							</div>
						</div>


						<div id="inventory-shipping"
							class="form-horizontal earnings-section">
							<div class="section-title">库存和运送</div>

							<div class="control-group">
								<label class="control-label" data-col-index="2"><span
									class="col-name">价格</span></label>

								<div class="controls input-append">
									<input class="input-block-level required" name="Price"
										onChange="updateEarnings()" id="price" type="text"
										value="<?php echo $price?>" placeholder="可接受：$100.99" />
								</div>
							</div>

							<div class="control-group" style="display: none;"
								id="increment_div">
								<label class="control-label" data-col-index="1"><span
									class="col-name">价格按尺码增量</span></label>

								<div class="controls input-append">
									<input class="input-block-level required"
										name="increment_price" id="increment_price" type="text"
										value="<?php echo $incrementPrice?>"
										placeholder="根据尺码的价格递增量； 可接受：$2" />
								</div>
							</div>

							<div class="control-group">
								<label class="control-label" data-col-index="4"><span
									class="col-name">数量</span></label>

								<div class="controls input-append">
									<input class="input-block-level required" name="Quantity"
										id="quantity" type="text" value="<?php echo $quantity?>"
										placeholder="可接受：1200" />
								</div>
							</div>

							<div class="control-group">
								<label class="control-label" data-col-index="5"><span
									class="col-name">运费</span></label>

								<div class="controls input-append">
									<input class="input-block-level required" name="Shipping"
										onchange="updateEarnings()" id="shipping" type="text"
										value="<?php echo $shipping?>" placeholder="可接受：$4.00" />
								</div>
							</div>

							<div class="control-group">
								<label class="control-label"><span class="col-name">净额</span></label>

								<div class="controls input-append">
									<input class="input-block-level" type="text" id="earnings"
										value="" disabled="disabled" />
								</div>
							</div>

							<div class="control-group">
								<label class="control-label" data-col-index="5"><span
									class="col-name">运输时间</span></label>

								<div class="controls input-append">
									<input class="input-block-level required" name="Shipping_Time"
										id="shipping_time" type="text"
										value="<?php echo $shippingTime?>" placeholder="可接受：5 - 10" />
								</div>
							</div>
						</div>

						<div id="optional-info" class="form-horizontal">
							<div class="section-title">
								可选信息
								<div id="toggle-optional" class="pull-right"></div>
							</div>

							<div id="optional-fields">
								<div class="control-group">
									<label class="control-label" data-col-index="12"><span
										class="col-name">MSRP(产品原价)</span></label>

									<div class="controls input-append">
										<input class="input-block-level" name="MSRP" id="MSRP"
											type="text" value="<?php echo $MSRP?>"
											placeholder="可接受：$19.00" />
									</div>
								</div>

								<div class="control-group">
									<label class="control-label" data-col-index="13"><span
										class="col-name">品牌</span></label>

									<div class="controls input-append">
										<input class="input-block-level" name="Brand" id="brand"
											type="text" value="<?php echo $brand?>"
											placeholder="可接受：Nike" />
									</div>
								</div>

								<div class="control-group">
									<label class="control-label" data-col-index="16"><span
										class="col-name">UPC</span></label>

									<div class="controls input-append">
										<input class="input-block-level" name="UPC" id="UPC"
											type="text" value="<?php echo $UPC?>"
											placeholder="可接受：716393133224" />
									</div>
								</div>

								<div class="control-group">
									<label class="control-label" data-col-index="14"><span
										class="col-name">Landing Page URL</span></label>

									<div class="controls input-append">
										<input class="input-block-level" name="Landing_Page_URL"
											id="landing_page_url" type="text"
											value="<?php echo $landingPageURL?>"
											placeholder="可接受：http://www.amazon.com/gp/product/B008PE00DA/ref=s9_simh_gw_p193_d0_i3?ref=wish" />
									</div>
								</div>

								<div class="control-group">
									<label class="control-label" data-col-index="14"><span
										class="col-name">产品源地址</span></label>

									<div class="controls input-append">
										<input class="input-block-level" name="Product_Source_URL"
											id="product_source_url" type="text"
											value="<?php echo $productSourceURL?>"
											placeholder="可接受：http://detail.1688.com/offer/xxxxx.html" />
									</div>
								</div>
							</div>
						</div>

						<div id="optional-info" class="form-horizontal">
							<div class="section-title">
								定时上传
								<div id="toggle-optional" class="pull-right"></div>
							</div>

							<div id="optional-fields">
								<div class="control-group">
									<label class="control-label" data-col-index="12"><span
										class="col-name">定时上传时间</span></label>

									<div class="controls input-append">
										<input class="input-block-level" name="Schedule_Date" type="text" value="<?php echo ($scheduleDate != null)?$scheduleDate:date('Y-m-d H:i')?>" id="datetimepicker" data-date-format="yyyy-mm-dd hh:ii" placeholder="可接受：20151225; 为空则立即上传">
									</div>
								</div>
							</div>
						</div>
						<div id="buttons-section" class="control-group text-right">
							<br/>
							<br/>
							<button id="submit-button" type="button"
								class="btn btn-primary btn-large" onclick="createProduct()">提交</button>
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
							
	function mainImageChange (){
		if($('#main_image').val() != null && $('#main_image').val() != ""){
			$('#main_image_view').show();
			$('#main_img_view').attr("src",$('#main_image').val());
	    }else{
	        $('#main_image_view').hide();
	    }
	}

	function extraImagesChange(){
		if($('#extra_images').val() != null && $('#extra_images').val() != ""){
        	$('#extra_images_view').show();
        	var images = $('#extra_images').val();
			var imagearray = images.split("|");
			
			
			$.each(imagearray,function(id,url){
				var imgid = "extra_img_view" + id;
				$('#'+ imgid).show();
				$('#'+ imgid).attr("src",url);
			});

			for(var i =0; i<6; i++){
				var imgid = "extra_img_view" + i;
				if(imagearray[i] != null && imagearray[i] != ""){
					$('#'+ imgid).show();
					$('#'+ imgid).attr("src",imagearray[i]);
				}else{
					$('#'+ imgid).hide();
					$('#'+ imgid).attr("src","");
				}
			}
			
        }else{
        	$('#extra_images_view').hide();
       	}
	}

	$(document).ready(function(){

		$('#datetimepicker').datetimepicker({
	    	language: 'zh-CN',
	        weekStart: 1,
	        todayBtn:  1,
			autoclose: 1,
			todayHighlight: 1,
			startView: 2,
			forceParse: 0,
	        showMeridian: 1});

		$('#tags').bind('input propertychange',function(){
			var tags = $('#tags').val().split(",");
			var tagsNoSpace = [];
			$.each(tags,function(i,v){
				if('' != $.trim(v))
					tagsNoSpace.push(v)
			});
			$('#tag_left_counts').text(10 - tagsNoSpace.length);
			if($('#tag_left_counts').text()<0){
				alert("Tag的个数不能超过10");
			}
		});
	    
	    $('#main_image').bind('input propertychange',function(){
	    	mainImageChange();
	    });


	    
	    $("#local_main_image").AjaxFileUpload({
			onComplete: function(filename, response) {
				switch(response['error']){
				case 0:
					$('#main_image').val("http://www.wishconsole.com/images/" + response['name']);
					mainImageChange();
					break;
				case -1:
					alert("不支持上传该类型的文件");
					break;
				case 1:
				case 2:
				case -2:
					alert("图片大小不能大于4M");
					break;
				case 3:
				case 4:
				case 5:
				case 6:	
				case -3:
				case -4:
				case -5:				
					alert("文件上传出错");
					break;
				}
			}
		});
		
	    $('#extra_images').bind('input propertychange',function(){
	    	extraImagesChange();
	    });

	    $("#local_extra_image").AjaxFileUpload({
			onComplete: function(filename, response) {
				switch(response['error']){
				case 0:
					var currentVal = $('#extra_images').val(); 
					if(currentVal != null && currentVal != ""){
						if(currentVal.chatAt(currentVal.length-1) == "|"){
							$('#extra_images').val(currentVal + "http://www.wishconsole.com/images/" + response['name']);
						}else{
							$('#extra_images').val(currentVal + "|http://www.wishconsole.com/images/" + response['name']);
						}
					}else{
						$('#extra_images').val(currentVal + "http://www.wishconsole.com/images/" + response['name']);
					}
					extraImagesChange();
					break;
				case -1:
					alert("不支持上传该类型的文件");
					break;
				case 1:
				case 2:
				case -2:
					alert("图片大小不能大于4M");
					break;
				case 3:
				case 4:
				case 5:
				case 6:	
				case -3:
				case -4:
				case -5:				
					alert("文件上传出错");
					break;
				}
			}
		});

	});
	
    function showIncrementPrice(obj){
		if(obj == "" || obj.length<2){
			document.getElementById("increment_div").style.display="none";
		}else{
			document.getElementById("increment_div").style.display="block";
		}
	}

	function updateEarnings(){
		var price = document.getElementById("price").value;
		var shipping = document.getElementById("shipping").value;
		if($.trim(price) == "")
			price = 0;
		if($.trim(shipping) == "")
			shipping = 0;
		var earn = (parseInt(price) + parseInt(shipping)) * 0.85;
		document.getElementById("earnings").value=earn;
	} 

	function createProduct(){
		var productName = document.getElementById("product_name").value;
		if(productName == null || productName == ''){
			alert("产品名称不能为空");
		return;}
		var description = document.getElementById("description").value;
		if(description == null || description == ''){
			alert("产品描述不能为空");
		return;}
		var tags = document.getElementById("tags").value;
		if(tags == null || tags == ''){
			alert("tags不能为空");
		return;}
		var uniqueID = document.getElementById("unique_id").value;
		if(uniqueID == null || uniqueID == ''){
			alert("父SKU不能为空");
			return;}
		var mainImage = document.getElementById("main_image").value;
		if(mainImage == null || mainImage == ''){
			alert("主图片不能为空");
			return;}
		var price = document.getElementById("price").value;
		if(price == null || $.trim(price) == ''){
			alert("价格不能为空");
			return;
		}else if(isNaN(price)){
			alert("价格不对，请输入数字");
			return;
		}
		var quantity = document.getElementById("quantity").value;
		if(quantity == null || $.trim(quantity) == ''){
			alert("数量不能为空");
			return;
		}else if(isNaN(quantity)){
			alert("数量不对，请输入数字");
			return;
		}
	    var shipping = document.getElementById("shipping").value;
		if(shipping == null || $.trim(shipping) == ''){
			alert("运费不能为空");
			return;
		}else if(isNaN(shipping)){
			alert("运费不对，请输入数字");
			return;
		}
		var shippingTime = document.getElementById("shipping_time").value;
		if(shippingTime == null || $.trim(shippingTime) == ''){
			alert("运输时间不能为空");
			return;
		}
		var shippingtimes = shippingTime.split("-");
		if(shippingtimes == null || shippingtimes.length != 2){
			alert("运输时间格式不对，请输入类似的数字区间:\"10-30\"");
			return;
		}else if($.trim(shippingtimes[0]) == "" || $.trim(shippingtimes[1]) == "" || isNaN($.trim(shippingtimes[0])) || isNaN($.trim(shippingtimes[1]))){
			alert("运输时间格式不对，请输入类似的数字区间:\"10-30\"");
			return;
		}
		var form = document.getElementById("add_product");
		form.submit();
	}
</script>
</body>
</html>