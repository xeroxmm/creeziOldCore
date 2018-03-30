<?php
class AJAXHandler {
	private $isAjaxRequest = false;	
	private $template = null;
	private $templateType = 0;
	
	private $core = null;
	
	private $status = false;
	private $statusFinal = false;
	private $statusNumber = 200;
	private $statusString = 'OK';
	
	private $version = '1.0.0.0';
	private $thisName = 'AJAX';
	
	private $dataObject = NULL;
	private $dataContentUpdate = NULL;
	private $explorerStuff = NULL;
	
	private $functionalityContentUpdate = FALSE;
	
	function __construct( $t = FALSE ){
		// status look up
		if($t || !isset($_POST[AJAX_DATAOBJECT]) || !isset($_POST[AJAX_OBLIGATORY]) || $_POST[AJAX_OBLIGATORY] != user::getUserIdentHash())
			return;
		// load data jSon
		$this->dataObject = @json_decode($_POST[AJAX_DATAOBJECT]);
        $this->dataObject->userhash = $_POST[AJAX_OBLIGATORY];

		if(!$this->isValidDataObject())
			return;
		
		$this->status = true;
		return;
	}
	public function getStatus(){
		return $this->status;
	}
	public function enableContentUpdateFunction($bool){
		$this->functionalityContentUpdate = (bool)$bool;
		
        return $this;
		return new AJAXHandler(TRUE);
	}
	public function triggerAPIRequests(){
		if(!$this->status)
			return new AJAXHandler();	
		
		api::loadAJAXTemplate();

		if( $this->isRequestContentUpdate() )
			$this->doContentUpdate();
        else if( $this->isRequestPostUpdateLive() )
            $this->doPostUpdateLive();
		else if( $this->isRequestPostUpdate() )
			$this->doPostUpdate();
		else if( $this->isRequestExplorerHandler() )
			$this->doExplorerStuff();
        else if( $this->isCreateEmptyPost() )
            $this->createEmptyPost();
        else if( $this->isChangePostType() )
            $this->changeContentType();
        else if( $this->isFileUpload() )
            $this->uploadFile();
        else
            $this->switchCaser();
        
		api::getTemplateObject()
			->setStatus( TRUE );
		
		return $this;

		return new AJAXHandler(TRUE);
	}
	
    private function switchCaser(){
        // check obligatory    
        if(
            !isset($this->dataObject->do) || 
            !isset($this->dataObject->do->type) || 
            !isset($this->dataObject->do->subType)
        )
            return FALSE;
        
        switch($this->dataObject->do->type){
            case 200:
                require_once SERVER_ROOT.'/'.VH_ROOT.'/intern/'.CORE_VERSION.'/classes/class.content.loader.php';
                $cL = new contentLoader();
                $cL->loadType( $this->dataObject->do->subType );
                if(isset($this->dataObject->do->info->url))
                    $cL->addURL( $this->dataObject->do->info->url);
                $cL->execute();
                break;
            default:
                return FALSE;
                break;
        }
    }
    
