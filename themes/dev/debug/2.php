<?php
	$url = core::getURLObj()->getPathArray();
	
	if(count($url) < 3)
		output::force404();
	
	$folder = (int)$url[count($url) - 2];
	$id = $url[count($url) - 1];
	
	$file = '/usr/local/lsws/CREEZI/urkraft/public_html/userUploads2/images/'.$folder.'/'.$id;
	
	if(file_exists($file)){
		header("Content-Type: image/jpeg");
	    ob_clean();
	    flush();
	    readfile($file);
	    exit;
	}
	
	echo $file.' DOES NOT EXIST:::'; exit;
?>