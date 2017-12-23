<?php

namespace cpws;

include dirname(__FILE__).'/SvcCall.php';

use cpws\Common_SvcCall;
use \SoapClient;

class CPWSManager{
	
	public function getProducts(){
		
		$products = array();
		$wsdl = 'http://cpws.ems.com.cn/default/svc/wsdl?wsdl';
		
		$options = array(
				"trace" => true,
				"connection_timeout" => $timeout,
				"encoding" => "utf-8"
		);
		
		$client = new SoapClient($wsdl, $options);
		
		$page = 1;
		while(true){
			$params = array(
					'pageSize' => '5',
					'page' => $page,
					'product_sku' => '',
					'product_sku_arr' => array()
			);
			$svc = new Common_SvcCall();
			$rs = $svc->getProductList($params);
			$productsData = $rs['data'];
			$products = array_merge($products,$productsData);
			if($rs['nextPage'] != 'true'){
				break;
			}
			$page ++;
		}
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