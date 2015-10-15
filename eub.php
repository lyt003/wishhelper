<?php
echo "start" . "\n";
header("Content-Type: text/html;charset=utf-8");
const TRACKING_URL = "http://www.ems.com.cn/partner/api/public/p/order/";

$curl = curl_init();
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_URL, TRACKING_URL);
curl_setopt($curl, CURLOPT_HEADER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, array('version: international_eub_us_1.1','authenticate: hongliang5301'));



$xmldata = <<< EUB
<?xml version="1.0" encoding="UTF-8"?>
<orders xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <order>
        <orderid>SO1231231</orderid>
        <operationtype>0</operationtype>
        <producttype>0</producttype>
        <customercode>deve360</customercode>
        <vipcode>00000000000001</vipcode>
        <clcttype>1</clcttype>
        <pod>false</pod>
        <untread>Abandoned</untread>
        <volweight>123</volweight>
        <startdate>2015-04-01T00:00:01</startdate>
        <enddate>2015-04-01T00:00:01</enddate>
        <printcode>01</printcode>
        <sender>
            <name>Wang Lin</name>
            <postcode>100055</postcode>
            <phone>2131231</phone>
            <mobile>1123333333313</mobile>
            <country>CN</country>
            <province>441402</province>
            <city>441402</city>
            <county>441402</county>
            <company>Teamsun</company>
            <street>Lotus Street</street>
            <email>mail@team.com</email>
        </sender>
        <receiver>
            <name>Tom.k</name>
            <postcode>10005</postcode>
            <phone>1111111</phone>
            <mobile>212-222-0111</mobile>
            <country>UNITED STATES OF AMERICA</country>
            <province>LA</province>
            <city>San Francisco</city>
            <county>St.</county>
            <company></company>
            <street>Lotus Street</street>
            <email></email>
        </receiver>
        <collect>
            <name>王大琳</name>
            <postcode>100067</postcode>
            <phone>123456-908-098</phone>
            <mobile>1233333333333</mobile>
            <country>CN</country>
            <province>441402</province>
            <city>441402</city>
            <county>441402</county>
            <company></company>
            <street>莲花池东路126号</street>
            <email>bin@team.com</email>
        </collect>
        <items>
            <item>
                <cnname>盒子</cnname>
                <enname>box</enname>
                <count>1</count>
                <unit></unit>
                <weight>0.1</weight>
                <delcarevalue>1</delcarevalue>
                <origin>CN</origin>
                <description></description>
            </item>
            <item>
                <cnname>电脑</cnname>
                <enname>computer</enname>
                <count>2</count>
                <unit>unit</unit>
                <weight>0.23</weight>
                <delcarevalue>1</delcarevalue>
                <origin>CN</origin>
                <description>Computer Machine</description>
            </item>
        </items>
        <remark></remark>
    </order>
</orders>
EUB;

//curl_setopt($curl, CURLOPT_HEADER, 1);
//curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

curl_setopt($curl, CURLOPT_POSTFIELDS, $xmldata);

$result = curl_exec($curl);
$error = curl_errno($curl);
//echo "result" . $result . "\n";
print_r($result);
print("******************");
print_r($error);