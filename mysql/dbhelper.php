<?php
namespace mysql;

include_once dirname ( '__FILE__' ) . './user/wconfig.php';

class dbhelper {
	private $db;
	public function __construct() {
		$dbhost = "localhost";
		$dbuser = "root";
		$dbpsd = "yangwu";
		$dbname = "wish";
		
		$db = mysql_connect ( $dbhost, $dbuser, $dbpsd, true );
		if (! $db) {
			echo "connection failed";
		}
		mysql_select_db ( $dbname );
		mysql_query ( "set names 'UTF8'" );
	}
	public function queryUser($username, $email) {
		$querySql = 'select userid,username,email from users where email = "' . $email . '" or username = "' . $username . '"';
		return mysql_query ( $querySql );
	}
	public function createUser($username, $password, $email) {
		$userInsert = 'insert into users(username,email,psd) values("' . $username . '","' . $email . '","' . $password . '")';
		mysql_query ( $userInsert );
		return mysql_insert_id ();
	}
	
	public function userLogin($username, $password) {
		$loginSql = 'select userid,username,email from users where psd = "' . $password . '" and ';
		if (stripos ( $username, "@" ) != false) {
			$loginSql = $loginSql . ' email = "' . $username . '"';
		} else {
			$loginSql = $loginSql . ' username = "' . $username . '"';
		}
		return mysql_query ( $loginSql );
	}
	
	public function addUseraccount($userid,$accountname,$clientid,$clientsecret){
		$addSql = 'insert into accounts(accountname,userid,clientid,clientsecret) values("'.$accountname.'",'.$userid.',"'.$clientid.'","'.$clientsecret.'")';
		mysql_query($addSql);
		return mysql_insert_id();
	}
	
	public function isClientidSecretExist($clientid,$clientsecret){
		$clientquery = 'select clientid,clientsecret from accounts where clientid = "'.$clientid.'" or clientsecret = "'.$clientsecret.'"';
		$result = mysql_query($clientquery);
		while($account = mysql_fetch_array($result)){
			return true;
		}
		return false;
	}
	
	public function getUserToken($email) {
		$querySql = 'select accountid,accountname, clientid,clientsecret,token,refresh_token from accounts, users where';
		if (stripos ( $email, "@" ) != false) {
			$querySql = $querySql . ' users.email = "' . $email . '" and users.userid = accounts.userid';
		} else {
			$querySql = $querySql . ' users.username = "' . $email . '" and users.userid = accounts.userid';
		}
		$result = mysql_query ( $querySql );
		return $result;
	}
	public function getAccountToken($accountid) {
		$result = mysql_query ( "select accountname, clientid,clientsecret,token,refresh_token from accounts where accountid = '" . $accountid . "'" );
		return $result;
	}
	public function updateUserToken($accountid, $newToken, $newRefreshToken) {
		if(strlen($newToken) > 1 && strlen($newRefreshToken) > 1){
			$updateTokenSql = "update accounts set token = '" . $newToken . "',refresh_token='" . $newRefreshToken . "' where accountid = '" . $accountid . "'";
			return mysql_query ( $updateTokenSql );
		}
		return false;
	}
	public function insertOrder($orderarray) {
		$insert_sql = 'insert into orders (orderid,orderNum,accountid,ordertime,transactionid,orderstate,
		sku,productname,productimage,color,size,price,cost,shipping,shippingcost,quantity,
		totalcost,provider,tracking,name,streetaddress1,streetaddress2,
		city,state,zipcode,phonenumber,countrycode,orderstatus) values("' . $orderarray ['orderid'] . '",' . $orderarray ['orderNum'] . ',"' . $orderarray ['accountid'] . '","' . $orderarray ['ordertime'] . '","' . $orderarray ['transactionid'] . '","' . $orderarray ['orderstate'] . '","' . $orderarray ['sku'] . '","' . $orderarray ['productname'] . '","' . $orderarray ['productimage'] . '","' . $orderarray ['color'] . '","' . $orderarray ['size'] . '","' . $orderarray ['price'] . '","' . $orderarray ['cost'] . '","' . $orderarray ['shipping'] . '","' . $orderarray ['shippingcost'] . '","' . $orderarray ['quantity'] . '","' . $orderarray ['totalcost'] . '","' . $orderarray ['provider'] . '","' . $orderarray ['tracking'] . '","' . $orderarray ['name'] . '","' . $orderarray ['streetaddress1'] . '","' . $orderarray ['streetaddress2'] . '","' . $orderarray ['city'] . '","' . $orderarray ['state'] . '","' . $orderarray ['zipcode'] . '","' . $orderarray ['phonenumber'] . '","' . $orderarray ['countrycode'] . '","' . $orderarray ['orderstatus'] . '")';
		
