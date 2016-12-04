<?php
$xmldata = '<?xml version="1.0" encoding="utf-8"?><root><status>0</status><timestamp>2016/12/03 23:29:36</timestamp><barcode guid="2420">80398223895</barcode><barcode guid="2511">80398223918</barcode><barcode guid="2653">80398223921</barcode><mark>0</mark><PDF_A4_EN_URL>https://wishpost.wish.com/api/v2/download_label?access_token=50b16225937b4cdda4e5bdd317cc7f44&amp;format=A4&amp;use_local=false&amp;mark=0</PDF_A4_EN_URL><PDF_10_EN_URL>https://wishpost.wish.com/api/v2/download_label?access_token=50b16225937b4cdda4e5bdd317cc7f44&amp;format=10&amp;use_local=false&amp;mark=0</PDF_10_EN_URL><PDF_A4_LCL_URL>https://wishpost.wish.com/api/v2/download_label?access_token=50b16225937b4cdda4e5bdd317cc7f44&amp;format=A4&amp;use_local=true&amp;mark=0</PDF_A4_LCL_URL><PDF_10_LCL_URL>https://wishpost.wish.com/api/v2/download_label?access_token=50b16225937b4cdda4e5bdd317cc7f44&amp;format=10&amp;use_local=true&amp;mark=0</PDF_10_LCL_URL></root>';

$xmlresult = simplexml_load_string($xmldata);
$barcodes = array();
foreach ($xmlresult as $key=>$value){
	if(strcmp($key,'barcode') == 0){
		$att = $value->attributes();
		$guid = $att[0];
		$trackingnumber = $value;
		echo "<br/> get barcode:".$guid.':'.$trackingnumber;;
		$barcodes[(string)$guid] = $trackingnumber;
		$barcodes['test']='tst1';
	}
}
var_dump($barcodes);


