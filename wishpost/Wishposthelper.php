<?php
namespace wishpost;

include_once dirname ( '__FILE__' ) . './mysql/dbhelper.php';
include_once dirname ( '__FILE__' ) . './user/wconfig.php';
use mysql\dbhelper;

class Wishposthelper{
	
	private $dbhelper;
	
	public function __construct(){
		$this->dbhelper = new dbhelper();
	}
	
	public function createorders($accountid,$orders,$senderinfo){
		$access_token = $this->getWPAccessToken($accountid);
		$bid = substr ( 10000 * microtime ( true ), 6, 5 );
		$xmldata = $this->generateXML($orders, $access_token, 0, $bid, $senderinfo);
		
		$this->execute("https://wishpost.wish.com/api/v2/create_order", $xmldata);
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
		echo "<br/>xml data:";
		echo "<xmp>".$xmldata."</xmp>";
	
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
	}
}
