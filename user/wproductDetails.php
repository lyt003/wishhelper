<?php
session_start ();
include dirname ( '__FILE__' ) . './Wish/WishClient.php';
include_once dirname ( '__FILE__' ) . './mysql/dbhelper.php';
include_once dirname ( '__FILE__' ) . './user/mailHelper.php';
include_once dirname ( '__FILE__' ) . './Wish/WishHelper.php';
use Wish\WishClient;
use mysql\dbhelper;
use Wish\Model\WishTracker;
use Wish\Exception\ServiceResponseException;
use Wish\WishResponse;
use user\mailHelper;
use Wish\WishHelper;

header ( "Content-Type: text/html;charset=utf-8" );
$dbhelper = new dbhelper ();
$wishhelper = new WishHelper();
$username = $_SESSION ['username'];
if ($username == null) { // 未登录
	header ( "Location:./wlogin.php?errorMsg=请先登录" );
	exit ();
}

$accountid = $_GET['uid'];
$productid = $_GET['pid'];

if($accountid == null || $productid == null){
	
	$accountid = $_POST['accountid'];
	$productid = $_POST['productid'];
	
	$newProductName = $_POST['Product_Name'];
	$newProductName = str_replace ( '"', "''", $newProductName );
	$newDescription = $_POST['Description'];
	$newTags = $_POST['Tags'];
	$newmainImage = $_POST['Main_Image'];
	$newExtraImages = $_POST['Extra_Images'];
}

$accountInfo = $dbhelper->getAccountToken($accountid);
$accounts = array ();
if($rows = mysql_fetch_array ( $accountInfo )){
	$accounts ['token'] = $rows ['token'];
	$accounts ['refresh_token'] = $rows ['refresh_token'];
	$accounts ['accountname'] = $rows ['accountname'];
}

$productDetails = $wishhelper->getProductDetails($dbhelper->getProductDetails($accountid, $productid));

$productName = $productDetails['name'];
$description = $productDetails['description'];
$tags = $productDetails['tags'];
$uniqueID = $productDetails['parent_sku'];
$mainImage = $productDetails['main_image'];
$extraImages = $productDetails['extra_images'];


