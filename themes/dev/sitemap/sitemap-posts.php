<?php
	$s = core::getURLObj()->getPathArray();
	$offset = 0;
	
	if((!is_array($s) || count($s) != 2 || (int)$s[1] < 1)){
		html::send404();
		exit;
	}
	
	$offset = (int)$s[1];
	
    $res = dbQueries::get()->allPublicPosts($offset);

	$r2 = dbQueries::get()->allPublicDescriptionsAndTexts();
	
    $c = count($r2); $res_2 = [];
    for($i = 0; $i < $c; $i++){
    	$res_2[ $r2[$i]->contentID ] = $r2[$i]->text;
		unset($r2[$i]);
    }
	unset($r2);

    if(isset($res[0])){
        header('Content-type: text/xml');   
        echo '<?xml version="1.0" encoding="UTF-8"?>'."\r\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\r\n";
        foreach($res as $val){
            if($val->link == 'unset')
            continue;
            echo "\t".'<url>'."\r\n";   
            if(!empty($val->link))
                echo "\t\t".'<loc>https://creezi.com/i/'.$val->contentID.'/'.$val->link."</loc>\r\n";
            else
                echo "\t\t".'<loc>https://creezi.com/i/'.$val->contentID."</loc>\r\n";

            echo "\t\t<image:image>\r\n";
            echo "\t\t\t<image:loc>".PIC_HOST.'/'.$val->thumbnailLink.'.'.$val->mime."</image:loc>\r\n";
			
			if(!empty($val->title))
				echo "\t\t\t<image:title>".$val->title."</image:title>\r\n";
			
			if(isset($res_2[ $val->contentID ]))
				echo "\t\t\t<image:caption>".htmlspecialchars($res_2[ $val->contentID ] , ENT_XML1)."</image:caption>\r\n";
            
            echo "\t\t</image:image>\r\n";  
            
            echo "\t".'</url>'."\r\n";
        }
        echo '</urlset> ';
        exit();
    } else {
    	html::send404();
		exit;
    }
?>