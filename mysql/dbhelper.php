<?php

namespace mysql;

class dbhelper {
    
    // online:
    const host = "bdm195587474.my3w.com";
    
    const user = "bdm195587474";
    
    const psd = "yangwu19821112";
    
    const dbname = "bdm195587474_db";
    
    
    
    
	/*const host = "localhost";
	const user = "root";
	const psd = "yangwu";
	const dbname = "wish";*/
	
	private $db;
	public function __construct() {
		echo "dbhelper create";
		$db = mysql_connect ("bdm195587474.my3w.com","bdm195587474","yangwu19821112", true );
		if (! $db) {
			echo "connection failed";
		}
		mysql_select_db ("bdm195587474_db");
		mysql_query ( "set names 'UTF8'" );
	}
	public function getUserToken($email) {
		$result = mysql_query ( "select accountid, clientid,clientsecret,token,refresh_token from accounts, users where users.email = '" . $email . "' and users.userid = accounts.userid" );
		return $result;
	}
	public function insertOrder($orderarray) {
		$insert_sql = 'insert into orders (orderid,orderNum,accountid,ordertime,transactionid,orderstate,
		sku,productname,productimage,color,size,price,cost,shipping,shippingcost,quantity,
		totalcost,provider,tracking,name,streetaddress1,streetaddress2,
		city,state,zipcode,phonenumber,countrycode,orderstatus) values("' . $orderarray ['orderid'] . '",' . $orderarray ['orderNum']. ',"' . $orderarray ['accountid'] . '","' . $orderarray ['ordertime'] . '","' . $orderarray ['transactionid'] . '","' . $orderarray ['orderstate'] . '","' . $orderarray ['sku'] . '","' . $orderarray ['productname'] . '","' . $orderarray ['productimage'] . '","' . $orderarray ['color'] . '","' . $orderarray ['size'] . '","' . $orderarray ['price'] . '","' . $orderarray ['cost'] . '","' . $orderarray ['shipping'] . '","' . $orderarray ['shippingcost'] . '","' . $orderarray ['quantity'] . '","' . $orderarray ['totalcost'] . '","' . $orderarray ['provider'] . '","' . $orderarray ['tracking'] . '","' . $orderarray ['name'] . '","' . $orderarray ['streetaddress1'] . '","' . $orderarray ['streetaddress2'] . '","' . $orderarray ['city'] . '","' . $orderarray ['state'] . '","' . $orderarray ['zipcode'] . '","' . $orderarray ['phonenumber'] . '","' . $orderarray ['countrycode'] . '","' . $orderarray ['orderstatus'] . '")';
		
		echo "insert sql:".$insert_sql."<br/>";
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
	function __destruct() {
		if (! empty ( $db ))
			mysql_close ( $db );
	}
}



		