if ($newProductName != null && $newDescription != null && $newmainImage != null && $newTags != null) {

	$client = new WishClient ($accounts ['token'], 'prod' );
	
	$onlineProduct = $client->getProductById($productid);
	
	$productParamsArray = array('id');
	if(strcmp($newProductName,$productName) != 0){
		$productParamsArray[] = 'name';
		$onlineProduct->name = $newProductName;
	}
		
	if(strcmp($newDescription,$description) != 0){
		$productParamsArray[] = 'description';
		$onlineProduct->description = $newDescription;
	}
		
	if(strcmp($newTags,$tags) != 0){
		$productParamsArray[] = 'tags';
		$onlineProduct->tags = $newTags;
	}
		
	if(strcmp($newmainImage,$mainImage) != 0){
		$productParamsArray[] = 'main_image';
		$onlineProduct->main_image = $newmainImage;
	}
		
	if(strcmp($newExtraImages,$extraImages) != 0){
		$productParamsArray[] = 'extra_images';
		$onlineProduct->extra_images = $newExtraImages;
	}
		
	//update product
	if(count($productParamsArray)>1){
		$params = $onlineProduct->getParams($productParamsArray);
		$client->updateProductByParams($params);
	}
		
	
	$price = $_POST ['Price'];
	$incrementPrice = $_POST ['increment_price'];
	$quantity = $_POST ['Quantity'];
	$shipping = $_POST ['Shipping'];
	$shippingTime = $_POST ['Shipping_Time'];
	$MSRP = $_POST ['MSRP'];
	$isEnabled = $_POST['isenabled'];
	
	/* $product = $client->getProductById($productid); */
	
	//update product vars
	if($price != null || $incrementPrice != null || $quantity != null || $shipping != null || $shippingTime != null || $MSRP != null || (strcmp($isEnabled,'2')!=0)){
		$skus = $wishhelper->getProductVars($productid);
		
		$paramsarray = array('sku');
		if($price != null ||$incrementPrice != null)
			$paramsarray[] = 'price';
		if($quantity != null)
			$paramsarray[] = 'inventory';
		if($shipping != null)
			$paramsarray[] = 'shipping';
		if($shippingTime != null)
			$paramsarray[] = 'shipping_time';
		if($MSRP != null)
			$paramsarray[] = 'msrp';
		if(strcmp($isEnabled,'2')!=0)
			$paramsarray[] = 'enabled';
 		
		if(count($paramsarray) > 1 ){
			foreach ($skus as $sku){
				$onlineProductVar = $client->getProductVariationBySKU($sku);

				if($price != null)
					$onlineProductVar->price = $price;
				if($quantity != null)
					$onlineProductVar->inventory = $quantity;
				if($shipping != null)
					$onlineProductVar->shipping = $shipping;
				if($shippingTime != null)
					$onlineProductVar->shipping_time = $shippingTime;
				if($MSRP != null)
					$onlineProductVar->msrp = $MSRP;
				if(strcmp($isEnabled,'1')==0){
					$onlineProductVar->enabled = "true";
				}else if(strcmp($isEnabled,'0')==0){
					$onlineProductVar->enabled = "false";
				}
					
				
				$params = $onlineProductVar->getParams($paramsarray);
				$client->updateProductVarByParams($params);
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
		<form id="update_product" action="./wproductDetails.php" method="post">
			<input type="hidden" id="productid" name="productid" value="<?php echo $productid?>"/>
			<input type="hidden" id="accountid" name="accountid" value="<?php echo $accountid?>"/>
			<div id="add-products-page" class="center">
				<div>
					<!-- NOTE: if you update this, make sure the add product page in onboarding flow still works -->
					<legend><?php echo ($updateParentSKU == null)?"添加产品":"修改产品:".$updateParentSKU?></legend>
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
									class="col-name">wish账号</span></label>

								<div class="controls input-append">
									<label  class="control-label"><?php echo $accounts ['accountname']?></label>
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
									<label  class="control-label"><?php echo $uniqueID?></label>
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
						</div>


						<div id="inventory-shipping"
							class="form-horizontal earnings-section">
							<div class="section-title">产品变量</div>

							<?php
							$productVars = $productDetails['productvars'];
		$orderCount = 0;
		echo "<div class=\"row-fluid\"><div class=\"span12\"><div class=\"widget\"><div class=\"widget-header\"><div class=\"title\">";
		echo "</div><span class=\"tools\"></div>";
		echo "<div class=\"widget-body\"><table class=\"table table-condensed table-striped table-bordered table-hover no-margin\"><thead><tr>";
		echo "<th style=\"width:20%\">子SKU</th><th style=\"width:10%\">颜色</th><th style=\"width:10%\">尺码</th><th style=\"width:10%\">价格</th><th style=\"width:10%\">运费</th>";
		echo "<th style=\"width:10%\">库存</th><th style=\"width:10%\">是否启用</th></tr></thead>";
		echo "<tbody>";
		foreach ($productVars as $productVar){
			
			//Get your product variation by its SKU
			/* $product_var = $client->getProductVariationBySKU($productVar['sku']);
			echo "<br/><br/><br/><br/><br/><br/>product:";
			print_r($product_var); */
				
			
			if ($orderCount % 2 == 0) {
				echo "<tr>";
			} else {
				echo "<tr class=\"gradeA success\">";
			}
			echo "<td style=\"width:20%;vertical-align:middle;\">" . $productVar['sku']. "</td>";
			echo "<td style=\"width:10%;vertical-align:middle;\">" . $productVar['color']."</td>";
			echo "<td style=\"width:10%;vertical-align:middle;\">" . $productVar ['size']."</td>";
			echo "<td style=\"width:10%;vertical-align:middle;\">" . $productVar ['price']."</td>";
			echo "<td style=\"width:10%;vertical-align:middle;\">" . $productVar ['shipping']."</td>";
			echo "<td style=\"width:10%;vertical-align:middle;\">" . $productVar ['inventory']."</td>";
			echo "<td style=\"width:10%;vertical-align:middle;\">" . $productVar ['enabled']."</td>";
			echo "</tr>";
			$orderCount ++;
		}
		echo "</tbody></table></div></div></div></div>";
?>
				</div>
				<div id="inventory-shipping"
							class="form-horizontal earnings-section">
							<div class="section-title">批量设置以下信息  (空白字段不会更新)</div>

							<div class="control-group">
								<label class="control-label" data-col-index="2"><span
									class="col-name">价格</span></label>

								<div class="controls input-append">
									<input class="input-block-level required" name="Price"
										onChange="updateEarnings()" id="price" type="text"
										value="<?php echo $price?>" placeholder="可接受：$100.99" />
								</div>
							</div>

							<div class="control-group" <?php echo ($incrementPrice == null)?"style=\"display: none;\"":" "?>
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
								<label class="control-label" data-col-index="5"><span
									class="col-name">产品启用</span></label>

								<div class="controls input-append">
									<label>
									<input type="radio" id="isenabled" name="isenabled" value="1">&nbsp;&nbsp;启用&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									<input type="radio" id="isenabled" name="isenabled" value="0">&nbsp;&nbsp;禁用&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									<input type="radio" id="isenabled" name="isenabled" value="2" checked>&nbsp;&nbsp;保持不变
									</label>
								</div>
							</div>
						</div>

						<div id="buttons-section" class="control-group text-right">
							<br/>
							<br/>
							<button id="submit-button" type="button"
								class="btn btn-primary btn-large" onclick="updateProduct()">提交</button>
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
						if(currentVal.substring(currentVal.length-1) == "|"){
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

	function updateProduct(){
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
		var mainImage = document.getElementById("main_image").value;
		if(mainImage == null || mainImage == ''){
			alert("主图片不能为空");
			return;}
		
		var price = document.getElementById("price").value;
		if(price != '' && isNaN(price)){
			alert("价格不对，请输入数字");
			return;
		}
		var quantity = document.getElementById("quantity").value;
		if(quantity != '' && isNaN(quantity)){
			alert("数量不对，请输入数字");
			return;
		}
	    var shipping = document.getElementById("shipping").value;
		if(shipping != '' && isNaN(shipping)){
			alert("运费不对，请输入数字");
			return;
		}
		var shippingTime = document.getElementById("shipping_time").value;
		if(shippingTime != ''){
			var shippingtimes = shippingTime.split("-");
			if(shippingtimes == null || shippingtimes.length != 2){
				alert("运输时间格式不对，请输入类似的数字区间:\"10-30\"");
				return;
			}else if($.trim(shippingtimes[0]) == "" || $.trim(shippingtimes[1]) == "" || isNaN($.trim(shippingtimes[0])) || isNaN($.trim(shippingtimes[1]))){
				alert("运输时间格式不对，请输入类似的数字区间:\"10-30\"");
				return;
			}
		} 
		
		var form = document.getElementById("update_product");
		form.submit();
	}
</script>
</body>
</html>