	private function isValidDataObject(){
		if(isset($this->dataObject->timestamp) && isset($this->dataObject->userhash))
			return true;
		return false;
	}
    private function isChangePostType(){
        return(
            isset($this->dataObject->do) && 
            isset($this->dataObject->do->type) && 
            isset($this->dataObject->do->subType) && 
            ($this->dataObject->do->type == 0) &&
            ($this->dataObject->do->subType == 2) &&
            isset($this->dataObject->do->info) &&
            isset($this->dataObject->do->info->contentID) 
        );
    }
    private function isCreateEmptyPost(){
        return(
            isset($this->dataObject->do) && 
            isset($this->dataObject->do->type) && 
            isset($this->dataObject->do->subType) && 
            ($this->dataObject->do->type == 0) &&
            ($this->dataObject->do->subType == 0)
        );
    }
    private function isFileUpload(){
        return(
            (isset($_FILES['file']) || (isset($_POST['fileurl']) && strlen($_POST['fileurl']) > 0)) &&
            isset($this->dataObject->do) && 
            isset($this->dataObject->do->type) && 
            isset($this->dataObject->do->subType) &&
            isset($this->dataObject->do->info) &&
            isset($this->dataObject->do->info->contentID) && 
            ($this->dataObject->do->type == 0) &&
            ($this->dataObject->do->subType == 1)
        );
    }
	private function isRequestExplorerHandler(){
		// echo "0.a";	
		return(
			isset($this->dataObject->eID) && 
			(strlen($this->dataObject->eID) > 0) && 
			isset($this->dataObject->type) && 
			($this->dataObject->type >= 100) &&
			($this->dataObject->type <  200)
		);
	}
	private function isRequestPostUpdate(){
		return(
			isset($this->dataObject->cID) && 
			(strlen($this->dataObject->cID) > 0) && 
			isset($this->dataObject->type) && 
			($this->dataObject->type >= 10) &&
			($this->dataObject->type <= 12) &&
			security::isUserAbleToEditContent($this->dataObject->cID)
		);
	}
    private function isRequestPostUpdateLive(){
        return(
            isset($this->dataObject->do) && 
            isset($this->dataObject->do->type) && 
            isset($this->dataObject->do->subType) && 
            ($this->dataObject->do->type == 1) &&
            (is_numeric($this->dataObject->do->subType)) &&
            isset($this->dataObject->do->info) &&
            isset($this->dataObject->do->info->contentID) &&
            security::isUserAbleToEditContent($this->dataObject->do->info->contentID)
        );
    }
	private function doExplorerStuff(){
		$this->explorerStuff = new AJAXExplorerStuff( $this->dataObject );
	}
    private function doPostUpdateLive(){
        $post = new contentManager( $this->dataObject->do->info->contentID );
        
        switch ($this->dataObject->do->subType){
            case 1:
                $post->setTitle( $this->dataObject->do->info->value );
                    break;
            case 2:
                $post->setTags( $this->dataObject->do->info->value );
                break;
            case 3:
                $post->setDescription( $this->dataObject->do->info->value );
                    break;
            case 4:
                $post->setTagsDeletion( $this->dataObject->do->info->value );
                    break; 
            default:
                break;
        }
        
        $post->setAsPartOfOneUpload( TRUE );
        $post->doDBDump();
        $post->apiSend()->contentChangedInfo( 'updated' );
    }
	private function doPostUpdate(){
		if($this->dataObject->type == 10 && isset($this->dataObject->is_private)){
			$this->dataObject->is_private = (int)$this->dataObject->is_private;	
			api::getTemplateObject()->setType(10);
			$r = dbQueries::change()->contentInformationStatusPrivate($this->dataObject->cID, (int)$this->dataObject->is_private);
			if($r){	
				api::getTemplateObject()->addStringNewContentElement($this->dataObject->cID, 10, "private set to ".((int)$this->dataObject->is_private));	
				return;
			}
			api::getTemplateObject()->setStatus(0);
			api::getTemplateObject()->setErrorAsInfo('cant update private status');
			return;
		} else if($this->dataObject->type == 11){
			api::getTemplateObject()->setType(11);
			
			$titleString = '';
			$tagArray = [];
			$descriptionString = '';
			$isPrivateInt = 0;
			$isAdultInt = 0;
			
			if(strlen($this->dataObject->title) > 3)
				$titleString = $this->dataObject->title;
			if(strlen($this->dataObject->tags) > 3){
				$temp = explode(',',trim($this->dataObject->tags,' ,'));
				if(is_array($temp) && count($temp) > 0)
					$tagArray = $temp;
			}
			if(strlen($tString = strip_tags(trim($this->dataObject->description,"\r\n\t "), '<br><br/><i><a><p>')) > 4){
				$descriptionString = $tString;
			}
			if(is_int($this->dataObject->is_private))
				$isPrivateInt = $this->dataObject->is_private;
			if(is_int($this->dataObject->is_adult))
				$isAdultInt = $this->dataObject->is_adult;
			
			$r = dbQueries::change()->contentInformationComplete($this->dataObject->cID, $titleString, $tagArray, $descriptionString, $isPrivateInt, $isAdultInt);
			
			if($r){	
				api::getTemplateObject()->addStringNewContentElement($this->dataObject->cID, 11, "all updated");	
				return;
			}
			api::getTemplateObject()->setStatus(0);
			api::getTemplateObject()->setErrorAsInfo('cant update private status');
			return;
		} else if($this->dataObject->type == 12 && (int)$this->dataObject->removeCollection == 1 && isset($this->dataObject->cID) && isset($this->dataObject->eID)){
			api::getTemplateObject()->setType(12);
			
			// check if content editable
			$r = FALSE;
			if(security::getUserObject()->getUserLevel() >= 10 || dbQueries::get()->userOwnerOfPost(user::getDBIDCloaked(), $this->dataObject->cID))
				$r = dbQueries::delete()->elementFromContent($this->dataObject->cID, $this->dataObject->eID);//($this->dataObject->cID, $titleString, $tagArray, $descriptionString, $isPrivateInt, $isAdultInt);

			if($r){	
				api::getTemplateObject()->addStringNewContentElement($this->dataObject->cID, 11, "all updated");	
				return;
			}
			api::getTemplateObject()->setStatus(0);
			api::getTemplateObject()->setErrorAsInfo('cant update private status');
		}
		api::getTemplateObject()->setStatus(0);
		api::getTemplateObject()->setErrorAsInfo('cant update nothing');
		return FALSE;
	}
	private function isRequestContentUpdate(){
		return (
			user::isValidUploadForm() && 
			isset($this->dataObject->contentInformation) &&
			isset($this->dataObject->contentInformation->isset) &&
			$this->dataObject->contentInformation->isset && 
			strlen($this->dataObject->contentInformation->formID) > 20
		);
	}
	private function doContentUpdate(){
		$this->dataContentUpdate = new AJAXContentUpdate($this->dataObject->contentInformation);
	}
    private function createEmptyPost(){
        // $this->dataContentCreate = new AJAXContentCreate( 0 );
        $post = new contentManager();
        $post->createNewImage();
        $post->apiSend()->contentCreationInfo( 'new' );
    }
    private function uploadFile(){
        if( count(dbQueries::get()->userOwnerOfPost(user::getDBIDCloaked(), $this->dataObject->do->info->contentID)) || security::getUserObject()->getUserLevel() >= userLevelEnum::SUPERADMIN){
            $eID = (isset($this->dataObject->do->info->elementID)) ? $this->dataObject->do->info->elementID : null;     
            $isURL = ( isset($_FILES['file']) ) ? FALSE : $_POST['fileurl'];
            new AJAXContentUpload( $this->dataObject->do->info->contentID , $eID, $isURL);
        }
    }
    private function changeContentType(){
        if(strlen($this->dataObject->do->info->contentID) > 0 && count(dbQueries::get()->userOwnerOfPost(user::getDBIDCloaked(), $this->dataObject->do->info->contentID))){ 
            // create new collection
            $post = new contentManager( $this->dataObject->do->info->contentID );
            $post->swapContentTypeImageToCollection();
            $post->apiSend()->contentCreationInfo( 'swap' );
        } else if($this->dataObject->do->info->contentID == 0){
            $this->dataContentCreate = new AJAXContentCreate( 1 );
        }
        
    }
}
class AJAXContentUpload{
    private $type = null;
    private $id = null;    
    private $image = null;
    private $src = 'raw';
    private $url = '';
    private $elementID = null;
    
