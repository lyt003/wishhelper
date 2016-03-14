<?php
session_start ();
header ( "Content-Type: text/html;charset=utf-8" );
include_once dirname ( '__FILE__' ) . './mysql/dbhelper.php';
use mysql\dbhelper;

$username = $_SESSION ['username'];

if ($username == null) { // 未登录
	header ( "Location:./wlogin.php" );
	exit ();
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
					<link href="../css/bootstrap.min.css" rel="stylesheet">
						<script src="../js/jquery-2.2.0.min.js"></script>
						<script src="../js/bootstrap.min.js"></script>

</head>
<body>
<?php 
echo "<div id=\"page-content\">";
echo "<div class=\"row-fluid\"><div class=\"span12\"><div class=\"widget\"><div class=\"widget-header\"><div class=\"title\"><h1>Wish颜色列表：</h1><br/>";
echo "</div><span class=\"tools\"><a class=\"fs1\" aria-hidden=\"true\" data-icon=\"&#xe090;\"></a></span></div>";
echo "<div class=\"widget-body\"><table class=\"table table-condensed table-striped table-bordered table-hover no-margin\"><thead><tr>";
echo "<th style=\"width:35%\"><h1>颜色</h1></th><th style=\"width:35%\"><h1>颜色</h1></th>";
echo "<th style=\"width:35%\"><h1>颜色</h1></th></tr></thead>";
echo "<tbody>";

$dbhelper = new dbhelper();
$colors = $dbhelper->getWishColors();
$colorcount = 0;
$currentLine = 0;
echo "<tr>";
while($color = mysql_fetch_array($colors)){
	if($currentLine % 3 == 0){
		if ($colorcount % 2 == 0) {
			echo "</tr><tr>";
		} else {
			echo "</tr><tr class=\"gradeA success\">";
		}
		echo "<td style=\"width:35%;vertical-align:middle;\"><h2>" . $color['color'] ."</h2></td>";
		$colorcount ++;
	}else{
		echo "<td style=\"width:35%;vertical-align:middle;\"><h2>" . $color['color'] ."</h2></td>";
	}
	
	$currentLine ++;
}
echo "</tbody></table></div></div></div></div>";
echo "</div>";
?>
</body>
</html>