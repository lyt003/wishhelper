<?php
$xml = simplexml_load_string ( '<?xml version="1.0" encoding="utf-8"?><ExpressType/>' );

$epcode = $xml->addChild ( "Epcode", "epcode1" );
$userid = $xml->addChild ( "Userid", "userid1" );//*
$channel = $xml->addChild("Channel","");//*
$userOrderNum = $xml->addChild("UserOrderNumber","");
$sendDate = $xml->addChild("SendDate","");//*
$quantity = $xml->addChild("Quantity");//*
$packageno = $xml->addChild("PackageNo");
$insure = $xml->addChild("Insure");
$memo = $xml->addChild("Memo");

$Receiver = $xml->addChild("Receiver");
$RcUserid = $Receiver->addChild("Userid","");//*
$RcName = $Receiver->addChild("Name");//*
$RcPhone = $Receiver->addChild("Phone");
$RcMobile = $Receiver->addChild("Mobile");
$RcEmail = $Receiver->addChild("Email");
$RcCompany = $Receiver->addChild("Company");
$RcCountry = $Receiver->addChild("Country");
$RcPostcode = $Receiver->addChild("Postcode");//*
$RcState = $Receiver->addChild("State");//*
$RcCity = $Receiver->addChild("City");//*
$RcAddress1 = $Receiver->addChild("Address1");//*
$RcAddress2 = $Receiver->addChild("Address2");


$Goods = $xml->addChild("GoodsName");
$gsUserid = $Goods->addChild("Userid");//*
$gsNameCh = $Goods->addChild("NameCh");//*
$gsNameEn = $Goods->addChild("NameEn");//*
$gsWeight = $Goods->addChild("Weight");//*
$gsDeclaredValue = $Goods->addChild("DeclaredValue");//*
$gsDeclaredCurrency = $Goods->addChild("DeclaredCurrency");//*
$gsMoreGoodsName = $Goods->addChild("MoreGoodsName");
$GsHsCode = $Goods->addChild("HsCode");

$xml->asXML("sample.xml");