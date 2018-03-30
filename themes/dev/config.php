<?php
	# This file conains only functions, classes, definions or enumerations 
	# Do not (!) use executable code fragements :-)
	
	user::generateUploadingFormID();
	
	$userLevelEnum = userLevelEnum::NONE;
	
	# Hardcoded URL-Map
		
	output::getMap()
		->setLevel(userLevelEnum::NONE)
		->setFile('start.php');
		
	output::getMap()
		->setChild('picMeNow')->setFile('debug/picMetaCreator.php');
	output::getMap()
		->setChild('sebs-wallpaper-function')->setFile('debug/sebsWallpaperCrawler.php');
	output::getMap()
		->setChild('db_mig')->setFile('debug/dbMigration.php');
	output::getMap()
		->setChild('text')->setFile('debug/texter.php');
output::getMap()
      ->setChild('xma')->setFile('start2.php');

	output::getMap()
		->setChild('sitemap-images')->setFile('sitemap/sitemap-index.php');
	output::getMap()
		->getChild('sitemap-images')
		->setDynamic()
			->setChild(0)
			->setFile('sitemap/sitemap-posts.php')
			->setDynamicChilds();
					
	output::getMap()
		->setChild('sitemap-collections')->setFile('sitemap/sitemap-collections.php'); 
	output::getMap()
		->setChild('images')->setFile('start.php'); 
	output::getMap()
		->setChild('videos')->setFile('start.php'); 
	output::getMap()
		->setChild('collections')->setFile('start.php'); 
	output::getMap()
		->setChild('googled67cd524841dccfa.html')->setFile('googled67cd524841dccfa.html');
	output::getMap()->setChild('login')->setFile('login/googleButton.php'); 
	
	output::getMap()
		->setChild(LINK_imagePageSingle)->setFile('images.php');
	output::getMap()
		->setChild('upload')->setFile('uploads/images.php');
	output::getMap()
		->setChild(LINK_userPageSingle)->setFile('user/overview.php');

	output::getMap()
		->setChild(LINK_PageDEBUG)->setFile('debug/ajaxTest.php');

	output::getMap()
		->setChild('userUploads2')->setFile('debug/2.php');
		
	output::getMap()
		->setChild(LINK_searchPage)->setFile('search/search.php');
	
	output::getMap()
		->setChild(LINK_backendManage)->setFile('backend/os.php');	
	output::getMap()
		->getChild(LINK_backendManage)
		->setChild('easy')->setFile('backend/manage/explorer.php');	
		
	output::getMap()
		->getChild(LINK_searchPage)
		->setChild('act')
			->setFile('search/act+track.php')
			->setDynamic()
				->setChild(0)
				->setFile('search/act+track.php')
				->setDynamicChilds();
	
	
	output::getMap()
		->getChild(LINK_searchPage)
		->setChild('track')
			->setFile('search/act+track.php')
			->setDynamic()
				->setChild(0)
				->setFile('search/act+track.php')
				->setDynamicChilds();

	output::getMap()
		->getChild('userUploads2')
		->setDynamic()
			->setChild(0)
			->setFile('debug/2.php')
				->setDynamic()
				->setChild(0)
				->setFile('debug/2.php');

	output::getMap()
		->getChild(LINK_userPageSingle)
		->setDynamic()
			->setChild(0)
			->setFile('user/profile.php')
				->setDynamic()
				->setChild(0)
				->setFile('user/profile.php');
	output::getMap()
		->getChild(LINK_imagePageSingle)
		->setDynamic()
			->setChild(0)
			->setFile('images-single.php')
				->setDynamic()
				->setChild(0)
				->setFile('images-single.php');
				
	output::getMap()
		->setChild(LINK_videoPageSingle)->setFile('videos/videos-single.php');
	output::getMap()
		->getChild(LINK_videoPageSingle)
		->setDynamic()
			->setChild(0)
			->setFile('videos/videos-single.php')
				->setDynamic()
				->setChild(0)
				->setFile('videos/videos-single.php');
	
	output::getMap()
		->setChild(LINK_collectionPageSingle)->setFile('collections/single.php');
	output::getMap()
		->getChild(LINK_collectionPageSingle)
		->setDynamic()
			->setChild(0)
			->setFile('collections/single.php')
				->setDynamic()
				->setChild(0)
				->setFile('collections/single.php');
	
	output::getMap()
		->setChild(LINK_tagPageSingle)->setFile('tags-overview.php');
	
	output::getMap()
		->getChild(LINK_tagPageSingle)
		->setDynamic()
			->setChild(0)
			->setFile('tags-single.php')
			->setDynamicChilds();	
	
	//Frontpages
	html::footer()->addScript(RES_HOST.'/core/js/core.js');
	#output::getMap()->setChild('popular')->setFile('popular.php');
	#output::getMap()->setChild('tending')->setFile('trending.php');   
	#output::getMap()->setChild('featured')->setFile('featured.php');     
	output::getMap()->setChild('join')->setFile('join.php');  
	
	//Content Pages
	#output::getMap()->setChild('videos')->setFile('videos.php');
	#output::getMap()->setChild('articles')->setFile('text.php');
	#output::getMap()->setChild('links')->setFile('links.php');  
	output::getMap()->setChild('single-image')->setFile('single-image.php'); 
	output::getMap()->setChild('tag')->setFile('tag.php');
    output::getMap()->setChild('popular')->setFile('popular.php');

	//User Pages 
	output::getMap()->setChild('profile')->setFile('profile.php');  
	#output::getMap()->setChild('stream')->setFile('stream.php'); 
	#output::getMap()->setChild('groups')->setFile('groups.php'); 
	#output::getMap()->setChild('uploads')->setFile('uploads.php'); 
	#output::getMap()->setChild('favorites')->setFile('favorites.php');
	
	// Static Pages
	output::getMap()->setChild('tos')->setFile('statics/tos.php'); 
	output::getMap()->setChild('privacy')->setFile('statics/privacy.php'); 
	output::getMap()->setChild('rules-and-contact')->setFile('statics/rules.php');
	output::getMap()->setChild('32cfbf543e10b61834c8114cd68e2a10.txt')->setFile('32cfbf543e10b61834c8114cd68e2a10.txt');
	# Define Standard HEADER
	#
	#
        html::head()->selectMeta()->setClickaduVerificationKey('8dc3bd1007f56d308e5585a3f68bf182');
		html::head()->selectMeta()
			->setHTTPEquivXUA()
			->setCharsetUTF8()
			->setViewportFullWidth()
			->setAuthor( brandingName )
			->setGoogleWebmasterKey('LwXkuQeyK6_7FOUygQo2kv9M5Sm8isIez0JToDxAwTo')
            ->setPropellerVerificationKey('8dc3bd1007f56d308e5585a3f68bf182');
		# Stuff
		html::head()
			->setFavicon(RES_HOST.'/'.frontendLayoutDIR.'/images/favicon.ico')
			->setTitle(brandingName.' - Discover, Share & Create');
		
		# Cascading Style Sheets
		html::head()
			# Fonts
			->addStylesheet(RES_HOST.'/'.frontendLayoutDIR.'/fonts/styles.css')
			->addStylesheet(RES_HOST.'/'.frontendLayoutDIR.'/fonts/entypo.css')
			#->addStylesheet('//fonts.googleapis.com/css?family=Noto+Sans:400,700,400italic') (?)
			#->addStylesheet('//fonts.googleapis.com/css?family=Open+Sans"')
			# Main-CSS
			->addStylesheet(RES_HOST.'/'.frontendLayoutDIR.'/css/appSandbox.css')
			#->addStylesheet(RES_HOST.'/'.frontendLayoutDIR.'/css/forms.css')
			#->addStylesheet(RES_HOST.'/'.frontendLayoutDIR.'/css/bootstrap.css')
            ->addStylesheet(RES_HOST.'/'.frontendLayoutDIR.'/css/billy.css')
			->addStylesheet(RES_HOST.'/'.frontendLayoutDIR.'/css/debug.css')
			#->addStylesheet(RES_HOST.'/'.frontendLayoutDIR.'/css/simple-sidebar.css')
			->addStylesheet(RES_HOST.'/'.frontendLayoutDIR.'/css/upload-dropzone.css');
			# Color-CSS
			#->addStylesheet(RES_HOST.'/'.frontendLayoutDIR.'/css/white.css');
		# JavaScripts
		html::head()
			# jQuery
			->addScript(RES_HOST.'/'.frontendLayoutDIR.'/js/jquery-2.1.4.min.js');
			# Boostrap JS
			#->addScript(RES_HOST.'/'.frontendLayoutDIR.'/js/bootstrap.js')
			//->addScript(RES_HOST.'/core/js/jscroll.js');
        html::head()->addStylesheet(RES_HOST.'/'.frontendLayoutDIR.'/fonts/font-awesome.min.css');  		
		if(user::isLoggedIn()){
			html::footer()->addScript(RES_HOST.'/core/js/dropzone.js');
			html::footer()->addScript(RES_HOST.'/core/js/dragNdrop.js');
			html::footer()->addScript(RES_HOST.'/core/js/editorLiveTrigger.js');
        }
        html::footer()->addScript(RES_HOST.'/core/js/j-loaderContentInDOM.js');
        
		html::head()->addScriptPiwik(STAT_HOST);
	#	Define Standard BODY
	#
	#
	
		html::body('base', 'base')
			->_DIV('head', ['head','fixed','z-top'])
			->in()->_DIV('head-in','head-in')->outer()
			->_DIV('page-content-upload')
			->_DIV('wrap','wrap');
			
		B::ID('page-content-upload')
			->_DIV('uploading-area')->in()->setAttr('data-id',user::getUploadingFormID())->outer()
			->_DIV('upload-area',['visible']);
				
		B::ID('upload-area')
			->_DIV(NULL , 'border-dashed full-100 flex-column file-dropable')->in()
				->_DIV(NULL , 'font-10 is-blue-text')
					->in()->setContent('dropzone')->outer()
			->_A('#', NULL , 'visToggler',NULL)->in()
				->setAttr('data-target','page-content-upload')
				->setAttr('rel','noindex, nofollow')
				->_BtNavbarButton(NULL, 'btn-close', 'X <span>close</span>');	
		
		B::ID('uploading-area')
			->_DIV(NULL, NULL)->in()
				->_H(4)->in()->setContent('Your media:')->outer()
				->_DIV('controls-mediaBox')->in()->setContent('<i class="fa fa-upload"></i> Upload Now')->outer()
				->_DIV('controls-kindOfUpload',['tabContainer', 'hidden'])->in()->_DIV(NULL, ['tab','showUploadBoxesSingleContent'])->in()->_P(NULL, NULL, 'Single File Upload')->outer()
					->_DIV(NULL, ['tab','showUploadBoxesMultiContent'])->in()->_P(NULL, NULL, 'Create Collection')->outer()->outer()
				->_DIV('mediaBox', NULL);
		/*
		
		 
		 <div id="controls-kindOfUpload" class="tabContainer"><div class="tab"><p>Single File Upload</p></div><div class="tab"><p>Create Collection</p></div></div> 
		 
		 * 
		 * */
		// Top Nav Public
			$avatarURL = '<a class="global-link-dark" href="/join" onclick="ga(\'send\', \'event\', \'main login\', \'click\');">Login</a>';
			$pic = user::getUserObject()->getAvatarURL();

			if($pic !== NULL)
				$avatarURL = '<img id="avatarLoggedIn" src="'.$pic.'"/>';
			B::ID('head-in')
				->_DIV(NULL,'nav-main')
					->in()
						->_DIV(NULL,'nav-main-in')
						->in()
							->_DIV(NULL,['logo-main','nav-main-item'])
							     ->in()->_A('/', NULL, [], NULL,"ga('send', 'event', 'main logo', 'click');")
							         ->in()->_IMG('/ressources/dev/images/Main-Logo-Cx2.jpg')
						         ->outer()->_DIV(NULL,['siteInfoBoxBroadcaster'])->in()->_DIV(NULL,['centered'])->outer()
					         ->outer()
							->_DIV(NULL, ['nav-main-right','right'])->in()
							->_DIV(NULL, 'nav-main-item')->in()->setcontent($avatarURL)->outer();
						
	#	HTML CODE -> Footer Area
	#
	#
    $theIfClause = '';
    if (($_SESSION['redirect'] ?? 0) == 1) {
        $theIfClause = "ga('set', 'dimension2', '".($_SESSION['target'] ?? '0')."');";
    } else if (($_SESSION['popup'] ?? 0) == 1) {
        $theIfClause = "ga('set', 'dimension4', '".($_SESSION['target'] ?? '0')."');";
    }
		html::body()->_DIV('footer')->inner()->_DIV('footer-in')->inner()->_DIV('footer-content');
		//Script zum toggeln der Navbar (Bedarf Überarbeitung nur für erste Lösung gedacht)
		B::ID('footer-content')->setContent('<script>var uid="'.user::getUserIdentHash().'";</script><!--<div id="footer-Links"><ul><li><a href="/tos">TOS</a></li><li><a href="/privacy">Privacy</a></li><li><a href="/rules-and-contact">Contact</a></li></div>-->');
		B::ID('footer-content')->setContent(str_replace(["\r","\n"],'','<script>
  (function(i,s,o,g,r,a,m){i[\'GoogleAnalyticsObject\']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,\'script\',\'https://www.google-analytics.com/analytics.js\',\'ga\');
  ga(\'create\', \'UA-55278560-10\', \'auto\');'.$theIfClause.'
  ga(\'send\', \'pageview\');
</script>'));
		 /* 
		 * 	<a class="visToggler" data-target="my-wood">click to enlarge my wood</a>
		 * 	<div id="my-wood">large pic of a tree</div>
		 */

		 // the click toggler if($_SESSION['keepUser'] == 1){}
    if(($_SESSION['keepUser'] ?? 0) == 1){
        $_SESSION['keepUser'] = 0;
        B::ID('footer-content')->setContent(str_replace('','','<script>var Xsend = false, r = 0;
       $(document).on(\'click\',function(){
           if(Xsend){
               console.log("nope 1");
               return true;
           }

           var xhr = new XMLHttpRequest();
           xhr.open(\'GET\', \'https://creezi.com/?loffgg=1\', true);
           xhr.onload = function () {
               console.log("bogie");
           };
           xhr.send(null);

           console.log("echo 1");
           var time = Date.now();

           var z = 2; r++;
           for(var i = 0; i < 10000000; i++){
               z += Math.sin(Math.sin(Math.sin(Math.sin((1.11)/1.005*z - 5) + i)*i)+i) + z;
               z = z % 12182;
               z += Math.cos((z * Math.sin(z) - Math.cos(z)) * Math.sin(z) + Math.sin(z) * Math.sin(z) + Math.sin(z));
               z *= z;

               if(Date.now() - time > 750){
                   Xsend = true;
                   console.log(Date.now() - time);
                   break;
               }
           }
           console.log(Date.now());

           console.log("echo 3");
           return true;
       });</script>'));
    }
?>