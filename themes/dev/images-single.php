<?php
	B::ID('wrap')
		->inner()
		->_DIV('content-image','content-image');
	
	$image = new contentPicture();
	if($image->getStatus() == 0)
		output::force404();
	else if($image->getStatus() == 2){
		output::force303($image->getLinkOfPictureSite());
	}
	//echo $image->getLinkOfPictureSite();die();
	//$image = new contentPicture($link); 
	html::head()->setCanonicalLink($image->getLinkOfPictureSite());
	$pictureURL = $image->getLinkHot();
	$pictureURLThumbMed = $image->getLinkHotThumbMed();
	$description = $image->getDescription();
	
	$colectionsArray = $image->getCollectionsIn();
    $sub = (isset($colectionsArray[0])) ? $colectionsArray[0] : false;
    
    if(strlen( $description ) <= 1 ){   
        $descriptionMeta = snippet::getStringEmptyDescriptionSingle( $image->getTitle() , $image->getTags(), $image->getUserNick(), 'Image posted', $sub);
    } else {
        $descriptionMeta = $description;
    }

    // FOLLO - NOFOLLOW
        if($image->isAdult() || $image->isPrivate())
            html::head()->selectMeta()->setContentNofollow(); 
    
    // TITLE
        $mTitle = $image->getTitle();
        $mTitle2 = '';
		
        if(empty($mTitle)){    
            if(empty($colectionsArray))
                $mTitle = 'A nice image';
            else {
                $mTitle = 'Image in \''.$colectionsArray[0]->title.'\'';
				$mTitle2 = $colectionsArray[0]->title;
            }
        }

	// IMAGE ALT
		$imageAlt = '';
		if(!empty( $image->getImageAltText() ))
			$imageAlt = $image->getImageAltText();
		else if(empty( $image->getTitle()))
			$imageAlt = $mTitle2;
		else
			$imageAlt = $image->getTitle();
        
        html::head()
            ->setTitle($mTitle.' - '.brandingName)
            ->selectMeta()
                ->setOGTitle( $mTitle )
                ->setTwitterTitle( $mTitle );
        
    // KEYWORDS
    	if(count($image->getTags()) > 0){
    		foreach($image->getTags() as $val)
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
    		->setOGImageURL( $pictureURL )
    		->setTwitterImageURL( $pictureURL )
    		->setOGImageWidth( $image->getDimensionX() )
    		->setOGImageHeight( $image->getDimensionY() );

	$url = core::getURLObj()->getPathArray();
	B::ID('content-image')
	->_DIV('single-box','single-box')
		->in()
		->_H(1,'',['single-headline','font-size-200','userContent-x'])->in()->setContent($image->getTitle())->outer()
			->_DIV('wrap-2','single-image-wrap')
			->in()		
	   		->_A($image->getLinkHot())->in()
	   			->_IMG( $pictureURLThumbMed , NULL, 'single-image', $imageAlt )->outer()
			->outer()
			->_DIV('single-image-source')
			->_DIV('single-info-box','single-info-box')
				->in()
				->_DIV(NULL,'single-user-meta')
					->in()
					
					->_DIV(NULL,'single-avatar')
						->in()
							->_A('/'.LINK_userPageSingle.'/'.$image->getUserURL())
							->in()
									->_IMG($image->getUserAvatar(),NULL, 'avatar-single-image')
							->outer()
						->outer()
					->_DIV(NULL,'single-uploader')
						->in()
							->_A('/'.LINK_userPageSingle.'/'.$image->getUserURL(), NULL, ['inline-block','a-black'])
								->in()
									->_P(NULL, 'single-img-username', $image->getUserNick())	
								->outer()	
						->outer()	
						->outer()
				->_DIV('single-image-meta')->in()->_DIV('image-description-box',['image-description','userContent-x'])
							->_DIV(NULL,['meta','tags'])
								->in()->_UL('meta-tags', ['single-tagList','userContent-x'])->outer()
				->outer()
				->outer()
			->outer();
	
	
								
	B::ID('wrap')->addElement(snippet::getRelatedItems($image->getRelatedItems()));
	
	if(is_array($image->getSource()))
		B::ID('single-image-source')->in()->setContent('Source: ')->outer()->_A($image->getSource()[0],NULL, NULL, $image->getSource()[1])->in()->setAttr('target','_blank');
	
	if($image->getFileSizeKB() > 1){
		B::ID('single-info-box')
            ->_DIV('single-image-meta-box','info-box xcMeta')->in()
                ->_H(5,null)->in()->setContent('Picture Meta Data')->outer()
                ->_HR(null,'h-hr')
                ->_DIV('container-col-meta-x', 'container-col-meta')->in()
					->_DIV(NULL,'xc-50')->in()
						->_H(6,null)->in()->setContent('<i class="fa fa-info-circle" aria-hidden="true"></i> Resolution:')->outer()
						->_DIV(NULL,'xcMetaPad')->in()->setContent($image->getResolution()['x'].' x '.$image->getResolution()['y'].' pixels')->outer()
						->_H(6,null)->in()->setContent('<i class="fa fa-file-image-o" aria-hidden="true"></i> Filesize:')->outer()
						->_DIV(NULL,'xcMetaPad')->in()->setContent($image->getFileSizeKB().' kByte')->outer()
					->outer()
					->_DIV(NULL,'xc-50')->in()
						->_H(6,null)->in()->setContent('<i class="fa fa-paint-brush" aria-hidden="true"></i> Colour Fingerprint:')->outer()
						->_DIV(NULL,'xcMetaPad')->in()->setContent($image->getColourFingerPrintString());
	}
	
	$o = $image->getCollectionsInPictureData();
    if(count($o)){
        B::ID('single-info-box')
            ->_DIV('single-image-collections','info-box')->in()
                ->_H(5,null)->in()->setContent('Explore featured Collections')
                ->_HR(null,'h-hr')
                ->_DIV('container-col-img', 'container-col-img')->in()
                    ->_DIV('main-col',['main-col','a-col'])->in()
                        ->_A($o[0]->link)->in()
                            ->_DIV(null,'img')->in()
                                ->_IMG(THUMB_HOST.'/'.$o[0]->thumb, null, null, 'collection 1 thumbnail')->outer()
                            ->_DIV(null, 'info')->in()
                                ->_P(null,'title', $o[0]->title)
                                ->_P(null,'descr', $o[0]->descr.' media files');
        if(count($o) > 1){
            B::ID('container-col-img')
                ->_DIV('other-col',['other-col','a-col']);
            for($i = 1; $i < count($o); $i++){
                B::ID('other-col')
                    ->_A($o[$i]->link)->in()
                        ->_DIV(null,'img')->setAttr('data-title',$o[$i]->title)->setAttr('data-descr',$o[$i]->descr)->in()
                            ->_IMG(THUMB_HOST.'/'.$o[$i]->thumb, null, null, 'collection 1 thumbnail');
            }
        }
    }
	
	if(security::getUserObject()->getUserLevel() > userLevelEnum::ADMIN || user::getDBID() == $image->getUserID()){
		$tag = new contentTag();
		html::footer()->addScript(RES_HOST.'/dev/js/adminChangingContent.js');
		$icon = ($image->isPrivate())? 'y' : 'z';
		B::ID('single-box')->addElementAtBegin(new htmlTag('div','grid-main'));
		B::ID('wrap-2')->
			_DIV('adminPanel', ['panel-admin', 'absolute', 'right-10', 'top-10'])->in()
				->_DIV(NULL, ['button-icon', 'button-left','button','inline','admin-delete'])->in()
					->_DIV(NULL,'icon')->in()->setAttr('data-icon','B')->outer()->outer()
				->_DIV(NULL, ['button-string','button','inline','admin-edit'])->in()
					->_DIV(NULL,'string')->in()->setContent('edit post')->outer()->outer()
				->_DIV(NULL, ['button-string','button-right','button','inline','admin-private'])->in()
					->_DIV(NULL,['icon'])->in()->setAttr('data-icon',$icon)->outer()->outer()
				->_DIV('theContentInfoBox',['hidden'])->in()
					->setAttr('data-contentID',$image->getContentID())
					->setAttr('data-tags',$image->getTagsAsString())
					->setAttr('data-adult',(int)$image->isAdult())
					->setAttr('data-private',(int)$image->isPrivate())
					->setAttr('data-title',$image->getTitle())
					->setAttr('data-alltags',$tag->getAllTagsAsString());
	}
	
	foreach($image->getTags() as $val){
		B::ID('meta-tags')->_LI(NULL, 'tag')->in()->_A(HTTP_HOST.'/'.LINK_tagPageSingle.'/'.$val[1],NULL,NULL,$val[0]);
	} 
	
	if(!empty($description))
		B::ID('image-description-box')->setContent($description);
	
?>