<?php
/**
 * This is just an example of how a file could be processed from the
 * upload script. It should be tailored to your own requirements.
 */

// Only accept files with these extensions
$whitelist = array('jpg', 'jpeg', 'png', 'gif');
$name      = null;
$error     = 'No file uploaded.';

if (isset($_FILES)) {
	
	foreach ($_FILES as $curFile){
		$tmp_name = $curFile['tmp_name'];
		$name     = basename($curFile['name']);
		$error    = $curFile['error'];
		
		if ($error === UPLOAD_ERR_OK) {
			$extension = pathinfo($name, PATHINFO_EXTENSION);
		
			if (!in_array($extension, $whitelist)) {
				$error = '非图片文件不能上传';
			} else {
				move_uploaded_file($tmp_name, $name);
			}
		}
	}
}

echo json_encode(array(
	'name'  => $name,
	'error' => $error,
));
die();
