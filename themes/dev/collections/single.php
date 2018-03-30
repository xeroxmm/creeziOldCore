<?php
	//  API UND InfScroll Check
	pagination::setAsPaginationAnker();
	
	B::ID('wrap')
		->inner()
		->_DIV('content-collection','content-collection');
	
	$coll = new contentCollection();
	
	if($coll->getStatus() == 0)
		output::force404();
	else if($coll->getStatus() == 2){
		output::force303($coll->getLinkOfPictureSite());
	}

	html::head()->setCanonicalLink($coll->getLinkOfPictureSite());
	$contentURL = $coll->getLinkHot();
	//$pictureURLThumbMed = $image->getLinkHotThumbMed();
	$description = $coll->getDescription();
	
	if(strlen( $description ) <= 1 ){
		$descriptionMeta = snippet::getStringEmptyDescriptionSingle( $coll->getTitle() , $coll->getTags(), $coll->getUserNick(), 'The collection');
	} else {
		$descriptionMeta = $description;
	}
	
	// FOLLO - NOFOLLOW
	if($coll->isAdult() || $coll->isPrivate())
		html::head()->selectMeta()->setContentNofollow(); 
	
	// TITLE
	html::head()
		->setTitle($coll->getTitle().' - '.brandingName)
		->selectMeta()
			->setOGTitle( $coll->getTitle() )
			->setTwitterTitle( $coll->getTitle() );
	
	// KEYWORDS
	if(count($coll->getTags()) > 0){
		foreach($coll->getTags() as $val)
			html::head()->addKeyword($val[0]);
	}
	
	html::head()
		->setDescription( $descriptionMeta )
		->selectMeta()
			->setOGDescription( $descriptionMeta )
			->setTwitterDescription( $descriptionMeta );
	
	// AUTHOR
	html::head()->selectMeta()
		->setAuthor(brandingName)
		->setOGAuthor(brandingName)
		->setTwitterAuthor(brandingName);
	
	// TYPEN
	html::head()->selectMeta()
		->setOGTypeArticle()
		->setTwitterTypeLargeImage();
	
	// IMAGE STUFF
	html::head()->selectMeta()
		->setOGImageURL( $contentURL )
		->setTwitterImageURL( $contentURL )
		->setOGImageWidth( $coll->getDimensionX() )
		->setOGImageHeight( $coll->getDimensionY() );
	
	$url = core::getURLObj()->getPathArray();

	if(!empty($coll->getTitle()) && $coll->getTitle() != '_'){
		B::ID('content-collection')
			->_DIV('content-main-head',['content-main-head','relative'])
					->in()
						->_DIV(NULL,'content-main-head-in')
							->in()
								->_H(1,'',['globalheadline', 'font-size-200', 'userContent-x'])->in()->setContent($coll->getTitle())->outer()
								->_DIV('single-image-meta')
								->_DIV(NULL,['meta','tags'])
								->in()->_UL('meta-tags', ['single-tagList','userContent-x'])->outer()						
							->outer()
					->outer()
					->outer()
		->_DIV('grid-main',['grid-main','infinite-scroll'])
			->in()->_DIV('newItemContainer')->in()
			->_DIV('contentAddButton', 'grid-main-item')
			->in()
			->_A('http://creezi.com/join',NULL,NULL)->in()
				->_DIV(NULL,'intro-item-fill')
				->_DIV(NULL,'intro-item')->in()->setContent('+');
		
	}

	if(security::getUserObject()->getUserLevel() > userLevelEnum::ADMIN || user::getDBID() == $coll->getUserID()){
		$tag = new contentTag();
		html::footer()->addScript(RES_HOST.'/dev/js/adminChangingContent.js');
		$icon = 'z'; if($coll->isPrivate()) $icon = 'y';
		B::ID('content-main-head')->
			_DIV('adminPanel', ['panel-admin', 'absolute', 'right-10', 'bottom-10'])->in()
				->_DIV(NULL, ['button-icon', 'button-left','button','inline','admin-delete'])->in()
					->_DIV(NULL,'icon')->in()->setAttr('data-icon','B')->outer()->outer()
				->_DIV(NULL, ['button-string','button','inline','admin-edit'])->in()
					->_DIV(NULL,'string')->in()->setContent('edit post')->outer()->outer()
				->_DIV(NULL, ['button-string','button-right','button','inline','admin-private'])->in()
					->_DIV(NULL,['icon'])->in()->setAttr('data-icon',$icon)->outer()->outer()
				->_DIV('theContentInfoBox',['hidden'])->in()
					->setAttr('data-contentID',$coll->getContentID())
					->setAttr('data-tags',$coll->getTagsAsString())
					->setAttr('data-adult',(int)$coll->isAdult())
					->setAttr('data-private',(int)$coll->isPrivate())
					->setAttr('data-title',$coll->getTitle())
					->setAttr('data-alltags',$tag->getAllTagsAsString());
		//B::ID('single-image-meta')->_DIV('image-description-box', 'single-image-description userContent-x');
	} else {
		if(!empty($coll->getDescription()))
			B::ID('single-image-meta')->_DIV(NULL, 'single-image-description')->in()->setContent($coll->getDescription());
		
	}

	$z = count($coll->getItems()); $i = 1; $nameA = [];
	foreach($coll->getItems() as $val){
		if(!isset($val->linkContent))
			$link = PIC_HOST.'/'.$val->linkStoredDBEntry.'.'.$val->mime;
		else
			$link = HTTP_HOST.'/'.$val->linkContent;
		;
		
		if(security::getUserObject()->getUserLevel() > userLevelEnum::ADMIN || user::getDBID() == $coll->getUserID()){
			$t = new htmlTag('div',NULL,['box-control','hidden']);
			$t->in()->setAttr('data-itemID',$val->contentID)
				->_DIV(NULL,'opacer')
				->_DIV(NULL, 'control-items')->in()
					->_DIV(NULL, ['icon-delete','icon'])->in()->setAttr('data-icon','B')->outer()->outer()->outer();
		} else $t = NULL;
		B::ID('grid-main')->_DIV(NULL, 'grid-main-item')->in()
			->addElement($t)
			->_A($link,NULL,['loadInDOM'])->in()->_IMG( THUMB_HOST.'/'.$val->linkStoredDBEntry.'.jpg', NULL, 'single-image', $i.'. image in: '.$coll->getTitle())->outer();
		$i++;
		/*
		debug::checkAndCopyImageToBot( $val->linkStoredDBEntry);
		*/
		
		if(isset($_GET['a'])){
			
			require_once '/usr/local/lsws/CREEZI/urkraft/public_html/intern/2nd-party/openText/tesseractOCR.php';
			$file = '/usr/local/lsws/CREEZI/urkraft/public_html/userUploads/images/'.$val->linkStoredDBEntry.'.jpg';
			$n = (new TesseractOCR($file))->psm(1)->run();
			($temp = explode('|', $n));
			if(isset($temp[0]) && strlen($temp[0]) > 3){
				$name = str_replace(['#','1','2','3','4','5','6','7','8','9','0',' '],'', $temp[0]);
				$nameA[$temp[0]] = 1;
			} else {
				echo $file.'<br />';
			}

		}	
	}
    if(isset($_GET['a'])){
    	print_r($nameA); die();
    }
	if($coll->isPaginated()){
		B::ID('grid-main')->addElement(snippet::getPaginationLinkStart());
	}	
	/*if(!empty($description))
		B::ID('single-image-meta')
			->_DIV('image-description-box',['image-description','userContent-x']);*/
	
	foreach($coll->getTags() as $val){
		B::ID('meta-tags')->_LI(NULL, 'tag')->in()->_A(HTTP_HOST.'/'.LINK_tagPageSingle.'/'.$val[1],NULL,NULL,$val[0]);
	} 

	if(user::getUserObject()->getUserLevel() >= userLevelEnum::ADMIN || $coll->getUserID() == user::getDBID())
		html::head()->addOwnScriptString('var thePostID = "'.$coll->getContentID().'"; var thePostType = "'.LINK_collectionPageSingle.'";');

?>