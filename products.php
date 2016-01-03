<?php
/*
 * create Product:
 * name Name of the product as shown to users on Wish
 * description Description of the product. Should not contain HTML. If you want a new line use "\n".
 * tags Comma separated list of strings that describe the product. Only 10 are allowed. Any tags past 10 will be ignored.
 * sku The unique identifier that your system uses to recognize this product
 * color optional The color of the product. Example: red, blue, green
 * size optional The size of the product. Example: Large, Medium, Small, 5, 6, 7.5
 * inventory The physical quantities you have for this product
 * price The price of the product when the user purchases one
 * shipping The shipping of the product when the user purchases one
 * msrp optional Manufacturer's Suggested Retail Price. This field is recommended as it will show as a strikethrough price on Wish and appears above the selling price for the product.
 * shipping_time optional The amount of time it takes for the shipment to reach the buyer. Please also factor in the time it will take to fulfill and ship the item. Provide a time range in number of days. Lower bound cannot be less than 2 days. Example: 15-20
 * main_image URL of a photo of your product. Link directly to the image, not the page where it is located. We accept JPEG, PNG GIF format. Images should be at least 100 x 100 pixels in size.
 * parent_sku optional When defining a variant of a product we must know which product to attach the variation to. parent_sku is the unique id of the product that you can use later when using the add product variation API.
 * brand optional Brand or manufacturer of your product
 * landing_page_url optional URL on your website containing the product details
 * upc optional 12-digit Universal Product Codes (UPC)-contains no letters or other characters
 * extra_images optional
 *
 *
 * create ProductVariation:
 *
 * parent_sku The parent_sku of the product this new product variation should be added to. If the product is missing a parent_sku, then this should be the SKU of a product variation of the product
 * sku The unique identifier that your system uses to recognize this variation
 * color optional The color of the variation. Example: red, blue, green
 * size optional The size of the variation. Example: Large, Medium, Small, 5, 6, 7.5
 * inventory The physical quantities you have for this variation
 * price The price of the variation when the user purchases one
 * shipping The shipping of the variation when the user purchases one
 * msrp optional Manufacturer's Suggested Retail Price. This field is recommended as it will show as a strikethrough price on Wish and appears above the selling price for the product.
 * shipping_time optional The amount of time it takes for the shipment to reach the buyer. Please also factor in the time it will take to fulfill and ship the item. Provide a time range in number of days. Lower bound cannot be less than 2 days. Example: 15-20
 * main_image optional URL of a photo for this product variation. Provide this when you have different pictures for different product variation of the product. If left out, it'll use the main_image of the product with the provided parent_sku. Link directly to the image, not the page where it is located. We accept JPEG, PNG or GIF format. Images should be at least 100 x 100 pixels in size.
 *
 */
header ( "Content-Type: text/html;charset=utf-8" );
include 'Wish/WishClient.php';
include 'mysql/dbhelper.php';
use Wish\WishClient;
use mysql\dbhelper;
use Wish\Model\WishTracker;
use Wish\Exception\ServiceResponseException;
use Wish\WishResponse;
session_start ();
$accountid = null;
$dbhelper = null;
$client = null;

