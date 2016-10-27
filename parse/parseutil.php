<?php
namespace parse;

include dirname ( '__FILE__' ) . './model/aliproduct.php';
include dirname ( '__FILE__' ) . './parse/simple_html_dom.php';

use parse\simple_html_dom;
use model\aliproduct;

define('VAR_COLOR','Color:');
define('VAR_SIZE','Size:');
class parseutil{
	
	function getProductFromali($url){
		$dom = \parse\file_get_html($url);
		if($dom != null){
			$product = new aliproduct();	
			 
			$product->url = $url;
			$title = $dom->find('h1[itemprop=name]',0);
			if($title != null)
				$product->title = $title->plaintext;
			
			$skuobj = $dom->find('li[id=product-prop-3]',0);
			if($skuobj != null)
				$product->SKU = $skuobj->getAttribute('data-title');
			
			$keywords = $dom->find('meta[name=keywords]',0);
			if($keywords != null)
				$product->keywords = $keywords->content;
			
			
			$mainphoto = $dom->find('meta[property=og:image]',0);
			if($mainphoto != null)
				$product->mainphoto = $mainphoto->content;
			
			$storeinfo = $dom->find('span.shop-name a',0);
			if($storeinfo != null){
				$product->storeurl = $storeinfo->href;
				$product->storename = $storeinfo->plaintext;
			}
			
			$productpriceobj = $dom->find('span[id=j-sku-price]',0);
			if($productpriceobj != null)
				$product->fullprice = $productpriceobj->plaintext;
			
			$productactualPrice = $dom->find('span[itemprop=price]',0);
			$productactuallowPrice = $dom->find('span[itemprop=lowPrice]',0);
			$productactualhighPrice = $dom->find('span[itemprop=highPrice]',0);
			if($productactualPrice != null)
				$product->discountprice = $productactualPrice->plaintext;
			if($productactuallowPrice != null)
				$product->lowprice = $productactuallowPrice->plaintext;
			if($productactualhighPrice != null)
				$product->highprice = $productactualhighPrice->plaintext;
			
			$productVars = $dom->find('div[id=j-product-info-sku]',0);
			
			if($productVars != null){
				$vars = $productVars->children;
					
				for($k=0;$k<count($vars);$k++){
					$tempobj = $productVars->children($k);
						
					$tempdom = \parse\str_get_html($tempobj->innertext);
					if(tempdom != null){
						$temptitleobj = $tempdom->find('dt[class=p-item-title]',0);
						$temptitle = $temptitleobj->plaintext;
							
						if(strcmp($temptitle,VAR_COLOR) == 0){
							$tempvars = $tempdom->find('a[data-role=sku]');
							for($v=0;$v<count($tempvars);$v++){
								$vtitle = $tempvars[$v]->title;
								if($vtitle != null && trim($vtitle) != ''){
									$product->colors .= trim($vtitle)."|";
								}
							}
						}
						
						$varphotos = $tempdom->find('img');
						if($varphotos != null){
							for($p=0;$p<count($varphotos);$p++){
								$bigpic = $varphotos[$p]->bigpic;
								if($bigpic != null && trim($bigpic) != ''){
									$product->varphotos .= trim($bigpic)."|&#xd;";
								}
							}
						}
							
						if(strcmp($temptitle,VAR_SIZE) == 0){
							$tempvars = $tempdom->find('a[data-role=sku]');
							for($v=0;$v<count($tempvars);$v++){
								$tempvalue = $tempvars[$v]->plaintext;
								if($tempvalue != null && trim($tempvalue) != '')
									$product->sizes .= trim($tempvalue)."|";
							}
						}
							
							
						$tempdom->clear();
					}else{
						echo "<br/>tempdom is null";
					}
				}	
			}
			
			$galleryphotos = $dom->find('span.img-thumb-item img');
		
			if($galleryphotos != null)
				for($g=0;$g<count($galleryphotos);$g++){
					$tempgallery = $galleryphotos[$g];
					$tempimagesrc= $tempgallery->src;
				
					$product->galleryphotos .= rtrim($tempimagesrc,'_50x50.jpg').'.jpg|&#xd;';
				}
			
			$scriptcode = $dom->find('script');
			$querystring = 'window.runParams.descUrl';
			$desurl = null;
			if($scriptcode != null)
				for($s=0;$s<count($scriptcode);$s++){
					$tempscript = $scriptcode[$s];
				
					$pos = strpos($tempscript->innertext, $querystring);
					if($pos > 0){
						$innertextvalue = $tempscript->innertext;
						$explodearray = explode(";",$innertextvalue);
				
						for($e=0;$e<count($explodearray);$e++){
							$epos = strpos($explodearray[$e],$querystring);
							if($epos>0){
								$temp = $explodearray[$e];
								$firstmark = strpos($temp,"\"");
								$lastmark = strrpos($temp,"\"");
								$desurl = substr($temp,$firstmark+3,$lastmark-strlen($temp));
								break;
							}
						}
						break;
					}
				}
			
			if($desurl != null){
				$curl = curl_init ();
				$options [CURLOPT_URL] = $desurl;
				$options[CURLOPT_RETURNTRANSFER]=1;
				curl_setopt_array ( $curl, $options );
				$result = curl_exec ( $curl );
				curl_close($curl);
				$result = trim($result);
				$deshtml = substr($result,(strpos($result,"=")+2),-2);
					
				$desdom = \parse\str_get_html($deshtml);
					
				if($desdom != null){
					$product->description = $desdom->plaintext;
					$product->descriptionhtml = $desdom->innertext;
					
					$extraimages = array();
					foreach($desdom->find('img') as $element){
						list ( $width, $height, $type )  = getimagesize($element->src );
						if($width>400 && $height>400){
							$product->extraphotos .= $element->src."|&#xd;";
						}
					}
				}
			}

			return $product; 
		}else{
			return null;
		}
	}
	
	
	function printarray($destarray){
		foreach($destarray as $key=>$value){
			if(is_array($value)){
				$this->printarray($value);
			}else{
				if (is_string ($value)){
					echo '['.$key."]=>".$value.'<br/>';
				}else{
					echo '['.$key."]=>".'<br/>';
					var_dump($value);
					echo '<br/>******************************************************************************<br/>';
				}
			}
		}
	}
	function getProductFromwish($wishid,$username) {
		$wishurl = 'https://www.wish.com/c/'.$wishid;
		
		$wishdom = \parse\file_get_html($wishurl);

		if($wishdom != null){

			$product = new aliproduct();
			
			$product->url = $wishurl;
			
			$title = $wishdom->find('meta[property=og:title]',0);
			if($title != null)
				$product->title = $title->content;
				
			
			$scriptcode = $wishdom->find('script');
			$querystring = 'mainContestObj';
			$desurl = null;
			if($scriptcode != null)
				for($s=0;$s<count($scriptcode);$s++){
				$tempscript = $scriptcode[$s];
					
				$pos = strpos($tempscript->innertext, $querystring);
				if($pos > 0){
					$innertextvalue = $tempscript->innertext;
					$pos = strpos($innertextvalue,'{');
					$lpos = strrpos($innertextvalue,'}');
					
					$jsonstr = substr($innertextvalue,$pos,$lpos-$pos + 1);
					$jsonarray = json_decode($jsonstr);
					
					$this->printarray($jsonarray);
					/* 
					for($l=0;$l<count($jsonarray);$l++){
						echo "<br/>";
						var_dump($jsonarray[$l]);
					} */
					break;
				}
			}
			
			$pre = substr($username,0,3);
			$sku = $pre.$jsonarray['remarket_tag']->id.substr($jsonarray['manufacturer_id'],0,4);
				
			$product->keywords = $jsonarray['keywords'];
				
				
			$mainminphoto = $jsonarray['small_picture'];
			$splitpos = strrpos($mainminphoto,'-');
			$product->mainphoto = substr($mainminphoto,0,$splitpos).'-big.jpg';
				
			$product->storename = $jsonarray['merchant'];
			/* 	
			$productpriceobj = $dom->find('span[id=j-sku-price]',0);
			if($productpriceobj != null)
				$product->fullprice = $productpriceobj->plaintext;
				
			$productactualPrice = $dom->find('span[itemprop=price]',0);
			$productactuallowPrice = $dom->find('span[itemprop=lowPrice]',0);
			$productactualhighPrice = $dom->find('span[itemprop=highPrice]',0);
			if($productactualPrice != null)
				$product->discountprice = $productactualPrice->plaintext;
			if($productactuallowPrice != null)
				$product->lowprice = $productactuallowPrice->plaintext;
			if($productactualhighPrice != null)
				$product->highprice = $productactualhighPrice->plaintext;
				
			$productVars = $dom->find('div[id=j-product-info-sku]',0);
				
			if($productVars != null){
				$vars = $productVars->children;
					
				for($k=0;$k<count($vars);$k++){
					$tempobj = $productVars->children($k);
			
					$tempdom = \parse\str_get_html($tempobj->innertext);
					if(tempdom != null){
						$temptitleobj = $tempdom->find('dt[class=p-item-title]',0);
						$temptitle = $temptitleobj->plaintext;
							
						if(strcmp($temptitle,VAR_COLOR) == 0){
							$tempvars = $tempdom->find('a[data-role=sku]');
							for($v=0;$v<count($tempvars);$v++){
								$vtitle = $tempvars[$v]->title;
								if($vtitle != null && trim($vtitle) != ''){
									$product->colors .= trim($vtitle)."|";
								}
							}
						}
			
						$varphotos = $tempdom->find('img');
						if($varphotos != null){
							for($p=0;$p<count($varphotos);$p++){
								$bigpic = $varphotos[$p]->bigpic;
								if($bigpic != null && trim($bigpic) != ''){
									$product->varphotos .= trim($bigpic)."|&#xd;";
								}
							}
						}
							
						if(strcmp($temptitle,VAR_SIZE) == 0){
							$tempvars = $tempdom->find('a[data-role=sku]');
							for($v=0;$v<count($tempvars);$v++){
								$tempvalue = $tempvars[$v]->plaintext;
								if($tempvalue != null && trim($tempvalue) != '')
									$product->sizes .= trim($tempvalue)."|";
							}
						}
							
							
						$tempdom->clear();
					}else{
						echo "<br/>tempdom is null";
					}
				}
			}
				
			$galleryphotos = $dom->find('span.img-thumb-item img');
			
			if($galleryphotos != null)
				for($g=0;$g<count($galleryphotos);$g++){
				$tempgallery = $galleryphotos[$g];
				$tempimagesrc= $tempgallery->src;
			
				$product->galleryphotos .= rtrim($tempimagesrc,'_50x50.jpg').'.jpg|&#xd;';
			}
				
			$scriptcode = $dom->find('script');
			$querystring = 'window.runParams.descUrl';
			$desurl = null;
			if($scriptcode != null)
				for($s=0;$s<count($scriptcode);$s++){
				$tempscript = $scriptcode[$s];
			
				$pos = strpos($tempscript->innertext, $querystring);
				if($pos > 0){
					$innertextvalue = $tempscript->innertext;
					$explodearray = explode(";",$innertextvalue);
			
					for($e=0;$e<count($explodearray);$e++){
						$epos = strpos($explodearray[$e],$querystring);
						if($epos>0){
							$temp = $explodearray[$e];
							$firstmark = strpos($temp,"\"");
							$lastmark = strrpos($temp,"\"");
							$desurl = substr($temp,$firstmark+3,$lastmark-strlen($temp));
							break;
						}
					}
					break;
				}
			}
				
			if($desurl != null){
				$curl = curl_init ();
				$options [CURLOPT_URL] = $desurl;
				$options[CURLOPT_RETURNTRANSFER]=1;
				curl_setopt_array ( $curl, $options );
				$result = curl_exec ( $curl );
				curl_close($curl);
				$result = trim($result);
				$deshtml = substr($result,(strpos($result,"=")+2),-2);
					
				$desdom = \parse\str_get_html($deshtml);
					
				if($desdom != null){
					$product->description = $desdom->plaintext;
					$product->descriptionhtml = $desdom->innertext;
						
					$extraimages = array();
					foreach($desdom->find('img') as $element){
						list ( $width, $height, $type )  = getimagesize($element->src );
						if($width>400 && $height>400){
							$product->extraphotos .= $element->src."|&#xd;";
						}
					}
				}
			} */
			
			return $product;
			}else{
				return null;
			} 
		
		/* $scriptcode = $wishdom->find('script');
		$querystring = 'mainContestObj';
		$desurl = null;
		if($scriptcode != null)
			for($s=0;$s<count($scriptcode);$s++){
			$tempscript = $scriptcode[$s];
		
			$pos = strpos($tempscript->innertext, $querystring);
			if($pos > 0){
				
				$innertextvalue = $tempscript->innertext;
				$pos = strpos($innertextvalue,'{');
				$lpos = strrpos($innertextvalue,'}');
				
				$jsonstr = substr($innertextvalue,$pos,$lpos-$pos + 1);
				$jsonarray = json_decode($jsonstr);
				echo "<br/><br/>content:";
				echo $jsonstr;
				echo "<br/><br/><br/><br/><br/>JSON Array:<br/>";
				print_r($jsonarray);
				/*$explodearray = explode(",",$innertextvalue);
		
				for($e=0;$e<count($explodearray);$e++){
					echo "CONTENT:".$explodearray[$e].'<br/>';
					/* $epos = strpos($explodearray[$e],$querystring);
					 if($epos>0){
					 $temp = $explodearray[$e];
					 $firstmark = strpos($temp,"\"");
					 $lastmark = strrpos($temp,"\"");
					 $desurl = substr($temp,$firstmark+3,$lastmark-strlen($temp));
					 break;
					 } */
					/*}
					 break; */
					 /*}
						} */
		}
	
