<?php

namespace mysql;

class dbhelper {
	private $db;
	public function __construct() {
		$online = false;
		
		if ($online) {
			$dbhost = "bdm195587474.my3w.com";
			$dbuser = "bdm195587474";
			$dbpsd = "yangwu19821112";
			$dbname = "bdm195587474_db";
		} else {
			$dbhost = "localhost";
			$dbuser = "root";
			$dbpsd = "yangwu";
			$dbname = "wish";
		}
		
		$db = mysql_connect ( $dbhost, $dbuser, $dbpsd, true );
		if (! $db) {
			echo "connection failed";
		}
		mysql_select_db ( $dbname );
		mysql_query ( "set names 'UTF8'" );
	}
	public function getUserToken($email) {
		$result = mysql_query ( "select accountid, clientid,clientsecret,token,refresh_token from accounts, users where users.email = '" . $email . "' and users.userid = accounts.userid" );
		return $result;
	}
	public function getAccountToken($accountid) {
		$result = mysql_query ( "select clientid,clientsecret,token,refresh_token from accounts where accountid = '" . $accountid . "'" );
		return $result;
	}
	public function updateUserToken($accountid, $newToken, $newRefreshToken) {
		$updateTokenSql = "update accounts set token = '" . $newToken . "',refresh_token='" . $newRefreshToken . "' where accountid = '" . $accountid . "'";
		echo "<br/> update result " . $updateTokenSql;
		return mysql_query ( $updateTokenSql );
	}
	public function insertOrder($orderarray) {
		$insert_sql = 'insert into orders (orderid,orderNum,accountid,ordertime,transactionid,orderstate,
		sku,productname,productimage,color,size,price,cost,shipping,shippingcost,quantity,
		totalcost,provider,tracking,name,streetaddress1,streetaddress2,
		city,state,zipcode,phonenumber,countrycode,orderstatus) values("' . $orderarray ['orderid'] . '",' . $orderarray ['orderNum'] . ',"' . $orderarray ['accountid'] . '","' . $orderarray ['ordertime'] . '","' . $orderarray ['transactionid'] . '","' . $orderarray ['orderstate'] . '","' . $orderarray ['sku'] . '","' . $orderarray ['productname'] . '","' . $orderarray ['productimage'] . '","' . $orderarray ['color'] . '","' . $orderarray ['size'] . '","' . $orderarray ['price'] . '","' . $orderarray ['cost'] . '","' . $orderarray ['shipping'] . '","' . $orderarray ['shippingcost'] . '","' . $orderarray ['quantity'] . '","' . $orderarray ['totalcost'] . '","' . $orderarray ['provider'] . '","' . $orderarray ['tracking'] . '","' . $orderarray ['name'] . '","' . $orderarray ['streetaddress1'] . '","' . $orderarray ['streetaddress2'] . '","' . $orderarray ['city'] . '","' . $orderarray ['state'] . '","' . $orderarray ['zipcode'] . '","' . $orderarray ['phonenumber'] . '","' . $orderarray ['countrycode'] . '","' . $orderarray ['orderstatus'] . '")';
		
