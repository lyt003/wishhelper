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
		$querySql = 'select userid,username,email from users where email = "' . mysql_real_escape_string($email) . '" or username = "' . mysql_real_escape_string($username) . '"';
		return mysql_query ( $querySql );
	}
	public function createUser($username, $password, $email) {
		$userInsert = 'insert into users(username,email,psd) values("' . mysql_real_escape_string($username) . '","' . mysql_real_escape_string($email) . '","' . $password . '")';
		mysql_query ( $userInsert );
		return mysql_insert_id ();
	}
	
	public function userLogin($username, $password) {
		$loginSql = 'select userid,username,email from users where psd = "' . $password . '" and ';
		if (stripos ( $username, "@" ) != false) {
			$loginSql = $loginSql . ' email = "' . mysql_real_escape_string($username) . '"';
		} else {
			$loginSql = $loginSql . ' username = "' . mysql_real_escape_string($username) . '"';
		}
		return mysql_query ( $loginSql );
	}
	
	public function addUseraccount($userid,$accountname,$clientid,$clientsecret){
		$addSql = 'insert into accounts(accountname,userid,clientid,clientsecret) values("'.mysql_real_escape_string($accountname).'",'.$userid.',"'.$clientid.'","'.$clientsecret.'")';
		mysql_query($addSql);
		return mysql_insert_id();
	}
	
	public function isClientidSecretExist($clientid,$clientsecret){
		$clientquery = 'select clientid,clientsecret,refresh_token from accounts where clientid = "'.$clientid.'" or clientsecret = "'.$clientsecret.'"';
		$result = mysql_query($clientquery);
		while($account = mysql_fetch_array($result)){
			if($account['refresh_token'] != null)
				return true;
		}
		return false;
	}
	
	public function getUserToken($email) {
		$querySql = 'select accountid,accountname, clientid,clientsecret,token,refresh_token from accounts, users where';
		if (stripos ( $email, "@" ) != false) {
			$querySql = $querySql . ' users.email = "' . mysql_real_escape_string($email) . '" and users.userid = accounts.userid';
		} else {
			$querySql = $querySql . ' users.username = "' . mysql_real_escape_string($email) . '" and users.userid = accounts.userid';
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
		city,state,zipcode,phonenumber,countrycode,orderstatus,iswishexpress,requiredeliveryconfirmation) values("' 
		. $orderarray ['orderid'] . '",' . $orderarray ['orderNum'] . ',"' . $orderarray ['accountid'] . '","' . $orderarray ['ordertime'] . '","' 
		. $orderarray ['transactionid'] . '","' . $orderarray ['orderstate'] . '","' . mysql_real_escape_string($orderarray ['sku']) . '","' 
		. mysql_real_escape_string($orderarray ['productname']) . '","' . $orderarray ['productimage'] . '","' . $orderarray ['color'] . '","' 
		. mysql_real_escape_string($orderarray ['size']) . '","' . $orderarray ['price'] . '","' . $orderarray ['cost'] . '","' . $orderarray ['shipping'] . '","' 
		. $orderarray ['shippingcost'] . '","' . $orderarray ['quantity'] . '","' . $orderarray ['totalcost'] . '","' . $orderarray ['provider'] . '","' 
		. $orderarray ['tracking'] . '","' . mysql_real_escape_string($orderarray ['name']) . '","' . mysql_real_escape_string($orderarray ['streetaddress1']) . '","' 
		. mysql_real_escape_string($orderarray ['streetaddress2']) . '","' . mysql_real_escape_string($orderarray ['city']) . '","' 
		. mysql_real_escape_string($orderarray ['state']) . '","' . $orderarray ['zipcode'] . '","' . $orderarray ['phonenumber'] . '","' 
		. $orderarray ['countrycode'] . '","' . $orderarray ['orderstatus'] . '","'.$orderarray['isWishExpress'].'","'.$orderarray['requireDeliveryConfirmation'].'")';
		
		return mysql_query ( $insert_sql );
	}
	
	public function getorderdetails($accountid,$orderid){
		$ordersql = 'select * from orders where accountid = "'.$accountid.'" and orderid = "'.$orderid.'"';
		return mysql_query($ordersql);
	}
	
	public function updateorderaddress($accountid,$orderid,$name,$streetaddress1,$streetaddress2,$city,$state,$zipcode,$phonenumber,$countrycode){
		$updateaddress = 'update orders set ';
		if(name == null || countrycode == null){
			return false;
		}
		if($name != null)
			$updateaddress .= 'name = "'.mysql_real_escape_string($name).'"  ';
		if($streetaddress1 != null)
			$updateaddress .= ',streetaddress1 = "'.mysql_real_escape_string($streetaddress1).'"  ';
		if($streetaddress2 != null)
			$updateaddress .= ',streetaddress2 = "'.mysql_real_escape_string($streetaddress2).'"  ';
		if($city != null)
			$updateaddress .= ',city = "'.mysql_real_escape_string($city).'"  ';
		if($state != null)
			$updateaddress .= ',state = "'.mysql_real_escape_string($state).'"  ';
		if($zipcode != null)
			$updateaddress .= ',zipcode = "'.mysql_real_escape_string($zipcode).'"  ';
		if($phonenumber != null)
			$updateaddress .= ',phonenumber = "'.mysql_real_escape_string($phonenumber).'"  ';
		if($countrycode != null)
			$updateaddress .= ',countrycode = "'.mysql_real_escape_string($countrycode).'"  ';
		
		$updateaddress .=' where accountid = "'.$accountid.'" and orderid = "'.$orderid.'"';
		
		return mysql_query($updateaddress);
	}
	
	public function insertEbayOrder($accountid,$orderarray){
		$orderarray[15] = str_replace('$','',$orderarray[15]);
		$orderarray[16] = str_replace('$','',$orderarray[16]);
		$orderarray[20] = str_replace('$','',$orderarray[20]);
		$insert_sql = 'insert into orders (orderid,orderNum,accountid,ordertime,transactionid,orderstate,
		sku,productname,productimage,color,size,price,cost,shipping,shippingcost,quantity,
		totalcost,provider,tracking,name,streetaddress1,streetaddress2,
		city,state,zipcode,phonenumber,countrycode,orderstatus) values("' . $orderarray [0] . '",0,' . $accountid . ',"' . $orderarray [24] . '","' . $orderarray [0] . '"," ","' . $orderarray [12] 
		. '","' . $orderarray [12] . '"," "," "," ","' . $orderarray [15] . '","' . $orderarray [15] . '","' . $orderarray [16] . '","' . $orderarray [16] . '","' . $orderarray [14] . '","' . $orderarray [20] . '"," "," ","' 
				. $orderarray [2] . '","' . $orderarray [5] . '","' . $orderarray [6] . '","' . $orderarray [7] . '","' . $orderarray [8] . '","' . $orderarray [9] . '","' . $orderarray [3] . '","' . $orderarray [10] . '","0")';
		return mysql_query ( $insert_sql );
	}
	
	public function updateOrder($orderarray) {
		$update_sql = "UPDATE orders set provider = '" . mysql_real_escape_string($orderarray ['provider']) . "', tracking = '" . mysql_real_escape_string($orderarray ['tracking']) . "', orderstatus = '" . $orderarray ['orderstatus'] . "' where accountid = '" . $orderarray ['accountid'] . "' and transactionid='" . $orderarray ['transactionid'] . "'";
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
		$updateSql = "UPDATE orders set orderstatus = " . $status . " WHERE tracking = '" . mysql_real_escape_string($tracking) . "'";
		return mysql_query ( $updateSql );
	}
	
	public function updateEUBOrderStatus($orderid,$status){
		$eubupdateSql = "UPDATE orders set orderstatus = " . $status . " WHERE orderid = '" . $orderid . "'";
		return mysql_query ( $eubupdateSql );
	}
	
	public function uploadEUBTracking($orderid,$tracking){
		$eubUpload = "update orders set provider='EPacket',tracking='". mysql_real_escape_string($tracking) ."'  where transactionid = '".$orderid."';";
		return mysql_query($eubUpload);
	}
	
	public function uploadWishpostTracking($transactionid,$tracking,$status){
		$wishpostUpload = "update orders set provider='WishPost',tracking='". mysql_real_escape_string($tracking) ."',orderstatus= ".$status." where transactionid = '".$transactionid."';";
		return mysql_query($wishpostUpload);
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
			$query_sql = "SELECT transactionid,orderid,provider, tracking,sku,color,size,quantity,iswishexpress,requiredeliveryconfirmation FROM orders WHERE accountid = '" 
					. $accountid . "' and orderstatus = '" . $orderstatus . "' order by iswishexpress,sku,color,size";
		} else if (strcmp ( $orderstatus, '0' ) == 0) {
			$query_sql = "SELECT orderid,orderNum,accountid,ordertime,transactionid,orderstate,
		sku,productname,productimage,color,size,price,cost,shipping,shippingcost,quantity,
		totalcost,provider,tracking,name,streetaddress1,streetaddress2,
		city,state,zipcode,phonenumber,countrycode,orderstatus,iswishexpress,requiredeliveryconfirmation FROM orders WHERE accountid = '" . $accountid . "' and orderstatus = '" . $orderstatus . "' order by transactionid, orderNum desc";
		}
		
		$result = mysql_query ( $query_sql );
		return $result;
	}
	
	public function getwishorders($accountid, $orderstatus){
		$woquery_sql = "SELECT transactionid,orderid,provider, tracking FROM orders WHERE provider = 'WishPost' and accountid = '" . $accountid . "' and orderstatus = '" . $orderstatus . "'";
		$result = mysql_query ( $woquery_sql );
		return $result;
	}
	
	public function getordersexpressinfo($accountid,$orderstatus){
		$oesql = 'select ei.express_name,ei.express_code,peo.product_id,peo.sku,peo.countrycode,peo.provider,peo.tracking, peo.express_id'.
					' from express_info ei,'.
					' (select ot.product_id,ot.sku,ot.countrycode,ot.provider,ot.tracking, pe.express_id'.
					' from product_express_info pe,'.
					' (select opv.product_id,od.sku, od.countrycode,od.provider,od.tracking'.
					' from onlineProductVars opv,'.
					' (select accountid,sku,countrycode,provider,tracking from orders where accountid = '.$accountid.' and orderstatus = '.$orderstatus.') od'.
					' where opv.accountid = od.accountid and opv.sku = od.sku) ot'.
					' where pe.product_id = ot.product_id and pe.countrycode = ot.countrycode) peo'.
					' where ei.express_id = peo.express_id';
		return mysql_query($oesql);
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
		$EUBOrderSql = "SELECT o.accountid, o.transactionid, o.orderid,o.sku,o.productname,o.color, o.size,o.quantity, o.name,o.streetaddress1,o.streetaddress2,o.city,o.state,o.zipcode,o.phonenumber,o.countrycode FROM orders o,accounts a WHERE a.userid = ". $userid. " and a.accountid = o.accountid and o.orderstatus = '0' order by o.transactionid";
		return mysql_query ( $EUBOrderSql );
	}
	public function insertProductSource($accountid, $productarray) {
		$insertSourceSQL = 'insert into productinfo(accountid, parent_sku,source_url,lowesttotalprice) values (' . $accountid . ',"' . mysql_real_escape_string($productarray ['parent_sku']) . '","' . mysql_real_escape_string($productarray ['productSourceURL']) . '","'.mysql_real_escape_string($productarray['lowesttotalprice']).'")';
		return mysql_query ( $insertSourceSQL );
	}
	
	public function updateProductSource($accountid, $productarray){
		$updateSourceSQL = 'update productinfo set source_url="'. mysql_real_escape_string($productarray ['productSourceURL']) . '", lowesttotalprice="'.mysql_real_escape_string($productarray['lowesttotalprice']).'"  where accountid = '.$accountid.' and parent_sku="'.mysql_real_escape_string($productarray ['parent_sku']).'"';
		return mysql_query ( $updateSourceSQL );
	}
	
	
	public function insertProduct($productarray) {
		$insert_sql = 'insert into products (parent_sku,sku,name,description,brand,color,main_image,extra_images,landingPageURL,MSRP,price,quantity,shipping,shipping_time,size,tags,UPC) 
					values("' . mysql_real_escape_string($productarray ['parent_sku']) . '","' . mysql_real_escape_string($productarray ['sku']) . '","' . mysql_real_escape_string($productarray ['name']) . '","' . mysql_real_escape_string($productarray ['description']) . '","' . mysql_real_escape_string($productarray ['brand']) . '","' . mysql_real_escape_string($productarray ['color']) . '","' . mysql_real_escape_string($productarray ['main_image']) . '","' . mysql_real_escape_string($productarray ['extra_images']) . '","' . $productarray ['landingPageURL'] . '","' . mysql_real_escape_string($productarray ['MSRP']) . '","' . mysql_real_escape_string($productarray ['price']) . '","' . mysql_real_escape_string($productarray ['quantity']) . '","' . mysql_real_escape_string($productarray ['shipping']) . '","' . mysql_real_escape_string($productarray ['shipping_time']) . '","' . mysql_real_escape_string($productarray ['size']) . '","' . mysql_real_escape_string($productarray ['tags']) . '","' . mysql_real_escape_string($productarray ['UPC']) . '")';
		return mysql_query ( $insert_sql );
	}
	public function removeProduct($parentSKU){
		$removeSql = 'delete from products where parent_sku="'.mysql_real_escape_string($parentSKU).'"';
		return mysql_query($removeSql);
	}
	
	
	
	public function getUploadProducts($accountid){
		$queryproducts = 'SELECT p.*,s.scheduledate,s.errormessage from products p, schedule_product s WHERE s.isfinished != 1 and s.accountid = '.$accountid.' and s.parent_sku = p.parent_sku order by UNIX_TIMESTAMP(s.scheduledate) desc, p.parent_sku,p.color,p.size';
		return mysql_query($queryproducts);
	}
	
	public function getOnlineProducts($accountid,$queryParentSKU,$start=0,$limit=50){
		if($queryParentSKU == null){
			//$queryonlineProducts = 'select * from onlineProducts where accountid = '.$accountid.' and review_status != "rejected"  and deleted is NULL order by number_saves desc limit '.$start.','.$limit;
			
			$queryonlineProducts = 'select max(a.price),a.* from ('.
									' select v.price, p.* from onlineProducts p ,onlineProductVars v where p.id = v. product_id and p.accountid =  '.$accountid.' and review_status != "rejected" and deleted is NULL'. 	
									' ) a'. 
									' group by id'.
									' order by number_sold desc,number_saves desc limit '.$start.','.$limit;
		}else{
			$queryonlineProducts = 'select * from onlineProducts where accountid = '.$accountid.' and parent_sku like "%'.mysql_real_escape_string($queryParentSKU).'%"';// limit '.$start.','.$limit;
		}
		return mysql_query($queryonlineProducts);
	}
	
	public function getProductDetails($accountid,$pid){
		$querypd = 'select p.*,pv.* from onlineProducts p, onlineProductVars pv where p.accountid = '.$accountid.' and p.id="'.$pid.'" and p.id = pv.product_id order by pv.color,pv.size';
		return  mysql_query($querypd);
	}
	
	public function getProductSummary($pid,$startdate,$enddate){
		$ssql = 'select * from onlineProducts op,productssummary ps where op.id = "'.$pid.'" and op.id = ps.productid'.
 				' and ps.startdate = "'.$startdate.'" and ps.enddate="'.$enddate.'"';
		return mysql_query($ssql);
	}
	
	public function getProducts($parentSKU) {
		$productsSQL = 'select * from products where parent_sku = "' . mysql_real_escape_string($parentSKU) . '"';
		return mysql_query ( $productsSQL );
	}
	
	public function getProductVars($productid){
		$pvars = 'select * from onlineProductVars where product_id = "'.$productid.'"';
		return mysql_query($pvars);
	}
	
	public function getProductIDByVSKU($accountid,$subsku){
		//$tempsku = str_replace(' ','_',$subsku);
		//$tempsku = str_replace('_','%',$tempsku);
		//$tempsku = str_replace('AND','%',$tempsku);
		//$tempsku = str_replace('"','&quot;',$tempsku);
		
		//if(strpos($tempsku,'%') === false){
			$pvs = 'select product_id from onlineProductVars where accountid = '.$accountid.' and sku = "'.mysql_real_escape_string($subsku).'"';
		//}else{
		//	$pvs = 'select product_id from onlineProductVars where accountid = '.$accountid.' and sku like "'.mysql_real_escape_string($tempsku).'"';
		//}
		return mysql_query($pvs);
	}
	
	public function getProductVarIDByVSKU($accountid,$subsku){
		$opvs = 'select id from onlineProductVars where accountid = '.$accountid.' and sku = "'.mysql_real_escape_string($subsku).'"';
		return mysql_query($opvs);
	}
	
	public function getProductSKUByID($productid){
		$psisql = 'select parent_sku from onlineProducts where id = "'.$productid.'"';
		return mysql_query($psisql);
	}
	
	public function getHotProducts($accountid,$startdate,$enddate){
		$hotsql = 'select productid from productssummary where accountid = '.$accountid.' and startdate = "'.$startdate.'" and enddate = "'.$enddate.'" and productid != "0" and orders > 0 order by orders desc';
		return mysql_query($hotsql);
	}
	
	public function getProductOrders($accountid,$productid,$startdate,$enddate){
		$posql = 'select * from productssummary where accountid = '.$accountid.' and UNIX_TIMESTAMP(startdate) >=UNIX_TIMESTAMP("'.$startdate.'") and UNIX_TIMESTAMP(startdate) <= UNIX_TIMESTAMP("'.$enddate.'") and productid = "'.$productid.'" order by UNIX_TIMESTAMP(startdate)';
		return mysql_query($posql);
	}
	
	public function getProductsMoreImpressions($accountid,$startdate,$enddate,$impressions){
		$impsql = 'select op.id,op.parent_sku,op.main_image,op.is_promoted,op.name,op.review_status,op.number_saves,op.number_sold, '.
       			  'ps.productimpressions,ps.buycart,ps.buyctr,ps.checkoutconversion,ps.orders '.
				  'from '. 
				  '(select productid,productimpressions,buycart,buyctr,checkoutconversion,orders from productssummary where accountid = '.
				  $accountid.' and startdate = "'.$startdate.'" and enddate = "'.$enddate .'" and productid != "0" and productimpressions >= '. $impressions. ') ps  '.
				  'left join onlineProducts op on op.id = ps.productid order by ps.productimpressions desc'; 
		return mysql_query($impsql);
	}
	
	public function getProductsLessImpressions($accountid,$startdate,$enddate,$impressions,$buyctr){
		$lesimpsql = 'select op.id,op.parent_sku,op.main_image,op.is_promoted,op.name,op.review_status,op.number_saves,op.number_sold, '.
       			  'ps.productimpressions,ps.buycart,ps.buyctr,ps.checkoutconversion,ps.orders '.
				  'from '. 
				  '(select productid,productimpressions,buycart,buyctr,orders,checkoutconversion from productssummary where accountid = '.
				  $accountid.' and startdate = "'.$startdate.'" and enddate = "'.$enddate .'" and productid != "0" and productimpressions < '. $impressions. 
				  ' and (left(buyctr,length(buyctr)-1)>'.$buyctr.' or orders > 0)) ps  '.
				  'left join onlineProducts op on op.id = ps.productid order by ps.productimpressions desc';
		return mysql_query($lesimpsql);
	}
	
	public function getLittleImpressionsTrend($accountid,$startdate,$enddate,$impressions){
		$littleSql = 'SELECT productid,productimpressions,startdate FROM productssummary  WHERE '. 
					' accountid = '.$accountid.' and productimpressions< '.$impressions.' and orders = 0 '.  
					' and UNIX_TIMESTAMP(startdate) >=UNIX_TIMESTAMP("'.$startdate.'") and UNIX_TIMESTAMP(startdate) <= UNIX_TIMESTAMP("'.$enddate.'") '.
					' order by productid,UNIX_TIMESTAMP(startdate) DESC';
		return mysql_query($littleSql);
	}
	
	
	public function getProductSKUCost($productsku,$accountid){
		$costsql = 'select p.sku,p.cost from ('.
				   ' select distinct pv.sku sku,p.product_id from onlineProductVars pv,'.
				   ' (select product_id from onlineProductVars where sku = "'.mysql_real_escape_string($productsku).'" and accountid = "'.$accountid.'") p'. 
				   ' where p.product_id = pv.product_id'.
				   ' ) a'. 
				   ' left join productskuinfo p on a.sku = p.sku  order by a.sku DESC';
		return mysql_query($costsql);
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
		$updateFinished = 'update schedule_product set isfinished = 1,errormessage= now() where accountid = ' . $productInfo ['accountid'] . ' and parent_sku="' . mysql_real_escape_string($productInfo ['parent_sku']) . '"';
		return mysql_query ( $updateFinished );
	}
	
	public function updateScheduleError($productInfo,$errorInfo) {
		$updateErrorFinished = 'update schedule_product set isfinished = -1, errormessage = "'.mysql_real_escape_string($errorInfo).'" where accountid = ' . $productInfo ['accountid'] . ' and parent_sku="' . mysql_real_escape_string($productInfo ['parent_sku']) . '"';
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
		$insertTracking = 'insert into tracking_data(userid,tracking_number,device_id,tracking_date) values(' . $trackingData ['user_id'] . ',"' . mysql_real_escape_string($trackingData ['tracking_number']) . '","' . $trackingData ['device_id'] . '","' . mysql_real_escape_string($trackingData ['tracking_date']) . '")';
		return mysql_query ( $insertTracking );
	}
	
	public function updateTrackingData($tracking_number,$destinate,$weight,$shippingcost,$finalcost,$orderdate,$uid){
		$updateTracking = 'update tracking_data set destinate="'.mysql_real_escape_string($destinate).'", weight="'.mysql_real_escape_string($weight).'",shippingcost="'.mysql_real_escape_string($shippingcost).'",finalshippingcost="'.mysql_real_escape_string($finalcost).'"  where tracking_number="'.mysql_real_escape_string($tracking_number).'"';
		mysql_query($updateTracking); 
		$result = mysql_affected_rows();
		if(!$result){
			$insertTracking = 'insert into tracking_data(userid,tracking_date,tracking_number,destinate,weight,shippingcost,finalshippingcost) values('.$uid.',"'.mysql_real_escape_string($orderdate).'","'.mysql_real_escape_string($tracking_number).'","'
					.mysql_real_escape_string($destinate).'",'.mysql_real_escape_string($weight).',"'.mysql_real_escape_string($shippingcost).'","'.mysql_real_escape_string($finalcost).'")';
			mysql_query($insertTracking);
			$result = mysql_affected_rows();
		}
		return $result;
	}
	
	public function updatePBKeyword($pbkeyword,$searchfactor,$competitivefactor,$highbidding){
		$queryPBKeyword = 'select * from PBKeywords where PBkeyword="'.mysql_real_escape_string($pbkeyword).'"';
		$queryresult = mysql_query($queryPBKeyword);
		
		if(mysql_fetch_array($queryresult)){
			$processPBKeyword = 'update PBKeywords set searchfactor="'.mysql_real_escape_string($searchfactor).'", competitivefactor="'.mysql_real_escape_string($competitivefactor).'",highbidding="'.mysql_real_escape_string($highbidding).'"  where PBkeyword="'.mysql_real_escape_string($pbkeyword).'"';
		}else{
			$processPBKeyword = 'insert into PBKeywords(PBkeyword,searchfactor,competitivefactor,highbidding) values("'.mysql_real_escape_string($pbkeyword).'","'.mysql_real_escape_string($searchfactor).'","'.mysql_real_escape_string($competitivefactor).'","'.mysql_real_escape_string($highbidding).'")';
		}
		mysql_query($processPBKeyword);
		$result = mysql_affected_rows();
		return $result;
	}
	
	public function getUserLabels($userid) {
		$querylabels = "SELECT l.id id,p.parent_sku parentsku,l.CN_Name cn_name,l.EN_Name en_name FROM labels l, product_label p WHERE p.userid = " . $userid . " and p.iswe=0 and p.label_id = l.id";
		return mysql_query ( $querylabels );
	}
	
	public function getWEUserLabels($userid){
		$welabels = 'select parent_sku, label_id from product_label where userid = '.$userid.' and iswe=1';
		return mysql_query($welabels);
	}
	
	public function insertLabel($cn_name, $en_name) {
		// when is WishExpress, just return the WE productid directly;
		if($en_name == null)
			return $cn_name;
		
		$sqllabel = 'select id from labels where CN_Name = "' . mysql_real_escape_string($cn_name) . '" and EN_Name = "' . mysql_real_escape_string($en_name) . '"';
		$result = mysql_query ( $sqllabel );
		$row = mysql_fetch_array ( $result );
		if ($row) {
			return $row ['id'];
		} else {
			$insertlabel = 'insert into labels(CN_Name,EN_Name) values("' . mysql_real_escape_string($cn_name) . '","' . mysql_real_escape_string($en_name) . '")';
			mysql_query ( $insertlabel );
			return mysql_insert_id ();
		}
	}
	public function insertproductLabel($userid, $parent_sku, $labelid, $iswe=0) {
		$delsql = 'delete from product_label where userid = '.$userid.' and parent_sku = "'.$parent_sku.'" and iswe='.$iswe.';';
		$insertpl = 'insert into product_label(label_id,parent_sku,userid,iswe) values(' . $labelid . ',"' . mysql_real_escape_string($parent_sku) . '",' . $userid . ",". $iswe.')';
		mysql_query($delsql);
		return mysql_query ( $insertpl );
	}
	public function getExpressInfo($userid) {
		$userSql = 'select express_attr_name,express_attr_value from express_attr_info where userid = ' . $userid;
		return mysql_query ( $userSql );
	}
	
	public function getSubExpressInfo(){
		$sei = 'select express_id,express_name,express_code from express_info where parent_express_id != 0';
		return mysql_query($sei);
	}

	public function getYanWenExpresses($yanwencode){
		$ywe = 'select c.express_id,c.express_name,c.express_code,c.provider_name from express_info c,('.
			   ' select express_id,express_name,express_code from express_info where express_code = "'.$yanwencode.'"'.
			   ' ) p'.
			   ' where c.parent_express_id = p.express_id';	
		return mysql_query($ywe);
	}
	
	public function getExpressInfos($userid,$iswe){
		$uei = 'SELECT pe.product_id,pe.countrycode,pe.express_id,e.express_name,e.express_code,e.provider_name FROM product_express_info pe,express_info e WHERE pe.userid='.$userid.'  and pe.iswe='.$iswe.' and pe.express_id = e.express_id';
		return mysql_query($uei);
	}
	
	public function insertProductExpress($userid,$productid,$expressid,$countrycode,$iswe=0){
		$delsql = 'delete from product_express_info where userid ='.$userid.' and product_id = "'.$productid.'" and iswe='.$iswe.' and  countrycode = "'.mysql_real_escape_string($countrycode).'"';
		$del = mysql_query($delsql);
		$ipe = 'insert into product_express_info(userid,product_id,express_id,countrycode,iswe) values('.$userid.',"'.$productid.'",'.$expressid.',"'.mysql_real_escape_string($countrycode).'",'.$iswe.')';
		$result = mysql_query($ipe);
		echo "<br/>ipe:".$ipe;
		return mysql_affected_rows();
	}
	
	public function  getProductSource($accountid,$parent_sku = null){
		$psource = "SELECT distinct p.parent_sku,p.name,p.main_image,i.source_url,i.lowesttotalprice FROM `products` p, `productinfo` i WHERE p.parent_sku = i.parent_sku and i.accountid = ".$accountid;
		if($parent_sku != null){
			$psource .= " and i.parent_sku like '%".mysql_real_escape_string($parent_sku)."%'";
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
		$queryToken = "select userid from resetpassword where token = '".mysql_real_escape_string($token)."'";
		$result = mysql_query($queryToken);
		while( $useridarray = mysql_fetch_array ( $result )){
			return $useridarray['userid'];
		}
		return null;
	}
	
	public function updatepsd($userid,$newpassword){
		$psdupdate = "update users set psd = '".mysql_real_escape_string($newpassword)."' where userid = ".$userid;
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
		return mysql_query($insertWeek);
	}
	
	public function isProductExist($productid){
		$querySql = 'select * from onlineProducts where id = "'.$productid.'"';
		return mysql_query($querySql);
	}
	
	public function insertOnlineProduct($productarray) {
		$insert_sql = 'insert into onlineProducts (accountid, id, parent_sku,name,description,original_image_url,main_image,extra_images,is_promoted,tags,review_status,number_saves,number_sold,date_uploaded,wecountrycodes,date_updated)
					values(' . $productarray ['accountid'] . ',"'.$productarray['id'].'","' . mysql_real_escape_string($productarray ['parent_sku']) . '","' . mysql_real_escape_string($productarray ['name']) 
							. '","' . mysql_real_escape_string($productarray ['description']) . '","' . mysql_real_escape_string($productarray ['original_image_url']) . '","' . mysql_real_escape_string($productarray ['main_image']) . '","' . mysql_real_escape_string($productarray ['extra_images']) . '","' . mysql_real_escape_string($productarray ['is_promoted']) . '","' 
							. mysql_real_escape_string($productarray ['tags']) . '","' . mysql_real_escape_string($productarray ['review_status']) . '",' . mysql_real_escape_string($productarray ['number_saves']) . ',' . mysql_real_escape_string($productarray ['number_sold']) . ',"' . mysql_real_escape_string($productarray ['wecountrycodes']) .'",DATE_FORMAT("' . mysql_real_escape_string($productarray ['date_uploaded']) . '","%Y-%m-%d"),DATE_FORMAT("' . mysql_real_escape_string($productarray ['date_updated']) . '","%Y-%m-%d"))';
		return mysql_query ( $insert_sql );
	}
	
	public function updateOnlineProduct($productarray){
		$update_sql = 'update onlineProducts set name="'.mysql_real_escape_string($productarray ['name']) . '", description = "'.mysql_real_escape_string($productarray ['description']) . '",main_image="'.mysql_real_escape_string($productarray ['main_image']) . '",extra_images = "'.mysql_real_escape_string($productarray ['extra_images']) 
		. '",is_promoted = "'.mysql_real_escape_string($productarray ['is_promoted']) . '",tags = "'.mysql_real_escape_string($productarray ['tags']) . '",review_status = "'.mysql_real_escape_string($productarray ['review_status']) 
		. '",number_saves = '.mysql_real_escape_string($productarray ['number_saves']) . ',number_sold = '.mysql_real_escape_string($productarray ['number_sold']) .',wecountrycodes = "'.mysql_real_escape_string($productarray ['wecountrycodes']) . '" where id = "'.$productarray['id'].'"';
		return mysql_query ( $update_sql );
	}
	
	public function isProductVarExist($productVarID){
		$querySql = 'select * from onlineProductVars where id = "'.$productVarID.'"';
		return mysql_query($querySql);
	}
	
	public function insertOnlineProductVar($productarray) {
		$insertvar_sql = 'insert into onlineProductVars (accountid, id, product_id,sku,color,size,enabled,price,all_images,inventory,shipping,shipping_time,MSRP)
					values(' . $productarray ['accountid'] . ',"'.$productarray['id'].'","' . $productarray ['product_id'] . '","' . mysql_real_escape_string($productarray ['sku'])
						. '","' . mysql_real_escape_string($productarray ['color']) . '","' . mysql_real_escape_string($productarray ['size']) . '","' . mysql_real_escape_string($productarray ['enabled']) . '","' . mysql_real_escape_string($productarray ['price']) . '","' . mysql_real_escape_string($productarray ['all_images']) . '",'
								. mysql_real_escape_string($productarray ['inventory']) . ',"' . mysql_real_escape_string($productarray ['shipping']) . '","' . mysql_real_escape_string($productarray ['shipping_time']) . '","' . mysql_real_escape_string($productarray ['MSRP']) . '")';
		return mysql_query ( $insertvar_sql );
	}
	
	public function updateOnlineProductVar($productarray){
		$update_sql = 'update onlineProductVars set color = "'.mysql_real_escape_string($productarray ['color']) . '",size = "'.mysql_real_escape_string($productarray ['size']) . '",enabled = "'
				.mysql_real_escape_string($productarray ['enabled']) . '",price = "'.mysql_real_escape_string($productarray ['price']) . '",all_images="'.mysql_real_escape_string($productarray ['all_images']) . '",inventory='.mysql_real_escape_string($productarray ['inventory']) 
				. ',shipping = "'.mysql_real_escape_string($productarray ['shipping']) . '",shipping_time="'.mysql_real_escape_string($productarray ['shipping_time']) . '",MSRP="'.mysql_real_escape_string($productarray ['MSRP']) .'" where id = "'.$productarray['id'].'"';
		return mysql_query ( $update_sql );
	}
	
	public function getSKUSforInventory($accountid){
		$inventorySql = 'SELECT v.sku,v.product_id,v.id,v.enabled,v.inventory FROM onlineProductVars v,optimizeparam o WHERE v.enabled = "true" and v.accountid = '.$accountid.' and v.inventory > 0 and v.inventory<o.inventory';
		return mysql_query($inventorySql);
	}
	
	public function getSKUUploadMoreThanDays($productid){
		$queryUploadDays = 'select parent_sku,is_promoted from onlineProducts where id = "'.$productid.'" and (TO_DAYS(now())-TO_DAYS(date_uploaded))> 90 ';
		return mysql_query($queryUploadDays);
	}
	
	public function getWeekImpressions($accountid,$startDate,$endDate,$daysuploaded){
		$optimizeSql = 'select * from onlineProducts op,'.
					   '  (select * from (select p.id, p.date_uploaded,p.number_sold,ps.productimpressions	from'.
					   '      (select distinct p.id ,p.date_uploaded,p.number_sold'.
					   '       from onlineProducts p, onlineProductVars pv'.
					   '       where p.accountid = '.$accountid.' and pv.enabled = "true" and (TO_DAYS(now())-TO_DAYS(p.date_uploaded))>'.$daysuploaded.' and p.id = pv.product_id) p'.
				       '       left join (SELECT * from productssummary where accountid = '.$accountid.' and startdate = "'.$startDate.'" and enddate = "'.$endDate.'") ps'.
					   '       on p.id = ps.productid  ) result where productimpressions is NULL) pids'.
					   '       where op.id = pids.id order by op.number_sold desc';
		return mysql_query($optimizeSql);
	}
	
	
	public function getNewProductImpressionsInfo($accountid,$startdate,$enddate){
		$newpi = 'select op.id,op.name,op.parent_sku,op.main_image,op.is_promoted,op.review_status,op.date_uploaded'.
      			 ',ps.productimpressions,ps.buycart,ps.buyctr,ps.orders,ps.checkoutconversion,ps.gmv'. 
				 ' from ('.
				 ' select *'. 
				 ' from onlineProducts'. 
				 ' where accountid = '. $accountid .' and UNIX_TIMESTAMP(date_uploaded)>=UNIX_TIMESTAMP("'.$startdate.'") and UNIX_TIMESTAMP(date_uploaded)<=UNIX_TIMESTAMP("'.$enddate.'")'. 
				 ' ) op'.
	             ' left join productssummary ps'. 
				 ' on op.id = ps.productid and ps.startdate = "'.$startdate.'" and ps.enddate = "'.$enddate.'"'.
				 ' order by ps.productimpressions DESC';
		return mysql_query($newpi);
	}
	
	public function getOptimizeParams(){
		$osql = 'select left(checkoutconversion,length(checkoutconversion)-1) checkoutconversion,left(buyctr,length(buyctr)-1) buyctr,impression,inventory,inventoryextra,daysuploaded from optimizeparam';
		return mysql_query($osql);
	}
	
	public function insertOptimizeJob($accountid,$operator,$productid,$startdate){
		$insertjob = 'insert into optimizejobs(accountid,operator,productid,startdate) values('.$accountid.',"'.$operator.'","'.$productid.'",DATE_FORMAT("'.$startdate.'","%Y-%m-%d"))';
		return mysql_query($insertjob);
	}
	
	public function getOptimizeJobs(){
		$getJobs = 'SELECT * FROM `optimizejobs` WHERE isfinished is NULL order by startdate limit 10';
		return mysql_query($getJobs);
	}
	
	public function updateJobMsg($accountid,$productid,$startdate,$msg) {
		$updateMsgSql = 'update optimizejobs set errormessage = "' . $msg . '" where accountid = "'.$accountid.'"  and productid = "'.$productid.'" and startdate = "'.$startdate.'"';
		return mysql_query ( $updateMsgSql );
	}
	
	public function updateJobFinished($accountid,$isFinished,$productid,$startdate,$errorMsg) {
		$updateFinished = 'update optimizejobs set isfinished = '.$isFinished.',errormessage= "'.$errorMsg.'" where  accountid = "'.$accountid.'"  and  productid = "'.$productid.'" and startdate = "'.$startdate.'"';
		return mysql_query ( $updateFinished );
	}
	
	public function getPromotedProducts($accountid){
		$pps = 'select id,parent_sku from onlineProducts where accountid = "'.$accountid.'" and is_promoted = "True" and review_status != "rejected"';
		return mysql_query($pps);
	}
	
	public function getProductsHasOrder($accountid){
		$pos = 'select distinct ov.product_id from ('.
			   ' select distinct sku from orders where TIMESTAMPDIFF(DAY,ordertime,now())<=2 and accountid = "'.$accountid.'"'.
			   ' ) os left join onlineProductVars ov on ov.sku = os.sku and ov.accountid = "'.$accountid.'"';
		return mysql_query($pos);
	}
	
	public function getLowesttotalprice($productid,$accountid){
		$ltp = 'select o.id,o.parent_sku,p.* from onlineProducts o ,productinfo p where o.id = "'.$productid.'" and o.accountid = "'.$accountid.'" and o.parent_sku = p.parent_sku and o.accountid = p.accountid';
		$lowesttotalpriceResult = mysql_query($ltp);
		if($lowesttotalpricearray = mysql_fetch_array($lowesttotalpriceResult)){
			$lowesttotalprice = $lowesttotalpricearray['lowesttotalprice'];
			return $lowesttotalprice;
		}else{
			$ltp2 = 'select o.id,o.sku,p.* from onlineProductVars o ,productinfo p where o.product_id = "'.$productid.'" and o.accountid = "'.$accountid.'" and o.sku = p.parent_sku and o.accountid = p.accountid';
			$lowesttotalpriceResult2 = mysql_query($ltp2);
			if($lowesttotalpricearray2 = mysql_fetch_array($lowesttotalpriceResult2)){
				$lowesttotalprice2 = $lowesttotalpricearray2['lowesttotalprice'];
				return $lowesttotalprice2;
			}
		}
		return null;
	}

	public function getDisabledProducts($accountid){
		$dpl = 'select * from '.
				' ('.
				' select o.id, o.deleted,o.name,o.parent_sku,o.main_image,o.number_saves,o.number_sold,o.date_uploaded,o.is_promoted,o.review_status,p.* from'. 
				' ('.
				' select distinct product_id from onlineProductVars where enabled = "false" and accountid = "'.$accountid.'"'.
				' ) p'.
				' left join onlineProducts o on o.id = p.product_id'.
				' ) r where r.review_status != "reject" and r.deleted is NULL order by r.number_sold desc, r.number_saves desc, r.date_uploaded DESC limit 100 ';
		return mysql_query($dpl);
	}
	
	public function getNeedDisableProducts($accountid){
		$ndsql = 'SELECT * FROM onlineProducts where accountid = "'.$accountid.'" and is_promoted = false and number_sold = 0 and wecountrycodes = "" and deleted is NULL and review_status != "rejected" order by review_status';
		return  mysql_query($ndsql);
	}
	
	public function deleteProduct($accountid,$productid){
		$dp = 'update onlineProducts set deleted = 1 where accountid = "'.$accountid.'" and id = "'.$productid.'"';
		return mysql_query($dp);
	}
	
	public function getProductVarsEnabled($pid){
		$pve = 'SELECT * FROM onlineProductVars WHERE product_id = "'.$pid.'" and enabled = "true"';
		return mysql_query($pve);
	}
	
	public function getProductShippingCost($sku,$accountid){

		$psc = 'select * from ('.
			   ' select ps.product_id,o.sku,o.tracking,o.ordertime,o.totalcost,o.quantity from orders o,'. 
			   ' ('.
			   ' select distinct pv.sku,p.product_id from onlineProductVars pv,'.
			   ' (select product_id from onlineProductVars where sku = "'.$sku.'" and accountid = "'.$accountid.'") p'.
			   ' where p.product_id = pv.product_id'.
	           ' ) ps'.
	    	   ' where o.accountid = "'.$accountid.'" and o.sku = ps.sku'.
			   ' ) a'.
			   ' left join tracking_data t on a.tracking = t.tracking_number'.
			   ' and t.finalshippingcost IS NOT NULL'. 
			   ' order by t.tracking_date DESC limit 400';
		return mysql_query($psc);
	}
	
	public function  getCountrycode(){
		$cc = 'select * from countrycode';
		return mysql_query($cc);
	}
	
	
	public function addWishpostaccount($accountid,$wishpostaccountname,$clientid,$clientsecret){
		$addSql = 'insert into wishpostaccounts(wishpostaccountname,accountid,clientid,clientsecret) values("'.$wishpostaccountname.'",'.$accountid.',"'.$clientid.'","'.$clientsecret.'")';
		mysql_query($addSql);
		return mysql_insert_id();
	}
	
	public function updateWishpostToken($wishpostaccountid, $newToken, $newRefreshToken) {
		if(strlen($newToken) > 1 && strlen($newRefreshToken) > 1){
			$updateTokenSql = "update wishpostaccounts set token = '" . $newToken . "',refresh_token='" . $newRefreshToken . "' where wishpostaccountid = '" . $wishpostaccountid . "'";
			return mysql_query ( $updateTokenSql );
		}
		return false;
	}
	
	public function getWishpostaccounts($userid){
		$wpsql = 'select wishpostaccountname,c.accountid accountid,c.accountname accountname  '.
					' from wishpostaccounts w,( '.
					' select accountid,accountname from accounts where userid = '.$userid.
					' ) c '.
					' where w.accountid = c.accountid and w.token is not null';
		return mysql_query($wpsql);
	}
	
	public function getWPAccessToken($accountid){
		$atsql = 'select * from wishpostaccounts where accountid = '.$accountid;
		return mysql_query($atsql);
	}
	
	public function insertRefundRecord($refundrecord){
		$shippingaddress = $refundrecord['Name'].$refundrecord['StreetAddress1'].$refundrecord['StreetAddress2'].$refundrecord['City'].$refundrecord['State'].$refundrecord['Zipcode'].$refundrecord['PhoneNumber'].$refundrecord['Countrycode'];
		$insertrrsql = 'insert into refundrecords(accountid,TransactionDate,OrderID,TransactionID,OrderState,SKU,Product,ProductID,ProductLink,ProductImageURL,Size,Color,Price,Cost,Shipping,ShippingCost,Quantity,TotalCost,DaystoFulfill,HourstoFulfill,ShippedDate,ConfirmedDelivery,ConfirmedDeliveryDate,Provider,Tracking,TrackingConfirmed,TrackingConfirmedDate,ShippingAddress,Name,FirstName,LastName,StreetAddress1,StreetAddress2,City,State,Zipcode,Country,LastUpdated,PhoneNumber,Countrycode,RefundResponsibility,RefundAmount,RefundDate,RefundReason,IsWishExpress,WishExpressDeliveryDeadline,IsRequireDelivery,md5code) values('.
						$refundrecord['accountid'].',"'.$refundrecord['TransactionDate'].'","'.$refundrecord['OrderID'].'","'.$refundrecord['TransactionID'].'","'.$refundrecord['OrderState'].'","'.mysql_real_escape_string($refundrecord['SKU']).'","'.mysql_real_escape_string($refundrecord['Product']).'","'.$refundrecord['ProductID'].'","'.mysql_real_escape_string($refundrecord['ProductLink']).'","'.mysql_real_escape_string($refundrecord['ProductImageURL']).'","'.mysql_real_escape_string($refundrecord['Size']).'","'.$refundrecord['Color'].'","'.$refundrecord['Price'].'","'.$refundrecord['Cost'].'","'.$refundrecord['Shipping'].'","'.$refundrecord['ShippingCost'].'","'.
						$refundrecord['Quantity'].'","'.$refundrecord['TotalCost'].'","'.$refundrecord['DaystoFulfill'].'","'.$refundrecord['HourstoFulfill'].'","'.$refundrecord['ShippedDate'].'","'.$refundrecord['ConfirmedDelivery'].'","'.$refundrecord['ConfirmedDeliveryDate'].'","'.$refundrecord['Provider'].'","'.$refundrecord['Tracking'].'","'.$refundrecord['TrackingConfirmed'].'","'.$refundrecord['TrackingConfirmedDate'].'","'.mysql_real_escape_string($refundrecord['ShippingAddress']).'","'.mysql_real_escape_string($refundrecord['Name']).'","'.mysql_real_escape_string($refundrecord['FirstName']).'","'.mysql_real_escape_string($refundrecord['LastName']).'","'.mysql_real_escape_string($refundrecord['StreetAddress1']).'","'.mysql_real_escape_string($refundrecord['StreetAddress2']).'","'.
						mysql_real_escape_string($refundrecord['City']).'","'.mysql_real_escape_string($refundrecord['State']).'","'.$refundrecord['Zipcode'].'","'.$refundrecord['Country'].'","'.$refundrecord['LastUpdated'].'","'.$refundrecord['PhoneNumber'].'","'.$refundrecord['Countrycode'].'","'.$refundrecord['RefundResponsibility'].'","'.$refundrecord['RefundAmount'].'","'.$refundrecord['RefundDate'].'","'.$refundrecord['RefundReason'].'","'.$refundrecord['IsWishExpress'].'","'.$refundrecord['WishExpressDeliveryDeadline'].'","'.$refundrecord['IsRequireDelivery'].'","'.md5($shippingaddress).'")';
		return mysql_query($insertrrsql);
	}
	
	public function getrefundorder($refundcode){
		$rcsql = 'select * from refundrecords where md5code = "'.$refundcode.'"';
		return mysql_query($rcsql);
	}
	
	
	public function getVacationProducts($accountid,$startdate,$enddate){
		$vpsql = ' SELECT distinct productid FROM optimizejobs WHERE operator = "DISABLEPRODUCT" and startdate > "'.$startdate.'"  and startdate < "'.$enddate.'" and accountid = "'.$accountid.'"';
		echo "<br/><br/><br/>,query sql:".$vpsql;
		return mysql_query($vpsql);
	}
	
	
	public function getProductsInventory($userid,$parentsku = null){
		if($parentsku == null){
			$pisql = 'select * from productsinventory where userid = "'.$userid.'" order by parentSKU,SKU';	
		}else{
			$pisql = 'select * from productsinventory where userid = "'.$userid.'" and parentSKU like "%'.mysql_real_escape_string($parentsku).'%" order by parentSKU,SKU';
		}
		echo "<br/> get inventory sql :".$pisql;
		return mysql_query($pisql);
	}
	
	public function getProductSKUs($accountid,$parentsku){
		$pssql = 'select p.id,p.parent_sku,pv.sku from onlineProducts p,onlineProductVars pv where p.accountid = '.$accountid.' and p.parent_sku = "'.mysql_real_escape_string($parentsku).'" and p.id = pv.product_id';
		return mysql_query($pssql);
	}
	
	public function getUserid($accountid){
		$aidsql = 'select userid from accounts where accountid = '.$accountid;
		return mysql_query($aidsql);
	}
	
	public function updateinventory($userid,$parentsku,$SKU,$operator,$quantity){
		$upitsql = 'update productsinventory set inventory = inventory ';
		if($operator == 0 ){
			$upitsql .= '-';
		}else if($operator == 1){
			$upitsql .= '+';
		}else{
			echo "<br/>update inventory, operator code invalid of userid:".$userid.", SKU:".$SKU;
			return;
		}
		
		$upitsql .= $quantity. ' where userid = '.$userid.' and parentSKU = "'.mysql_real_escape_string($parentsku).'" and SKU = "'.mysql_real_escape_string($SKU).'"';
		//echo "<br/> inventory update sql:".$upitsql;
		return mysql_query($upitsql);
	}
	
	public function inventoryoperaterecord($userid,$parentsku,$SKU,$operator,$quantity,$note){
		$itosql = 'insert into inventoryoperate(operatoruserid,operatetime,parentSKU,SKU,operator,quantity,note) values('.$userid.',"'.date('Y-m-d H:i').'","'.mysql_real_escape_string($parentsku).'","'.
					mysql_real_escape_string($SKU).'",'.$operator.','.$quantity.',"'.$note.'")';
		//echo "insert inventory operator sql:".$itosql;
		return mysql_query($itosql);
	}
	
	
	public function addWEProduct($weproductid,$weproductsku){
		$weaddsql = 'insert into weproducts(weproductid,weproductsku) values('.$weproductid.',"'.mysql_real_escape_string($weproductsku).'")';
		return mysql_query($weaddsql);
	}
	
	public function getWEProducts(){
		$getwesql = 'select weproductid product_id,weproductsku product_sku from weproducts';
		return mysql_query($getwesql);
	}
	
	public function getWEProductSKUBYID($weproductid){
		$getwepsku = 'select weproductsku from weproducts where weproductid = '.$weproductid;
		return mysql_query($getwepsku);
	}
	
	public function getWEShippingMethod($weproductid,$wecountrycode){
		$getweshippingsql = 'select e.express_code,e.provider_name from express_info e, product_express_info pe where pe.iswe = 1 and pe.product_id ="'.$weproductid.'"  and pe.countrycode = "'.$wecountrycode.'" and pe.express_id = e.express_id';
		return mysql_query($getweshippingsql);
	}
	
	public function getWEProductID($accountid, $weproductsku){
		$getwepid = 'select label_id from product_label pl,accounts a where a.userid = pl.userid and a.accountid = '.$accountid.' and pl.iswe = 1 and pl.parent_sku = "'.mysql_real_escape_string($weproductsku).'"';
		echo "<br/>get wepid:".$getwepid;
		return mysql_query($getwepid);
	}
	
	public function addweorderinfo($orderinfo){
		$addweordersql = 'insert into weorders(orderid,weordercode,weorderstatus,wetrackingno,wetotalfee,weshippingfee,weoptfee) values("'.$orderinfo['orderid'].'","'.
						$orderinfo['weordercode'].'","'.$orderinfo['weorderstatus'].'","'.$orderinfo['wetrackingno'].'","'.$orderinfo['wetotalfee'].'","'.
						$orderinfo['weshippingfee'].'","'.$orderinfo['weoptfee'].'")';
		return mysql_query($addweordersql);
	}
	
	public function getweordercodebyid($orderid){
		$getcodesql = 'select weordercode from weorders where orderid = "'.$orderid.'"';
		return mysql_query($getcodesql);
	}
	
	public function updateweorderinfo($weorderinfo){
		$updatewesql = 'update weorders set weorderstatus ="'.$weorderinfo['order_status'].'", wetrackingno="'.$weorderinfo['tracking_no'].'", wetotalfee="'.$weorderinfo['totalFee'].'",weshippingfee="'.
						$weorderinfo['SHIPPING'].'",weoptfee="'.$weorderinfo['OPF'].'" where weordercode="'.$weorderinfo['order_code'].'"';
		return mysql_query($updatewesql);
	}
	
	public function getTrackingsFromDay($sinceday){
		$gettrackingssql = 'select o.ordertime,o.tracking from orders o left join tracking_data t  on o.tracking = t.tracking_number where UNIX_TIMESTAMP(o.ordertime)>UNIX_TIMESTAMP("'.$sinceday.'")  and t.tracking_number is not NULL and  t.shippingdays is NULL order by o.ordertime ASC limit 40';
		return mysql_query($gettrackingssql);
	}
	
	public function updateDeliveryData($tracking,$deliverydate,$days){
		$updatetksql = 'update tracking_data set delivery_date="'.$deliverydate.'" , shippingdays='.$days.'  where tracking_number="'.$tracking.'"';
		return mysql_query($updatetksql);
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



		