    function __construct( string $id , string $elementID = null, $isURL = FALSE){
        $this->id = $id;
        $this->elementID = $elementID;
        $this->image = (object)[
                            'thumbUrl' => null,
                            'imageID' => null 
                       ];
        $isCrawled = FALSE;
        
        if($isURL != FALSE){
            $picS = new crawlImage(urldecode(urldecode($isURL)));
            $_FILES['file'] = $picS->getLastImageAsTempFile();
            
            if($picS->isLastCrawlAnError() !== false)
                api::getTemplateObject()->addError(160,'file upload error -> e: '.$picS->isLastCrawlAnError() );
            
            $picS->deleteLastImage();
            
            $meta = @getimagesize($_FILES['file']['tmp_name']);
            
            if(!isset($meta[0])){    
                api::getTemplateObject()->addError(166,'no meta available -> url: '.urldecode(urldecode( $isURL )));      
                return;
            }
            
            $isCrawled = TRUE;
            $this->src = 'web';
            $this->url = urldecode(urldecode($isURL));
        }
        
        if(
            (!$isCrawled && !$this->checkMeta($_FILES['file']['tmp_name'])) ||
            !$this->getPostType() 
          )
            return;
        
        $this->image = new uploadImage( $isCrawled );
        if(!$this->image->process( FALSE ))
            return FALSE;

        $this->buildPostAddNewImage();
        $this->buildPostUpdateImage();
    }
    private function buildPostUpdateImage(){
        if($this->type != 'i')
            return;
        
        //update thumbnail
            if(!dbQueries::change()->contentInformationThumbUrl( $this->id, $this->image->getThumbStoreURL()) )
                return;
            
        // raise counter of this post
            if(!dbQueries::change()->counterElementsPost( $this->id, 1 ))
                return;
        
        // Add item to matchElementsTable
            if(!dbQueries::add()->elementToMatchItems($this->id, [(object)['type' => 'si', 'contentID' => $this->id,'imageID' => $this->image->getImageID(), 'ID' => $this->image->getLastIDContentLibrary()]]))
                return;

        // Add item to sourceTable
            if(!dbQueries::add()->elementToSrcTable((object)['type' => $this->src, 'userID' => user::getDBIDCloaked(), 'srcID' => $this->image->getImageID(), 'src' => $this->url]))
                return;
    
        // ok, looks like success, add image to ajax respnse
            api::getTemplateObject()->addContentElement( (object)['id' => $this->id, 'type' => 'i', 'info' => null, 'thumb' => $this->image->getImageID()] );
       
        return;
    }
    private function buildPostAddNewImage(){
        if($this->type != 'c')
            return;
        
        if($this->elementID !== NULL)
            $newContentID = $this->elementID;   
        else if(($newContentID = contentCreation::getItemToLibraryListID()) === FALSE)
            return;

        $cItem = new cCreation();
            $cItem->setUserID( user::getDBIDCloaked());
            $cItem->setType('i');
            $cItem->setContentID( $newContentID );
            $cItem->setSrcID( $this->image->getImageID() );
            $cItem->setIsPrivate( 0 );
            $cItem->setIsAdult( 0 );
            $cItem->setThumbLink( $this->image->getThumbStoreURL() );

        if($this->elementID !== NULL && !$lastLibraryID = contentCreation::updateLibraryContentPost( $cItem )){
            //

            return;
        } else if($this->elementID === NULL && !$lastLibraryID = contentCreation::createLibraryContentPost( $cItem )){
            // set newly created contentID to is_deleted == 1
            dbQueries::delete()->libraryListIDEntrie( $cItem->getContentID() );
            return;
        }
        
        // Add item to matchElementsTable
            if(!dbQueries::add()->elementToMatchItems($newContentID, [(object)['type' => 'si', 'contentID' => $newContentID,'imageID' => $this->image->getImageID(), 'ID' => $this->image->getLastIDContentLibrary()]]))
                return;

        // Add item to sourceTable
            if(!dbQueries::add()->elementToSrcTable((object)['type' => $this->src, 'userID' => user::getDBIDCloaked(), 'srcID' => $this->image->getImageID(), 'src' => $this->url]))
                return;
        
        // raise counter of this post
            if(!dbQueries::change()->counterElementsPost( $newContentID, 1 ))
                return;
        
        // add image to collection
            if(!dbQueries::add()->elementToMatchItems($this->id, [(object)['type' => 'i', 'contentID' => $newContentID,'imageID' => $this->image->getImageID(), 'ID' => $this->image->getLastIDContentLibrary()]]))
                return;
        // set as part of collection
            dbQueries::change()->contentInformationIsCollectionUpload( $newContentID, TRUE);
            
        // raise counter of this collection
            if(!dbQueries::change()->counterElementsPost( $this->id, 1 ))
                return;
            
        // ok, looks like success, add image to ajax respnse
            api::getTemplateObject()->addContentElement( (object)['id' => $newContentID, 'type' => 'i', 'info' => null, 'thumb' => $this->image->getImageID()] );
        
        // change thumbUrl if no url set
            $res = dbQueries::get()->thumbURL( $this->id );
            if(!isset($res[0]->thumbnailLink) || empty($res[0]->thumbnailLink))
                dbQueries::change()->contentInformationThumbUrl( $this->id, $this->image->getThumbStoreURL());
        
        
        return;
    }
    function getPostType(){
        $res = dbQueries::get()->typeOfContentIDTitleByContentID( $this->id );
        if(!isset($res[0]->type))
            return FALSE;
        
        $this->type = $res[0]->type;
        
        return TRUE;
    }
    function checkMeta($file){
        $meta = @getimagesize($_FILES['file']['tmp_name']);
        return isset($meta[0]);
    }
}
class AJAXContentCreate {
    private $postType = null;
    private $postID = null;
    private $postUser = null;
    private $postDate = null;

    
    function __construct(int $i){
        $this->postType = $i;    
        $this->postDate = time();
        $this->postUser = user::getUserObject();
    
        if( $this->isProperRequest() ){
            $this->createPost();
        }
    }
    private function isProperRequest(){
        switch($this->postType){
            case 1:
            case 0:
                break;
            default:
                return FALSE;
        }
        return $this->postUser->isLoggedIn();
    }
    private function createPost(){
        switch($this->postType){
            case 0:
                $this->createPostEmpty();
                break;
            case 1:
                $this->createCollectionEmpty();
                break;
        }
        return;
    }
    public function addIDElementToCollection( string $ID ){
        $res = dbQueries::get()->typeOfContentIDTitleByContentID( $ID );
        if(!isset($res[0]->type))
            return;
        
        switch($res[0]->type){
            case 'i':
                dbQueries::add()->elementToMatchItems( $this->contentID , [(object)['type' => 'i', 'contentID' => $ID]]);
                break;
            default:
                return;
        }
    }
    public function setIDElementThumbAsCollectionThumb( string $ID ){
        $res = dbQueries::get()->thumbURL( $ID );
        if(isset($res[0]->thumbnailLink))
            dbQueries::change()->contentInformationThumbUrl( $this->contentID, $res[0]->thumbnailLink);
    }
    private function createCollectionEmpty(){
        if(($newContentID = contentCreation::getItemToLibraryListID()) === FALSE){
            api::getTemplateObject()->addError('cant create contentID');
            return;
        }
        $this->contentID = $newContentID;
        
        $cItem = new cCreation();
            $cItem->setUserID( user::getDBIDCloaked());
            $cItem->setType('c');
            $cItem->setContentID( $newContentID );
            $cItem->setIsPrivate( 0 );
            $cItem->setIsAdult( 0 );
        
        if(!$lastLibraryID = contentCreation::createLibraryContentPost( $cItem )){
            // set newly created contentID to is_deleted == 1
            dbQueries::delete()->libraryListIDEntrie( $cItem->getContentID() );
            api::getTemplateObject()->addError('cant create libraryID');
            
            return;
        }
        
        api::getTemplateObject()->addContentID( $newContentID );
        api::getTemplateObject()->addContentType('c');
        api::getTemplateObject()->addContentInfo( 'new' );
        
        $this->buildingDone();
    }
    private function createPostEmpty(){
        if(($newContentID = contentCreation::getItemToLibraryListID()) === FALSE){
            api::getTemplateObject()->addError('cant create contentID');
            return;
        }
        $cItem = new cCreation();
            $cItem->setUserID( user::getDBIDCloaked());
            $cItem->setType('i');
            $cItem->setContentID( $newContentID );
            $cItem->setIsPrivate( 0 );
            $cItem->setIsAdult( 0 );
        
        if(!$lastLibraryID = contentCreation::createLibraryContentPost( $cItem )){
            // set newly created contentID to is_deleted == 1
            dbQueries::delete()->libraryListIDEntrie( $cItem->getContentID() );
            api::getTemplateObject()->addError('cant create libraryID');
            
            return;
        }
        
        api::getTemplateObject()->addContentID( $newContentID );
        api::getTemplateObject()->addContentType('i');
        api::getTemplateObject()->addContentInfo( 'new' );
        
        $this->buildingDone();
    }
    private function buildingDone(){
        $this->status = TRUE;
        $this->statusClosed = TRUE;
    }
}
class AJAXExplorerStuff {
	private $dataObject = NULL;
	private $requestType = 0;
	private $linkArray = NULL;
		
