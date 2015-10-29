<?php
const host = "localhost";
const user = "root";
const psd = "yangwu";

$db = mysql_connect ( host, user, psd );
mysql_select_db ( 'wish' );
mysql_query ( "set names 'utf-8'" );

$insert_sql = "insert into orders (orderid,accountid,transactiondate,transactionid,orderstatus,
		sku,product,productlink,variation,price,cost,shipping,shippingcost,quantity,totalcost,shippedon,
		provider,tracking,shippingaddress,name,firstname,lastname,streetaddress1,streetaddress2,
		city,state,zipcode,country,lastupdated,phonenumber,countrycode,orderstate) values()";
		