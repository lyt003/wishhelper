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
	
	$xml = simplexml_load_string($response);
	$access_token = (string)$xml->access_token;
	$refresh_token = (string)$xml->refresh_token;
	
	if((strcmp($access_token,'0') == 0) || (strcmp($refresh_token,'0') == 0)){
		echo "绑定出错，请尝试使用翻墙软件，确保能正常访问https://merchant.wish.com,或者您可在绑定页面直接联系客服<br/>";
	}else{
		if($dbhelper->updateWishpostToken( $wishpostaccountid, $access_token, $refresh_token )){
			header ( "Location:./wusercenter.php" );
		}else{
			"保存access token 出错请直接联系客服<br/>";
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


