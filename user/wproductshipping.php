<?php 
use mysql\dbhelper;
include_once dirname ( '__FILE__' ) . './mysql/dbhelper.php';
header ( "Content-Type: text/html;charset=utf-8" );

$accountid = $_GET['uid'];
$productid = $_GET['pid'];
$varsku = $_GET['sku'];

$dbhelper = new dbhelper();

if($varsku == null){
	$pdetails = $dbhelper->getProductDetails($accountid, $productid);
	if($pvar = mysql_fetch_array($pdetails)){
		$varsku = $pvar['sku'];
	}	
}


$shippingcosts = $dbhelper->getProductShippingCost($varsku);


echo "<div class=\"row-fluid\"><div class=\"span12\"><div class=\"widget\"><div class=\"widget-header\"><div class=\"title\">&nbsp;&nbsp;&nbsp;&nbsp;账号:&nbsp;&nbsp;" . $accountid."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;下列产品  ".$varsku." 最近订单的运费数据:";
echo "</div><span class=\"tools\"></div>";
echo "<div class=\"widget-body\"><table class=\"table table-condensed table-striped table-bordered table-hover no-margin\"><thead><tr>";
echo "<th style=\"width:20%\">SKU</th>";
echo "<th style=\"width:10%\">日期</th><th style=\"width:10%\">目的国</th><th style=\"width:10%\">重量(g)</th><th style=\"width:20%\">物流跟踪号</th><th style=\"width:10%\">费用</th><th style=\"width:10%\">总价</th>";
echo "<tbody>";
$orderCount = 0;
while($shippingcost = mysql_fetch_array($shippingcosts)){
		
	if ($orderCount % 2 == 0) {
		echo "<tr>";
	} else {
		echo "<tr class=\"gradeA success\">";
	}
		
	echo "<td style=\"width:20%;vertical-align:middle;\">" . $shippingcost['sku']."</td>";
	echo "<td style=\"width:10%;vertical-align:middle;\">" . $shippingcost ['tracking_date']."</td>";
	echo "<td style=\"width:10%;vertical-align:middle;\">" . $shippingcost ['destinate']."</td>";
	echo "<td style=\"width:10%;vertical-align:middle;\">" . $shippingcost ['weight']."</td>";
	echo "<td style=\"width:20%;vertical-align:middle;\">" . $shippingcost ['tracking_number']."</td>";
	echo "<td style=\"width:10%;vertical-align:middle;\">" . $shippingcost ['finalshippingcost']."</td>";
	echo "<td style=\"width:10%;vertical-align:middle;\">6 x " . $shippingcost ['totalcost']."=".($shippingcost ['totalcost']*6)."</td>";

	echo "</tr>";
	$orderCount ++;
		
}
echo "</tbody></table></div></div></div></div>";

?>