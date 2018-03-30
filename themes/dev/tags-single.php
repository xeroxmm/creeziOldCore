<?php
	security::loadUserLevel(userLevelEnum::NONE);
	
	pagination::setAsPaginationAnker();
	
	html::head();
	
	$urlObj = core::getURLObj()->getPathArray();
	$tagName = $urlObj[count($urlObj)-1];
	$tagArray = security::getTagArrayFromRequestString($urlObj);
	
	$tagTypes = [];
	if(isset($urlObj[1])){
		if($urlObj[1] == 'images')
			$tagTypes[] = 'i';
		else if($urlObj[1] == 'videos')
			$tagTypes[] = 'v';
		else if($urlObj[1] == 'collections')
			$tagTypes[] = 'c';
		else {
			$tagTypes[] = 'i';
			$tagTypes[] = 'v';
			$tagTypes[] = 'c';
		}
			
	}
	
	//$tagName = 'boobies'; 
	$tagMeta = snippet::getStringTags($tagArray);
	$titleMeta = snippet::getAmountOfFilesByTag($tagArray, NULL, $tagTypes).' about '.$tagMeta.' on creezi';
	$descriptionMeta = 'Discover thousands of files about '.$tagMeta.' on creezi, we help you discover, create and share your content.';
	html::head()->setTitle( $titleMeta );
	html::head()
		->setDescription( $descriptionMeta )
		->selectMeta()
			->setOGDescription( $descriptionMeta )
			->setTwitterDescription( $descriptionMeta ); 

	B::ID('wrap')
		#->_DIV('sidebar-main',['sidebar-main','fixed'])
		->_DIV('content-main','content-main')
			->in()
				->_DIV('content-main-in','content-main-in')
				->in()
					->_DIV(NULL,'content-main-head')
						->in()
							->_DIV(NULL,'content-main-head-in')->in()->setContent('<h1 class="globalheadline font-size-200">#'.implode($tagArray,' #').'</h1><span class="header-subline">'.snippet::getAmountOfFilesByTag($tagArray,'file').' found</span>')->outer()
						->outer()
					->_DIV('content-main-nav','content-main-nav')
					->outer();

	B::ID('content-main-nav')->addElement( snippet::getAdvancedFilterDOMElements() );

	$rsult = snippet::getTopContent(20 , $tagArray, pagination::getTimeStamp(), pagination::getPage());
	if($rsult->isEmptyElement()){
		html::head()->selectMeta()->setContentNofollow();
	} else {
    	B::ID('content-main-in')
    		->addElement( $rsult );
    	B::ID('img-grid-box')->addElement( snippet::getPaginationLinkStart() );
    }
	html::send200();
	
	exit;
?>