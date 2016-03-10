<?php
/**
 * This is just an example of how a file could be processed from the
 * upload script. It should be tailored to your own requirements.
 */

// Only accept files with these extensions
$whitelist = array('jpg', 'jpeg', 'png', 'gif');
$name      = null;
$newname   = null;
$error     = 'No file uploaded.';

if (isset($_FILES)) {
	
	foreach ($_FILES as $curFile){
		$tmp_name = $curFile['tmp_name'];
		$name     = basename($curFile['name']);
		$error    = $curFile['error'];
		$extension = pathinfo($name, PATHINFO_EXTENSION);
		
		$newname = uniqid().".".$extension;
		$dest = "../images/".$newname;
		
		if ($error === UPLOAD_ERR_OK) {
			if (!in_array($extension, $whitelist)) {
				$error = -1;
			} else {
				move_uploaded_file($tmp_name, $dest);
			}
		}
	}
}

echo json_encode(array(
	'name'  => $newname,
	'error' => $error,
));
die();