	function __construct($dataObject){
		$this->dataObject = $dataObject;

		if( $this->isProperRequest() ){
			$this->requestType = (int)$this->dataObject->type;
			$this->switchRequestType();
		}
	}
	
	private function isProperRequest(){
		if(
			isset($this->dataObject->eID) && 
			(strlen($this->dataObject->eID) > 0) && 
			isset($this->dataObject->type) && 
			($this->dataObject->type >= 100) &&
			($this->dataObject->type <  200)
		) return TRUE;
		return FALSE;
	}
	
	private function switchRequestType(){
		switch($this->requestType){
			case 100:
				if( !isset($this->dataObject->hardLink) || 
					!is_string($this->dataObject->hardLink) || 
					!$this->isAnalyzeRequestedURI() || 
					!$this->isUserCorrect()){
					// build API negativ
					break;
				}
				$this->hardlinkSwitch();
				break;
			default:
				break; 
		}
	}
	private function isAnalyzeRequestedURI(){
		$this->linkArray = explode('/',substr($this->dataObject->hardLink,1));
		if(count($this->linkArray) < 1){
			$this->linkArray = NULL;	
			// echo "a.1";
			return FALSE;
		}
		return TRUE;
	}
	private function isUserCorrect(){
		if(count($this->linkArray) < 2 || $this->linkArray[0] != 'usr' || $this->linkArray[1] != user::getURL())
			return FALSE;

		return TRUE;
	}
	private function hardlinkSwitch(){
		if(count($this->linkArray) < 3)
			return FALSE;
		
		switch($this->linkArray[2]){
			case 'data':
				$this->hardlinkSwitchData();
				break;
			default:
				return FALSE;
		}
	}
	private function hardlinkSwitchData(){
		if(count($this->linkArray) < 4)
			return FALSE;

		switch($this->linkArray[3]){
			case 'uploads':
				$this->hardlinkSwitchDataUploads();
				break;
			default:
				return FALSE;
		}
	}
	private function hardlinkSwitchDataUploads(){
		if(count($this->linkArray) < 5)
			return FALSE;

		switch($this->linkArray[4]){
			case 'untagged':
				$this->hardlinkSwitchDataUploadsUntagged();
				break;
			default:
				return FALSE;
		}
	}
	private function hardlinkSwitchDataUploadsUntagged(){
		if(count($this->linkArray) < 6){
			$this->generateUntaggedInfoOverview();
			return TRUE;
		}
		
		switch($this->linkArray[5]){
			case 'images':
				$this->generateUntaggedInfoImages();
				break;
			default:
				return FALSE;
		}
	}
	private function generateUntaggedInfoOverview(){
		$untaggedFolder = (object)['addWindow' => [
									(object)['text'=>'collections', 'type' => 'folder','link' => $this->dataObject->hardLink.'/collections'],
									(object)['text'=>'images', 'type' => 'folder','link' => $this->dataObject->hardLink.'/images'],
									(object)['text'=>'videos', 'type' => 'folder','link' => $this->dataObject->hardLink.'/videos']
								]
							];
		// echo "7";
		api::getTemplateObject()->setStatus(0);
		api::getTemplateObject()->addDataObject($untaggedFolder);
	}
	private function generateUntaggedInfoImages(){
		$untaggedMedia = dbQueries::get()->allTagLinkS();
	}
}
class AJAXContentUpdateLive{
    private $status = true; 
    private $contentIDString = 0;
    
