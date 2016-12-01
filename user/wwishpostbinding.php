<?php
session_start ();

include_once dirname ( '__FILE__' ) . './Wish/WishHelper.php';
use mysql\dbhelper;

function arrayToXml($arr){
	$xml = "<root>";
	foreach ($arr as $key=>$val){
		if(is_array($val)){
			$xml.="<".$key.">".arrayToXml($val)."</".$key.">";
		}else{
			$xml.="<".$key.">".$val."</".$key.">";
		}
	}
	$xml.="</root>";
	return $xml;
}

function getArray($node) {
	$array = false;
	if ($node->hasAttributes()) {
		foreach ($node->attributes as $attr) {
			$array[$attr->nodeName] = $attr->nodeValue;
		}
	}


	if ($node->hasChildNodes()) {
		if ($node->childNodes->length == 1) {
			$array[$node->firstChild->nodeName] = getArray($node->firstChild);
		} else {
			foreach ($node->childNodes as $childNode) {
				if ($childNode->nodeType != XML_TEXT_NODE) {
					$array[$childNode->nodeName][] = getArray($childNode);
				}
			}
		}
	} else {
		return $node->nodeValue;
	}
	return $array;
}

$access_code = $_GET ['code'];

$clientid = $_POST ["clientid"];
$clientsecret = $_POST ["clientsecret"];
$storename = $_POST ["storename"];
$accountid = $_POST["currentAccountid"];
$userid = $_SESSION ['userid'];


$dbhelper = new dbhelper ();
if ($access_code != null) {
	$redirect_uri = "https://wishconsole.com/user/wwishpostbinding.php";
	$wishpostaccountid = $_SESSION ['wishpostaccountid'];
	$clientid = $_SESSION ['clientid'];
	$clientsecret = $_SESSION ['clientsecret'];
	/**
	 * get the access token
	 */
	$url = "https://wishpost.wish.com/api/v2/oauth/access_token";
	$data = array("client_id" => $clientid,"client_secret" => $clientsecret,"code" => $access_code,"grant_type" =>"authorization_code","redirect_uri" => $redirect_uri);
	$data = arrayToXml($data);
	
	$opts = array(
			'http'=>array(
					'method'=>"POST",
					'content' => $data,
					'ignore_errors' => true
			)
	);
	$context = stream_context_create($opts);
	$response = file_get_contents($url, TRUE, $context);
	
	// Send the request
	echo "<br/>Request:";
	echo $data;
	echo "<br/>get the response:";
	echo "<xmp>".$response."</xmp>";
	echo "\n";
	
	$xml = simplexml_load_string($response);
	$access_token = (string)$xml->access_token;
	$refresh_token = (string)$xml->refresh_token;
	/* $dom = new DOMDocument();
	echo "<br/>new Dom";
	$dom->loadXML($response);
	echo "<br/>load Dom";
	$result=getArray($dom->documentElement);
	echo "<br/>get array Dom";
	var_dump($result); */
	// get the access token and refresh token
	// json data: {"message":"","code":0,"data":{"expiry_time":1446073198,"token_type ":"access_token","access_token":"c10a316adfb449ffb321984aee91fe50","expires_in":2591918,"merchant_user_id":"535bb01471795166f8be12d0","refresh_token":"3cdbddd6c23249d39ab951d58b454a93"}}
	//$response = json_decode ( $response );
	/* $access_obj = $response->{'data'};
	$access_token = '0';
	$refresh_token = '0'; */
	/* foreach ( $response as $k => $v ) {
		echo 'key  ' . $k . '  value:' . $v;
		if ($k == 'access_token') {
			$access_token = $v;
		}
		if ($k == 'refresh_token') {
			$refresh_token = $v;
		}
	}
	echo "\n";
	echo $access_token; */
	
	if((strcmp($access_token,'0') == 0) || (strcmp($refresh_token,'0') == 0)){
		echo "绑定出错，请尝试使用翻墙软件，确保能正常访问https://merchant.wish.com,或者您可在绑定页面直接联系客服<br/>";
	}else{
		if($dbhelper->updateWishpostToken( $wishpostaccountid, $access_token, $refresh_token )){
			echo "<br/>success";
			//header ( "Location:./wusercenter.php" );
		}
	}
} else if ($clientid != null && $clientsecret != null && $storename != null) {
	
	/* if($dbhelper->isClientidSecretExist($clientid, $clientsecret)){
		header ( "Location:./wbindwish.php?error=该wish账号已经被绑定过,不能重复绑定" );
	}else{ */
		$result = $dbhelper->addWishpostaccount ( $accountid, $storename, $clientid, $clientsecret );
		if ($result != null) {
			$_SESSION ['wishpostaccountid'] = $result;
			$_SESSION ['clientid'] = $clientid;
			$_SESSION ['clientsecret'] = $clientsecret;
			header ( "Location:http://wishpost.wish.com/oauth/authorize?client_id=" . $clientid );
		}	 
	//}
}


