<?php
require_once 'SvcCall.php';

// 测试用例 start
function getCountry()
{
    $page = 1;
    $params = array(
        'pageSize' => '2',
        'page' => $page
    );
    $svc = new Common_SvcCall();
    $rs = $svc->getCountry($params);
    print_r($rs);
}

function getRegion()
{
    $page = 1;
    $params = array(
        'pageSize' => '2',
        'page' => $page
    );
    $svc = new Common_SvcCall();
    $rs = $svc->getRegion($params);
    print_r($rs);
}

function getRegionForReceiving()
{
    // $svc = new Common_Svc();
    $svc = new Common_SvcCall();
    $rs = $svc->getRegionForReceiving();
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
        'page' => $page
    );
    $svc = new Common_SvcCall();
    $rs = $svc->getShippingMethod($params);
    print_r($rs);
}

function getCategory()
{
    $page = 1;
    $params = array(
        'pageSize' => '2',
        'page' => $page
    );
    $svc = new Common_SvcCall();
    $rs = $svc->getCategory($params);
    print_r($rs);
}

function getAccount()
{
    $svc = new Common_SvcCall();
    $rs = $svc->getAccount();
    print_r($rs);
}

function createProduct()
{
    $sku = 'EA' . date('ymdHis');
    $productInfo = array(
        'product_sku' => $sku,
        'reference_no' => $sku,
        'product_title' => $sku,
        
        'product_weight' => '0.35', // 单位KG
        'product_length' => '29.70', // 单位cm
        'product_width' => '21.00', // 单位cm
        'product_height' => '4', // 单位cm
        
        'contain_battery' => '0', // 0不含电池，1：含电池
        
        'product_declared_value' => '10', // currency:USD
        'product_declared_name' => $sku,
        
        'cat_lang' => 'en', // zh,en
        'cat_id_level0' => '400001', // 1级品类
        'cat_id_level1' => '500013', // 2级品类
        'cat_id_level2' => '600109', // 3级品类
        
        'verify' => '1'
    );
    $svc = new Common_SvcCall();
    $rs = $svc->createProduct($productInfo);
    print_r($rs);
}

function modifyProduct()
{
    $sku = 'EA' . date('ymdHis');
    $productInfo = array(
        'product_sku' => $sku,
        'reference_no' => $sku,
        'product_title' => $sku,
        
        'product_weight' => '0.35', // 单位KG
        'product_length' => '29.70', // 单位cm
        'product_width' => '21.00', // 单位cm
        'product_height' => '4', // 单位cm
        
        'contain_battery' => '0', // 0不含电池，1：含电池
        
        'product_declared_value' => '10', // currency:USD
        'product_declared_name' => $sku,
        
        'cat_lang' => 'en', // zh,en
        'cat_id_level0' => '400001', // 1级品类
        'cat_id_level1' => '500013', // 2级品类
        'cat_id_level2' => '600109', // 3级品类
        'verify' => '1'
    );
    $svc = new Common_SvcCall();
    $rs = $svc->modifyProduct($productInfo);
    print_r($rs);
}

function getProductList()
{
    $page = 1;
    while(true){
        $params = array(
            'pageSize' => '2',
            'page' => $page,
            'product_sku' => '',
            'product_sku_arr' => array()
        );
        $svc = new Common_SvcCall();
        $rs = $svc->getProductList($params);
        print_r($rs);
        if($rs['nextPage'] != 'true'){
            break;
        }
        $page ++;
        break;
    }
}

function createAsn()
{
    $receivingInfo = array(
        // 'receiving_code'=>'RV100002-140508-0002',
        'reference_no' => 'dfdfd' . time(), // 入库单参考号
        'income_type' => '0', // 交货方式，0：自送，1：揽收
        'warehouse_code' => 'HRBW', // 目的仓
        
        'transit_warehouse_code' => 'SZW', // income_type为自送时，必填
        'shipping_method' => '顺丰', // 配送方式
        'tracking_number' => '12313213', // 跟踪号
        
        'receiving_desc' => 'dfdfdf', // 入库单描述
        'eta_date' => '2013-04-15', // 预计到达时间
        
        'contacter' => $receivingInfo['contacter'], // income_type为揽收时，联系人
        'contact_phone' => $receivingInfo['contact_phone'], // income_type为揽收时，
                                                            // 联系方式
        'region_id_level0' => '1', // income_type为揽收时， 省份ID,从region表获得
        'region_id_level1' => '1', // income_type为揽收时， 市ID,从region表获得
        'region_id_level2' => '1', // income_type为揽收时， 区ID,从region表获得
        'street' => 'address',
        'verify' => '1'
    );
    
    $items = array();
    $items[] = array(
        'product_sku' => 'EA140509201610',
        'quantity' => '10',
        'box_no' => '1'
    );
    $items[] = array(
        'product_sku' => 'EA140509201610',
        'quantity' => '10',
        'box_no' => '2'
    );
    $items[] = array(
        'product_sku' => 'EA140509201610',
        'quantity' => '10',
        'box_no' => '3'
    );
    $receivingInfo['items'] = $items;
    $svc = new Common_SvcCall();
    $rs = $svc->createAsn($receivingInfo);
    print_r($rs);
}

