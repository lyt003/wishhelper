<?php
echo strtotime ( date ( 'Y-m-d  H:i:s' ) ) . "<br/>";
echo strtotime ( date ( 'Y-m-d  H:i:s' ) ) . "<br/>";
echo substr(10000 * microtime(true),4). "<br/>";

$printTrackingnumbers = "<string>RG228167292CN,RG228167292CN,";
$printTrackingnumbers = substr ( $printTrackingnumbers, 0, strlen ( $printTrackingnumbers ) - 1 ) . "</string>";
echo "tracingnumbers: " . $printTrackingnumbers. "<br/>";

$testStr = 'NWT 4 pcs Mens soft bamboo fiber Underwears Comfort Boxer briefs M 28"-38"';
echo $testStr. "before <br/>";
$result = str_replace ( '"', "''", $testStr ); // use '' replace the " in the sql;
echo $result. "after<br/>";