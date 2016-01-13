<?php


//---------------------------------------------------------------------------------------------
//矮个芝麻版权所有 http://www.286shequ.com
//QQ:470784782
//---------------------------------------------------------------------------------------------
//启动会话
session_start();
//将客户端cookie设置为过去时间，即过期
setcookie("RememberCookieUserName","UserName",time()-60);
setcookie("RememberCookiePassword","Password",time()-60);
//删除会话
session_unset();
session_destroy();
//回到登录界面
header("refresh:1;url=http://localhost/members/login.php");
?>