    function __construct( string $cString){
        $this->contentIDString = $cString;
    }
    function updateContentByType(int $type, $value){
        switch ($type){
            case 1:
                if(dbQueries::change()->contentInformationComplete($this->contentIDString, $value, [], '', -1, -1))
                    break;
            case 2:
                if(is_array($value) && dbQueries::change()->contentInformationComplete($this->contentIDString, '', $value, '', -1, -1))
                    break;
            case 3:
                if(dbQueries::change()->contentInformationComplete($this->contentIDString, '', [], $value, -1, -1))
                    break;    
            default:
                $this->setStatus(FALSE);
                break;
        }
    }
    function setStatus( bool $status){
        $this->status = $status;
    }
    function sendAPI(){
        if($this->status)
            api::getTemplateObject()->addContentID( $this->contentIDString );
        
        $this->statusClosed = TRUE; 
    }
}
class AJAXContentUpdate{
	private $status = false;
	private $rawObj = NULL;
	
	private $titleUpdate = [];
	private $tagUpdate = [];
	private $descrUpdate = [];
    private $typeUpdate = [];
	
	private $dbTableIDListEditableFiles = FALSE;
	private $idList = [];
	
	private $hasUpdateImages = FALSE;
	private $hasUpdateVideos = FALSE;
	private $hasUpdateText = FALSE;
	
