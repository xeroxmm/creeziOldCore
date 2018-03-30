<?php
	die();
	$sql = new dbObj();
	$sql->setTypeSELECT();
	
	$databaseName = 'content_videos';
	$databaseName2 = 'content_library';
	$sql->setDatabase($databaseName);	
	$sql->setSELECTField('videoID', $databaseName);
	$sql->setSELECTField('title', $databaseName);
	
	$sql->setLimit(100000);
	
	$res = db::query($sql);
	$i = 50;
	foreach($res as $val){
		$i = (int)$i;
		$temp = explode(' ',$val->title, 999);
		$ret = ''; $z = 0;
		for($j = 0; $j < count($temp); $j++){
			$z += strlen($temp[$j])	;
			if($z < $i || count($temp) == 1)	
				$ret .= $temp[$j].' ';
			else {
				$ret .= '&hellip; ';
				break;
			}
		}
		$ret = substr($ret,0,-1);
		echo $ret."\n";
		
		$s = new dbObj();
		$s->setTypeUPDATE(); $s->setDatabase($databaseName2);
		$s->setUpdatedFieldValueString('shortTitle', $ret, $databaseName2);
		$s->setConditionStringEqual('videoID', $val->videoID, $databaseName2);
		
		//db::query($s);
	}
	die();
	
	$sql = new dbObj();
	
	$moveToTempNEU = '/usr/local/lsws/CREEZI/neu/';
	$databaseName = DBTableNameContentImages;
	
	$sql->setTypeSELECT();
	$sql->setDatabase($databaseName);
	
	$sql->setSELECTField('imageID', $databaseName);
	$sql->setSELECTField('hash', $databaseName);
	$sql->setSELECTField('linkStored', $databaseName);
	$sql->setSELECTField('linkFilename', $databaseName);
	$sql->setSELECTField('imageID', $databaseName);
	$sql->setSELECTField('mime', $databaseName);
	
	//$sql->setConditionStringEqual('linkFilename', '404', $databaseName);
	
	$res = db::query($sql);
	
	foreach($res as $val){
		// look, if it is new datainame
		$tempC = explode('/',$val->linkStored,99);
		if(count($tempC) > 2){
			// schaue ob es die datei dort gibt
			$filename = '/usr/local/lsws/CREEZI/urkraft/public_html/userUploads/images'.$val->linkStored.'/'.$val->hash.'.'.$val->mime;
			
			// neuer datainame
			$newFileName = base_convert($val->imageID, 36, 10);	
			
			// WENN es nihct existiert, dann schaue ob es unter /0/... existiert
			//$datei = fopen('/usr/local/lsws/CREEZI/neu/'.$val->linkFilename.'.'.$val->mime,"w");
			//fclose($datei);
			if(!file_exists($filename)){
				$filename = '/usr/local/lsws/CREEZI/urkraft/public_html/userUploads/images/0/'.$newFileName.'.jpg';
				if(!file_exists($filename)){
					echo $filename."\n";
					// Dann loesche eintrag
					$sql = new dbObj();
					$sql->setTypeDELETE();
					$sql->setDatabase($databaseName);
					
					$sql->setConditionStringEqual('imageID', $val->imageID, $databaseName);
					
					if(db::query($sql)){
						echo $val->imageID." deleted ... \n";
												
						$dbName = 'content_images_meta';
						
						$sql = new dbObj();
						$sql->setTypeDELETE();
						$sql->setDatabase($dbName);
						
						$sql->setConditionStringEqual('imageID', $val->imageID, $databaseName);
						
						if(db::query($sql))
							echo $val->imageID." from $dbName deleted ... \n";
						
						$dbName = 'match_images_tags';
						
						$sql = new dbObj();
						$sql->setTypeDELETE();
						$sql->setDatabase($dbName);
						
						$sql->setConditionStringEqual('imageID', $val->imageID, $databaseName);
						
						if(db::query($sql))
							echo $val->imageID." from $dbName deleted ... \n";
						
						$dbName = 'content_images_IDList';
						
						$sql = new dbObj();
						$sql->setTypeDELETE();
						$sql->setDatabase($dbName);
						
						$sql->setConditionStringEqual('ident', $val->imageID, $databaseName);
						
						if(db::query($sql))
							echo $val->imageID." from $dbName deleted ... \n";
					}
					// next FILE
					continue;
				}
			}
			// erzeuge mime-Endung
			$img = getimagesize($filename);
			$mime = $img['mime'];
			$no = false;
			switch($mime){
		        case 'image/gif':
		            $mime = "gif";
		            
		            break;
		 
		        case 'image/png':
		            $mime = "png";
		            
		            break;
		 
		        case 'image/jpeg':
		            $mime = "jpg";
		            
		            break;
	
		        default:
		            
					$no = true;
		            break;
		    }

			if($no){
				echo $mime."\n";
				continue;
			}
			
			// update Name in der DB
			$databaseName = DBTableNameContentImages;
			$sql = new dbObj();
			$sql->setTypeUPDATE();
			
			$sql->setDatabase($databaseName);
			$sql->setConditionStringEqual('imageID', $val->imageID, $databaseName);
			$sql->setUpdatedFieldValueString('linkStored', '0/'.base_convert($val->imageID, 36, 10), $databaseName);
			$sql->setUpdatedFieldValueString('linkFilename', base_convert($val->imageID, 36, 10), $databaseName);
			$sql->setUpdatedFieldValueString('mime', $mime, $databaseName);
			
			db::query($sql);
		} else {
			// Die Datei hat zumindest in der DB schonmal den richtigen Namen
			$filename = '/usr/local/lsws/CREEZI/urkraft/public_html/userUploads/images/'.$val->linkStored;
			$filename2 = '/usr/local/lsws/CREEZI/urkraft/public_html/userUploads/thumbnails/'.$val->linkStored;
			if(!file_exists($filename.'.'.$val->mime) && !file_exists($filename.'.'.$val->mime)){
				echo $filename.'.'.$val->mime."\n";
				continue;
			}
			/*
			// MIME ueberprufen
			if(file_exists($filename.'.jpg'))
				$filename2 = $filename.'.jpg';
			else if(file_exists($filename.'.png'))
				$filename2 = $filename.'.png';
			
			$img = @getimagesize($filename2);
			if(!isset($img['mime'])){
				echo $filename2." ----------- \n";
				continue;
			}
			$mime = $img['mime'];
			$no = false;
			switch($mime){
		        case 'image/gif':
		            $mime = "gif";
		            
		            break;
		 
		        case 'image/png':
		            $mime = "png";
		            
		            break;
		 
		        case 'image/jpeg':
		            $mime = "jpg";
		            
		            break;
	
		        default:
		            
					$no = true;
		            break;
		    }

			if($no){
				echo $mime."\n";
				continue;
			}
			
			$databaseName = DBTableNameContentImages;
			$sql = new dbObj();
			$sql->setTypeUPDATE();
			
			$sql->setDatabase($databaseName);
			$sql->setConditionStringEqual('imageID', $val->imageID, $databaseName);
			$sql->setUpdatedFieldValueString('mime', $mime, $databaseName);
			
			db::query($sql);
			*/
			// RENAME DER DATEI IM DATEISYSTEM MIT UNTERSTRICH
			rename($filename.'.'.$val->mime,$filename.'_.'.$val->mime);			
			unlink($filename.'-med.'.$val->mime);
			unlink($filename2.'.'.$val->mime);
			// ERSTELLEN DER CRAWL-DATEI
			$datei = fopen('/usr/local/lsws/CREEZI/neu/'.$val->linkFilename.'.'.$val->mime,"w");
			fclose($datei);	
		}

		/*
		if(strpos($val->hash, '-') === FALSE && $val->mime != 'png'){
			if($val->linkStored[0] == '/')
				$val->linkStored = substr($val->linkStored, 1);
					
			$filename = '/usr/local/lsws/CREEZI/urkraft/public_html/userUploads/images/'.$val->linkStored.'/'.$val->hash.'.'.$val->mime;
			if(!file_exists($filename))
				echo $filename."\n";
			else {
				
				continue;
				$newHash = ((int)(filesize($filename)/100)).'-'.$val->hash;
				// rename file
				$filename2 = '/usr/local/lsws/CREEZI/urkraft/public_html/userUploads/images/0/'.base_convert($val->imageID, 36, 10).'_.jpg';
				// moving file
				rename($filename, $filename2);
				// build meta

				require_once '/usr/local/lsws/CREEZI/urkraft/public_html/intern/core/classes/class.process.picture.php';
				$i = new picture();
				$i->setLoadPath($filename2);
				$i->copyPictureIntoBuffer();
				
				$i->loadPicture();
				$i->cleanBuffer();
				$i->buildColorClusterMap();
				
				$databaseName = DBTableNameImageMetaInfo;
				$cluster = $i->getColorPaletteSub();
				$main = $i->getColorPaletteMain();
					
				$sql = new dbObj();
				$sql->setTypeINSERT();
			
				$sql->setDatabase($databaseName);
				
				$sql->setInsertFieldValueNULL('ID', $databaseName);
				$sql->setInsertFieldValueString('imageID', $val->imageID, $databaseName);
				
				for($i = 0; $i < 5; $i++){
					if(isset($cluster[$i])){
						$sql->setInsertFieldValueInteger('cluster'.($i + 1).'_number', $cluster[$i]['cluster'], $databaseName);
						$sql->setInsertFieldValueFloat('cluster'.($i + 1).'_var', $cluster[$i]['var'], $databaseName);
					}
					if(isset($main[$i]))
						$sql->setInsertFieldValueString('main'.($i + 1).'_color', $main[$i]['color'], $databaseName);
				}
				
				if(db::query($sql)){
					$databaseName = DBTableNameContentImages;
						
					$sql = new dbObj();
					$sql->setTypeUPDATE();
					
					$sql->setDatabase($databaseName);
					$sql->setConditionStringEqual('imageID', $val->imageID, $databaseName);
					$sql->setUpdatedFieldValueString('hash', $newHash, $databaseName);
					$sql->setUpdatedFieldValueInteger('isMETAupdated', 1, $databaseName);
					$sql->setUpdatedFieldValueString('linkStored', '0/'.base_convert($val->imageID, 36, 10), $databaseName);
					$sql->setUpdatedFieldValueString('linkFilename', base_convert($val->imageID, 36, 10), $databaseName);
					$sql->setUpdatedFieldValueString('mime', 'jpg', $databaseName);
					
					if(db::query($sql)){
						$datei = fopen($moveToTempNEU.base_convert($val->imageID, 36, 10),"w");
						fclose($datei);
					}
				} else 
					echo $sql->getQueryString()."\n";
			}
		}*/
	}
die();
?>