function modifyAsn()
{
    $receivingInfo = array(
        'receiving_code' => 'RV100002-140509-0008',
        'reference_no' => 'dfdfd' . time(), // 入库单参考号
        'income_type' => '0', // 交货方式，0：自送，1：揽收
        'warehouse_code' => 'HRBW', // 目的仓
        
        'transit_warehouse_code' => 'SZW', // income_type为自送时，必填
        'shipping_method' => '顺丰', // 配送方式
        'tracking_number' => '12313213', // 跟踪号
        
        'receiving_desc' => 'dfdfdf', // 入库单描述
        'eta_date' => '2013-04-15', // 预计到达时间
        
        'contacter' => $receivingInfo['contacter'], // income_type为揽收时，联系人
        'contact_phone' => $receivingInfo['contact_phone'], // income_type为揽收时，
                                                            // 联系方式
        'region_id_level0' => '1', // income_type为揽收时， 省份ID,从region表获得
        'region_id_level1' => '1', // income_type为揽收时， 市ID,从region表获得
        'region_id_level2' => '1', // income_type为揽收时， 区ID,从region表获得
        'street' => 'address',
        'verify' => '1'
    );
    
    $items = array();
    $items[] = array(
        'product_sku' => 'EA140509201610',
        'quantity' => '10',
        'box_no' => '1'
    );
    $items[] = array(
        'product_sku' => 'EA140509201610',
        'quantity' => '10',
        'box_no' => '2'
    );
    $items[] = array(
        'product_sku' => 'EA140509201610',
        'quantity' => '10',
        'box_no' => '3'
    );
    $receivingInfo['items'] = $items;
    $svc = new Common_SvcCall();
    $rs = $svc->modifyAsn($receivingInfo);
    print_r($rs);
}

function getAsnList()
{
    $page = 1;
    while(true){
        $params = array(
            'pageSize' => '2',
            'page' => $page,
            'receiving_code' => '',
            'receiving_code_arr' => array()
        );
        $svc = new Common_SvcCall();
        $rs = $svc->getAsnList($params);
        print_r($rs);
        if($rs['nextPage'] != 'true'){
            break;
        }
        $page ++;
        break;
    }
}

function getProductInventory()
{
    $page = 1;
    while(true){
        $params = array(
            'pageSize' => '2',
            'page' => $page,
            'product_sku' => '',
            'product_sku_arr' => array(),
            'warehouse_code' => 'HRBW',
            'warehouse_code_arr' => array()
        );
        $svc = new Common_SvcCall();
        $rs = $svc->getProductInventory($params);
        print_r($rs);
        if($rs['nextPage'] != 'true'){
            break;
        }
        $page ++;
        break;
    }
}

function createOrder()
{
    $orderInfo = array(
        'platform' => 'OTHER',
        'warehouse_code' => 'HRBW',
        'shipping_method' => 'F4',
        'reference_no' => 'ref_' . time(),
        'order_desc' => '订单描述',
        'country_code' => 'RU',
        'province' => 'province',
        'city' => 'city',
        'address1' => 'address1',
        'address2' => 'address2',
        'address3' => 'address3',
        'zipcode' => '142970',
        'doorplate' => 'doorplate',
        'name' => 'name',
        'phone' => 'phone',
        
        // 'verify' => 1,
        'email' => 'email'
    );
    $items = array();
    $items[] = array(
        'product_sku' => 'EA140509201610',
        'quantity' => '1'
    );
    $orderInfo['items'] = $items;
    $svc = new Common_SvcCall();
    $rs = $svc->createOrder($orderInfo);
    print_r($rs);
}

function modifyOrder()
{
    $orderInfo = array(
        'platform' => 'OTHER',
        'warehouse_code' => 'HRBW',
        'shipping_method' => 'F4',
        'reference_no' => 'ref_' . time(),
        'order_desc' => '订单描述',
        'country_code' => 'RU',
        'province' => 'province',
        'city' => 'city',
        'address1' => 'address1',
        'address2' => 'address2',
        'address3' => 'address3',
        'zipcode' => 'zipcode',
        'doorplate' => 'doorplate',
        'name' => 'name',
        'phone' => 'phone',
        'email' => 'email',
        
        'verify' => 1
    );
    $items = array();
    $items[] = array(
        'product_sku' => 'EA140509201610',
        'quantity' => '1'
    );
    $orderInfo['items'] = $items;
    
    $svc = new Common_SvcCall();
    $rs = $svc->modifyOrder($orderInfo);
    print_r($rs);
}

function cancelOrder()
{
    $orderInfo = array(
        'order_code' => '10001-1000-11',
        'reason' => '客户买错了'
    );
    $svc = new Common_SvcCall();
    $rs = $svc->cancelOrder($orderInfo);
    print_r($rs);
}

function getOrderList()
{
    $params = array(
        'pageSize' => '2',
        'page' => '1',
        'order_code' => '',
        'order_code_arr' => array(),
        'create_date_from' => '',
        'create_date_to' => '',
        'modify_date_from' => '',
        'modify_date_to' => ''
    );
    $svc = new Common_SvcCall();
    $rs = $svc->getOrderList($params);
    print_r($rs);
}

function orderTrail()
{
    $params = array(
        'warehouse_code' => 'HRBW',
        'country_code' => 'RU',
        'shipping_method' => 'F4', // 运输方式
        'order_weight' => '0.2', // 重量 单位KG
        'length' => '',
        'width' => '',
        'height' => '',
        '1' => '1'
    );
    $svc = new Common_SvcCall();
    $rs = $svc->orderTrail($params);
    print_r($rs);
}
// 测试用例 end

// 测试 start
getWarehouse();
/* 
getCountry();

getRegion();

getRegionForReceiving();

getWarehouse();

getShippingMethod();

getCategory();

getAccount();

createProduct();

modifyProduct();

getProductList();

createAsn();

modifyAsn();

getAsnList();

getProductInventory();

createOrder();

modifyOrder();

cancelOrder();

getOrderList();

orderTrail(); */
//测试 end