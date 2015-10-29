<?php

namespace mysql;

class dbhelper {
	const host = "localhost";
	const user = "root";
	const psd = "yangwu";
	private $db;
	public function __construct() {
		$db = mysql_connect ( host, user, psd );
		mysql_select_db ( 'wish' );
		mysql_query ( "set names 'utf-8'" );
	}
	public function insertOrder($orderarray) {
		$insert_sql = "insert into orders (orderid,accountid,ordertime,transactionid,orderstate,
		sku,productname,productimage,color,size,price,cost,shipping,shippingcost,quantity,
		totalcost,name,streetaddress1,streetaddress2,
		city,state,zipcode,phonenumber,countrycode,orderstatus) values('" . $orderarray ['orderid'] . "','" . $orderarray ['accountid'] . "','" . $orderarray ['ordertime'] . "','" . $orderarray ['transactionid'] . "','" . $orderarray ['orderstate'] . "','" . $orderarray ['sku'] . "','" . $orderarray ['productname'] . "','" . $orderarray ['productimage'] . "','" . $orderarray ['color'] . "','" . $orderarray ['size'] . "','" . $orderarray ['price'] . "','" . $orderarray ['cost'] . "','" . $orderarray ['shipping'] . "','" . $orderarray ['shippingcost'] . "','" . $orderarray ['quantity'] . "','" . $orderarray ['totalcost'] . "','" . $orderarray ['name'] . "','" . $orderarray ['streetaddress1'] . "','" . $orderarray ['streetaddress2'] . "','" . $orderarray ['city'] . "','" . $orderarray ['state'] . "','" . $orderarray ['zipcode'] . "','" . $orderarray ['phonenumber'] . "','" . $orderarray ['countrycode'] . "','" . $orderarray ['orderstatus'] . "')";
		
		echo $insert_sql;
		return mysql_query ( $insert_sql );
	}
	function __destruct() {
	}
}




		