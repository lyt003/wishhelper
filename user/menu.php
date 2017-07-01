<?php 
header ( "Content-Type: text/html;charset=utf-8" );
?>
<ul class="nav">
							<!-- <li><a href="./wusercenter.php"> 订单处理 </a></li> -->
							<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown">产品<b class="caret"></b> </a>
								<ul class="dropdown-menu">
								<li><a href="./wuploadproduct.php">产品上传</a></li>
								<li><a href="./waliparse.php">导入速卖通产品</a></li>
								<li><a href="./wwishparse.php">导入Wish产品</a></li>
								<li><a href="./wproductstatus.php">定时产品状态</a></li>
								<li><a href="./wproductsource.php">产品源查询</a></li>
								</ul>
							</li>   
							<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown">库存<b class="caret"></b> </a>
								<ul class="dropdown-menu">
								<li><a href="./wproductinventory.php">产品实时库存</a></li>
								<!-- <li><a href="./waddproductinventory.php">新增产品库存</a></li> -->
								</ul>
							</li>
							<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown">店铺优化<b class="caret"></b> </a>
								<ul class="dropdown-menu">
								<li><a href="./csvupload.php">CSV文档上传</a></li>
								<li><a href="./wproductlist.php">店铺产品同步</a></li>
								<li><a href="./wproductInfo.php">产品优化</a></li>
								</ul>
							</li> 
							<!-- <li><a href="./wuserinfo.php"> 个人信息 </a></li> -->
							<li> <a href="./whelper.php">帮助文档</a></li>
						</ul>
