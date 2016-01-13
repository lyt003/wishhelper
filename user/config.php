<?php
$server="localhost";//���ݿ��ַ
$username="root";//���ݿ��û���
$password="yangwu";//���ݿ�����
$database="wish";//���ݿ���

//---------------------------------------------------------------------------------------------
//����֥���Ȩ���� http://www.286shequ.com
//QQ:470784782
//---------------------------------------------------------------------------------------------
if($database=="")
{
 $query="use databasename";
 if(mysql_query($query)==null)
 {
  $query="create database sqlsd66juu689ku";
  if(mysql_query($query)==1)
  {
   //�������ݿ�ɹ�����ʼ�������ݿ�
   $database="sqlsd66juu689ku";
   $conn=mysql_connect($server,$username,$password)
   or die("could not connect mysql");
   mysql_select_db($database,$conn)
   or die("could not open database");
  }
  else
  {
   echo "Error while creating database (Error".mysql_errno().":\"".mysql_error()."\")<br>";//�������ݿ����
  }
 }
 else
 {
  //������ݿ��д���sqlsd66juu689ku���ݿ�
  $database="sqlsd66juu689ku";
  $conn=mysql_connect($server,$username,$password)
  or die("could not connect mysql");
  mysql_select_db($database,$conn)
  or die("could not open database");
 }
}
else
{
 //���ѡ����Ǳ�����ݿ⣬Ҳ����˵$database��Ϊ��
 $conn=mysql_connect($server,$username,$password)
 or die("could not connect mysql");
 mysql_select_db($database,$conn)
 or die("could not open database");
}
?>