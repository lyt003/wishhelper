<?php
include dirname ( '__FILE__' ) . './parse/simple_html_dom.php';

use parse\simple_html_dom;
header ( "Content-Type: text/html;charset=utf-8" );
set_time_limit ( 0 );


$dom = \parse\file_get_html("https://www.aliexpress.com/item/High-waist-Faux-Leather-Skirt-Black-red-sexy-Pencil-skirts-middle-long-loose-Casual-mermaid/32218080368.html?spm=2114.01010108.3.1.FVve5X&ws_ab_test=searchweb201556_8,searchweb201602_3_10057_10066_10056_10065_10068_10055_10054_10069_10059_10058_10073_10017_10070_10060_10061_10052_10062_10053_10050_10051,searchweb201603_7&btsid=eaabc78b-0131-4df3-ad46-d2335ec53f4f");
//$dom = \parse\file_get_html("https://www.aliexpress.com/store/product/WenPod-Sport-X1-Wearable-Gopro-1-axis-Gimbal-Stabilizer-Smartphone-one-Axis-Gimbal-steadicam-mobile-mount/228291_32726169226.html?spm=a2g01.8047711.2169899.22.jLV3T2");

$title = $dom->find('title',0);
echo "<br/>title:".$title->plaintext;

$keywords = $dom->find('meta[name=keywords]',0);
echo "<br/>keywords:".$keywords->content;


$mainphoto = $dom->find('meta[property=og:image]',0);
echo "<br/>mainImage:".$mainphoto->content;
$mainphotourl = $mainphoto->content;
echo "<img src=\"".$mainphotourl."\"/>";

$storeinfo = $dom->find('span.shop-name a',0);
$storeinfourl = $storeinfo->href;
$storeinfoname = $storeinfo->plaintext;

echo "<br/>storename:".$storeinfoname.",storeurl=".$storeinfourl;


$productpriceobj = $dom->find('span[id=j-sku-price]',0);
echo "<br/>price:".$productpriceobj->plaintext;


$productactuallowPrice = $dom->find('span[itemprop=lowPrice]',0);
$productactualhighPrice = $dom->find('span[itemprop=highPrice]',0);
echo "<br/>actual low price:".$productactuallowPrice->plaintext;
echo "<br/>actual high price:".$productactualhighPrice->plaintext;


$productVars = $dom->find('div[id=j-product-info-sku]',0);

$vars = $productVars->children;

for($k=0;$k<count($vars);$k++){
	$tempobj = $productVars->children($k);
	echo "<br/> child:".$k;
	//echo "<br/>child text tag:".$tempobj->tag;
	//echo "<br/>child text outertext:".$tempobj->outertext;
	
	
	$tempdom = \parse\str_get_html($tempobj->innertext);
	if(tempdom != null){
		$temptitleobj = $tempdom->find('dt[class=p-item-title]',0);
		echo "<br/>temptitle:".$temptitleobj->plaintext;
		
		$tempvars = $tempdom->find('a[data-role=sku]');
		for($v=0;$v<count($tempvars);$v++){
			$temptitle = $tempvars[$v]->title;
			if($temptitle != null && trim($temptitle) != '')
				echo "<br/>var:".$temptitle;
			$tempvalue = $tempvars[$v]->plaintext;
			if($tempvalue != null && trim($tempvalue) != '')
				echo "<br/>val:".$tempvalue;
		}
		
		$tempdom->clear();
	}else{
		echo "<br/>tempdom is null";
	}
}

$galleryphotos = $dom->find('span.img-thumb-item img');
for($g=0;$g<count($galleryphotos);$g++){
	$tempgallery = $galleryphotos[$g];
	$tempimagesrc= $tempgallery->src;
	
	echo "<br/>thumbimage:".rtrim($tempimagesrc,'_50x50.jpg').'.jpg';
}

$scriptcode = $dom->find('script');
$querystring = 'window.runParams.descUrl';
for($s=0;$s<count($scriptcode);$s++){
	$tempscript = $scriptcode[$s];
	
	$pos = strpos($tempscript->innertext, $querystring);
	echo "<br/>".$s."    ".$pos;
	if($pos > 0){
		echo "<br/>script:".$tempscript->innertext;
		$innertextvalue = $tempscript->innertext;
		$explodearray = explode(";",$innertextvalue);
		
		for($e=0;$e<count($explodearray);$e++){
			echo "<br/>array:".$explodearray[$e];
			$epos = strpos($explodearray[$e],$querystring);
			if($epos>0){
				$temp = $explodearray[$e];
				$firstmark = strpos($temp,"\"");
				$lastmark = strrpos($temp,"\"");
				$desurl = substr($temp,$firstmark+3,$lastmark-strlen($temp));
				echo "<br/>desurl:".$desurl;
				break;
			}
		}
		break;
	}
}

echo "<br/>DESCRIPTION:<br/>";
$curl = curl_init ();
$options [CURLOPT_URL] = $desurl;
$options[CURLOPT_RETURNTRANSFER]=1;
curl_setopt_array ( $curl, $options );
$result = curl_exec ( $curl );
curl_close($curl);
//echo "result:".$result;
$result = trim($result);
$deshtml = substr($result,(strpos($result,"=")+2),-2);
//echo "<br/>deshtml:".$deshtml;

$desdom = \parse\str_get_html($deshtml);

echo "<br/>des plaintext:<br/>".$desdom->plaintext;

$extraimages = array();
foreach($desdom->find('img') as $element){
	echo $element->src . '<br>';
	list ( $width, $height, $type )  = getimagesize($element->src );
	if($width>400 && $height>400){
		$extraimages[] =$element->src; 
	}			
}

echo "<br/>extraimages:<br/>";
for($e=0;$e<count($extraimages);$e++){
	echo "<br/><img src=\"".$extraimages[$e]."\"/>";
}
//var_dump($extraimages);
	

