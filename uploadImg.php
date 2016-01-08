<?php
// Function: 获取远程图片并把它保存到本地
// 确定您有把文件写入本地服务器的权限
// 变量说明:
// $url 是远程图片的完整URL地址，不能为空。
// $filename 是可选变量: 如果为空，本地文件名将基于时间和日期
// 自动生成.
function GrabImage($url,$filename="") {
	if($url==""):return false;endif;
	if($filename=="") {
		$ext=strrchr($url,".");
		if($ext!=".gif" && $ext!=".jpg"):return false;endif;
		$filename=date("dMYHis").$ext;
	}
	ob_start();
	readfile($url);
	$img = ob_get_contents();
	ob_end_clean();
	$size = strlen($img);
	$fp2=@fopen($filename, "a");
	fwrite($fp2,$img);
	fclose($fp2);
	return $filename;
}

$oldimageurl = $_POST ['old_image'];
echo "<br/>".$oldimageurl."<br/>";
if ($oldimageurl != null) {
	$new=GrabImage($oldimageurl,"./images/".basename($oldimageurl));
	
	//获取压缩该图片文件的地址;
	$newURL = "http://www.wishconsole.com/images/".basename($oldimageurl)."_800x800.jpg";
}
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="generator"
	content="HTML Tidy for HTML5 (experimental) for Windows https://github.com/w3c/tidy-html5/tree/c63cc39" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>Wish 商户平台</title>
<meta name="keywords" content="" />
<link rel="stylesheet" type="text/css"
	href="./css/add_products_page.css" />
</head>
<script type="text/javascript">
	function cropImage(){
		var oldimage = document.getElementById("old_image").value;
		if(oldimage == null || oldimage == ''){
			alert("old image address can't be empty");
			return;
			}
		var form = document.getElementById("cropimage");
		form.submit();
		}
</script>
<body>
	<form id="cropimage" action="uploadImg.php" method="post">
		<div id="add-products-page" class="center">
			<div id="add-product-form">
				<div id="basic-info" class="form-horizontal">
					<div class="control-group">
						<label class="control-label" data-col-index="3"><span
							class="col-name">原图片地址</span></label>
						<div class="controls input-append">
							<input class="input-block-level required" id="old_image"
								name="old_image" type="text" value="" />
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" data-col-index="3"><span
							class="col-name">压缩后图片地址</span></label>
						<div class="controls input-append">
							<input class="input-block-level required" id="new_image"
								name="new_image" type="text" value="<?php echo $newURL?>" />
						</div>
					</div>

				</div>
				<div id="buttons-section" class="control-group text-right">
					<button id="submit-button" type="button"
						class="btn btn-primary btn-large" onclick="cropImage()">压缩</button>
				</div>
				<div id="buttons-section" class="control-group text-right">
					<img id="new_url" alt="" src="<?php echo $newURL?>" />
				</div>
			</div>
		</div>

	</form>
</body>
</html>
