<?php
namespace cpws;

include dirname(__FILE__).'/Common.php';

use \SoapClient;

class Common_SvcCall
{

    protected $_appToken = '8f6ef3079a08219fdba69a989721f280'; // token
    protected $_appKey = 'c0035f0759b7f9c4f130988bec099247'; // key
    public $_active = true; // 是否启用发送到oms
    private $_client = null; // SoapClient
    public $_error = '';

    private function getClient()
    {
        if(empty($this->_client)){
            $this->setClient();
        }
        
        return $this->_client;
    }

    private function setClient()
    {
        $omsConfig = array(
            'active' => '1',
            'appToken' => $this->_appToken,
            'appKey' => $this->_appKey,
            'timeout' => '10',
			'wsdl' => 'http://cpws.ems.com.cn/default/svc/wsdl?wsdl',
        	'wsdl-file' => 'http://cpws.ems.com.cn/default/svc/wsdl-file?wsdl'		
            //'wsdl' => 'http://www.oms-heb.com/default/svc/wsdl?wsdl',
            //'wsdl-file' => 'http://www.oms-heb.com/default/svc/wsdl-file?wsdl'
        );
        
        $wsdl = $omsConfig['wsdl'];
        $this->_appToken = $omsConfig['appToken'];
        $this->_appKey = $omsConfig['appKey'];
        // 超时
        $timeout = isset($omsConfig['timeout']) && is_numeric($omsConfig['timeout']) ? $omsConfig['timeout'] : 1000;
        
        $streamContext = stream_context_create(array(
            'ssl' => array(
                'verify_peer' => false,
                'allow_self_signed' => true
            ),
            'socket' => array()
        ));
        
        $options = array(
            "trace" => true,
            "connection_timeout" => $timeout,
            "encoding" => "utf-8"
        );
        
        echo "<br/>create soapclient";
        $client = new SoapClient($wsdl, $options);
        echo "<br/>create soapclient finished";
        $this->_client = $client;
    }

    /**
     * 调用webservice
     * ====================================================================================
     *
     * @param unknown_type $req            
     * @return Ambigous <mixed, NULL, multitype:, multitype:Ambigous <mixed,
     *         NULL> , StdClass, multitype:Ambigous <mixed, multitype:,
     *         multitype:Ambigous <mixed, NULL> , NULL> , boolean, number,
     *         string, unknown>
     */
    private function callService($req)
    {
        $client = $this->getClient();
        $req['appToken'] = $this->_appToken;
        $req['appKey'] = $this->_appKey;
        $result = $client->callService($req);
        $result = Common_Common::objectToArray($result);
        $return = json_decode($result['response']);
        $return = Common_Common::objectToArray($return);
        return $return;
    }

    /**
     * 禁止数组中有null
     *
     * @param unknown_type $arr            
     * @return unknown string
     */
    private function arrFormat($arr)
    {
        if(! is_array($arr)){
            return $arr;
        }
        foreach($arr as $k => $v){
            if(! isset($v)){
                $arr[$k] = '';
            }
        }
        return $arr;
    }

    public function getCountry($params)
    {
        $return = array(
            'ask' => 'Failure',
            'message' => ''
        );
        try{
            $req = array(
                'service' => 'getCountry',
                'paramsJson' => json_encode($params)
            );
            $return = $this->callService($req);
        }catch(Exception $e){
            $return['message'] = $e->getMessage();
        }
        return $return;
    }

    public function getRegion($params)
    {
        $return = array(
            'ask' => 'Failure',
            'message' => ''
        );
        try{
            $req = array(
                'service' => 'getRegion',
                'paramsJson' => json_encode($params)
            );
            $return = $this->callService($req);
        }catch(Exception $e){
            $return['message'] = $e->getMessage();
        }
        return $return;
    }

    public function getRegionForReceiving()
    {
        $return = array(
            'ask' => 'Failure',
            'message' => ''
        );
        try{
            $req = array(
                'service' => 'getRegionForReceiving'
            );
            $return = $this->callService($req);
        }catch(Exception $e){
            $return['message'] = $e->getMessage();
        }
        return $return;
    }

    public function getWarehouse($params)
    {
        $return = array(
            'ask' => 'Failure',
            'message' => ''
        );
        try{
            $req = array(
                'service' => 'getWarehouse',
                'paramsJson' => json_encode($params)
            );
            $return = $this->callService($req);
        }catch(Exception $e){
            $return['message'] = $e->getMessage();
        }
        return $return;
    }

    public function getShippingMethod($params)
    {
        $return = array(
            'ask' => 'Failure',
            'message' => ''
        );
        try{
            $req = array(
                'service' => 'getShippingMethod',
                'paramsJson' => json_encode($params)
            );
            $return = $this->callService($req);
        }catch(Exception $e){
            $return['message'] = $e->getMessage();
        }
        return $return;
    }

    public function getCategory($params)
    {
        $return = array(
            'ask' => 'Failure',
            'message' => ''
        );
        try{
            $req = array(
                'service' => 'getCategory',
                'paramsJson' => json_encode($params)
            );
            $return = $this->callService($req);
        }catch(Exception $e){
            $return['message'] = $e->getMessage();
        }
        return $return;
    }

    public function getAccount()
    {
        $return = array(
            'ask' => 'Failure',
            'message' => ''
        );
        try{
            $req = array(
                'service' => 'getAccount'
            );
            $return = $this->callService($req);
        }catch(Exception $e){
            $return['message'] = $e->getMessage();
        }
        return $return;
    }

