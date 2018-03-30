<?php
	$res = dbQueries::get()->allPublicCollections();
	//echo $sql->getQueryString();
	if(isset($res[0])){
		header('Content-type: text/xml');	
		echo '<?xml version="1.0" encoding="UTF-8"?>'."\r\n";
		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\r\n";
		foreach($res as $val){
			if($val->link == 'unset')
			continue;
			echo "\t".'<url>'."\r\n";	
			echo "\t\t".'<loc>https://creezi.com/c/'.$val->contentID.'/'.$val->link."</loc>\r\n";
			
			//$databaseName2 = DBTableNameContentAll;
			$col = dbQueries::get()->allItemsOfCollection( $val->contentID );
			if(isset($col[0])){
				foreach($col as $vCol){
					$r = dbQueries::get()->oneImageInformationByContentID( $vCol->contentIDSub );
					//print_r($r); die();
					if(isset($r[0]->linkStored)){
						foreach($r as $v){
							echo "\t\t<image:image>\r\n";
		     				echo "\t\t\t<image:loc>".PIC_HOST.'/'.$v->linkStored.'.'.$v->mime."</image:loc>\r\n";
							echo "\t\t</image:image>\r\n";
						}
					}
				}
			}
			
			
			
			echo "\t".'</url>'."\r\n";
		}
		echo '</urlset> ';
		exit();
	}
?>