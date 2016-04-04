<?php
use mysql\dbhelper;
include_once dirname ( '__FILE__' ) . './mysql/dbhelper.php';

$filename = $_FILES ['file'] ['tmp_name'];

$accountid = $_POST['currentAccountid'];

$dbhelper = new dbhelper();
$index = 0;
$isProduct = 0;
$csvfile = fopen($filename,'r');
$result;
while ($data = fgetcsv($csvfile)) {
	if($index == 0){
		if(strcmp($data[1],'Product URL') == 0){
			$isProduct = 1;
		}
		$index = 1;
		continue;
	}
		if($isProduct == 1){//product summary
			$weekdata = array();
			$daterange = $data[0];
			$datearray = explode("/",$daterange);
			if($datearray != null && count($datearray) == 2){
				//03-07-2016 转换为 2016-03-07
				$startarray = explode("-",trim($datearray[0]));
				$weekdata['startdate'] = $startarray[2]."-".$startarray[0]."-".$startarray[1];
				$endarray = explode("-",trim($datearray[1]));
				$weekdata['enddate'] = $endarray[2]."-".$endarray[0]."-".$endarray[1];
			}
			$weekdata['productid'] = substr($data[1],strrpos($data[1],"/") + 1);
			$weekdata['productimpression'] = str_replace(",","",$data[2]);
			$weekdata['buycart'] = str_replace(",","",$data[3]);
			$weekdata['buyctr']= $data[4];
			$weekdata['orders']= str_replace(",","",$data[6]);
			$weekdata['checkoutconversion']= $data[7];
			$weekdata['gmv']= str_replace(",","",$data[8]);
			$result = $dbhelper->insertWeeklySummary($accountid,$weekdata);
		}else{//weekly summary;
			$weekdata = array();
			$daterange = $data[0];
			$datearray = explode("/",$daterange);
			if($datearray != null && count($datearray) == 2){
				//03-07-2016 转换为 2016-03-07
				$startarray = explode("-",trim($datearray[0]));
				$weekdata['startdate'] = $startarray[2]."-".$startarray[0]."-".$startarray[1];
				$endarray = explode("-",trim($datearray[1]));
				$weekdata['enddate'] = $endarray[2]."-".$endarray[0]."-".$endarray[1];
			}
			
			$weekdata['productimpression'] = str_replace(",","",$data[1]);
			$weekdata['buycart'] = str_replace(",","",$data[2]);
			$weekdata['buyctr']= $data[3];
			$weekdata['orders']= str_replace(",","",$data[5]);
			$weekdata['checkoutconversion']= $data[6];
			$weekdata['gmv']= str_replace(",","",$data[7]);
			
			$weekdata['productid'] = '0';
			
			$result = $dbhelper->insertWeeklySummary($accountid,$weekdata);
		}
}
fclose($csvfile);
header ( "Location:./csvupload.php?msg=".$result);
exit ();
?>