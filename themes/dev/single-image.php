<?php
	security::loadUserLevel(userLevelEnum::NONE);
	
	html::head();
	/*
	 * Meta Tags
	<meta name="robots" content="follow,index" /> (wenn es in den Index von Suchmaschinen soll, wenn nicht nofollow,noindex)
	<title>#image-title - Croup#</title>
	<meta name="Description" content="#image-description#" />
	<meta name="keywords" content="#tag1,tag2,tag3#" />
	
	 * Facebook,Twitter
 	<meta name="twitter:title" content="#image-title#"/>
    <meta property="og:title" content="#image-title#"/>
	<meta property="author" content="Croup" />
	<meta property="article:author" content="Croup" />
	<meta property="og:site_name" content="Croup" />
	<meta property="og:type"         content="article" /> (es gibt kein Image OG Type...Facebook mal wieder)
	<meta property="og:image"        content="http://i.croup.co/XMmEZbh.png?fb" /> (müssen die Bilder auf alle Fälle via sub domain serven für parallel loading und via ?fb parameter später für Facebook optimierte thumbnails serven)
	<meta property="og:image:width"  content="600" /> (empfohlene Size für Facebook Feed, müssen spezielle thumbnails machen)
	<meta property="og:image:height" content="315" />
	<meta name="twitter:card"        content="summary_large_image"/>
	<meta name="twitter:image"       content="https://i.croup.co/XMmEZbh.png"/>
    <meta property="og:description" content="#image-description#"/>
    <meta name="twitter:description" content="#image-description#"/>
	 * 
	 */
		
	html::send200();
	
	exit;
?>