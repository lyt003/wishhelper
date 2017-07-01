<?php
session_start ();
include_once dirname ( '__FILE__' ) . './Wish/WishClient.php';
include_once dirname ( '__FILE__' ) . './Wish/WishHelper.php';
include_once dirname ( '__FILE__' ) . './mysql/dbhelper.php';
include_once dirname ( '__FILE__' ) . './user/wconfig.php';
use Wish\WishClient;
use mysql\dbhelper;
use Wish\WishHelper;
use Wish\Model\WishTracker;
use Wish\Exception\ServiceResponseException;
header ( "Content-Type: text/html;charset=utf-8" );

set_time_limit ( 0 );
$dbhelper = new dbhelper ();
$wishHelper = new WishHelper ();

$username = $_SESSION ['username'];
session_commit();
if ($username == null) { // 未登录
	header ( "Location:./wlogin.php?errorMsg=登录失败" );
	exit ();
}

// 已登录
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
		
		$accounts[$rows ['accountid']] = $rows ['token'];
		$i ++;
	}
}

$currentAccountid = $_GET['currentAccountid'];
if($currentAccountid != null){
	
		$nextday = date ( 'Y-m-d',strtotime('+1 day'));
		$dbhelper->insertOptimizeJob($currentAccountid, SYNCHRONIZEDSTORE, "", $nextday);
	
	//最近的订单时间为产品的最近更新时间。
			/* $client = new WishClient ($accounts [$currentAccountid] , 'prod' );
			$start = 0;
			$limit = 50;
			do{
				$productsResult = $client->getProducts($start, $limit);
				$hasmore = $productsResult['more'];
				$products = $productsResult['data'];
				foreach ($products as $product){
	
					$tempProduct = array();
	
					$vars = get_object_vars($product);
					foreach ($vars as $key=>$val){
						$tempProduct[$key] = $val;
					}
	
					$tempTags = "";
					foreach ($tempProduct['tags'] as $tagObj){
						$tempTags = $tempTags.$tagObj->Tag->name.",";
					}
					$tempTags = rtrim($tempTags,",");
					$tempProduct['tags'] = $tempTags;
	
					$tempProduct['accountid'] = $currentAccountid ;
					$uploaded = $tempProduct['date_uploaded'];
					$tempdate = explode("-",trim($uploaded));
					$tempProduct['date_uploaded'] = $tempdate[2]."-".$tempdate[0]."-".$tempdate[1];
					$tempProduct['date_updated'] = $tempProduct['date_uploaded'];
					$wishHelper->insertOnlineProduct($tempProduct);
						
					$productVars = $tempProduct['variants'];
					foreach ($productVars as $productvar){
							
						$tempVars = array();
						$vvvvars = get_object_vars($productvar);
						foreach ($vvvvars as $key=>$val){
							$tempVars[$key] = $val;
						}
							
						$tempVars['accountid'] = $currentAccountid ;
						$wishHelper->insertOnlineProductVar($tempVars);
					}
				}
					
				$start += $limit;
			}while ($hasmore); */
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
" style="left: 0px;">
		<div class="container-fluid ">
			<a class="brand" href="https://wishconsole.com/"> <span
				class="merchant-header-text">Wish管理助手-更有效率的Wish商户实用工具</span>
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
						<?php include("./menu.php");?>
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
		<form id="addform" action="./wproductlist.php" method="get"> 
			<div id="add-products-page" class="center">
				<div>
					<div id="add-product-form">
						<div id="basic-info" class="form-horizontal">

						<?php 
	                      		if(isset($msg)){
	                      			echo "<div class=\"alert alert-block alert-success fade in\">";
	                      			echo "<h4 class=\"alert-heading\">";
	                      			if($msg){
	                      				echo "同步成功";
	                      			}else{
	                      				echo "同步失败，请联系管理员 admin@wishconsole.com";
	                      			}
	                      			echo "</h4>";
	                      			echo "</div>";
	                      			$msg = null;
	                      		}
	                    ?>
	                    
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
									echo "<input type=\"radio\" id=\"currentAccountid\" name=\"currentAccountid\" value=\"" . $accounts ['accountid' . $count] . "\"" . ($accountid == null ? ($count == 0 ? "checked" : "") : ((strcmp ( $accounts ['accountid' . $count], $accountid ) == 0) ? "checked" : "")) . ">";
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
						</div>

						<div id="buttons-section" class="control-group text-right">
							<br/>
							<br/>
							<button id="submit-button" type="submit"
								class="btn btn-primary btn-large">自动同步和优化店铺产品</button>
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
</body>
</html>