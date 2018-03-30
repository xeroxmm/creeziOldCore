<?php

$r = dbQueries::get()->allItemsOfCollection('c3c'); $z = 20; $i = 0;
require_once '/usr/local/lsws/CREEZI/urkraft/public_html/intern/core-v1.1.0-radegast/classes/class.process.picture.php';
require_once '/usr/local/lsws/CREEZI/urkraft/public_html/intern/2nd-party/openText/tesseractOCR.php';

foreach($r as $val){
	$s = dbQueries::get()->oneImageInformationByContentID($val->contentIDSub);
	
	if(isset($s[0]->linkStored)) {
		$file = '/usr/local/lsws/CREEZI/urkraft/public_html/userUploads/images/'.$s[0]->linkStored.'.'.$s[0]->mime;	
		
		$p = new picture();
		$p->setLoadPath($file);
		
		$p->copyPictureIntoBuffer();
		$p->cropPartOfImageByPercentage(92, 100, 0, 30);
		
		if(stristr($p->getPath(), 'temp')){
			$file = '/usr/local/lsws/CREEZI/urkraft/public_html/userUploads/images/'.$s[0]->linkStored.'.'.$s[0]->mime;
			//$file = $p->getPath();
			$n = (new TesseractOCR($file))->psm(7)->config('load_system_dawg', FALSE)->userWords('/usr/local/lsws/CREEZI/urkraft/intern/core-v1.1.0-radegast/wordLists/pokemons.txt')->run();
			$temp = explode('I', str_replace('|','I',$n));
			if(isset($temp[1]) && strlen($temp[0]) > 3){
				$name = str_replace(['#','1','2','3','4','5','6','7','8','9','0',' '],'', $temp[0]);
				$nameA[$temp[0]] = 1;
				echo $temp[0].'<a href="'.PIC_HOST.'/'.$s[0]->linkStored.'.'.$s[0]->mime.'">Bild</a><br />';
			} else {
				echo "2nd attempt... ";	
				$n = (new TesseractOCR($p->getPath()))->psm(7)->config('load_system_dawg', FALSE)->userWords('/usr/local/lsws/CREEZI/urkraft/intern/core-v1.1.0-radegast/wordLists/pokemons.txt')->run();
				($temp = explode('|', $n));
				if(isset($temp[1]) && strlen($temp[0]) > 3){
					$name = str_replace(['#','1','2','3','4','5','6','7','8','9','0',' '],'', $temp[0]);
					$nameA[$temp[0]] = 1;
					echo $temp[0].'<a href="'.PIC_HOST.'/'.$s[0]->linkStored.'.'.$s[0]->mime.'">Bild</a><br />';
				} else
					echo '??? '.$p->getPath().' not found<br />';
			}
		} else echo "!!! no crop<br/>";
		$p->cleanBuffer();//echo $file;
		if($i > $z)
			die();
		$i++;
	}
}
die();
?>