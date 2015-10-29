<?php
echo strtotime ( date ( 'Y-m-d  H:i:s' ) ) . "<br/>";
echo strtotime ( date ( 'Y-m-d  H:i:s' ) ) . "<br/>";
echo substr(10000 * microtime(true),4). "<br/>";

$printTrackingnumbers = "<string>RG228167292CN,RG228167292CN,";
$printTrackingnumbers = substr ( $printTrackingnumbers, 0, strlen ( $printTrackingnumbers ) - 1 ) . "</string>";
echo "tracingnumbers: " . $printTrackingnumbers;