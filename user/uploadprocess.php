<?php
use mysql\dbhelper;
include_once dirname ( '__FILE__' ) . './mysql/dbhelper.php';
header ( "Content-Type: text/html;charset=utf-8" );

$filename = $_FILES ['file'] ['tmp_name'];

$name     = basename($_FILES ['file']['name']);
$extension = pathinfo($name, PATHINFO_EXTENSION);

session_start ();
$currentUserid = $_SESSION ['userid'];
session_commit();

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
}else if(strcasecmp($extension,'xls') == 0 || strcasecmp($extension,'xlsx') == 0){
	$result = $name;
	/** PHPExcel_IOFactory */
	include_once dirname ( '__FILE__' ) . './PHPExcel/Classes/PHPExcel/IOFactory.php';
	include_once dirname ( '__FILE__' ) . './PHPExcel/Classes/PHPExcel/Exception.php';
	
	
	if(strcasecmp($extension,'xlsx') == 0){
		$reader = PHPExcel_IOFactory::createReader('Excel2007');
		$objPHPExcel = $reader->load($filename);
	}else{
		$objPHPExcel = PHPExcel_IOFactory::load($filename);
	}
	try {
		$scount = $objPHPExcel->getSheetCount();
		if($scount <=0){
			$result .= "ERROR: no sheet, parse xls faild, you can save this file as xls 2003 and try it again";
		}else{ 
			$sheet = $objPHPExcel->getActiveSheet();
			$rows = $sheet->getHighestDataRow();
			$columns = $sheet->getHighestDataColumn();
			$columnMaxIndex = PHPExcel_Cell::columnIndexFromString($columns);
		}
	} catch (PHPExcel_Exception $e) {
		$result .= "<br/> ERROR:".$e;;
	}
	
	$result .= " max column index:".$columnMaxIndex.',rows:'.$rows.' columns:'.$columns;
	if($columnMaxIndex == 8){//飞宇
	
		for($row = 0;$row<=$rows;$row ++){
			if($column == 0){
				$orderdate = $sheet->getCellByColumnAndRow($column,$row)->getValue();
				if(!is_numeric($orderdate) && !checkDatetime($orderdate))
					continue;
			}
			for($column = 0;$column<=$columnMaxIndex;$column ++){
				
				switch ($column){
					case 0:
						$orderdate = $sheet->getCellByColumnAndRow($column,$row)->getValue(); 
						break;
					case 1:					
						$trackingdata = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 2:
						$destinate = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 3:
						$weight = 1000 * $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 4:
						$shippingcost = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 5:
						$offprice = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
				}
			}
			if(isset($trackingdata) && isset($destinate) && isset($weight) && isset($shippingcost) && isset($offprice)){
				if(isset($orderdate)){
					if(is_numeric($orderdate)){
						$orderdateformat = intval(($orderdate - 25569) * 3600 * 24); //转换成1970年以来的秒数
						$orderdate = date('Ymd',$orderdateformat);
					}else if(checkDatetime($orderdate)){
						$orderdate = date('Ymd',strtotime($orderdate));
					}
				}
				$updateResult = $dbhelper->updateTrackingData($trackingdata, $destinate, $weight, $shippingcost, $offprice*$shippingcost,$orderdate,$currentUserid);
				$result .= $row.$trackingdata."行完成;".$updateResult."  |";
			}else{
				$result .= $row."行没数据"; 
			}
		}
		
	}else if($columnMaxIndex == 5){//读取WishPost首页sheet
		
		$sheetcount = $objPHPExcel->getSheetCount();
		
		$result .= "current sheetcount:".$sheetcount;
		for($index = 1;$index<$sheetcount;$index++){
			
			$result .= " current sheet  ".$index.":  ";
			$activesheet = $objPHPExcel->getSheet($index);
			
			$rows = $activesheet->getHighestDataRow();
			$columns = $activesheet->getHighestDataColumn();
			$columnMaxIndex = PHPExcel_Cell::columnIndexFromString($columns);
			
			$orderdate = $activesheet->getCellByColumnAndRow($columnMaxIndex,0)->getValue();
			for($row = 0;$row<=$rows;$row ++){
				if($column == 0){
					$curValue = $sheet->getCellByColumnAndRow($column,$row)->getValue();
					if(!is_numeric($curValue))
						continue;
				}
				for($column = 0;$column<=$columnMaxIndex;$column ++){
			
					switch ($column){
						case 2:
							$trackingdata = $activesheet->getCellByColumnAndRow($column,$row)->getValue();
							break;
						case 3:
							$destinate = $activesheet->getCellByColumnAndRow($column,$row)->getValue();
							break;
						case 4:
							$weight = 1000 * $activesheet->getCellByColumnAndRow($column,$row)->getValue();
							break;
						case 5:
							$shippingcost = $activesheet->getCellByColumnAndRow($column,$row)->getValue();
							break;
					}
				}
				if(isset($trackingdata) && isset($destinate) && isset($weight) && isset($shippingcost)){
					if(isset($orderdate)){
						if(is_numeric($orderdate)){
							$orderdateformat = intval(($orderdate - 25569) * 3600 * 24); //转换成1970年以来的秒数
							$orderdate = date('Ymd',$orderdateformat);
						}else if(checkDatetime($orderdate)){
							$orderdate = date('Ymd',strtotime($orderdate));
						}
					}
					$updateResult = $dbhelper->updateTrackingData($trackingdata, $destinate, $weight, $shippingcost, $shippingcost,$orderdate,$currentUserid);
					$result .= $row.$trackingdata."行完成;".$updateResult."  |";
				}else{
					$result .= $row."行没数据";
				}
			}
		}
	}else if($columnMaxIndex >= 12 && $columnMaxIndex<=15){//Yanwen
		for($row = 0;$row<=$rows;$row ++){
			if($column == 0){
				$orderdate = $sheet->getCellByColumnAndRow($column,$row)->getValue();
				if(!checkDatetime($orderdate))
					continue;
			}
			for($column = 0;$column<=$columnMaxIndex;$column ++){
			
				switch ($column){
					case 0:
						$orderdate = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 1:
						$trackingdata = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 5:
						$destinate = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 6:
						$weight = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 9:
						$shippingcost = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 12:
						$finalcost = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
				}
			}
			if(isset($trackingdata) && isset($destinate) && isset($weight) && isset($shippingcost) && isset($finalcost)){
				if(isset($orderdate)){
					$orderdate = date('Ymd',strtotime($orderdate));
				}
				$updateResult = $dbhelper->updateTrackingData($trackingdata, $destinate, $weight, $shippingcost, $finalcost,$orderdate,$currentUserid);
				$result .= $row.$trackingdata."行完成;".$updateResult."  |";
			}else{
				$result .= $row."行没数据"; 
			}	
		}
	}else if($columnMaxIndex == 44){//refund orders xls.
		$result .= " process refund orders ";
		for($row = 0;$row<=$rows;$row ++){
			
			$refundorder = array();
			$refundorder['accountid'] = $accountid;
			for($column = 0;$column<=$columnMaxIndex;$column ++){
				switch ($column){
					case 0:
						$refundorder['TransactionDate'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 1:
						$refundorder['OrderID'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 2:
						$refundorder['TransactionID'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 3:
						$refundorder['OrderState'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 4:
						$refundorder['SKU'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 5:
						$refundorder['Product'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();		
						break;
					case 6:
						$refundorder['ProductID'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 7:
						$refundorder['ProductLink'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 8:
						$refundorder['ProductImageURL'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 9:
						$refundorder['Size'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 10:
						$refundorder['Color'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 11:
						$refundorder['Price'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 12:
						$refundorder['Cost'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 13:
						$refundorder['Shipping'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 14:
						$refundorder['ShippingCost'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 15:
						$refundorder['Quantity'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 16:
						$refundorder['TotalCost'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 17:
						$refundorder['DaystoFulfill'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 18:
						$refundorder['HourstoFulfill'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 19:
						$refundorder['ShippedDate'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 20:
						$refundorder['ConfirmedDelivery'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 21:
						$refundorder['Provider'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 22:
						$refundorder['Tracking'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 23:
						$refundorder['TrackingConfirmed'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 24:
						$refundorder['TrackingConfirmedDate'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 25:
						$refundorder['ShippingAddress'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 26:
						$refundorder['Name'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 27:
						$refundorder['FirstName'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 28:
						$refundorder['LastName'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 29:
						$refundorder['StreetAddress1'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 30:
						$refundorder['StreetAddress2'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 31:
						$refundorder['City'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 32:
						$refundorder['State'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 33:
						$refundorder['Zipcode'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 34:
						$refundorder['Country'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 35:
						$refundorder['LastUpdated'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 36:
						$refundorder['PhoneNumber'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 37:
						$refundorder['Countrycode'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 38:
						$refundorder['RefundResponsibility'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 39:
						$refundorder['RefundAmount'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 40:
						$refundorder['RefundDate'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 41:
						$refundorder['RefundReason'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 42:
						$refundorder['IsWishExpress'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
					case 43:
						$refundorder['WishExpressDeliveryDeadline'] = $sheet->getCellByColumnAndRow($column,$row)->getValue();
						break;
				}
			}
			if($refundorder['accountid'] != null && $refundorder['OrderID']!= null && strlen($refundorder['OrderID'])>10){
				
				if($refundorder['TransactionDate'] != null)
					$refundorder['TransactionDate'] = exchangeTimeFormat($refundorder['TransactionDate']);
				if($refundorder['ShippedDate'] != null)
					$refundorder['ShippedDate'] = exchangeTimeFormat($refundorder['ShippedDate']);
				if($refundorder['TrackingConfirmedDate'] != null)
					$refundorder['TrackingConfirmedDate'] = exchangeTimeFormat($refundorder['TrackingConfirmedDate']);
				if($refundorder['LastUpdated'] != null)
					$refundorder['LastUpdated'] = exchangeTimeFormat($refundorder['LastUpdated']);
				if($refundorder['RefundDate'] != null)
					$refundorder['RefundDate'] = exchangeTimeFormat($refundorder['RefundDate']);
				$insertResult = $dbhelper->insertRefundRecord($refundorder);
				$result .= $row."行完成;".$insertResult."  |";
			}else{
				$result .= $row."行没数据";
			}
		}
	}
	
	$objPHPExcel->disconnectWorksheets();
	
	$length = strlen($result);
	if($length>2048){
		$result = substr($result,0,2048).'...';
	}
	header ( "Location:./csvupload.php?msg=".$result);
	exit ();
}

// curdate format: 12-28-2014
function exchangeTimeFormat($curdate){
	$tempY = substr($curdate,6);
	$tempM = substr($curdate,0,2);
	$tempD = substr($curdate,3,2);
	return $tempY.$tempM.$tempD;
}
function excelTime($date, $time = false) {
	if(function_exists('GregorianToJD')){
		if (is_numeric( $date )) {
			$jd = GregorianToJD( 1, 1, 1970 );
			$gregorian = JDToGregorian( $jd + intval ( $date ) - 25569 );
			$date = explode( '/', $gregorian );
			$date_str = str_pad( $date [2], 4, '0', STR_PAD_LEFT )
			."-". str_pad( $date [0], 2, '0', STR_PAD_LEFT )
			."-". str_pad( $date [1], 2, '0', STR_PAD_LEFT )
			. ($time ? " 00:00:00" : '');
			return $date_str;
		}
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