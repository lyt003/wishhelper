<?php
namespace wishpost;

include_once dirname ( '__FILE__' ) . './mysql/dbhelper.php';
include_once dirname ( '__FILE__' ) . './user/wconfig.php';
include_once dirname ( '__FILE__' ) . './model/ordersresult.php';
use mysql\dbhelper;
use model\ordersresult;

class Wishposthelper{
	
	private $dbhelper;
	
	public function __construct(){
		$this->dbhelper = new dbhelper();
	}
	
	public  function getWishPostNumbersForLabel($accountid){
		$wishnumbers = array();
		$wishorders = $this->dbhelper->getwishorders($accountid, ORDERSTATUS_APPLIEDTRACKING);
		while($curorder = mysql_fetch_array($wishorders)){
			if($curorder['tracking'] != null && $curorder['tracking']!= ''){
				echo "<br/>cur tracking:".$curorder['tracking'];
				$wishnumbers[] =  $curorder['tracking'];
			}
		}
		return $wishnumbers;
	}
	
	public function getWishPostAccounts($userid){
		$accountids = array();
		$result = $this->dbhelper->getWishpostaccounts($userid);
		while($wprow = mysql_fetch_array($result)){
			$accountids[] = (string)$wprow['accountid']; 
		}
		return $accountids;
	}
	public function createorders($accountid,$orders,$senderinfo){
		$access_token = $this->getWPAccessToken($accountid);
		$bid = substr ( 10000 * microtime ( true ), 6, 5 );
		$xmldata = $this->generateXML($orders, $access_token, 0, $bid, $senderinfo);
		
		$xmlresult = $this->execute("https://wishpost.wish.com/api/v2/create_order", $xmldata);
		$xmlresult = simplexml_load_string($xmlresult);
		$ordersresult = new ordersresult();
		$ordersresult->status = (string)$xmlresult->status;
		$ordersresult->timestamp = (string)$xmlresult->timestamp;
		$ordersresult->mark = (string)$xmlresult->mark;
		$ordersresult->PDF_A4_EN_URL = (string)$xmlresult->PDF_A4_EN_URL;
		$ordersresult->PDF_10_EN_URL = (string)$xmlresult->PDF_10_EN_URL;
		$ordersresult->PDF_A4_LCL_URL = (string)$xmlresult->PDF_A4_LCL_URL;
		$ordersresult->PDF_10_LCL_URL = (string)$xmlresult->PDF_10_LCL_URL;
		$barcodes = array();
		foreach ($xmlresult as $key=>$value){
			if(strcmp($key,'barcode') == 0){
				$att = $value->attributes();
				$guid = (string)$att[0];
				$trackingnumber = $value;
				$barcodes[$guid] = $trackingnumber;
			}
		}
		
		$ordersresult->barcodes = $barcodes;
		//$PDF_15_EN_URL,$recipient_country,$recipient_country_short,$c_code,$q_code,$y_code,$user_desc;
		if($xmlresult->PDF_15_EN_URL!= null)
			$ordersresult->PDF_15_EN_URL = (string)$xmlresult->PDF_15_EN_URL;
		if($xmlresult->recipient_country!= null)
			$ordersresult->recipient_country = (string)$xmlresult->recipient_country;
		if($xmlresult->recipient_country_short!= null)
			$ordersresult->recipient_country_short = (string)$xmlresult->recipient_country_short;
		if($xmlresult->c_code!= null)
			$ordersresult->c_code = (string)$xmlresult->c_code;
		if($xmlresult->q_code!= null)
			$ordersresult->q_code = (string)$xmlresult->q_code;
		if($xmlresult->y_code!= null)
			$ordersresult->y_code = (string)$xmlresult->y_code;
		if($xmlresult->user_desc!= null)
			$ordersresult->user_desc = (string)$xmlresult->user_desc;
		echo "<br/> return orders";
		return $ordersresult;	
		/*
		 * <?xml version="1.0" encoding="utf-8"?>
		 <root>
		 <status>0</status>
		 <timestamp>2016/12/03 23:29:36</timestamp>
		
		 <barcode guid="0">80398223895</barcode>
		 <barcode guid="1">80398223918</barcode>
		 <barcode guid="2">80398223921</barcode>
		 <mark>0</mark>
		 <PDF_A4_EN_URL>https://wishpost.wish.com/api/v2/download_label?access_token=50b16225937b4cdda4e5bdd317cc7f44&amp;format=A4&amp;use_local=false&amp;mark=0</PDF_A4_EN_URL>
		 <PDF_10_EN_URL>https://wishpost.wish.com/api/v2/download_label?access_token=50b16225937b4cdda4e5bdd317cc7f44&amp;format=10&amp;use_local=false&amp;mark=0</PDF_10_EN_URL>
		 <PDF_A4_LCL_URL>https://wishpost.wish.com/api/v2/download_label?access_token=50b16225937b4cdda4e5bdd317cc7f44&amp;format=A4&amp;use_local=true&amp;mark=0</PDF_A4_LCL_URL>
		 <PDF_10_LCL_URL>https://wishpost.wish.com/api/v2/download_label?access_token=50b16225937b4cdda4e5bdd317cc7f44&amp;format=10&amp;use_local=true&amp;mark=0</PDF_10_LCL_URL>
		
		 </root>
		 * */
	}
	