	private $title = NULL;
	private $tags = NULL;
	private $tagsAll = NULL;
	private $description = NULL;

	private $isFinalCall = FALSE;
	private $isCollectionRequest = FALSE;
	
	function __construct($obj){
		$this->rawObj = $obj;
		$this->loadRequestTyp1();
		$this->loadRequestTyp2();
		$this->loadRequestTyp3();
		$this->loadRequestTyp4();
		$this->loadRequestTyp5();
		
		if($this->isEditableContent()){
			$this->doDatabaseStuff();
		}
	}
	private function resetContentInformation(){
		$this->title = NULL;
		$this->tags = NULL;
		$this->description = NULL;
	}
	private function addContentTypeToIDList($id, $type){
		if(!isset($this->idList[$id]))
			$this->idList[$id] = [];
			
		$this->idList[$id][$type] = TRUE;
	}
	private function loadRequestTyp1(){
		if(!isset($this->rawObj->typ1) || !$this->rawObj->isset || !is_array($this->rawObj->typ1->array) || count($this->rawObj->typ1->array) < 1)
			return;
		foreach($this->rawObj->typ1->array as $val){
			$id = (int)$val->id;
			if($id == 0 || !is_string($val->str))
				continue;
			
			$this->titleUpdate[$id] = ['id' => $id, 't' => $val->str];
			$this->addContentTypeToIDList($id, 'titleUpdate');
		}
	}
	private function loadRequestTyp2(){
		if(!isset($this->rawObj->typ2) || !$this->rawObj->isset || !is_array($this->rawObj->typ2->array) || count($this->rawObj->typ2->array) < 1)
			return;
		foreach($this->rawObj->typ2->array as $val){
			$id = (int)$val->id;
			if($id == 0 || !is_string($val->str))
				continue;
			
			$this->tagUpdate[$id] = ['id' => $id, 't' => $val->str];
			$this->addContentTypeToIDList($id, 'tagUpdate');
		}
	}
	private function loadRequestTyp3(){
		if(!isset($this->rawObj->typ3) || !$this->rawObj->isset || !is_array($this->rawObj->typ3->array) || count($this->rawObj->typ3->array) < 1)
			return;
		foreach($this->rawObj->typ3->array as $val){
			$id = (int)$val->id;
			if($id == 0 || !is_string($val->str))
				continue;
			
			$this->descrUpdate[$id] = ['id' => $id, 't' => $val->str];
			$this->addContentTypeToIDList($id, 'descrUpdate');
		}
	}
	private function loadRequestTyp4(){
		// Close FormularData
		user::closeUploadingFormular();
		$this->isFinalCall = TRUE;
	}
	private $hasCollectionDescription = FALSE;
	private $collectionTitel = '';
	private function loadRequestTyp5(){
		// Close FormularData

		if(isset($this->rawObj->typ5->array->title) && strlen($this->rawObj->typ5->array->title) > 0){
			$this->isCollectionRequest = TRUE;
			$this->collectionTitel = $this->rawObj->typ5->array->title;
			if(isset($this->rawObj->typ5->array->descr) && strlen($this->rawObj->typ5->array->descr) > 0){
				$this->hasCollectionDescription = TRUE;
			}
		}
	}
	private function doDatabaseStuff(){
		// Array sortieren
		if($this->dbTableIDListEditableFiles === FALSE)
			return;
		
		if($this->isFinalCall)
			$this->tagsAll = '';
		
		foreach($this->dbTableIDListEditableFiles as $dbEntry){
			$this->resetContentInformation();	
			foreach($this->idList[$dbEntry->ID] as $methode => $val){
				$str = 'addToDB_'.$methode;
				$this->$str($dbEntry->ID);
			}
			$this->addToDB_complete($dbEntry);
			if($this->isFinalCall){
				$this->doStorageDatabaseImages($dbEntry);
			}
		}
		if($this->isFinalCall && !$this->isCollectionRequest)
			$this->addAllTagsToGlobalDB();
		if($this->isFinalCall && $this->isCollectionRequest){	
			$this->collID = contentCreation::addCollectionDBAndReturnColID($this->rawObj->typ5->array->title,$this->rawObj->typ5->array->descr,count($this->idList));
			contentCreation::addCollectionMatchDBImages($this->dbTableIDListEditableFiles, $this->collID);
			contentCreation::addCollectionMatchDBTags($this->tags, $this->collID);
			contentCreation::deleteOldContentFromDB($this->dbTableIDListEditableFiles);
		}
	}
	
