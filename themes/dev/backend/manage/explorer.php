<?php
	security::loadUserLevel(userLevelEnum::NORMAL);
	
	html::head()->addStylesheet(RES_HOST.'/'.frontendLayoutDIR.'/css/explorer.css');
	html::head()->addStylesheet(RES_HOST.'/'.frontendLayoutDIR.'/css/perfect-scrollbar.min.css');
	html::head()->addStylesheet(RES_HOST.'/'.frontendLayoutDIR.'/fonts/font-awesome.min.css');
	html::head()->addStylesheet("https://fonts.googleapis.com/css?family=Arya");
	
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
	
	// All strings
	$hLeftWindow = 'Your Content Organigram';
	$hMidWindow = 'Content Information';
	$hRightWindow = 'Your Source List';
	
	B::ID('wrap')
		->_DIV('explorerContainer',['fixed','right-10','left-10','bottom-10','top-headline','color-888'])->in()
			->_DIV('leftWindow',['scalable','width-25p','position-left','height-100p','relative'])
			->_DIV('middleWindow',['scalable','width-50p','position-middle','height-100p','relative'])
			->_DIV('rightWindow',['scalable','width-25p','position-right','height-100p','relative']);
	
	B::ID('leftWindow')
		->_DIV('cPartLeftContent',['absolute','top-0','right-10','bottom-0','left-0','flex-column-0'])->in()
			->_H(3, NULL, 'windowHeadline')->in()
				->setContent( $hLeftWindow )->outer()
			->_DIV('cOrganigram',['bordered','border-round-5','color-border-main','border-solid','noOverflow','niceScrollY','margin-5','flex-height-asPos','whiteboard'])->in()
				->_DIV('cOrganigram-tree', ['presenter-tree','margin-5'])->outer()->outer()
		->_DIV('cPartLeftScale',['width-10','top-0','right-0','bottom-0','absolute','noselect'])->in()
			->_p(NULL, ['scale-button','center-vertical-horizontal','no-margin','scaler'],' || ');
	
	B::ID('rightWindow')
		->_DIV('cPartRightContent',['absolute','top-0','right-0','bottom-0','left-10','flex-column-0'])->in()
			->_H(3, NULL, 'windowHeadline')->in()
				->setContent( $hRightWindow )->outer()
			->_DIV('cSrcList',['bordered','border-round-5','color-border-main','border-solid','noOverflow','niceScrollY','margin-5','flex-height-asPos','whiteboard'])->in()
				->_DIV('cSrcList-tree', ['presenter-tree','margin-5'])->outer()->outer()
		->_DIV('cPartRightScale',['width-10','top-0','left-0','bottom-0','absolute','noselect'])->in()
			->_p(NULL, ['scale-button','center-vertical-horizontal','no-margin','scaler'],' || ');
			
	B::ID('middleWindow')
		->_DIV('cPartLeftContent',['absolute','top-0','right-0','bottom-0','left-0','flex-column-0'])->in()
			->_H(3, NULL, 'windowHeadline')->in()
				->setContent( $hMidWindow )->outer()
			->_DIV('cInfobox',['bordered','border-round-5','color-border-main','border-solid','noOverflow','niceScrollY','margin-5','flex-height-asPos','whiteboard'])->in()
				->_DIV('cInfo-tree', ['presenter-tree','margin-5']);
				
	/* Static Content */
	B::ID('cOrganigram-tree')
		->_DIV(NULL,'fGroup-0')->in()
			->_P(NULL, 'folder')->in()
				->_I(NULL, NULL, 'fa fa-plus-square-o')
				->_I(NULL, NULL, 'fa fa-folder-o')
				->setContent('Articles')->outer()
			->_HR()
			->_P(NULL, 'folder')->in()
				->_I(NULL, NULL, 'fa fa-plus-square-o nope')
				->_I(NULL, NULL, 'fa fa-folder-o')
				->setContent('Collections')->outer()
			->_HR()
			->_P(NULL, 'folder')->in()
				->_I(NULL, NULL, 'fa fa-minus-square-o')
				->_I(NULL, NULL, 'fa fa-folder-open-o')
				->setContent('Images')->outer()
			->_DIV(NULL,'fGroup')->in()
				->_P(NULL, 'folder')->in()
					->_I(NULL, NULL, 'fa fa-plus-square-o nope')
					->_I(NULL, NULL, 'fa fa-folder-o')
					->setContent('tagged')->outer()
				->_P(NULL, 'folder')->in()
					->_I(NULL, NULL, 'fa fa-minus-square-o')
					->_I(NULL, NULL, 'fa fa-folder-open-o')
					->setContent('untagged')->outer()
				->_DIV(NULL,'fGroup')->in()
					->_P(NULL, 'folder')->in()
						->_I(NULL, NULL, 'fa fa-plus-square-o nope')
						->_I(NULL, NULL, 'fa fa-file-image-o')
						->setContent('Image 1')->outer()
					->_P(NULL, 'folder')->in()
						->_I(NULL, NULL, 'fa fa-plus-square-o nope')
						->_I(NULL, NULL, 'fa fa-file-image-o')
						->setContent('Image 2 -> Image 2 -> Image 2 -> Image 2 -> Image 2 -> Image 2 -> Image 2 -> Image 2 -> Image 2 -> Image 2 -> Image 2 -> Image 2 -> Image 2 -> Image 2 -> ')->outer()->outer()->outer()
			->_P(NULL, 'folder')->in()
				->_I(NULL, NULL, 'fa fa-plus-square-o')
				->_I(NULL, NULL, 'fa fa-folder-o')
				->setContent('Your uploads')->outer()
?>