		/* 	$scriptcode = $wishdom->find('script');
			$querystring = 'mainContestObj';
			$desurl = null;
			if($scriptcode != null)
				for($s=0;$s<count($scriptcode);$s++){
					$tempscript = $scriptcode[$s];
				
					$pos = strpos($tempscript->innertext, $querystring);
					if($pos > 0){
						$innertextvalue = $tempscript->innertext;
						$explodearray = explode(",",$innertextvalue);
				
						for($e=0;$e<count($explodearray);$e++){
							echo "CONTENT:".$explodearray[$e].'<br/>';
							/* $epos = strpos($explodearray[$e],$querystring);
							if($epos>0){
								$temp = $explodearray[$e];
								$firstmark = strpos($temp,"\"");
								$lastmark = strrpos($temp,"\"");
								$desurl = substr($temp,$firstmark+3,$lastmark-strlen($temp));
								break;
							} */
						/*}
						break;
					}
				}
	} */
}
/*
[commerce_product_info]=>
object(stdClass)#276 (12) 
{ 
["fbw_pending"]=> int(0) 
["sizing_chart_url"]=> string(60) "https://www.wish.com/m/sizing_chart/57c68911cdd60e1f7fe91686" 
["is_fulfill_by_wish"]=> bool(false) 
["product_badges"]=> array(0) { } 
["logging_fields"]=> object(stdClass)#277 (2) { 
	["log_product_id"]=> string(24) "57c68911cdd60e1f7fe91686" 
	["log_type"]=> string(7) "product" 
	} 
["variations"]=> array(40) { 
	[0]=> object(stdClass)#278 (48) { 
		["original_price"]=> int(3) 
		["return_policy_long"]=> string(866) "We want you to be completely satisfied with your purchase on Wish. You may return all products within 30 days of delivery. You can initiate a return or a refund on items from your Order History page. Simply click on 'Contact Support' next to the item you wish to request a return or a refund and Wish Support will be ready to assist. We aim to process all requests within 72 hours upon receiving. Refunds are issued back to the original form of payment used to purchase the order. In the event that a Wish gift card is applied to an order and that order is refunded for any reason, any gift cards used in that order will not be refunded. At this time, Wish cannot refund, reimburse, cover, or otherwise be responsible for any fees not paid to Wish. This includes any customs taxes or VAT as well as any return shipping costs you may incur in the refund process." 
		["shipping_before_personal_price"]=> float(2) 
		["is_fulfill_by_wlc"]=> bool(false) 
		["merchant_rating_count"]=> int(4725) 
		["color"]=> string(5) "Black" 
		["max_fulfillment_time"]=> int(3) 
		["variation_id"]=> string(24) "57c6891103fe8949f7cea5ed" 
		["min_fulfillment_time"]=> int(1) 
		["max_shipping_time"]=> int(28) 
		["shipping_price_country_code"]=> string(2) "US" 
		["min_shipping_time"]=> int(18) 
		["ships_from"]=> string(8) "Overseas" 
		["size"]=> string(1) "M" 
		["merchant_id"]=> string(24) "567bad8582c35f28103e5813" 
		["merchant_dp"]=> string(82) "https://s3-us-west-1.amazonaws.com/sweeper-production-merchantimage/default_dp.png" 
		["is_fulfill_by_wish"]=> bool(false) 
		["max_delivery_time"]=> int(25) 
		["localized_price_before_personal_price"]=> object(stdClass)#279 (4) { 
			["localized_value"]=> int(4) 
			["table_id"]=> string(24) "57ffaf9b90b8a64909122875" 
			["symbol"]=> string(1) "$" 
			["currency_code"]=> string(3) "USD" 
			} 
		["inventory"]=> int(8297) 
		["original_shipping"]=> int(2) 
		["merchant_rating_class"]=> string(13) "star star-4-0" 
		["manufacturer_id"]=> string(12) "B62470102FGA" 
		["merchant_name"]=> string(25) "guangzhoufashiongirlcoltd" 
		["merchant"]=> string(30) "Guangzhou Fashion Girl co.,ltd" 
		["use_pretty_localized_shipping"]=> bool(false) 
		["return_policy_short"]=> string(55) "You may return all products within 30 days of delivery." 
		["price"]=> int(4) 
		["min_time_to_door"]=> int(18) 
		["sequence_id"]=> int(0) 
		["size_ordering"]=> int(3) 
		["is_self_serve_fulfill_by_wish"]=> bool(false) 
		["new_with_tags"]=> bool(false) 
		["retail_price"]=> int(45) 
		["localized_price"]=> object(stdClass)#280 (4) { 
			["localized_value"]=> int(4) 
			["table_id"]=> string(24) "57ffaf9b90b8a64909122875" 
			["symbol"]=> string(1) "$" 
			["currency_code"]=> string(3) "USD" 
			} 
		["shipping_time_string"]=> string(15) "Oct 31 - Nov 10" 
		["merchant_rating"]=> float(4.226) 
		["price_before_personal_price"]=> int(4) 
		["product_id"]=> string(24) "57c68911cdd60e1f7fe91686" 
		["localized_shipping"]=> object(stdClass)#281 (4) { 
			["localized_value"]=> float(2) 
			["table_id"]=> string(24) "57ffaf9b90b8a64909122875" 
			["symbol"]=> string(1) "$" 
			["currency_code"]=> string(3) "USD" 
			} 
		["localized_retail_price"]=> object(stdClass)#282 (4) { 
			["localized_value"]=> int(45) 
			["table_id"]=> string(24) "57ffaf9b90b8a64909122875" 
			["symbol"]=> string(1) "$" 
			["currency_code"]=> string(3) "USD" 
			} 
		["min_delivery_time"]=> int(15) 
		["removed"]=> bool(false) 
		["enabled"]=> bool(true) 
		["max_time_to_door"]=> int(28) 
		["shipping"]=> float(2) 
		["shipping_countries_string"]=> string(45) "Ships to United States and 61 other countries" 
		["is_c2c"]=> bool(false) 
		} 
	[1]=> object(stdClass)#283 (38) 
 * */