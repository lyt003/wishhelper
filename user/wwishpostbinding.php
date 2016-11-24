<?php
session_start ();

include_once dirname ( '__FILE__' ) . './Wish/WishHelper.php';
use mysql\dbhelper;

$access_code = $_GET ['code'];

$clientid = $_POST ["clientid"];
$clientsecret = $_POST ["clientsecret"];
$storename = $_POST ["storename"];
$accountid = $_POST["currentAccountid"];
$userid = $_SESSION ['userid'];


$dbhelper = new dbhelper ();
if ($access_code != null) {
	$redirect_uri = urlencode ( 'https://wishconsole.com/user/wwishpostbinding.php' );
	$wishpostaccountid = $_SESSION ['wishpostaccountid'];
	$clientid = $_SESSION ['clientid'];
	$clientsecret = $_SESSION ['clientsecret'];
	/**
	 * get the access token
	 */
	/* $url = sprintf ( "https://wishpost.wish.com/api/v2/oauth/access_token?&client_id=%s&client_secret=%s&code=%s&redirect_uri=%s&grant_type=authorization_code", $clientid, $clientsecret, $access_code, $redirect_uri );
	$context = stream_context_create ( array (
			'http' => array (
					'method' => 'POST',
					'ignore_errors' => true 
			) 
	) ); */
	$url = "https://wishpost.wish.com/api/v2/oauth/access_token";
	$data = array("client_id" => $clientid,"client_secret" => $clientsecret,"grant_type" =>"authorization_code","redirect_uri" => $redirect_uri);
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
	//$response = file_get_contents ( $url, TRUE, $context );
	echo "<br/>get the response:";
	echo $response;
	echo "\n";
	
	// get the access token and refresh token
	// json data: {"message":"","code":0,"data":{"expiry_time":1446073198,"token_type ":"access_token","access_token":"c10a316adfb449ffb321984aee91fe50","expires_in":2591918,"merchant_user_id":"535bb01471795166f8be12d0","refresh_token":"3cdbddd6c23249d39ab951d58b454a93"}}
	$response = json_decode ( $response );
	$access_obj = $response->{'data'};
	$access_token = '0';
	$refresh_token = '0';
	foreach ( $access_obj as $k => $v ) {
		echo 'key  ' . $k . '  value:' . $v;
		if ($k == 'access_token') {
			$access_token = $v;
		}
		if ($k == 'refresh_token') {
			$refresh_token = $v;
		}
	}
	echo "\n";
	echo $access_token;
	
	if((strcmp($access_token,'0') == 0) || (strcmp($refresh_token,'0') == 0)){
		echo "绑定出错，请尝试使用翻墙软件，确保能正常访问https://merchant.wish.com,或者您可在绑定页面直接联系客服<br/>";
	}else{
		$dbhelper->updateWishpostToken( $wishpostaccountid, $access_token, $refresh_token );
		
		header ( "Location:./wusercenter.php" );
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


