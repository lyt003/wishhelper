<?php
$server="localhost";//数据库地址
$username="root";//数据库用户名
$password="yangwu";//数据库密码
$database="wish";//数据库名

//---------------------------------------------------------------------------------------------
//矮个芝麻版权所有 http://www.286shequ.com
//QQ:470784782
//---------------------------------------------------------------------------------------------
if($database=="")
{
 $query="use 数据库名";
 if(mysql_query($query)==null)
 {
  $query="create database sqlsd66juu689ku";
  if(mysql_query($query)==1)
  {
   //创建数据库成功，开始连接数据库
   $database="sqlsd66juu689ku";
   $conn=mysql_connect($server,$username,$password)
   or die("could not connect mysql");
   mysql_select_db($database,$conn)
   or die("could not open database");
  }
  else
  {
   echo "Error while creating database (Error".mysql_errno().":\"".mysql_error()."\")<br>";//创建数据库出错
  }
 }
 else
 {
  //如果数据库中存在sqlsd66juu689ku数据库
  $database="sqlsd66juu689ku";
  $conn=mysql_connect($server,$username,$password)
  or die("could not connect mysql");
  mysql_select_db($database,$conn)
  or die("could not open database");
 }
}
else
{
 //如果选择的是别的数据库，也就是说$database不为空
 $conn=mysql_connect($server,$username,$password)
 or die("could not connect mysql");
 mysql_select_db($database,$conn)
 or die("could not open database");
}
?>
