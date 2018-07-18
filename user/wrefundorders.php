<?php 
use mysql\dbhelper;
use Wish\WishHelper;
include_once dirname ( '__FILE__' ) . './mysql/dbhelper.php';
include_once dirname ( '__FILE__' ) . './Wish/WishHelper.php';
header ( "Content-Type: text/html;charset=utf-8" );

$accountid = $_GET['uid'];
$productid = $_GET['pid'];
$varsku = urldecode($_GET['sku']);
$varcountrycode = urldecode($_GET['countrycode']);

$dbhelper = new dbhelper();
$wishhelper = new WishHelper();

$productid = $wishhelper->getPidBySKU($accountid, $varsku);
$refundorders = $dbhelper->getRefundOrders($accountid, $productid, $varcountrycode);

echo "<div class=\"row-fluid\"><div class=\"span12\"><div class=\"widget\"><div class=\"widget-header\"><div class=\"title\">&nbsp;&nbsp;&nbsp;&nbsp;账号:&nbsp;&nbsp;" . $accountid."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;下列产品  ".$varsku." 最近的退款订单信息:";
echo "</div><span class=\"tools\"></div>";
echo "<div class=\"widget-body\"><table class=\"table table-condensed table-striped table-bordered table-hover no-margin\" border=\"1px\" cellspacing=\"0px\"><thead><tr>";
echo "<th style=\"width:10%\">SKU</th>";
echo "<th style=\"width:10%\">日期</th><th style=\"width:7%\">目的国</th><th style=\"width:50%\">地址信息</th><th style=\"width:15%\">退款原因</th></tr>";
echo "<tbody>";
$orderCount = 0;

while($refundorder = mysql_fetch_array($refundorders)){
	if ($orderCount % 2 == 0) {
		echo "<tr>";
	} else {
		echo "<tr class=\"gradeA success\">";
	}
		
	echo "<td style=\"width:10%;vertical-align:middle;\">" . $refundorder['SKU']."</td>";
	echo "<td style=\"width:10%;vertical-align:middle;\">" . $refundorder ['TransactionDate']."</td>";
	echo "<td style=\"width:7%;vertical-align:middle;\">" . $refundorder ['Country']."</td>";
	echo "<td style=\"width:50%;vertical-align:middle;\">" . $refundorder ['ShippingAddress']."</td>";
	echo "<td style=\"width:15%;vertical-align:middle;\">" . $refundorder['RefundReason']."</td>";
	echo "</tr>";
	$orderCount ++;
		
}
echo "</tbody></table></div></div></div></div>";

?>