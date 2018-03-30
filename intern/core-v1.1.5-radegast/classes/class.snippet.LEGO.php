<?php
class snippetLEGO {
	public static function getOverviewCollectionBox(&$snippet, $imageElement, $val){
		$sThumbs = new htmlTag('div', NULL, ['preview-item']);

		$z = min(count($imageElement->getColLinks()), 1);
		$i = 1;
		
		foreach($imageElement->getColLinks() as $valX){
			$link = THUMB_HOST.'/'.$valX.'.jpg';	
			// $link = PIC_HOST.'/'.$valX.'.jpg';
			//if(!file_exists(uploadTempDirUserUpload.'/thumbnails/'.$valX.'.jpg'))
			//	$link = PIC_HOST.'/'.$valX.'_.jpg';
			
			$sThumbs->_DIV(NULL,'preview-thumb-1'.$z.'-'.$i)->in()->_IMG($link,NULL,'img-responsive');
			$i++;
			if($i > $z)
				break;
		}

		$snippet->_DIV(NULL, 'grid-main-item')
				->in()
					->_A('/'.LINK_collectionPageSingle.'/'.$imageElement->getLinkOfElementSite(),NULL, ['global-link-white'])
					->in()
					->_DIV(NULL,'media-container')->in()
					->_DIV(NULL,'media-badge leftallign')->in()->setContent('collection')->outer()
					->_DIV(NULL,'media-badge rightallign')->in()->setContent( '<i class="fa fa-object-group" aria-hidden="true"></i> '.$imageElement->getItemAmount() )->outer()
					->addElement($sThumbs)
					->outer()
					->outer()
					->_DIV(NULL,'post-top')
						->in()
							->_DIV(NULL,'post-top-left')
								->in()
									->_A('/'.LINK_userPageSingle.'/'.$val->userURL,NULL,['a-black','font-size-80'])
										->in()
										->_IMG($val->avatarHTML,NULL, 'grid-avatar')
										->setContent($val->nick)
										->outer()
								->outer()
								->_DIV(NULL,['post-top-right','font-size-80'])->in()->setContent( $imageElement->getTimeStampHumanReadable())->outer()		
						->outer()					
					#->_DIV(NULL,'post-bottom')->in()->setContent('<h2 class="globalheadline font-size-90 a-black text-bold">'.$imageElement->getTitleShort().'</h2>')
					;
					/*
					->_DIV(NULL, ['img-info-field'])
						->in()
						->_A('/'.$imageElement->getLinkOfDeepSite().'/'.$imageElement->getLinkOfElementSite(),NULL, 'global-link-white');
					*/
		return $snippet;
	}	
	public static function getOverviewImageBox(&$snippet, $imageElement, $val){
		$snippet->_DIV(NULL, 'grid-main-item')
					->in()
					->_A('/'.$imageElement->getLinkOfDeepSite().'/'.$imageElement->getLinkOfElementSite(),NULL, ['global-link-white'])
						->in()
							->_IMG($imageElement->getLinkHot(),NULL,'img-responsive')
					->_DIV(NULL,'post-top')
					->in()
					->_DIV(NULL,'post-top-left')
					->in()
						->_A('/'.LINK_userPageSingle.'/'.$val->userURL,NULL,['a-black','font-size-80'])
							->in()
							->_IMG($val->avatarHTML,NULL, 'grid-avatar')
							->setContent($val->nick)
							->outer()
					->outer()		
					->_DIV(NULL,['post-top-right','font-size-80'])->in()->setContent( $imageElement->getTimeStampHumanReadable())->outer()				
					->outer()
						#->_DIV(NULL,'post-bottom')->in()->setContent('<h2 class="globalheadline font-size-90 a-black text-bold">'.$imageElement->getTitleShort().'</h2>')
;

		return $snippet;
	}
    public static function echoCollectionImagesInfScroll( $obj ){
        $i = 1;    
        foreach($obj->items as $val){
            if(!isset($val->linkContent))
                $link = PIC_HOST.'/'.$val->linkStoredDBEntry.'.'.$val->mime;
            else
                $link = HTTP_HOST.'/'.$val->linkContent;

            if(security::getUserObject()->getUserLevel() > userLevelEnum::ADMIN || user::getDBID() == $obj->userID){
                $t = new htmlTag('div',NULL,['box-control','hidden']);
                $t->in()->setAttr('data-itemID',$val->contentID)
                    ->_DIV(NULL,'opacer')
                    ->_DIV(NULL, 'control-items')->in()
                        ->_DIV(NULL, ['icon-delete','icon'])->in()->setAttr('data-icon','B')->outer()->outer()->outer();
            } else $t = NULL;
            
            $html = new htmlTag('div', NULL, ['grid-main-item']);
            $html->addElement($t)->_A($link, NULL, ['loadInDOM'])->in()->_IMG( 
                THUMB_HOST.'/'.$val->linkStoredDBEntry.'.jpg', 
                NULL, 
                ['single-image'], 
                ($obj->colObj->startImage + $i).'. image in: '.$obj->title 
            );
            
            // debug::checkAndCopyImageToBot( $val->linkStoredDBEntry);
            // debug::checkAndCopyImageToBotByContentID( $val->contentID );
            
            echo $html->broadcastAsString();
            $i++;
        }
        if($obj->collectionHasPaginationLink)
            echo snippet::getPaginationLinkStart( TRUE )->broadcastAsString();
    }
	public static function getOverviewVideoBox(&$snippet, $imageElement, $val){
		$snippet->_DIV(NULL, 'grid-main-item')
					->in()
					->_A('/'.LINK_videoPageSingle.'/'.$imageElement->getLinkOfElementSite(),NULL, ['global-link-white'])
						->in()
						->_DIV(NULL,'media-container')->in()
						->_DIV(NULL,'media-badge leftallign')->in()->setContent('video')->outer()
						->_IMG($imageElement->getLinkHot(),NULL,'img-responsive')
					->outer()		
					->_DIV(NULL,'post-top')
					->in()
					->_DIV(NULL,'post-top-left')
					->in()
						->_A('/'.LINK_userPageSingle.'/'.$val->userURL,NULL,['a-black','font-size-80'])
							->in()
							->_IMG($val->avatarHTML,NULL, 'grid-avatar')
							->setContent($val->nick)
							->outer()
					->outer()		
					->_DIV(NULL,['post-top-right','font-size-80'])->in()->setContent( $imageElement->getTimeStampHumanReadable())->outer()				
					->outer()
						#->_DIV(NULL,'post-bottom')->in()->setContent('<h2 class="globalheadline font-size-90 a-black text-bold">'.$imageElement->getTitleShort().'</h2>')
;

		return $snippet;
	}
	public static function getRelatedContentBox(&$snippet, $relatedContentElement){
		if(isset($relatedContentElement[5])){	
			$linkToSubFolder = LINK_collectionPageSingle;
			$linkToContent = $relatedContentElement[5];
			$linkToContentLink = $relatedContentElement[6];
			$linkToContentTitle = $relatedContentElement[7];
		} else {
			$linkToSubFolder = (isset($relatedContentElement[4]) && $relatedContentElement[4] == 'v')? LINK_videoPageSingle : LINK_imagePageSingle;
			$linkToContent = $relatedContentElement[0];
			$linkToContentLink = $relatedContentElement[2];
			$linkToContentTitle = $relatedContentElement[1];
		}
		$snippet->_A(HTTP_HOST.'/'.$linkToSubFolder.'/'.$linkToContent.'/'.$linkToContentLink,NULL,'relatedContentLink')
			->in()->_DIV(NULL,'relatedItemContainer')->in()
				->_DIV(NULL,'related-thumb-container')
				->in()
				->_IMG(THUMB_HOST.'/'.$relatedContentElement[3].'.jpg',NULL, 'related-thumb')
			->outer()
			/*
			->_DIV(NULL,'related-title-container')
				->in()->_DIV(NULL,'related-title')
					->in()->setContent( $linkToContentTitle )*/;
	} 
}
?>