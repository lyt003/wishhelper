<?php 
use mysql\dbhelper;
use Wish\WishHelper;
include_once dirname ( '__FILE__' ) . './mysql/dbhelper.php';
include_once dirname ( '__FILE__' ) . './Wish/WishHelper.php';
header ( "Content-Type: text/html;charset=utf-8" );

$accountid = $_GET['uid'];
$productid = $_GET['pid'];
$varsku = $_GET['sku'];

$dbhelper = new dbhelper();
$wishhelper = new WishHelper();

if($varsku == null){
	$pdetails = $dbhelper->getProductDetails($accountid, $productid);
	if($pvar = mysql_fetch_array($pdetails)){
		$varsku = $pvar['sku'];
	}	
}


$rate = 6.2;
$shippingcosts = $dbhelper->getProductShippingCost($varsku,$accountid);
$productcosts = $wishhelper->getProductSKUCost($varsku, $accountid);

echo "<div class=\"row-fluid\"><div class=\"span12\"><div class=\"widget\"><div class=\"widget-header\"><div class=\"title\">&nbsp;&nbsp;&nbsp;&nbsp;账号:&nbsp;&nbsp;" . $accountid."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;下列产品  ".$varsku." 最近订单的运费数据:";
echo "</div><span class=\"tools\"></div>";
echo "<div class=\"widget-body\"><table class=\"table table-condensed table-striped table-bordered table-hover no-margin\" border=\"1px\" cellspacing=\"0px\"><thead><tr>";
echo "<th style=\"width:10%\">SKU</th>";
echo "<th style=\"width:10%\">日期</th><th style=\"width:10%\">目的国</th><th style=\"width:5%\">重量(g)</th><th style=\"width:20%\">物流跟踪号</th><th style=\"width:7%\">物流费用</th><th style=\"width:15%\">总价</th><th style=\"width:5%\">成本</th><th style=\"width:8%\">利润</th></tr>";
echo "<tbody>";
$orderCount = 0;
$countprofit = 0;

$trackings = array();
$currshippingcost = 0;
while($shippingcost = mysql_fetch_array($shippingcosts)){
	
	if($trackings[$shippingcost ['tracking_number']] == 1){
		$currshippingcost = 0;
	}else{
		$currshippingcost = $shippingcost ['finalshippingcost'];
		$trackings[$shippingcost ['tracking_number']] = 1;
	}
		
	$totalprice = $shippingcost ['totalcost']*$rate;
	$totalcost = $productcosts [$shippingcost['sku']]*$shippingcost ['quantity'];
	if($shippingcost ['finalshippingcost'] != null && $shippingcost ['finalshippingcost']>0 && $productcosts [$shippingcost['sku']] != null && $productcosts [$shippingcost['sku']] >0){
		$profit = $totalprice - $currshippingcost - $totalcost;
	}else{
		$profit = '0';
	}
	
	$countprofit += $profit;
	if ($orderCount % 2 == 0) {
		echo "<tr>";
	} else {
		echo "<tr class=\"gradeA success\">";
	}
		
	echo "<td style=\"width:10%;vertical-align:middle;\">" . $shippingcost['sku']."</td>";
	echo "<td style=\"width:10%;vertical-align:middle;\">" . $shippingcost ['tracking_date']."</td>";
	echo "<td style=\"width:10%;vertical-align:middle;\">" . $shippingcost ['destinate']."</td>";
	echo "<td style=\"width:5%;vertical-align:middle;\">" . $shippingcost ['weight']."</td>";
	echo "<td style=\"width:20%;vertical-align:middle;\">" . $shippingcost ['tracking_number']."</td>";
	echo "<td style=\"width:7%;vertical-align:middle;\">" . $currshippingcost."</td>";
	echo "<td style=\"width:15%;vertical-align:middle;\">".$rate." x " . $shippingcost ['totalcost']."=".$totalprice."</td>";
	echo "<td style=\"width:5%;vertical-align:middle;\">" . $totalcost."</td>";
	echo "<td style=\"width:8%;vertical-align:middle;\">" . $profit."</td>";
	echo "</tr>";
	$orderCount ++;
		
}
echo "<tr>";
echo "<td colspan=\"7\" style=\"width:20% text-align:right\"> </td>";
echo "<td colspan=\"2\" style=\"width:20% text-align:right\">总利润为：".$countprofit." 元</td>";
echo "</tr>";
echo "</tbody></table></div></div></div></div>";

?>