	public function downloadlabels($accountid,$printlang,$printcode,$barcodes){
		$downdata = $this->generateDownLabelXML($accountid, $printlang, $printcode, $barcodes);
		$result = $this->execute("https://wishpost.wish.com/api/v2/generate_label", $downdata);
		$xmlresult = simplexml_load_string($result);
		$status = $xmlresult->status;
		$PDF_URL = $xmlresult->PDF_URL;
		return $PDF_URL;
	}
	
	private function generateDownLabelXML($accountid,$printlang,$printcode,$barcodes){
		if($barcodes != null){
			$downlabelxml = '<?xml version="1.0" ?>';
			$downlabelxml .= '<root>';
			$downlabelxml .= '<access_token>'.$this->getWPAccessToken($accountid).'</access_token>';
			$downlabelxml .= '<printlang>'.$printlang.'</printlang>';
			$downlabelxml .= '<printcode>'.$printcode.'</printcode>';
			$downlabelxml .= '<barcodes>';
			foreach ($barcodes as $barcode){
				$downlabelxml .= '<barcode>'.$barcode.'</barcode>';
			}
			$downlabelxml .= '</barcodes>';
			$downlabelxml .= '</root>';
			return $downlabelxml;
		}
		return null;
	}
	private function getWPAccessToken($accountid){
		$result = $this->dbhelper->getWPAccessToken($accountid);
		if($result != null){
			if($curtoken = mysql_fetch_array($result)){
				return $curtoken['token'];
			}
		}
		return null;
	}
	private function generateXML($orders,$access_token,$mark,$bid,$senderinfo){
		if($orders != null){
			$destxml = "<?xml version=\"1.0\" ?>";
			$destxml .= "<orders>";
			$destxml .= "<access_token>".$access_token."</access_token>";
			$destxml .= "<mark>".$mark."</mark>";
			$destxml .= "<bid>".$bid."</bid>";
			
			foreach ($orders as $order){
				$destxml .= "<order>";
				$destxml .= "<guid>".$order->guid."</guid>";
				$destxml .= "<otype>".$order->otype."</otype>";
				$destxml .= "<from>".$senderinfo->from."</from>";
				$destxml .= "<sender_addres>".$senderinfo->sender_addres."</sender_addres>";
				$destxml .= "<sender_province>".$senderinfo->sender_province."</sender_province>";
				$destxml .= "<sender_city>".$senderinfo->sender_city."</sender_city>";
				$destxml .= "<sender_phone>".$senderinfo->sender_phone."</sender_phone>";
				$destxml .= "<to>".$order->to."</to>";
				$destxml .= "<to_local>".$order->to_local."</to_local>";
				$destxml .= "<recipient_addres>".$order->recipient_address."</recipient_addres>";
				$destxml .= "<recipient_addres_local>".$order->recipient_addres_local."</recipient_addres_local>";
				$destxml .= "<recipient_country>".$order->recipient_country."</recipient_country>";
				$destxml .= "<recipient_country_short>".$order->recipient_country_short."</recipient_country_short>";
				$destxml .= "<recipient_country_local>".$order->recipient_country_local."</recipient_country_local>";
				$destxml .= "<recipient_province>".$order->recipient_province."</recipient_province>";
				$destxml .= "<recipient_province_local>".$order->recipient_province_local."</recipient_province_local>";
				$destxml .= "<recipient_city>".$order->recipient_city."</recipient_city>";
				$destxml .= "<recipient_city_local>".$order->recipient_city_local."</recipient_city_local>";
				$destxml .= "<recipient_postcode>".$order->recipient_postcode."</recipient_postcode>";
				$destxml .= "<recipient_phone>".$order->recipient_phone."</recipient_phone>";
				$destxml .= "<content>".$order->content."</content>";
				$destxml .= "<type_no>".$order->type_no."</type_no>";
				$destxml .= "<weight>".$order->weight."</weight>";
				$destxml .= "<num>".$order->num."</num>";
				$destxml .= "<single_price>".$order->single_price."</single_price>";
				$destxml .= "<from_country>".$order->from_country."</from_country>";
				$destxml .= "<user_desc>".$order->user_desc."</user_desc>";
				$destxml .= "<trande_no>".$order->trande_no."</trande_no>";
				$destxml .= "<trade_amount>".$order->trade_amount."</trade_amount>";
				$destxml .= "<receive_from>".$senderinfo->receive_from."</receive_from>";
				$destxml .= "<receive_province>".$senderinfo->receive_province."</receive_province>";
				$destxml .= "<receive_city>".$senderinfo->receive_city."</receive_city>";
				$destxml .= "<receive_addres>".$senderinfo->receive_addres."</receive_addres>";
				$destxml .= "<doorpickup>".$senderinfo->doorpickup."</doorpickup>";
				$destxml .= "<receive_phone>".$senderinfo->receive_phone."</receive_phone>";
				$destxml .= "<warehouse_code>".$senderinfo->warehouse_code."</warehouse_code>";
				
				$destxml .= "</order>";
			}
			$destxml .= "</orders>";
			
			return $destxml;
		}
		return null;
	}
	private function execute($desturl,$xmldata){
		$header[] = "Content-type: text/xml";//定义content-type为xml
		$options = array (
				CURLOPT_CONNECTTIMEOUT => 10,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_USERAGENT => 'wish-php-sdk',
				CURLOPT_HEADER => 'true',
				CURLOPT_HTTPHEADER => $header,
				CURLOPT_SSL_VERIFYPEER => 'true',
				CURLOPT_CAINFO => '/cert/ca.crt',
				CURLOPT_URL => $desturl,
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => $xmldata
		);
		
		$curl = curl_init();
		curl_setopt_array ( $curl, $options );
		
		$result = curl_exec ( $curl );
		$error = curl_errno ( $curl );
		
		$error_message = curl_error ( $curl );
		
		curl_close($curl);
	     
		echo "<br/>get result:";
		echo "<xmp>".$result."</xmp>";
		return $result;
	}
	
	public function getOrdersExpressInfo($accountid,$orderstatus){
		$ordersexpressinfo = array();
		$result = $this->dbhelper->getordersexpressinfo($accountid, $orderstatus);
		if($result != null){
			while($orderexpressinfo = mysql_fetch_array($result)){
				$ordersexpressinfo[$orderexpressinfo['tracking']] = $orderexpressinfo['express_code'].'|'.$orderexpressinfo['countrycode'].'|'.$orderexpressinfo['sku'];
			}
		}
		return $ordersexpressinfo;
	}
}