		echo "insert sql:" . $insert_sql . "<br/>";
		return mysql_query ( $insert_sql );
	}
	public function updateOrder($orderarray) {
		$update_sql = "UPDATE orders set provider = '" . $orderarray ['provider'] . "', tracking = '" . $orderarray ['tracking'] . "', orderstatus = '" . $orderarray ['orderstatus'] . "' where accountid = '" . $orderarray ['accountid'] . "' and transactionid='" . $orderarray ['transactionid'] . "'";
		echo $update_sql . "<br.>";
		return mysql_query ( $update_sql );
	}
	public function getOrdersNotUploadTracking($accountid) {
		return $this->getOrders ( $accountid, '1' );
	}
	public function getOrdersNoTracking($accountid) {
		return $this->getOrders ( $accountid, '0' );
	}
	/**
	 *
	 * @param
	 *        	accountid
	 *        	orderstatus
	 */
	private function getOrders($accountid, $orderstatus) {
		$query_sql;
		if (strcmp ( $orderstatus, '1' ) == 0) {
			$query_sql = "SELECT transactionid,orderid,provider, tracking FROM orders WHERE accountid = '" . $accountid . "' and orderstatus = '" . $orderstatus . "'";
		} else if (strcmp ( $orderstatus, '0' ) == 0) {
			$query_sql = "SELECT orderid,orderNum,accountid,ordertime,transactionid,orderstate,
		sku,productname,productimage,color,size,price,cost,shipping,shippingcost,quantity,
		totalcost,provider,tracking,name,streetaddress1,streetaddress2,
		city,state,zipcode,phonenumber,countrycode,orderstatus FROM orders WHERE accountid = '" . $accountid . "' and orderstatus = '" . $orderstatus . "' order by transactionid, orderNum desc";
		}
		
		$result = mysql_query ( $query_sql );
		var_dump ( $result );
		if (! $result) {
			echo "error:" . mysql_error ();
		}
		return $result;
	}
	public function getUSOrders() {
		$USOrderSql = "SELECT transactionid, orderid,sku,productname,color, size,quantity, name,streetaddress1,streetaddress2,city,state,zipcode,phonenumber FROM `orders` WHERE countrycode = 'US' and orderstatus = '0' order by transactionid";
		return mysql_query ( $USOrderSql );
	}
	public function insertProductSource($accountid, $productarray) {
		$insertSourceSQL = 'insert into productinfo(accountid, parent_sku,source_url) values (' . $accountid . ',"' . $productarray ['parent_sku'] . '","' . $productarray ['productSourceURL'] . '")';
		return mysql_query ( $insertSourceSQL );
	}
	public function insertProduct($productarray) {
		$insert_sql = 'insert into products (parent_sku,sku,name,description,brand,color,main_image,extra_images,landingPageURL,MSRP,price,quantity,shipping,shipping_time,size,tags,UPC) 
					values("' . $productarray ['parent_sku'] . '","' . $productarray ['sku'] . '","' . $productarray ['name'] . '","' . $productarray ['description'] . '","' . $productarray ['brand'] . '","' . $productarray ['color'] . '","' . $productarray ['main_image'] . '","' . $productarray ['extra_images'] . '","' . $productarray ['landingPageURL'] . '","' . $productarray ['MSRP'] . '","' . $productarray ['price'] . '","' . $productarray ['quantity'] . '","' . $productarray ['shipping'] . '","' . $productarray ['shipping_time'] . '","' . $productarray ['size'] . '","' . $productarray ['tags'] . '","' . $productarray ['UPC'] . '")';
		return mysql_query ( $insert_sql );
	}
	public function getProducts($parentSKU) {
		$productsSQL = "select * from products where parent_sku = '" . $parentSKU . "'";
		return mysql_query ( $productsSQL );
	}
	public function isScheduleRunning() {
		$sql = "select schedule_running from setting";
		$result = mysql_query ( $sql );
		if ($row = mysql_fetch_array ( $result )) {
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
	public function getScheduleProducts($curDate) {
		$scheduleSql = 'select accountid,parent_sku from schedule_product where scheduledate = "' . $curDate . '" and isfinished = 0 order by accountid,parent_sku';
		return mysql_query ( $scheduleSql );
	}
	public function updateScheduleFinished($productInfo) {
		$updateFinished = 'update schedule_product set isfinished = 1 where accountid = ' . $productInfo ['accountid'] . ' and parent_sku="' . $productInfo ['parent_sku'] . '"';
		return mysql_query ( $updateFinished );
	}
	public function updateSettingCount() {
		$updateSql = 'update setting set running_count = running_count + 1';
		return mysql_query ( $updateSql );
	}
	public function resetSettingCount() {
		$resetSql = 'update setting set running_count = 0';
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
	function __destruct() {
		if (! empty ( $db ))
			mysql_close ( $db );
	}
}



		