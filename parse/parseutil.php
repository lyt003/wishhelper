<?php
namespace parse;

include dirname ( '__FILE__' ) . './model/aliproduct.php';
include dirname ( '__FILE__' ) . './parse/simple_html_dom.php';

use parse\simple_html_dom;
use model\aliproduct;

define('VAR_COLOR','Color:');
define('VAR_SIZE','Size:');
class parseutil{
	
	function getProduct($url){
		$dom = file_get_html($url);
		if($dom != null){
			$product = new aliproduct();	

			$product->url = $url;
			$title = $dom->find('h1[itemprop=name]',0);
			$product->title = $title->plaintext;
			
			$skuobj = $dom->find('li[id=product-prop-3]',0);
			$product->SKU = $skuobj->getAttribute('data-title');
			
			$keywords = $dom->find('meta[name=keywords]',0);
			$product->keywords = $keywords->content;
			
			
			$mainphoto = $dom->find('meta[property=og:image]',0);
			$product->mainphoto = $mainphoto->content;
			
			$storeinfo = $dom->find('span.shop-name a',0);
			$product->storeurl = $storeinfo->href;
			$product->storename = $storeinfo->plaintext;
			
			$productpriceobj = $dom->find('span[id=j-sku-price]',0);
			$product->fullprice = $productpriceobj->plaintext;
			
			$productactualPrice = $dom->find('span[itemprop=price]',0);
			$productactuallowPrice = $dom->find('span[itemprop=lowPrice]',0);
			$productactualhighPrice = $dom->find('span[itemprop=highPrice]',0);
			$product->discountprice = $productactualPrice->plaintext;
			$product->lowprice = $productactuallowPrice->plaintext;
			$product->highprice = $productactualhighPrice->plaintext;
			
			$productVars = $dom->find('div[id=j-product-info-sku]',0);
			
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
								$product->colors .= $vtitle."|";
							}
								
						}
					}
			
					if(strcmp($temptitle,VAR_SIZE) == 0){
						$tempvars = $tempdom->find('a[data-role=sku]');
						for($v=0;$v<count($tempvars);$v++){
							$tempvalue = $tempvars[$v]->plaintext;
							if($tempvalue != null && trim($tempvalue) != '')
								$product->sizes .= $tempvalue;
						}
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
			
				$product->galleryphotos .= rtrim($tempimagesrc,'_50x50.jpg').'.jpg|';
			}
			
			$scriptcode = $dom->find('script');
			$querystring = 'window.runParams.descUrl';
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
			
			$curl = curl_init ();
			$options [CURLOPT_URL] = $desurl;
			$options[CURLOPT_RETURNTRANSFER]=1;
			curl_setopt_array ( $curl, $options );
			$result = curl_exec ( $curl );
			curl_close($curl);
			$result = trim($result);
			$deshtml = substr($result,(strpos($result,"=")+2),-2);
			
			$desdom = \parse\str_get_html($deshtml);
			
			$product->description = $desdom->plaintext;
			$product->descriptionhtml = $desdom->innertext;
			
			$extraimages = array();
			foreach($desdom->find('img') as $element){
				list ( $width, $height, $type )  = getimagesize($element->src );
				if($width>400 && $height>400){
					$product->extraphotos .= $element->src."|";
				}
			}
			
			return $product;
		}else{
			return null;
		}
	}
}