    public function createProduct($productInfo)
    {
        $return = array(
            'ask' => 'Failure',
            'message' => ''
        );
        try{
            $req = array(
                'service' => 'createProduct',
                'paramsJson' => json_encode($productInfo)
            );
            $return = $this->callService($req);
        }catch(Exception $e){
            $return['message'] = $e->getMessage();
        }
        return $return;
    }

    public function modifyProduct($productInfo)
    {
        $return = array(
            'ask' => 'Failure',
            'message' => ''
        );
        try{
            $req = array(
                'service' => 'modifyProduct',
                'paramsJson' => json_encode($productInfo)
            );
            $return = $this->callService($req);
        }catch(Exception $e){
            $return['message'] = $e->getMessage();
        }
        return $return;
    }

    public function createAsn($receivingInfo)
    {
        $return = array(
            'ask' => 'Failure',
            'message' => ''
        );
        try{
            $req = array(
                'service' => 'createAsn',
                'paramsJson' => json_encode($receivingInfo)
            );
            $return = $this->callService($req);
        }catch(Exception $e){
            $return['message'] = $e->getMessage();
        }
        return $return;
    }

    public function modifyAsn($receivingInfo)
    {
        $return = array(
            'ask' => 'Failure',
            'message' => ''
        );
        try{
            $req = array(
                'service' => 'modifyAsn',
                'paramsJson' => json_encode($receivingInfo)
            );
            $return = $this->callService($req);
        }catch(Exception $e){
            $return['message'] = $e->getMessage();
        }
        return $return;
    }

    public function getAsnList($params)
    {
        $return = array(
            'ask' => 'Failure',
            'message' => ''
        );
        try{
            $req = array(
                'service' => 'getAsnList',
                'paramsJson' => json_encode($params)
            );
            $return = $this->callService($req);
        }catch(Exception $e){
            $return['message'] = $e->getMessage();
        }
        return $return;
    }

    private function getProduct($productInfo)
    {
        $return = array(
            'ask' => 'Failure',
            'message' => ''
        );
        try{
            $req = array(
                'service' => 'getProduct',
                'paramsJson' => json_encode($productInfo)
            );
            $return = $this->callService($req);
        }catch(Exception $e){
            $return['message'] = $e->getMessage();
        }
        return $return;
    }

    public function getProductList($params)
    {
        $return = array(
            'ask' => 'Failure',
            'message' => ''
        );
        try{
            $req = array(
                'service' => 'getProductList',
                'paramsJson' => json_encode($params)
            );
            echo "<br/> start call service";
            $return = $this->callService($req);
            echo "<br/> end call service";
        }catch(Exception $e){
            $return['message'] = $e->getMessage();
        }
        return $return;
    }

    public function getProductInventory($productInfo)
    {
        $return = array(
            'ask' => 'Failure',
            'message' => ''
        );
        try{
            $req = array(
                'service' => 'getProductInventory',
                'paramsJson' => json_encode($productInfo)
            );
            $return = $this->callService($req);
        }catch(Exception $e){
            $return['message'] = $e->getMessage();
        }
        return $return;
    }

    public function createOrder($orderInfo)
    {
        $return = array(
            'ask' => 'Failure',
            'message' => ''
        );
        try{
            $req = array(
                'service' => 'createOrder',
                'paramsJson' => json_encode($orderInfo)
            );
            $return = $this->callService($req);
        }catch(Exception $e){
            $return['message'] = $e->getMessage();
        }
        return $return;
    }

    public function modifyOrder($orderInfo)
    {
        $return = array(
            'ask' => 'Failure',
            'message' => ''
        );
        try{
            $req = array(
                'service' => 'modifyOrder',
                'paramsJson' => json_encode($orderInfo)
            );
            $return = $this->callService($req);
        }catch(Exception $e){
            $return['message'] = $e->getMessage();
        }
        return $return;
    }

    public function cancelOrder($orderInfo)
    {
        $return = array(
            'ask' => 'Failure',
            'message' => ''
        );
        try{
            $req = array(
                'service' => 'cancelOrder',
                'paramsJson' => json_encode($orderInfo)
            );
            $return = $this->callService($req);
        }catch(Exception $e){
            $return['message'] = $e->getMessage();
        }
        return $return;
    }

    public function getorderbycode($params){
    	$return = array(
    			'ask' => 'Failure',
    			'message' => ''
    	);
    	try{
    		$req = array(
    				'service' => 'getOrderByCode',
    				'paramsJson' => json_encode($params)
    		);
    		$return = $this->callService($req);
    	}catch(Exception $e){
    		$return['message'] = $e->getMessage();
    	}
    	print_r($return);
    	return $return;
    }
    
    public function getOrderList($params)
    {
        $return = array(
            'ask' => 'Failure',
            'message' => ''
        );
        try{
            $req = array(
                'service' => 'getOrderList',
                'paramsJson' => json_encode($params)
            );
            $return = $this->callService($req);
        }catch(Exception $e){
            $return['message'] = $e->getMessage();
        }
        return $return;
    }

    public function orderTrail($params)
    {
        $return = array(
            'ask' => 'Failure',
            'message' => ''
        );
        try{
            $req = array(
                'service' => 'orderTrail',
                'paramsJson' => json_encode($params)
            );
            $return = $this->callService($req);
        }catch(Exception $e){
            $return['message'] = $e->getMessage();
        }
        return $return;
    }
}