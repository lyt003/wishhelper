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
//$url = ServiceEndPoint."/Users/".userid."/GetChannels";
//$url = ServiceEndPoint."/Users/".userid."/Expresses";
$url = ServiceEndPoint."/Users/".userid."/Expresses/"."RG224865656CN"."/A4LCLabel";

curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl,CURLOPT_HTTPHEADER, $post_header); 


$xmldata = <<<DATA
<ExpressType>
	<Epcode></Epcode>
    <Userid>104903</Userid>
    <Channel>154</Channel>
    <UserOrderNumber>5102196</UserOrderNumber>
    <SendDate>2015-10-21T22:14:00</SendDate>
    <Receiver>
        <Userid>104903</Userid>
        <Name>tang</Name>
        <Phone>1236548</Phone>
        <Mobile>802</Mobile>
        <Email>jpcn@mpc.com.br</Email>
        <Company></Company>
        <Country>RU</Country>
        <Postcode>253400</Postcode>
        <State>FL</State>
        <City>City</City>
        <Address1>String content1</Address1>
        <Address2>String content2</Address2>
	</Receiver>
	<Memo></Memo>
    <Quantity>1</Quantity>
    <GoodsName>
        <Userid>104903</Userid>
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
//$xml1 = simplexml_load_string($result);
//echo $xml1;
//print_r($xml1);
//var_dump($xml1);
//print_r($result);
//var_dump($result);
//print $result;
//var_dump($result);
//$result = iconv("gb2312", "utf-8", $result);
//$result = mb_convert_encoding($result, "GBK","GBK,UTF-8,ASCII");
$filename = "label.pdf";
$filesize = file_put_contents($filename, $result);
header('Cache-Control: public');
header('Content-type: application/pdf');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Content-Length: '.$filesize);

readfile($filename);
