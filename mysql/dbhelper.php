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
	
	public function uploadWishpostTracking($transactionid,$tracking,$status){
		$wishpostUpload = "update orders set provider='WishPost',tracking='". $tracking ."',orderstatus= ".$status." where transactionid = '".$transactionid."';";
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
	
	public function getwishorders($accountid, $orderstatus){
		$woquery_sql = "SELECT transactionid,orderid,provider, tracking FROM orders WHERE provider = 'WishPost' and accountid = '" . $accountid . "' and orderstatus = '" . $orderstatus . "'";
		echo "<br/>query:".$woquery_sql;
		$result = mysql_query ( $woquery_sql );
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
		$EUBOrderSql = "SELECT o.accountid, o.transactionid, o.orderid,o.sku,o.productname,o.color, o.size,o.quantity, o.name,o.streetaddress1,o.streetaddress2,o.city,o.state,o.zipcode,o.phonenumber,o.countrycode FROM orders o,accounts a WHERE a.userid = ". $userid. " and a.accountid = o.accountid and o.orderstatus = '0' order by o.transactionid";
		return mysql_query ( $EUBOrderSql );
	}
	public function insertProductSource($accountid, $productarray) {
		$insertSourceSQL = 'insert into productinfo(accountid, parent_sku,source_url,lowesttotalprice) values (' . $accountid . ',"' . $productarray ['parent_sku'] . '","' . $productarray ['productSourceURL'] . '","'.$productarray['lowesttotalprice'].'")';
		return mysql_query ( $insertSourceSQL );
	}
	
	public function updateProductSource($accountid, $productarray){
		$updateSourceSQL = 'update productinfo set source_url="'. $productarray ['productSourceURL'] . '", lowesttotalprice="'.$productarray['lowesttotalprice'].'"  where accountid = '.$accountid.' and parent_sku="'.$productarray ['parent_sku'].'"';
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
	
	public function getOnlineProducts($accountid,$queryParentSKU,$start=0,$limit=50){
		if($queryParentSKU == null){
			//$queryonlineProducts = 'select * from onlineProducts where accountid = '.$accountid.' and review_status != "rejected"  and deleted is NULL order by number_saves desc limit '.$start.','.$limit;
			
			$queryonlineProducts = 'select max(a.price),a.* from ('.
									' select v.price, p.* from onlineProducts p ,onlineProductVars v where p.id = v. product_id and p.accountid =  '.$accountid.' and review_status != "rejected" and deleted is NULL'. 	
									' ) a'. 
									' group by id'.
									' order by number_sold desc,number_saves desc limit 100';
		}else{
			$queryonlineProducts = 'select * from onlineProducts where accountid = '.$accountid.' and parent_sku like "%'.$queryParentSKU.'%" limit '.$start.','.$limit;
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
		$productsSQL = 'select * from products where parent_sku = "' . $parentSKU . '"';
		return mysql_query ( $productsSQL );
	}
	
	public function getProductVars($productid){
		$pvars = 'select * from onlineProductVars where product_id = "'.$productid.'"';
		return mysql_query($pvars);
	}
	
	public function getProductIDByVSKU($accountid,$subsku){
		$tempsku = str_replace('_','%',$subsku);
		$tempsku = str_replace('AND','%',$tempsku);
		if(strpos($tempsku,'%') === false){
			$pvs = 'select product_id from onlineProductVars where accountid = '.$accountid.' and sku = "'.$tempsku.'"';
		}else{
			$pvs = 'select product_id from onlineProductVars where accountid = '.$accountid.' and sku like "'.$tempsku.'"';
		}
		return mysql_query($pvs);
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
	
	public function updateTrackingData($tracking_number,$destinate,$weight,$shippingcost,$finalcost,$orderdate,$uid){
		$updateTracking = 'update tracking_data set destinate="'.$destinate.'", weight="'.$weight.'",shippingcost="'.$shippingcost.'",finalshippingcost="'.$finalcost.'"  where tracking_number="'.$tracking_number.'"';
		mysql_query($updateTracking); 
		$result = mysql_affected_rows();
		if(!$result){
			$insertTracking = 'insert into tracking_data(userid,tracking_date,tracking_number,destinate,weight,shippingcost,finalshippingcost) values('.$uid.',"'.$orderdate.'","'.$tracking_number.'","'
					.$destinate.'",'.$weight.',"'.$shippingcost.'","'.$finalcost.'")';
			mysql_query($insertTracking);
			$result = mysql_affected_rows();
		}
		return $result;
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
	
	public function getExpressInfos($userid){
		$uei = 'SELECT pe.product_id,pe.countrycode,pe.express_id,e.express_name,e.express_code,e.provider_name FROM product_express_info pe,express_info e WHERE pe.userid='.$userid.'  and pe.express_id = e.express_id';
		return mysql_query($uei);
	}
	
	public function insertProductExpress($userid,$productid,$expressid,$countrycode){
		$delsql = 'delete from product_express_info where userid ='.$userid.' and product_id = "'.$productid.'" and  countrycode = "'.$countrycode.'"';
		$del = mysql_query($delsql);
		$ipe = 'insert into product_express_info(userid,product_id,express_id,countrycode) values('.$userid.',"'.$productid.'",'.$expressid.',"'.$countrycode.'")';
		$result = mysql_query($ipe);
		return mysql_affected_rows();
	}
	
	public function  getProductSource($accountid,$parent_sku = null){
		$psource = "SELECT distinct p.parent_sku,p.name,p.main_image,i.source_url,i.lowesttotalprice FROM `products` p, `productinfo` i WHERE p.parent_sku = i.parent_sku and i.accountid = ".$accountid;
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
		return mysql_query($insertWeek);
	}
	
	public function isProductExist($productid){
		$querySql = 'select * from onlineProducts where id = "'.$productid.'"';
		return mysql_query($querySql);
	}
	
	public function insertOnlineProduct($productarray) {
		$insert_sql = 'insert into onlineProducts (accountid, id, parent_sku,name,description,original_image_url,main_image,extra_images,is_promoted,tags,review_status,number_saves,number_sold,date_uploaded,date_updated)
					values(' . $productarray ['accountid'] . ',"'.$productarray['id'].'","' . $productarray ['parent_sku'] . '","' . $productarray ['name'] 
							. '","' . $productarray ['description'] . '","' . $productarray ['original_image_url'] . '","' . $productarray ['main_image'] . '","' . $productarray ['extra_images'] . '","' . $productarray ['is_promoted'] . '","' 
							. $productarray ['tags'] . '","' . $productarray ['review_status'] . '",' . $productarray ['number_saves'] . ',' . $productarray ['number_sold'] . ',DATE_FORMAT("' . $productarray ['date_uploaded'] . '","%Y-%m-%d"),DATE_FORMAT("' . $productarray ['date_updated'] . '","%Y-%m-%d"))';
		return mysql_query ( $insert_sql );
	}
	
	public function updateOnlineProduct($productarray){
		$update_sql = 'update onlineProducts set name="'.$productarray ['name'] . '", description = "'.$productarray ['description'] . '",main_image="'.$productarray ['main_image'] . '",extra_images = "'.$productarray ['extra_images'] 
		. '",is_promoted = "'.$productarray ['is_promoted'] . '",tags = "'.$productarray ['tags'] . '",review_status = "'.$productarray ['review_status'] 
		. '",number_saves = '.$productarray ['number_saves'] . ',number_sold = '.$productarray ['number_sold'] . ' where id = "'.$productarray['id'].'"';
		return mysql_query ( $update_sql );
	}
	
	public function isProductVarExist($productVarID){
		$querySql = 'select * from onlineProductVars where id = "'.$productVarID.'"';
		return mysql_query($querySql);
	}
	
	public function insertOnlineProductVar($productarray) {
		$insertvar_sql = 'insert into onlineProductVars (accountid, id, product_id,sku,color,size,enabled,price,all_images,inventory,shipping,shipping_time,MSRP)
					values(' . $productarray ['accountid'] . ',"'.$productarray['id'].'","' . $productarray ['product_id'] . '","' . $productarray ['sku']
						. '","' . $productarray ['color'] . '","' . $productarray ['size'] . '","' . $productarray ['enabled'] . '","' . $productarray ['price'] . '","' . $productarray ['all_images'] . '",'
								. $productarray ['inventory'] . ',"' . $productarray ['shipping'] . '","' . $productarray ['shipping_time'] . '","' . $productarray ['MSRP'] . '")';
		return mysql_query ( $insertvar_sql );
	}
	
	public function updateOnlineProductVar($productarray){
		$update_sql = 'update onlineProductVars set color = "'.$productarray ['color'] . '",size = "'.$productarray ['size'] . '",enabled = "'
				.$productarray ['enabled'] . '",price = "'.$productarray ['price'] . '",all_images="'.$productarray ['all_images'] . '",inventory='.$productarray ['inventory'] 
				. ',shipping = "'.$productarray ['shipping'] . '",shipping_time="'.$productarray ['shipping_time'] . '",MSRP="'.$productarray ['MSRP'] .'" where id = "'.$productarray['id'].'"';
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
			   ' select ps.product_id,o.sku,o.tracking,o.ordertime,o.totalcost from orders o,'. 
			   ' ('.
			   ' select distinct pv.sku,p.product_id from onlineProductVars pv,'.
			   ' (select product_id from onlineProductVars where sku = "'.$sku.'" and accountid = "'.$accountid.'") p'.
			   ' where p.product_id = pv.product_id'.
	           ' ) ps'.
	    	   ' where o.accountid = "'.$accountid.'" and o.sku = ps.sku'.
			   ' ) a'.
			   ' left join tracking_data t on a.tracking = t.tracking_number'.
			   ' and t.finalshippingcost IS NOT NULL'. 
			   ' order by t.tracking_date DESC limit 50';
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
		echo "get userid";
		$wpsql = 'select wishpostaccountname,c.accountid accountid,c.accountname accountname  '.
					' from wishpostaccounts w,( '.
					' select accountid,accountname from accounts where userid = '.$userid.
					' ) c '.
					' where w.accountid = c.accountid and w.token is not null';
		echo "<br/>".$wpsql;
		return mysql_query($wpsql);
	}
	
	public function getWPAccessToken($accountid){
		$atsql = 'select token from wishpostaccounts where accountid = '.$accountid;
		return mysql_query($atsql);
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



		