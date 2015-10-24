<?php
$host="localhost";
$user="root";
$psd="yangwu";
$db = mysql_connect($host,$user,$psd);
echo $db;
mysql_select_db('mysql');
mysql_query("set names 'utf-8'");
$res = mysql_query('show tables',$db);

var_dump($res);
$rows = mysql_fetch_array($res);
var_dump($rows);


