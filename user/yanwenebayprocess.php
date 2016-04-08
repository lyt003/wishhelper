<?php
use mysql\dbhelper;
include_once dirname ( '__FILE__' ) . './mysql/dbhelper.php';
header ( "Content-Type: text/html;charset=utf-8" );
$filename = $_FILES ['file'] ['tmp_name'];

$accountid = '0';

$dbhelper = new dbhelper();
$index = 0;
$isProduct = 0;
$csvfile = fopen($filename,'r');
$result;
$end;
while ($data = fgetcsv($csvfile)) {
	if($index == 0){
		if(strcmp('	Sales Record Number',$data[0]) != 0){
			$result = "文件格式错误";
			header ( "Location:./yanwenebay.php?msg=".$result);
			exit();
		}
		$index++;
		continue;
	}
	
	if($data[10] != null && strcmp($data[10],"") !=0){
		$dbhelper->insertEbayOrder($accountid,$data);
	}
	$index++;
}
fclose($csvfile);
$result = 'success';
header ( "Location:./yanwenebay.php?msg=".$result);
exit ();
?>