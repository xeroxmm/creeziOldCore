<?php
	//security::loadUserLevel(userLevelEnum::NORMAL);
	
	html::head()->addStylesheet(RES_HOST.'/'.frontendLayoutDIR.'/css/os.css');
	html::head()->addStylesheet(RES_HOST.'/'.frontendLayoutDIR.'/css/explorer.css');
	html::head()->addStylesheet(RES_HOST.'/'.frontendLayoutDIR.'/css/perfect-scrollbar.min.css');
	html::head()->addStylesheet(RES_HOST.'/'.frontendLayoutDIR.'/fonts/font-awesome.min.css');
	html::head()->addStylesheet("https://fonts.googleapis.com/css?family=Ubuntu:400,500,400italic");
	
	html::footer()->addScript(RES_HOST.'/core/js/jquery-ui.min.js');
	html::footer()->addScript(RES_HOST.'/dev/js/perfect-scrollbar.jquery.min.js');
	html::footer()->addScript(RES_HOST.'/dev/js/explorer.js');
	html::footer()->removeScript(RES_HOST.'/core/js/dropzone.js');
	
	$titel = brandingName.' - manage your content';
	
	// TITLE
	html::head()
		->setTitle( $titel )
		->selectMeta()
			->setOGTitle( $titel )
			->setTwitterTitle( $titel );
	
	B::ID('base')
		->addClass('fixed')
		->addClass('fullScreen')
		->addClass('background-contains')
		->addElementAtBegin(new htmlTag('div','head-os'));
	B::ID('head-os')
		->_DIV(NULL,'blur-transparent')
		->_DIV('osBar', ['z-top','relative','fullHeight'])->in()
			->_DIV('osBarRight',['fullHeight'])
			->_DIV('osBarLeft',['fullHeight']);
		
	// OS BAr elements
	B::ID('osBarLeft')
		->_DIV('buttonMenuHome',['barButton','buttonString','togglerString'])->in()
			->_P('osBarHomePage',['osBarString','color-branding'])->in()
				->_IMG(RES_HOST.'/'.frontendLayoutDIR.'/images/logo-main.png',NULL,['logo'],'Discover Creezi')->outer()->outer()
		->_DIV('buttonWindowConfig',['barButton','buttonIcon','togglerConfigWindow', 'active'])->in()
			->_I(NULL, 'buttonBarTogglerFS', ['fa','fa-cogs'])->outer()
		->_DIV('buttonWindowConfig',['barButton','buttonIcon','togglerConfigWindow', 'openWindow'])->in()
			->_I(NULL, 'buttonBarTogglerFS', ['fa','fa-folder'])->outer()
		//
		->_DIV('buttonWindowConfig',['barButton','buttonIcon','togglerConfigWindow'])->in()
			->_I(NULL, 'buttonBarTogglerFS', ['fa','fa-group'])->outer()
		->_DIV('buttonWindowConfig',['barButton','buttonIcon','togglerConfigWindow'])->in()
			->_I(NULL, 'buttonBarTogglerFS', ['fa','fa-heart'])->outer()
		->_DIV('buttonWindowConfig',['barButton','buttonIcon','togglerConfigWindow'])->in()
			->_I(NULL, 'buttonBarTogglerFS', ['fa','fa-area-chart'])->outer()
		->_DIV('buttonWindowConfig',['barButton','buttonIcon','togglerConfigWindow'])->in()
			->_I(NULL, 'buttonBarTogglerFS', ['fa','fa-calendar'])->outer()
		->_DIV('buttonWindowConfig',['barButton','buttonIcon','togglerConfigWindow'])->in()
			->_I(NULL, 'buttonBarTogglerFS', ['fa','fa-paper-plane'])->outer();
			
	B::ID('osBarRight')
		->_DIV('buttonViewFullScreen',['barButton','buttonIcon','togglerFullscreen'])->in()
			->_I(NULL, 'buttonBarTogglerFS', ['fa','fa-desktop'])->outer()
		->_DIV(NULL,['barButton','buttonString','togglerString'])->in()
			->_P('osBarDate',['osBarString','dualLine'],'Tuesday<br />12.07.2016')->outer()
		->_DIV(NULL,['barButton','buttonString','togglerString'])->in()
			->_P('osBarTime',['osBarString','oneLine'],'15:03')->outer()
		->_DIV('buttonWindowConfig',['barButton','buttonIcon','togglerConfigWindow'])->in()
			->_I(NULL, 'buttonBarTogglerFS', ['fa','fa-envelope'])->outer()
		->_DIV('buttonWindowConfig',['barButton','buttonIcon','togglerConfigWindow'])->in()
			->_I(NULL, 'buttonBarTogglerFS', ['fa','fa-bell'])->outer()
		->_DIV('buttonWindowConfig',['barButton','buttonIcon','togglerConfigWindow'])->in()
			->_I(NULL, 'buttonBarTogglerFS', ['fa','fa-comment'])->outer();
	
	B::ID('base')->addClass('osBackgroundImage')
		->_DIV('osLoading',['fixed','fullScreen','z-top'])->in()
			->_DIV(NULL, ['absolute','fullHeight','fullwidth','opacity-50p','color-FFF','z-1000'])
			->_DIV(NULL, ['fullHeight','fullwidth','z-1001'])->in()
				->_DIV(NULL, ['color-branding','fontSize-large', 'centered','z-1001'])->in()
					->_P(NULL,NULL)->in()
						->_I(NULL, NULL,['fa','fa-spinner','fa-pulse','space-right-05e'])
						->setContent('Loading');/*->outer()->outer()->outer()->outer()
		->_DIV('explorerContainer',['fixed','right-10','left-10','bottom-10','top-headline','color-888'])->in()
			->_DIV('leftWindow',['scalable','width-25p','position-left','height-100p','relative'])
			->_DIV('middleWindow',['scalable','width-50p','position-middle','height-100p','relative'])
			->_DIV('rightWindow',['scalable','width-25p','position-right','height-100p','relative']);*/
	// add dekstop area
	//print_r(user::getUserObject()); die();
	B::ID('base')->_DIV('osDesktop',['fixed','left-0','right-0','bottom-0','top-bar','nonVis'])->in()->addElement( snippet::getWindowAccountInfo(1, user::getUserObject(),TRUE ));
	B::ID('osDesktop')->addElement( snippet::getWindowExplorer(2, user::getUserObject() ));
?>