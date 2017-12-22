<?php

namespace cpws;

include dirname(__FILE__).'/SvcCall.php';

use cpws\Common_SvcCall;
use \SoapClient;

class CPWSManager{
	
	public function getProducts(){
		
		$products = array();
		echo "***getProducts";
		echo "start client";
		$wsdl = 'http://cpws.ems.com.cn/default/svc/wsdl?wsdl';
		
		$options = array(
				"trace" => true,
				"connection_timeout" => $timeout,
				"encoding" => "utf-8"
		);
		
		$client = new SoapClient($wsdl, $options);
		var_dump($client);
		
		echo "finish client";
		$page = 1;
		while(true){
			$params = array(
					'pageSize' => '5',
					'page' => $page,
					'product_sku' => '',
					'product_sku_arr' => array()
			);
			echo "<br/>start1";
			$svc = new Common_SvcCall();
			echo "<br/>start2";
			$rs = $svc->getProductList($params);
			echo "<br/>start3";
			print_r($rs);
			$productsData = $rs['data'];
			$products = array_merge($products,$productsData);
			//print_r($productsData);
			echo '<br/>*******next page:'.$rs['nextPage'];
			if($rs['nextPage'] != 'true'){
				break;
			}
			$page ++;
			//break;
		}
		echo "FINISHED";
		return $products;
	}
	
	public function processorder($currentorder){
		$orderInfo = array(
				'platform' => 'WISH',
				'warehouse_code' => 'USEA',
				'shipping_method' => $currentorder['shippingmethod'],
				'reference_no' => 'ref_' . time(),
				'order_desc' => '订单描述',
				'country_code' => $currentorder['countrycode'],
				'province' => $currentorder['state'],
				'city' => $currentorder['city'],
				'address1' => $currentorder['streetaddress1'],
				'address2' => $currentorder['streetaddress2'],
				'address3' => '',
				'zipcode' => $currentorder['zipcode'],
				'doorplate' => '',
				'name' => $currentorder['name'],
				'phone' => $currentorder['phonenumber'],
		
				'verify' => 0,
				//'email' => ''
		);
		$items = array();
		$items[] = array(
				'product_sku' => $currentorder['WEProductSKU'],
				'quantity' => $currentorder['quantity']
		);
		$orderInfo['items'] = $items;
		
		$svc = new Common_SvcCall();
		$rs = $svc->createOrder($orderInfo);
		print_r($rs);
	}
	
	function getWarehouse()
	{
		$page = 1;
		$params = array(
				'pageSize' => '2',
				'page' => $page
		);
		$svc = new Common_SvcCall();
		$rs = $svc->getWarehouse($params);
		print_r($rs);
	}
	function getShippingMethod()
	{
		$page = 1;
		$params = array(
				'pageSize' => '2',
				'page' => $page,
				'warehouseCode' => 'USEA'
		);
		$svc = new Common_SvcCall();
		$rs = $svc->getShippingMethod($params);
		return $rs;
	}
}