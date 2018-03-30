<?php
	$res = dbQueries::get()->allPublicPosts(0);
	
	$e = count($res);
	$r = (int)($e / 45000 - 0.01) + 1;
	
	header('Content-type: text/xml');   
    echo '<?xml version="1.0" encoding="UTF-8"?>'."\r\n";
    echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\r\n";
	
	for($i = 1; $i <= $r; $i++){
		echo "<sitemap>\r\n\t<loc>https://creezi.com/sitemap-images/$i</loc>\r\n\t<lastmod>".date('Y-m-d')."T00:00:".(24+$i)."+00:00</lastmod>\r\n</sitemap>\r\n";
	}
	
	echo "</sitemapindex>";
	exit;
/*

   
   
   <sitemap>
      <loc>http://www.ihrebeispielurl.de/sitemap2.xml.gz</loc>
      <lastmod>2005-01-01</lastmod>
   </sitemap>
   </sitemapindex>
*/

?>