$accountid = $_GET ['accountid'];

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
	$productarray ['extra_images'] = $extraImages;
	$productarray ['landingPageURL'] = $landingPageURL;
	$productarray ['main_image'] = $mainImage;
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
				}
			} else {
				if ($size != null) {
					$productarray ['sku'] = $uniqueID . "_" . $size;
					$productarray ['size'] = $size;
					$productarray ['price'] = $basePrice + $sizeCount * $incrementPrice;
					$sizeCount ++;
				} else {
					$productarray ['sku'] = $uniqueID;
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
				$currentProduct ['msrp'] = $product ['MSRP'];
				$currentProduct ['shipping_time'] = $product ['shipping_time'];
				$currentProduct ['main_image'] = $product ['main_image'];
				$currentProduct ['parent_sku'] = $product ['parent_sku'];
				$currentProduct ['brand'] = $product ['brand'];
				$currentProduct ['landing_page_url'] = $product ['landingPageURL'];
				$currentProduct ['upc'] = $product ['UPC'];
				$currentProduct ['extra_images'] = $product ['extra_images'];
				
				try {
					$prod_res = $client->createProduct ( $currentProduct );
				} catch ( ServiceResponseException $e ) {
					if ($e->getStatusCode () == 1015) {
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
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="generator"
	content="HTML Tidy for HTML5 (experimental) for Windows https://github.com/w3c/tidy-html5/tree/c63cc39" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>Wish 商户平台</title>
<meta name="keywords" content="" />
<link rel="stylesheet" type="text/css"
	href="./css/add_products_page.css" />
</head>
<script type="text/javascript">

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
<body>
	<form id="add_product"
		action="products.php<?php echo "?accountid=".$accountid?>"
		method="post">
		<div id="add-products-page" class="center">
			<div>
				<!-- NOTE: if you update this, make sure the add product page in onboarding flow still works -->
				<legend>添加产品</legend>

				<div id="add-product-form">
					<div id="basic-info" class="form-horizontal">
						<div class="section-title">基本信息</div>

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
								<textarea rows="5" class="input-block-level required"
									name="Description" id="description" type="text"
									placeholder="可接受：This dress shirt is 100% cotton and fits true to size."><?php echo $description?>
</textarea>
							</div>
						</div>

						<div class="control-group">
							<label class="control-label" data-col-index="7"><span
								class="col-name">Tags</span></label>

							<div class="controls input-append">
								<ul class="typeahead-tokenizer">
									<li class="token-li first-li"><input class="token-input"
										type="text" id="tags" name="Tags" value="<?php echo $tags?>"
										placeholder="可接受：Shirt, Men&#39;s Fashion, Navy, Blue, Casual, Apparel" /></li>
								</ul>
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

						<div class="control-group" style="display: block;">
							<label class="control-label" data-col-index="1"><span
								class="col-name">Extra Images</span></label>

							<div class="controls input-append">
								<input class="input-block-level required" name="Extra_Images"
									id="extra_images" type="text" value="<?php echo $extraImages?>"
									placeholder="可接受：imageurl|imageurl|imageurl" />
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
									id="sizes" type="text" value="<?php echo $sizes?>"
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
								<input  class="input-block-level required" name="Price" onChange="updateEarnings()"
									id="price" type="text" value="<?php echo $price?>"
									placeholder="可接受：$100.99" />
							</div>
						</div>

						<div class="control-group" style="display: block;">
							<label class="control-label" data-col-index="1"><span
								class="col-name">increment price</span></label>

							<div class="controls input-append">
								<input class="input-block-level required" name="increment_price"
									id="increment_price" type="text"
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
								<input class="input-block-level required" name="Shipping" onchange="updateEarnings()"
									id="shipping" type="text" value="<?php echo $shipping?>"
									placeholder="可接受：$4.00" />
							</div>
						</div>

						<div class="control-group">
							<label class="control-label"><span class="col-name">利润</span></label>

							<div class="controls input-append">
								<input class="input-block-level" type="text" id="earnings" value=""
									disabled="disabled" />
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
											<input class="input-block-level" name="Schedule_Date"
												id="Schedule_Date" type="text"
												value="<?php echo $scheduleDate?>"
												placeholder="可接受：20151225; 为空则立即上传" />
										</div>
									</div>
								</div>
							</div>

							<div id="buttons-section" class="control-group text-right">
								<button id="clear-button" class="btn btn-large">清除</button>
								<button id="submit-button" type="button"
									class="btn btn-primary btn-large" onclick="createProduct()">提交</button>

								<div id="loading-spinner" class="loading hide"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
	
	</form>
</body>
</html>