		return mysql_query ( $insert_sql );
	}
	public function updateOrder($orderarray) {
		$update_sql = "UPDATE orders set provider = '" . $orderarray ['provider'] . "', tracking = '" . $orderarray ['tracking'] . "', orderstatus = '" . $orderarray ['orderstatus'] . "' where accountid = '" . $orderarray ['accountid'] . "' and transactionid='" . $orderarray ['transactionid'] . "'";
		return mysql_query ( $update_sql );
	}
	public function getOrdersNotUploadTracking($accountid) {
		return $this->getOrders ( $accountid, '1' );
	}
	
	public function getOrdersForUploadTracking($accountid) {
		return $this->getOrders ( $accountid, ORDERSTATUS_DOWNLOADEDLABEL);
	}
	
	// get the orders that the status is 1 and then get the labels.
	public function getAccountOrdersForLabels($accountid) {
		return $this->getOrders ( $accountid, '1' );
	}
	
	// get the orders that the status is 1 and then get the labels.
	public function getUserOrdersForLabels($userid) {
		return $this->getUserOrders ( $userid, '1' );
	}
	
	// get the orders that the status is 0 and then apply tracking numbers.
	public function getOrdersNoTracking($accountid) {
		return $this->getOrders ( $accountid, '0' );
	}
	public function updateOrderStatus($tracking, $status) {
		$updateSql = "UPDATE orders set orderstatus = " . $status . " WHERE tracking = '" . $tracking . "'";
		return mysql_query ( $updateSql );
	}
	
	public function updateEUBOrderStatus($orderid,$status){
		$eubupdateSql = "UPDATE orders set orderstatus = " . $status . " WHERE orderid = '" . $orderid . "'";
		return mysql_query ( $eubupdateSql );
	}
	
	public function uploadEUBTracking($orderid,$tracking){
		$eubUpload = "update orders set provider='USPS',tracking='". $tracking ."'  where transactionid = '".$orderid."';";
		return mysql_query($eubUpload);
	}
	/**
	 *
	 * @param
	 *        	accountid
	 *        	orderstatus
	 */
	private function getOrders($accountid, $orderstatus) {
		$query_sql;
		if (strcmp ( $orderstatus, '1' ) == 0 || strcmp ( $orderstatus, ORDERSTATUS_DOWNLOADEDLABEL) == 0) {
			$query_sql = "SELECT transactionid,orderid,provider, tracking FROM orders WHERE accountid = '" . $accountid . "' and orderstatus = '" . $orderstatus . "'";
		} else if (strcmp ( $orderstatus, '0' ) == 0) {
			$query_sql = "SELECT orderid,orderNum,accountid,ordertime,transactionid,orderstate,
		sku,productname,productimage,color,size,price,cost,shipping,shippingcost,quantity,
		totalcost,provider,tracking,name,streetaddress1,streetaddress2,
		city,state,zipcode,phonenumber,countrycode,orderstatus FROM orders WHERE accountid = '" . $accountid . "' and orderstatus = '" . $orderstatus . "' order by transactionid, orderNum desc";
		}
		
		$result = mysql_query ( $query_sql );
		return $result;
	}
	private function getUserOrders($userid, $orderstatus) {
		$userOrderSql = "";
		if (strcmp ( $orderstatus, '1' ) == 0) {
			$userOrderSql = "SELECT o.transactionid,o.orderid,o.provider,o.tracking FROM orders o,accounts a WHERE a.userid = " . $userid . " and a.accountid = o.accountid and o.orderstatus = " . $orderstatus;
		}
		return mysql_query ( $userOrderSql );
	}
	public function getUSOrders() {
		$USOrderSql = "SELECT transactionid, orderid,sku,productname,color, size,quantity, name,streetaddress1,streetaddress2,city,state,zipcode,phonenumber FROM `orders` WHERE countrycode = 'US' and orderstatus = '0' order by transactionid";
		return mysql_query ( $USOrderSql );
	}
	
	public function getEUBOrders($userid) {
		$EUBOrderSql = "SELECT o.transactionid, o.orderid,o.sku,o.productname,o.color, o.size,o.quantity, o.name,o.streetaddress1,o.streetaddress2,o.city,o.state,o.zipcode,o.phonenumber FROM orders o,accounts a WHERE a.userid = ". $userid. " and a.accountid = o.accountid and o.countrycode = 'US' and o.orderstatus = '0' order by o.transactionid";
		return mysql_query ( $EUBOrderSql );
	}
	public function insertProductSource($accountid, $productarray) {
		$insertSourceSQL = 'insert into productinfo(accountid, parent_sku,source_url) values (' . $accountid . ',"' . $productarray ['parent_sku'] . '","' . $productarray ['productSourceURL'] . '")';
		return mysql_query ( $insertSourceSQL );
	}
	
	public function updateProductSource($accountid, $productarray){
		$updateSourceSQL = 'update productinfo set source_url="'. $productarray ['productSourceURL'] . '" where accountid = '.$accountid.' and parent_sku="'.$productarray ['parent_sku'].'"';
		return mysql_query ( $updateSourceSQL );
	}
	
	
	public function insertProduct($productarray) {
		$insert_sql = 'insert into products (parent_sku,sku,name,description,brand,color,main_image,extra_images,landingPageURL,MSRP,price,quantity,shipping,shipping_time,size,tags,UPC) 
					values("' . $productarray ['parent_sku'] . '","' . $productarray ['sku'] . '","' . $productarray ['name'] . '","' . $productarray ['description'] . '","' . $productarray ['brand'] . '","' . $productarray ['color'] . '","' . $productarray ['main_image'] . '","' . $productarray ['extra_images'] . '","' . $productarray ['landingPageURL'] . '","' . $productarray ['MSRP'] . '","' . $productarray ['price'] . '","' . $productarray ['quantity'] . '","' . $productarray ['shipping'] . '","' . $productarray ['shipping_time'] . '","' . $productarray ['size'] . '","' . $productarray ['tags'] . '","' . $productarray ['UPC'] . '")';
		return mysql_query ( $insert_sql );
	}
	public function removeProduct($parentSKU){
		$removeSql = 'delete from products where parent_sku="'.$parentSKU.'"';
		return mysql_query($removeSql);
	}
	
	
	
	public function getUploadProducts($accountid){
		$queryproducts = 'SELECT p.*,s.scheduledate,s.errormessage from products p, schedule_product s WHERE s.isfinished != 1 and s.accountid = '.$accountid.' and s.parent_sku = p.parent_sku order by UNIX_TIMESTAMP(s.scheduledate) desc, p.parent_sku,p.color,p.size';
		return mysql_query($queryproducts);
	}
	
	public function getProducts($parentSKU) {
		$productsSQL = "select * from products where parent_sku = '" . $parentSKU . "'";
		return mysql_query ( $productsSQL );
	}
	public function isScheduleRunning() {
		$sql = "select schedule_running from setting";
		$result = mysql_query ( $sql );
		$row = mysql_fetch_array ( $result );
		if ($row != null) {
			$value = $row ['schedule_running'];
			return ($value == 1);
		}
		return false;
	}
	public function startScheduleRunning() {
		$this->updateScheduleRunning ( 1, "started" );
	}
	public function stopScheduleRunning() {
		$this->updateScheduleRunning ( 0, "stoped" );
	}
	private function updateScheduleRunning($scheduleRunning, $msg) {
		$updateSql = 'update setting set schedule_running=' . $scheduleRunning . ', message = "' . $msg . '"';
		return mysql_query ( $updateSql );
	}
	public function insertScheduleProduct($productInfo) {
		$insertSql = 'insert into schedule_product(accountid,parent_sku,scheduledate,isfinished) values (' . $productInfo ['accountid'] . ',"' . $productInfo ['parent_sku'] . '","' . $productInfo ['scheduledate'] . '",0)';
		return mysql_query ( $insertSql );
	}
	public function removeScheduleProduct($parentSKU,$accountid){
		$removeSql = 'delete from schedule_product where parent_sku = "'.$parentSKU.'" and accountid = '.$accountid;
		return mysql_query ( $removeSql );
	}
	
	
	public function getScheduleProducts($curDate) {
		$scheduleSql = 'select accountid,parent_sku from schedule_product where UNIX_TIMESTAMP(scheduledate) <= UNIX_TIMESTAMP("' . $curDate . '")  and isfinished = 0 order by accountid,parent_sku';
		return mysql_query ( $scheduleSql );
	}
	public function updateScheduleFinished($productInfo) {
		$updateFinished = 'update schedule_product set isfinished = 1,errormessage= now() where accountid = ' . $productInfo ['accountid'] . ' and parent_sku="' . $productInfo ['parent_sku'] . '"';
		return mysql_query ( $updateFinished );
	}
	
	public function updateScheduleError($productInfo,$errorInfo) {
		$updateErrorFinished = 'update schedule_product set isfinished = -1, errormessage = "'.$errorInfo.'" where accountid = ' . $productInfo ['accountid'] . ' and parent_sku="' . $productInfo ['parent_sku'] . '"';
		return mysql_query ( $updateErrorFinished );
	}
	
	public function updateSettingCount() {
		$updateSql = 'update setting set running_count = running_count + 1';
		return mysql_query ( $updateSql );
	}
	public function resetSettingCount() {
		$resetSql = 'update setting set running_count = 0, pid= NULL';
		return mysql_query ( $resetSql );
	}
	public function updateSettingMsg($msg) {
		$updateMsgSql = 'update setting set message = "' . $msg . '"';
		return mysql_query ( $updateMsgSql );
	}
	public function getSettingDuringTime() {
		$getDuringTimeSql = "select during_time from setting";
		return mysql_query ( $getDuringTimeSql );
	}
	public function insertTrackingData($trackingData) {
		$insertTracking = 'insert into tracking_data(userid,tracking_number,device_id,tracking_date) values(' . $trackingData ['user_id'] . ',"' . $trackingData ['tracking_number'] . '","' . $trackingData ['device_id'] . '","' . $trackingData ['tracking_date'] . '")';
		return mysql_query ( $insertTracking );
	}
	public function getUserLabels($userid) {
		$querylabels = "SELECT l.id id,p.parent_sku parentsku,l.CN_Name cn_name,l.EN_Name en_name FROM labels l, product_label p WHERE p.userid = " . $userid . " and p.label_id = l.id";
		return mysql_query ( $querylabels );
	}
	public function insertLabel($cn_name, $en_name) {
		$sqllabel = 'select id from labels where CN_Name = "' . $cn_name . '" and EN_Name = "' . $en_name . '"';
		$result = mysql_query ( $sqllabel );
		$row = mysql_fetch_array ( $result );
		if ($row) {
			return $row ['id'];
		} else {
			$insertlabel = 'insert into labels(CN_Name,EN_Name) values("' . $cn_name . '","' . $en_name . '")';
			mysql_query ( $insertlabel );
			return mysql_insert_id ();
		}
	}
	public function insertproductLabel($userid, $parent_sku, $labelid) {
		$insertpl = 'insert into product_label(label_id,parent_sku,userid) values(' . $labelid . ',"' . $parent_sku . '",' . $userid . ')';
		return mysql_query ( $insertpl );
	}
	public function getExpressInfo($userid, $expressid) {
		$userSql = 'select express_attr_name,express_attr_value from express_attr_info where userid = ' . $userid . ' and express_id = ' . $expressid;
		return mysql_query ( $userSql );
	}
	
	public function  getProductSource($accountid,$parent_sku = null){
		$psource = "SELECT distinct p.parent_sku,p.name,p.main_image,i.source_url FROM `products` p, `productinfo` i WHERE p.parent_sku = i.parent_sku and i.accountid = ".$accountid;
		if($parent_sku != null){
			$psource .= " and i.parent_sku like '%".$parent_sku."%'";
		}
		$psource .= " limit 50";
		return mysql_query($psource);
	}
	
	public function registerPID($pid){
		$insertpid = "update setting set pid = '".$pid."'";
		return mysql_query($insertpid);
	}
	
	public function getPID(){
		$psql = "select pid from setting";
		$result = mysql_query($psql);
		while( $pid = mysql_fetch_array ( $result )){
			return $pid['pid'];
		}
		return null;
	}
	
	public function insertResetToken($userid,$token){
		$tokensql = "insert into resetpassword(userid,token) values(".$userid.",'".$token."')";
		return mysql_query ( $tokensql );
	}
	
	public function removeResetToken($userid){
		$delfirst = "delete from resetpassword where userid = ".$userid;
		return mysql_query ( $delfirst );
	}
	
	public function queryResetpsdUser($token){
		$queryToken = "select userid from resetpassword where token = '".$token."'";
		$result = mysql_query($queryToken);
		while( $useridarray = mysql_fetch_array ( $result )){
			return $useridarray['userid'];
		}
		return null;
	}
	
	public function updatepsd($userid,$newpassword){
		$psdupdate = "update users set psd = '".$newpassword."' where userid = ".$userid;
		return mysql_query($psdupdate);
		/* if($result){
			echo "success<br/>";
		}else{
			echo "failed<br/>".mysql_error();
		} */
	}
	
	
	public function insertColors($color){
		$colorsql = 'insert into wishcolors(color) values("'.$color.'")';
		$result = mysql_query($colorsql);
		if($result){
			echo "success";
		}else{
			echo "failed".mysql_error();
		}
		return $reult;
	}
	
	public function getWishColors(){
		$wishcolors = "select id,color from wishcolors";
		return mysql_query($wishcolors);
	}
	
	
	public function insertWeeklySummary($accountid,$weekdata){
		$insertWeek = 'insert into productssummary(accountid,startdate,enddate,productid,productimpressions,buycart,buyctr,orders,checkoutconversion,gmv) values('.$accountid.',DATE_FORMAT("'.
			$weekdata['startdate'].'","%Y-%m-%d"),DATE_FORMAT("'.$weekdata['enddate'].'","%Y-%m-%d"),"'.$weekdata['productid'].'",'.$weekdata['productimpression'].','.$weekdata['buycart'].',"'.$weekdata['buyctr'].'",'.$weekdata['orders'].',"'.$weekdata['checkoutconversion'].'",'.$weekdata['gmv'].')';
		echo "insert:".$insertWeek;
		return mysql_query($insertWeek);
	}
	public function insertOnlineProduct($productarray) {
		$insert_sql = 'insert into onlineProducts (accountid, id, parent_sku,name,description,original_image_url,main_image,extra_images,is_promoted,tags,review_status,number_saves,number_sold,date_uploaded,date_updated)
					values(' . $productarray ['accountid'] . ',"'.$productarray['id'].'","' . $productarray ['parent_sku'] . '","' . $productarray ['name'] 
							. '","' . $productarray ['description'] . '","' . $productarray ['original_image_url'] . '","' . $productarray ['main_image'] . '","' . $productarray ['extra_images'] . '","' . $productarray ['is_promoted'] . '","' 
							. $productarray ['tags'] . '","' . $productarray ['review_status'] . '",' . $productarray ['number_saves'] . ',' . $productarray ['number_sold'] . ',DATE_FORMAT("' . $productarray ['date_uploaded'] . '","%Y-%m-%d"),DATE_FORMAT("' . $productarray ['date_updated'] . '","%Y-%m-%d"))';
		echo "insertSQL:".$insert_sql;
		return mysql_query ( $insert_sql );
	}
	
	public function insertOnlineProductVar($productarray) {
		$insertvar_sql = 'insert into onlineProductVars (accountid, id, product_id,sku,color,size,enabled,price,all_images,inventory,shipping,shipping_time,MSRP)
					values(' . $productarray ['accountid'] . ',"'.$productarray['id'].'","' . $productarray ['product_id'] . '","' . $productarray ['sku']
						. '","' . $productarray ['color'] . '","' . $productarray ['size'] . '","' . $productarray ['enabled'] . '","' . $productarray ['price'] . '","' . $productarray ['all_images'] . '",'
								. $productarray ['inventory'] . ',"' . $productarray ['shipping'] . '","' . $productarray ['shipping_time'] . '","' . $productarray ['MSRP'] . '")';
		echo "insertSQL:".$insertvar_sql;
		return mysql_query ( $insertvar_sql );
	}
	
	public function getJaveUploadAppToken(){
		$querySql = "select apptoken from apptoken";
		$result = mysql_query($querySql);
		$values = mysql_fetch_array($result);
		if($values !=  null)
			return $values['apptoken'];
		return null;
	}
	
	function __destruct() {
		if (! empty ( $db ))
			mysql_close ( $db );
	}
}



		