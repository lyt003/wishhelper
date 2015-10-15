<?php

class EUBOrders
{

    const TRACKING_URL = "http://www.ems.com.cn/partner/api/public/p/order/";

    private $params;

    public function __construct($params = array())
    {
       /*  $params['version'] = "international_eub_us_1.1";
        $params['authenticate'] = "pdfTest_dhfjh98983948jdf78475fj65375fjdhfj";
        $params['operationtype'] = '0';
        $params['producttype'] = '0';
        $params['customercode'] = 'hongliang5301';
        $params['clcttype'] = '0';
        $params['pod'] = FALSE;
        $params['untread'] = 'Returned'; */
        
        $this->params = $params;
    }

    public function getTrackingID()
    {
        $curl = curl_init();
       /*  $options = array(
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_USERAGENT => 'wish-php-sdk',
            CURLOPT_HEADER  => 'true',
            CURLOPT_POST => '1',
            CURLOPT_SSL_VERIFYPEER => 'true',
            CURLOPT_CAINFO => '/cert/ca.crt'
        );
        
        $options[CURLOPT_POSTFIELDS] = $this->params;
        $options[CURLOPT_URL] = TRACKING_URL;
        curl_setopt_array($curl, $options); */
        
        $result = curl_exec($curl);
        $error = curl_errno($curl);
        
        curl_close($curl);
        
        echo $result;
        echo $error;
    }
}