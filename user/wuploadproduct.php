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
		$check = $dbhelper->queryUser ( $username, $email );
		$checkrow = mysql_fetch_array ( $check );
		if ($checkrow) {
			if ($checkrow ['username'] == $username) {
				header ( "Location:./wregister.php?errorMsg=该用户已经存在" );
				exit ();
			}
			if ($checkrow ['email'] == $email) {
				header ( "Location:./wregister.php?errorMsg=该邮箱地址已经被注册" );
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
	if ($scheduleDate != null) {
		$productarray ['accountid'] = $accountid;
		$productarray ['scheduledate'] = $scheduleDate;
		$dbhelper->insertScheduleProduct ( $productarray );
	} else {
		$products = $dbhelper->getProducts ( $uniqueID );
		$addProduct = 0;
		$prod_res = null;
		while ( $product = mysql_fetch_array ( $products ) ) {
			if ($addProduct == 0) { // add product;
				
				$currentProduct = array ();
				$currentProduct ['name'] = $product ['name'];
				$currentProduct ['description'] = $product ['description'];
				$currentProduct ['tags'] = $product ['tags'];
				$currentProduct ['sku'] = $product ['sku'];
				if ($product ['color'] != null)
					$currentProduct ['color'] = $product ['color'];
				if ($product ['size'] != null)
					$currentProduct ['size'] = $product ['size'];
				$currentProduct ['inventory'] = $product ['quantity'];
				$currentProduct ['price'] = $product ['price'];
				$currentProduct ['shipping'] = $product ['shipping'];
				if ($product ['MSRP'] != null)
					$currentProduct ['msrp'] = $product ['MSRP'];
				$currentProduct ['shipping_time'] = $product ['shipping_time'];
				$currentProduct ['main_image'] = $product ['main_image'];
				$currentProduct ['parent_sku'] = $product ['parent_sku'];
				if ($product ['brand'] != null)
					$currentProduct ['brand'] = $product ['brand'];
				if ($product ['landingPageURL'] != null)
					$currentProduct ['landing_page_url'] = $product ['landingPageURL'];
				if ($product ['UPC'] != null)
					$currentProduct ['upc'] = $product ['UPC'];
				if ($product ['extra_images'] != null)
					$currentProduct ['extra_images'] = $product ['extra_images'];
				
				try {
					$prod_res = $client->createProduct ( $currentProduct );
				} catch ( ServiceResponseException $e ) {
					if ($e->getStatusCode () == 1015 || $e->getStatusCode() == 1016) {
						$response = $client->refreshToken ( $clientid, $clientsecret, $refresh_token );
						echo "<br/>errorMessage:" . $response->getMessage ();
						$values = $response->getResponse ()->{'data'};
						$newToken = '0';
						$newRefresh_token = '0';
						foreach ( $values as $k => $v ) {
							echo 'key  ' . $k . '  value:' . $v;
							if ($k == 'access_token') {
								$newToken = $v;
							}
							if ($k == 'refresh_token') {
								$newRefresh_token = $v;
							}
						}
						echo "<br/>newToken = " . $newToken . $newRefresh_token;
						$dbhelper->updateUserToken ( $accountid, $newToken, $newRefresh_token );
						$client = new WishClient ( $newToken, 'prod' );
						$prod_res = $client->createProduct ( $currentProduct );
					}
				}
				print_r ( $prod_res );
				if ($prod_res != null) {
					echo "add product success<br/>";
					$addProduct = 1;
				} else {
					echo "add product failed<br/>";
				}
			} else { // add product variation
				$currentProductVar = array ();
				$currentProductVar ['parent_sku'] = $product ['parent_sku'];
				$currentProductVar ['sku'] = $product ['sku'];
				if ($product ['color'] != null)
					$currentProductVar ['color'] = $product ['color'];
				if ($product ['size'] != null)
					$currentProductVar ['size'] = $product ['size'];
				$currentProductVar ['inventory'] = $product ['quantity'];
				$currentProductVar ['price'] = $product ['price'];
				$currentProductVar ['shipping'] = $product ['shipping'];
				if ($product ['MSRP'] != null)
					$currentProductVar ['msrp'] = $product ['MSRP'];
				$currentProductVar ['shipping_time'] = $product ['shipping_time'];
				$currentProductVar ['main_image'] = $product ['main_image'];
				$prod_var = $client->createProductVariation ( $currentProductVar );
				print_r ( $prod_var );
				if (prod_var != null) {
					echo "add product var success<br/>";
				}
			}
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
			<title>Wish 商户平台</title>
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
			<a class="brand" href="http://wishconsole.com/"> <span
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
								<li><a href="./wproductstatus.php">产品状态</a></li>
								</ul>
								</li>  
							<!-- <li><a href="./wuserinfo.php"> 个人信息 </a></li> -->
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
									class="col-name">Product Name</span></label>

								<div class="controls input-append">
									<input class="input-block-level required" id="product_name"
										name="Product_Name" type="text"
										value="<?php echo $productName?>"
										placeholder="可接受：Men&#39;s Dress Casual Shirt Navy" />
								</div>
							</div>

							<div class="control-group">
								<label class="control-label" data-col-index="8"><span
									class="col-name">Description</span></label>

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
									class="col-name">Unique Id</span></label>

								<div class="controls input-append">
									<input class="input-block-level required" name="Unique_Id"
										value="<?php echo $uniqueID?>" id="unique_id" type="text"
										value="" placeholder="可接受：HSC0424PP" />
								</div>
							</div>

							<div class="control-group" style="display: block;">
								<label class="control-label" data-col-index="1"><span
									class="col-name">Main Image</span></label>

								<div class="controls input-append">
									<input class="input-block-level required" name="Main_Image"
										id="main_image" type="text" value="<?php echo $mainImage?>"
										placeholder="可接受：image url" />
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
									class="col-name">Extra Images</span></label>

								<div class="controls input-append">
									<textarea rows="5" class="form-control" id="extra_images"
										name="Extra_Images" id="extra_images" type="text"
										placeholder="可接受：imageurl|imageurl|imageurl"><?php echo $extraImages?></textarea>
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
									class="col-name">Colors</span></label>

								<div class="controls input-append">
									<input class="input-block-level required" name="colors"
										id="colors" type="text" value="<?php echo $colors?>"
										placeholder="可接受：color|color|color" />
								</div>
							</div>

							<div class="control-group" style="display: block;">
								<label class="control-label" data-col-index="1"><span
									class="col-name">Sizes</span></label>

								<div class="controls input-append">
									<input class="input-block-level required" name="sizes"
										onchange="showIncrementPrice(this.value)" id="sizes"
										type="text" value="<?php echo $sizes?>"
										placeholder="可接受：size|size|size" />
								</div>
							</div>
						</div>


						<div id="inventory-shipping"
							class="form-horizontal earnings-section">
							<div class="section-title">库存和运送</div>

							<div class="control-group">
								<label class="control-label" data-col-index="2"><span
									class="col-name">Price</span></label>

								<div class="controls input-append">
									<input class="input-block-level required" name="Price"
										onChange="updateEarnings()" id="price" type="text"
										value="<?php echo $price?>" placeholder="可接受：$100.99" />
								</div>
							</div>

							<div class="control-group" style="display: none;"
								id="increment_div">
								<label class="control-label" data-col-index="1"><span
									class="col-name">increment price</span></label>

								<div class="controls input-append">
									<input class="input-block-level required"
										name="increment_price" id="increment_price" type="text"
										value="<?php echo $incrementPrice?>"
										placeholder="根据尺码的价格递增量； 可接受：$2" />
								</div>
							</div>

							<div class="control-group">
								<label class="control-label" data-col-index="4"><span
									class="col-name">Quantity</span></label>

								<div class="controls input-append">
									<input class="input-block-level required" name="Quantity"
										id="quantity" type="text" value="<?php echo $quantity?>"
										placeholder="可接受：1200" />
								</div>
							</div>

							<div class="control-group">
								<label class="control-label" data-col-index="5"><span
									class="col-name">Shipping</span></label>

								<div class="controls input-append">
									<input class="input-block-level required" name="Shipping"
										onchange="updateEarnings()" id="shipping" type="text"
										value="<?php echo $shipping?>" placeholder="可接受：$4.00" />
								</div>
							</div>

							<div class="control-group">
								<label class="control-label"><span class="col-name">利润</span></label>

								<div class="controls input-append">
									<input class="input-block-level" type="text" id="earnings"
										value="" disabled="disabled" />
								</div>
							</div>

							<div class="control-group">
								<label class="control-label" data-col-index="5"><span
									class="col-name">Shipping Time</span></label>

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
										class="col-name">MSRP</span></label>

									<div class="controls input-append">
										<input class="input-block-level" name="MSRP" id="MSRP"
											type="text" value="<?php echo $MSRP?>"
											placeholder="可接受：$19.00" />
									</div>
								</div>

								<div class="control-group">
									<label class="control-label" data-col-index="13"><span
										class="col-name">Brand</span></label>

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
										class="col-name">Product Source URL</span></label>

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
										class="col-name">定时上传日期</span></label>

									<div class="controls input-append">
										<input class="input-block-level" name="Schedule_Date" type="text" value="<?php echo ($scheduleDate != null)?$scheduleDate:date('Y-m-d  H:i')?>" id="datetimepicker" data-date-format="yyyy-mm-dd hh:ii" placeholder="可接受：20151225; 为空则立即上传">
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
				<span><a href="http://wishconsole.com/">关于我们</a></span> <span><a>2016
						wishconsole版权所有 京ICP备16000367号</a></span>
			</div>
		</div>
	</div>
	<!-- END FOOTER -->
	
<script type="text/javascript" src="../js/jquery-2.2.0.min.js" charset="UTF-8"></script>
<script type="text/javascript" src="../js/bootstrap.min.js"></script>
<script type="text/javascript" src="../js/bootstrap-datetimepicker.js" charset="UTF-8"></script>
<script type="text/javascript" src="../js/locales/bootstrap-datetimepicker.zh-CN.js" charset="UTF-8"></script>
<script type="text/javascript">
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
        if($(this).val() != null && $(this).val() != ""){
    			$('#main_image_view').show();
    			$('#main_img_view').attr("src",$(this).val());
        }else{
            	$('#main_image_view').hide();
        }
     });

    $('#extra_images').bind('input propertychange',function(){
        if($(this).val() != null && $(this).val() != ""){
        	$('#extra_images_view').show();
        	var images = $(this).val();
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
		if(price == "")
			price = 0;
		if(shipping == "")
			shipping = 0;
		var earn = (parseInt(price) + parseInt(shipping)) * 0.85;
		document.getElementById("earnings").value=earn;
	} 

	function createProduct(){
		var productName = document.getElementById("product_name").value;
		if(productName == null || productName == ''){
			alert("name can't be empty");
		return;}
		var description = document.getElementById("description").value;
		if(description == null || description == ''){
			alert("description can't be empty");
		return;}
		var tags = document.getElementById("tags").value;
		if(tags == null || tags == ''){
			alert("tags can't be empty");
		return;}
		var uniqueID = document.getElementById("unique_id").value;
		if(uniqueID == null || uniqueID == ''){
			alert("uniqueID can't be empty");
			return;}
		var mainImage = document.getElementById("main_image").value;
		if(mainImage == null || mainImage == ''){
			alert("mainImage can't be empty");
			return;}
		var price = document.getElementById("price").value;
		if(price == null || price == ''){
			alert("price can't be empty");
			return;}
		var quantity = document.getElementById("quantity").value;
		if(quantity == null || quantity == ''){
			alert("quantity can't be empty");
			return;}
		var shipping = document.getElementById("shipping").value;
		if(shipping == null || shipping == ''){
			alert("shipping can't be empty");
			return;}
		var shippingTime = document.getElementById("shipping_time").value;
		if(shippingTime == null || shippingTime == ''){
			alert("shippingTime can't be empty");
			return;} 
		var form = document.getElementById("add_product");
		form.submit();
	}
</script>
</body>
</html>