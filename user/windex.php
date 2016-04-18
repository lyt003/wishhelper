<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- saved from url=(0047)http://china-merchant.wish.com/welcome?next=%2F -->
<html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Wish管理助手-更有效率的Wish商户实用工具</title>
<meta name="keywords" content="">
<link rel="stylesheet" type="text/css" href="../css/welcome_page.css">
</head>
<body>
<!-- HEADER -->
<div id="header" class="navbar navbar-fixed-top" style="left: 0px;">
<div class="container-fluid">
<a class="brand" href="https://wishconsole.com/">
<span class="merchant-header-text">
Wish管理助手-更有效率的Wish商户实用工具
</span>
</a>

<div class="pull-right">

<a href="./wregister.php" class="login-btn btn">

注册
</a>
&nbsp;&nbsp;&nbsp;&nbsp;
<div class="pull-right">

<a href="./wlogin.php" class="login-btn btn">
<?php 
session_start ();
$username = $_SESSION ['username'];
session_commit();
if($username != null){
	echo "进入";
}else{
	echo "登录";
}
?>
</a>
</div>

</div>


</div>
</div>
<!-- END HEADER -->
<!-- SUB HEADER NAV-->
<!-- splash page subheader-->




<div id="page-content" class="container-fluid 
fixed-width
 ">

<div id="welcome-page-content" class="center">
<div id="section-1" class="section">
<div>
<div class="column left">
<div class="message message-1">以更有效率的方式<br/>刊登产品</div>
<div class="message message-2">
多账号轻松切换<br />
图片自动缩放到合适尺寸<br />
再多的颜色和尺码也能瞬间搞定<br />
支持定时上传，一天搞定一周的上传工作<br />
<br />
全网采用SSL加密传输，操作更安全<br />
<br />
更多更好的功能，等待你来发现......<br />
</div>

<a class="signup-btn" href="./wregister.php">马上使用</a>

</div>
<div class="column right">
<img src="../image/b1.jpg" alt=""  />
</div>
</div>
</div>
</div>
<!-- BOTTOM LOAD JS -->
<script type="text/javascript" src="../js/jquery-require-bootstrap.js">
</script>

<!-- global js data -->
<script type="text/javascript">
window.locale_info = {};
window.locale_info['locale'] = "zh";
window.locale_info['locale_json'] = null;
window.pageParams = {"locale":"zh","monitor_key":"page.welcome","env":"fe_prod"}
</script>
<script type="text/javascript">
var lemmings_url="http:\/\/contestimg.wish.com\/api\/webimage";
</script>

<!-- end global js data -->
<script type="text/javascript" defer="" async="" src="../js/welcome_page.js">
</script>
<!-- END BOTTOM LOAD JS -->
<!-- begin user voice code -->

<!-- end user voice code -->

<!-- FOOTER -->
	<div id="footer" class="navbar navbar-fixed-bottom" style="left: 0px;">
		<div class="navbar-inner">
			<div class="footer-container">
				<span><a href="https://wishconsole.com/">关于我们</a></span> <span><a>2016
						wishconsole版权所有 京ICP备16000367号</a>
						<!-- 51.la 网站统计 -->
						<script language="javascript" type="text/javascript" src="http://js.users.51.la/18799105.js"></script>
						<noscript><a href="http://www.51.la/?18799105" target="_blank"><img alt="&#x6211;&#x8981;&#x5566;&#x514D;&#x8D39;&#x7EDF;&#x8BA1;" src="http://img.users.51.la/18799105.asp" style="border:none" /></a></noscript>
				</span>
			</div>
		</div>
	</div>
	<!-- END FOOTER -->
	<!-- GoStats JavaScript Based Code -->
<script type="text/javascript" src="https://ssl.gostats.com/js/counter.js"></script>
<script type="text/javascript">_gos='c5.gostats.cn';_goa=1068962;
_got=5;_goi=1;_gol='淘宝店铺计数器';_GoStatsRun();</script>
<noscript><a target="_blank" title="淘宝店铺计数器" 
href="http://gostats.cn"><img alt="淘宝店铺计数器" 
src="https://ssl.gostats.com/bin/count/a_1068962/t_5/i_1/ssl_c5.gostats.cn/counter.png" 
style="border-width:0" /></a></noscript>
<!-- End GoStats JavaScript Based Code -->
</body></html>