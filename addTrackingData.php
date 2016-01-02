<?php
header ( "Content-Type: text/html;charset=utf-8" );
include 'mysql/dbhelper.php';
use mysql\dbhelper;


$trackingData = array ();
$trackingData['device_id'] = $_POST['device_id'];
$trackingData['user_id'] = $_POST['user_id'];
$trackingData['tracking_date'] = date('Ymd');
$trackingData['tracking_number'] = $_POST['tracking_number'];

$dbhelper = new dbhelper();

$result = $dbhelper->insertTrackingData($trackingData);
echo $result;
