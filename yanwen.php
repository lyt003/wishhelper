<?php
header("Content-Type: text/html;charset=utf-8");
//const ServiceEndPoint = "http://online.yw56.com.cn/service_sandbox"; // for test
const ServiceEndPoint = "http://online.yw56.com.cn/service";

const LoginURL = "/Common/LoginUser/";

const userid = "104903";
const userpsd = "yangwu19821112";

const ApiToken = "MTA0OTAzOnlhbmd3dTE5ODIxMTEy";

$post_header = array('Authorization: basic MTA0OTAzOnlhbmd3dTE5ODIxMTEy','Content-Type: text/xml; charset=utf-8');

$curl = curl_init();
//$url = ServiceEndPoint.LoginURL.userid."/".userpsd;
$url = ServiceEndPoint."/Users/".userid."/GetChannels";
//$url = ServiceEndPoint."/Users/".userid."/Expresses";
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl,CURLOPT_HTTPHEADER, $post_header); 


$xmldata = <<<DATA
<ExpressType>
<Epcode></Epcode>
    <Userid>100000</Userid>
    <Channel>ÑàÓÊ±¦¹ÒºÅ</Channel>
    <UserOrderNumber>123456</UserOrderNumber>
    <SendDate>2014-07-09T00:00:00</SendDate>
    <Receiver>
        <Userid>100000</Userid>
        <Name>tang</Name>
        <Phone>1236548</Phone>
        <Mobile>802</Mobile>
        <Email>jpcn@mpc.com.br</Email>
        <Company></Company>
        <Country>¶íÂÞË¹</Country>
        <Postcode>253400</Postcode>
        <State>FL</State>
        <City>City</City>
        <Address1>String content1</Address1>
        <Address2>String content2</Address2>
</Receiver>
<Memo></Memo>
    <Quantity>1</Quantity>
    <GoodsName>
        <Userid>100000</Userid>
        <NameCh>¶àÃ½Ìå²¥·ÅÆ÷</NameCh>
        <NameEn>MedialPlayer</NameEn>
        <Weight>213</Weight>
        <DeclaredValue>125</DeclaredValue>
        <DeclaredCurrency>USD</DeclaredCurrency>
<MoreGoodsName>MedialPlayer</MoreGoodsName>
    </GoodsName>
</ExpressType>
DATA;

//curl_setopt($curl, CURLOPT_POSTFIELDS, $xmldata);

$result = curl_exec($curl);
//$decode_result = json_decode($result);
curl_close($curl);
//echo $url;
print_r($result);
//var_dump($result);
//print $result;
//var_dump($result);