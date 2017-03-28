<?php

define('YANWEN_USER_ATTR','Userid');
define('YANWEN_SERVICE_URL','ServiceEndPoint');
define('YANWEN_API_TOKEN','ApiToken');

define('WISHPOST_FROM','from');
define('WISHPOST_SENDERPROVINCE','sender_province');
define('WISHPOST_SENDERCITY','sender_city');
define('WISHPOST_SENDERADDRESS','sender_addres');
define('WISHPOST_SENDERPHONE','sender_phone');

define('WISHPOST_DOORPICKER','doorpickup');
define('WISHPOST_RECEIVEADDRESS','receive_addres');
define('WISHPOST_RECEIVECITY','receive_city');
define('WISHPOST_RECEIVEFROM','receive_from');
define('WISHPOST_RECEIVEPHONE','receive_phone');
define('WISHPOST_RECEIVEPROVINCE','receive_province');
define('WISHPOST_WAREHOUSECODE','warehouse_code');

//orderstatus: 0: new order; 1: applied tracking number; 2: has download label; 3: has uploaded tracking number;
define('ORDERSTATUS_NEWORDER',0);
define('ORDERSTATUS_APPLIEDTRACKING',1);
define('ORDERSTATUS_DOWNLOADEDLABEL',2);
define('ORDERSTATUS_UPLOADEDTRACKING',3);

define('NOTETOCUSTOMERS','Thanks for buying,i will be very happy if you would help me to confirm the order when you received the item. thanks and have a nice day, welcome next time!');


define('DISABLEPRODUCT','DISABLEPRODUCT');
define('LOWERSHIPPING','LOWERSHIPPING');
define('ADDINVENTORY','ADDINVENTORY');
define('SYNCHRONIZEDSTORE','SYNCHRONIZEDSTORE');

define('FROMALIEXPRESS','aliexpress');
define('FROMWISH','wish');


define('PROVIDER_YANWEN','YW');
define('PROVIDER_WISHPOST','WishPost');
define('PROVIDER_EUB','EUB');