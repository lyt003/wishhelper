<?php

//---------------------------------------------------------------------------------------------
//����֥���Ȩ���� http://www.286shequ.com
//QQ:470784782
//---------------------------------------------------------------------------------------------
//�������ݿ������ļ�
include 'config.php';
//�Զ���װ���ݿ��
$query="create table als_signup (
UserName varchar(20),
Password varchar(20),
Email varchar(20),
actNum varchar(20),
UserLevel tinyint,
SignUpdate varchar(20),
LastLogin varchar(20),
LastLoginFail varchar(20),
NumLoginFail tinyint
) DEFAULT CHARSET=utf8";
$result=mysql_query($query);
if($result==1)
{
 echo "signup table succesfully created.<br>";
}
else
{
 echo "Error while creating table(ErrorNumber".mysql_errno().":\"".mysql_error()."\")<br>";
}
?>