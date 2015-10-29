<?php
header("Content-Type: text/html;charset=utf-8");
const ServiceEndPoint = "http://online.yw56.com.cn/service";

const LoginURL = "/Common/LoginUser/";

const userid = "104903";
const userpsd = "yangwu19821112";

const ApiToken = "MTA0OTAzOnlhbmd3dTE5ODIxMTEy";

$post_header = array('Authorization: basic MTA0OTAzOnlhbmd3dTE5ODIxMTEy','Content-Type: text/xml; charset=utf-8');

$curl = curl_init();
$url = ServiceEndPoint."/Users/".userid."/Expresses/"."A4LLabel";

curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl,CURLOPT_HTTPHEADER, $post_header); 

$numbers = $_POST ['labels'];
$xmldata = '<string>' . substr ( $numbers, 0, strlen ( $numbers ) - 1 ) . '</string>';

curl_setopt($curl, CURLOPT_POSTFIELDS, $xmldata);

$result = curl_exec($curl);
$error = curl_error($curl);
echo "error".$error;
curl_close($curl);

$filename = "label.pdf";
$filesize = file_put_contents($filename, $result);
header('Cache-Control: public');
header('Content-type: application/pdf');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Content-Length: '.$filesize);

readfile($filename);
