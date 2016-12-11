<?php
use wishpost\Wishposthelper;
use Wish\WishHelper;
include_once dirname ( '__FILE__' ) . '/wconfig.php';
include_once dirname ( '__FILE__' ) . './wishpost/Wishposthelper.php';
include_once dirname ( '__FILE__' ) . './Wish/WishHelper.php';

header ( "Content-type:application/vnd.ms-excel" );
header ( "Content-Disposition:filename=importdata.xls" );
session_start ();
$wishhelper = new WishHelper();
$wishposthelper = new Wishposthelper();
$userid = $_SESSION ['userid'];
session_commit();

$accountids = $wishposthelper->getWishPostAccounts($userid);

$countries = $wishhelper->getChineseCountrynames();
$labels = $wishhelper->getUserLabelsArray ( $userid );
//运单号	国家	发货方式	中文品名1	英文品名1	申报价值1	币种1
echo "运单号\t";
echo "国家\t";
echo "发货方式\t";
echo "中文品名1\t";
echo "英文品名1\t";
echo "申报价值1\t";
echo "币种1\t";

foreach ($accountids as $accountid){
	$trackingsexpress = $wishposthelper->getOrdersExpressInfo($accountid, ORDERSTATUS_APPLIEDTRACKING);
	foreach ($trackingsexpress as $key=>$value){
		echo "\n";
		echo $key."\t";
		$splitvalue = explode ( "|", $value );
		$expresscode = $splitvalue[0];
		$countrycode = $splitvalue[1];
		$sku = $splitvalue[2];
		$tempsku = str_replace(' ','_',$sku);
		$tempsku = str_replace('&amp;','AND',$tempsku);
		$currLabel = explode ( "|", $labels[$tempsku]);
		echo $countries[$countrycode]."\t";
		
		/*
		 *          WISH邮平邮=0
					WISH邮挂号=1
					--------------------------------------
					DLP平邮=9-0
					DLP挂号=9-1
					DLE=10-0
					E邮宝=11-0
					英伦速邮小包=14-0
					欧洲经济小包=200-0
					欧洲标准小包=201-0
		 * */
		if(strcmp($expresscode,'0') == 0 || strcmp($expresscode,'9-0') == 0){
			echo "WISH邮-平邮-北京仓\t";
		}else{
			echo "WISH邮-挂号-北京仓\t";
		}
		echo $currLabel[0]."\t";
		echo $currLabel[1]."\t";
		echo "5\t";
		echo "USD\t";
	}
}