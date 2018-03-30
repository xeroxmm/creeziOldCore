<?php
	security::loadUserLevel(userLevelEnum::NONE);
	
	//  API UND InfScroll Check
	snippet::useSpecialContentDB();
	pagination::setAsPaginationAnker();
	
	html::head();
	
	$titel = brandingName;
	
	// TITLE
	html::head()
		->setTitle( $titel )
		->selectMeta()
			->setOGTitle( $titel )
			->setTwitterTitle( $titel );
	
	// KEYWORDS
	$tags = ['creezi', 'pictures', 'wallpapers', 'memes', 'collections'];
	if(count($tags) > 0){
		foreach($tags as $val)
			html::head()->addKeyword($val);
	}
	
	// DESCRIPTION
	$descriptionMeta = 'Hi I\'m creezi,a quick way to discover, save and share websites and files. Get content recommendations based on your interests and topics you follow.';
	html::head()
		->setDescription( $descriptionMeta )
		->selectMeta()
			->setOGDescription( $descriptionMeta )
			->setTwitterDescription( $descriptionMeta );
	
	B::ID('wrap')
		//->inner()
		#->_DIV('sidebar-main',['sidebar-main','fixed'])
		->_DIV('content-main','content-main')
			->in()
				->_DIV('content-main-in','content-main-in')
				->in()
					/*
					->_DIV(NULL,'content-main-head')
						->in()
							->_DIV(NULL,'content-main-head-in')->in()->setContent('<h1 class="globalheadline font-size-160">Hi, I\'m creezi</h1><span class="header-subline">Social collaboration for everyone</span>')->outer()
						->outer()
					 */
					->_DIV('content-main-nav','content-main-nav');
	
	B::ID('content-main-nav')->addElement(snippet::getAdvancedFilterDOMElements());
    
	B::ID('content-main-in')->addElement(snippet::getTopContent(20));
	
	B::ID('img-grid-box')->addElement( snippet::getPaginationLinkStart() );
	
	html::send200();
	
	exit;
?>