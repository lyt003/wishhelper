<?php
use mysql\dbhelper;
include_once dirname ( '__FILE__' ) . './mysql/dbhelper.php';
header ( "Content-Type: text/html;charset=utf-8" );

$filename = $_FILES ['file'] ['tmp_name'];

$name     = basename($_FILES ['file']['name']);
$extension = pathinfo($name, PATHINFO_EXTENSION);

$accountid = $_POST['currentAccountid'];

$dbhelper = new dbhelper();
$result = false;
if(strcasecmp($extension,'csv') == 0){
	$index = 0;
	$isProduct = 0;
	$csvfile = fopen($filename,'r');
	$result = true;
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
			$result = $dbhelper->insertWeeklySummary($accountid,$weekdata) && $result;
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
				
			$result = $dbhelper->insertWeeklySummary($accountid,$weekdata) || $result;
		}
	}
	fclose($csvfile);
	header ( "Location:./csvupload.php?msg=".$result);
	exit ();
}else if(strcasecmp($extension,'xls') == 0){
	$result = "process xls:";
	echo "<br/>parse xls";
	/** PHPExcel_IOFactory */
	include_once dirname ( '__FILE__' ) . './PHPExcel/Classes/PHPExcel/IOFactory.php';
	
	
	//$inputFileName = '../example1.xls';
	echo '<br/>Loading file ',pathinfo($filename,PATHINFO_BASENAME),' using IOFactory to identify the format<br />';
	$objPHPExcel = PHPExcel_IOFactory::load($filename);
	
	
	echo '<hr />';
	
	$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
	$sheet = $objPHPExcel->getActiveSheet();
	$rows = $sheet->getHighestDataRow();
	$columns = $sheet->getHighestDataColumn();
	$columnMaxIndex = PHPExcel_Cell::columnIndexFromString($columns);
	echo "<br/>max row:".$rows.",column:".$columns.$columnMaxIndex;
	
	if($columnMaxIndex <= 10){//飞宇
	
		for($row = 0;$row<=$rows;$row ++){
			echo "<br/>";
			if($column == 0){
				$orderdata = $sheet->getCellByColumnAndRow($column,$row)->getValue();
				if(!is_numeric($orderdata))
					continue;
			}
			for($column = 0;$column<=$columnMaxIndex;$column ++){
				
				switch ($column){
					case 1:					
						$trackingdata = $sheet->getCellByColumnAndRow($column,$row)->getValue();
					case 2:
						$destinate = $sheet->getCellByColumnAndRow($column,$row)->getValue();
					case 3:
						$weight = 1000 * $sheet->getCellByColumnAndRow($column,$row)->getValue();
					case 4:
						$shippingcost = $sheet->getCellByColumnAndRow($column,$row)->getValue();
					case 5:
						$offprice = $sheet->getCellByColumnAndRow($column,$row)->getValue();
				}
			}
			if(isset($trackingdata) && isset($destinate) && isset($weight) && isset($shippingcost) && isset($offprice)){
				echo $trackingdata."  ".$destinate."  ".$weight."  ".$shippingcost."  ".$offprice*$shippingcost;
				$dbhelper->updateTrackingData($trackingdata, $destinate, $weight, $shippingcost, $offprice*$shippingcost);
			}else{
				$result .= $column."列没数据"; 
			}
		}
		
	}else if($columnMaxIndex >= 15){//Yanwen
		for($row = 0;$row<=$rows;$row ++){
			echo "<br/>";
			if($column == 0){
				$orderdata = $sheet->getCellByColumnAndRow($column,$row)->getValue();
				if(!checkDatetime($orderdata))
					continue;
			}
			for($column = 0;$column<=$columnMaxIndex;$column ++){
			
				switch ($column){
					case 1:
						$trackingdata = $sheet->getCellByColumnAndRow($column,$row)->getValue();
					case 5:
						$destinate = $sheet->getCellByColumnAndRow($column,$row)->getValue();
					case 6:
						$weight = $sheet->getCellByColumnAndRow($column,$row)->getValue();
					case 10:
						$shippingcost = $sheet->getCellByColumnAndRow($column,$row)->getValue();
					case 14:
						$finalcost = $sheet->getCellByColumnAndRow($column,$row)->getValue();
				}
			}
			if(isset($trackingdata) && isset($destinate) && isset($weight) && isset($shippingcost) && isset($finalcost)){
				echo $trackingdata."  ".$destinate."  ".$weight."  ".$shippingcost."  ".$finalcost;
				$dbhelper->updateTrackingData($trackingdata, $destinate, $weight, $shippingcost, $finalcost);
			}else{
				$result .= $column."列没数据";
			}	
		}
	}
	
	$objPHPExcel->disconnectWorksheets();
	header ( "Location:./csvupload.php?msg=".$result);
	exit ();
}

function excelTime($date, $time = false) {
 	echo "<br/> data number:".is_numeric( $date );
	if(function_exists('GregorianToJD')){
		echo "use GregorianToJD";
		if (is_numeric( $date )) {
			echo "isnumberic";
			$jd = GregorianToJD( 1, 1, 1970 );
			$gregorian = JDToGregorian( $jd + intval ( $date ) - 25569 );
			$date = explode( '/', $gregorian );
			$date_str = str_pad( $date [2], 4, '0', STR_PAD_LEFT )
			."-". str_pad( $date [0], 2, '0', STR_PAD_LEFT )
			."-". str_pad( $date [1], 2, '0', STR_PAD_LEFT )
			. ($time ? " 00:00:00" : '');
			return $date_str;
		}
		echo "no numberic";
	}else{
		$date=$date>25568?$date+1:25569;
		/*There was a bug if Converting date before 1-1-1970 (tstamp 0)*/
		$ofs=(70 * 365 + 17+2) * 86400;
		$date = date("Y-m-d",($date * 86400) - $ofs).($time ? " 00:00:00" : '');
	}
	return $date;
}

function checkDatetime($str, $format="Y-m-d"){
	$unixTime=strtotime($str);
	$checkDate= date($format, $unixTime);
	return $checkDate==$str;
}
?>