	private function addAllTagsToGlobalDB(){
		$tagArray = security::getTagStringArrayHarmonized($this->tagsAll);
		if(count($tagArray) > 0){
			$databaseName = DBTableNameContentTag;
			
			$sql = new dbObj();
			$sql->setTypeINSERT();
			$sql->setDatabase($databaseName);
				
			foreach($tagArray as $tag){
				$sql->setInsertFieldValueString('label', security::getTagNameHarmonized($tag), $databaseName);
				$sql->setInsertFieldValueString('tagLinkS', security::getNormalizedLinkURL($tag), $databaseName);
				$sql->setOnDuplicateFieldValueAddIntegerToColumn('counter', 1, 'counter');
			}
			db::query($sql);
		}
	}
	private $collID = 0;
		
	private function doStorageDatabaseImages($dbObject){
		// Split up the tag string				
		$tagArray = security::getTagStringArrayHarmonized($this->tags);
		
		// update content to tag databases
		$databaseName = 'match_images_tags';
		
		if(count($tagArray) > 0){
			$sql = new dbObj();
			$sql->setTypeINSERT();
			$sql->setDatabase($databaseName);	
			foreach($tagArray as $tag){
				$tagLink = security::getNormalizedLinkURL($tag);
				$hash = md5($dbObject->imageID.'-'.$tagLink);
					
				$sql->setInsertFieldValueString('imageID', $dbObject->imageID, $databaseName);
				$sql->setInsertFieldValueString('tagLink', $tagLink, $databaseName);
				$sql->setInsertFieldValueString('hash', $hash, $databaseName);
			}
			db::query($sql);
		}
	}
	private function addToDB_complete($dbObject){
		if($dbObject->imageID !== NULL)
			if(uploadImageInformation::changeImageInformationComplete($dbObject->imageID, $this->title, $this->tags, $this->description)){
				api::getTemplateObject()->addStringNewContentElement($dbObject->imageID, 1, $this->title);
				api::getTemplateObject()->addStringNewContentElement($dbObject->imageID, 2, $this->tags);
				api::getTemplateObject()->addStringNewContentElement($dbObject->imageID, 3, $this->description);
				
				if($this->isFinalCall)
					api::getTemplateObject()->addStringNewContentElement($dbObject->imageID, 4, 1);
			}
		else if($dbObject->videoID !== NULL)
			uploadVideoInformation::changeInformationComplete($dbObject->imageID, $this->title, $this->tags, $this->description);
		else if($dbObject->textID !== NULL)
			$z = 1;
	}
	private function addToDB_titleUpdate($id){
		$this->title = $this->titleUpdate[$id]['t'];
	}
	private function addToDB_tagUpdate($id){
		$this->tags = security::getTagStringHarmonized($this->tagUpdate[$id]['t']);
		
		if($this->isFinalCall)
			$this->tagsAll .= $this->tagUpdate[$id]['t'];
	}
	private function addToDB_descrUpdate($id){
		$this->description = $this->descrUpdate[$id]['t'];
	}
	
	private function isEditableContent(){
		$databaseName = DBTableNameContentAll;
		
		$sql = new dbObj();
		$sql->setTypeSELECT();
		$sql->setDatabase($databaseName);
		
		$sql->setSELECTField('ID', $databaseName);
		$sql->setSELECTField('imageID', $databaseName);
		$sql->setSELECTField('videoID', $databaseName);
		$sql->setSELECTField('textID', $databaseName);
		$sql->setSELECTField('userID', $databaseName);

		foreach ($this->idList as $id => $value) {
			$sql->setConditionIntegerEqual('ID', $id, $databaseName, 'OR', 1);
		}
		
		$sql->setConditionIntegerEqual('userID', user::getDBIDCloaked(), $databaseName, 'AND');
		$sql->setLimit(10000);

		$res = db::query($sql);
		//var_dump( $res );
		if(isset($res[0]->ID)){
			$this->dbTableIDListEditableFiles = $res;
			return TRUE;
		}
		
		return FALSE;
	}

}

?>