<?php
class contentManagerLastCheck{
    private $contentID = 0;
    private $userID = 0;
    private $groupID = 0;
    private $status = FALSE;
    
    public function setContentID( string $ID ){
        $this->contentID = $ID;
    }
    public function setGroupID( string $ID ){
        $this->groupID = $ID;
    }
    public function setUserID( string $ID ){
        $this->userID = $ID;
    }
    public function setStatus( bool $status ){
        $this->status = $status;
    }
    public function getContentID(){
        return $this->contentID;
    }
    public function getGroupID(){
        return $this->groupID;
    }
    public function getUserID(){
        return $this->userID;
    }
    public function getStatus(){
        return $this->status;
    }
}
class contentManagerInformation{
    private $info = FALSE;
    function __construct( string $contentID ){
        $this->info = dbQueries::get()->typeOfContentIDTitleByContentID( $contentID );
        if(!isset($this->info[0]->type))
            $this->info = FALSE;
    }
    public function getType(){
        return ($this->info === FALSE) ? FALSE : $this->info[0]->type;
    }
    public function getDateCreatedString(){
        return ($this->info === FALSE) ? FALSE : $this->info[0]->dateCreated;
    }
    public function getTitle(){
        return ($this->info === FALSE) ? FALSE : $this->info[0]->title;
    }
    public function getPrivate(){
        return ($this->info === FALSE) ? TRUE : $this->info[0]->is_private;
    }
    public function getLibraryID(){
        return ($this->info === FALSE) ? 0 : $this->info[0]->ID;
    }
    public function isPartOfCollection(){
        return ($this->info === FALSE) ? 0 : $this->info[0]->is_colUpload;
    }
}
class contentManagerAPI {
    private $parent = NULL;
    function __construct( contentManager &$parent ){
        $this->parent = &$parent;
    }
    public function contentCreationInfo( string $info ){
        api::getTemplateObject()->addContentID( $this->parent->getContentID() );
        api::getTemplateObject()->addContentType( $this->parent->getType() );
        api::getTemplateObject()->addContentInfo( $info );
    }
    public function contentChangedInfo( string $info ){
        api::getTemplateObject()->addContentID( $this->parent->getContentID() );
        api::getTemplateObject()->addContentInfo( $info );
    }
}
class contentDBDump {
    private $dump = FALSE;
    private $delete = FALSE;
    private $sql;
    
    function __construct(){
        $this->dump = new stdClass();
        $this->delete = new stdClass();
    }
    function hasDump(){
        $v = (array) $this->dump;
        $w = (array) $this->delete;
        
        return (empty($v) && empty($w));
    }
    function addTitle( string $title ){
        $this->dump->title = $title;
    }
	function addImageAltText(string $text){
		$this->dump->imageAltText = $text;
	}
    function addDescription( string $description ){
        $this->dump->description = $description;
    }
    function addTags( array $tagArray ){
        if(!isset($this->dump->tags))
            $this->dump->tags = [];
        
        foreach($tagArray as $val){
            if(isset($val->value))
                $this->dump->tags[] = (string)$val->value;
            else
                $this->dump->tags[] = (string)$val;
        }
    }
    function deleteTags( array $tagArray ){
        if(!isset($this->delete->tags))
            $this->delete->tags = [];
        
        foreach($tagArray as $val){
            if(isset($val->value))
                $this->delete->tags[] = $val->value;
            else
                $this->delete->tags[] = $val;
        }
    }
    function addAdult( bool $status ){
        $this->dump->adult = $status;
    }
    function addPrivate( bool $status ){
        $this->dump->private = $status;
    }
    function addUserID( int $userID ){
        $this->dump->userID = $userID;
    }
    function addThumbnailLink( string $thumbLink ){
        $this->dump->thumbLink = $thumbLink;
    }
    function execute( string $contentID ){
        $this->deleteTagsExecute( $contentID ); 
		if(isset($this->dump->imageAltText))
			 dbQueries::change()->contentInformationAltTag( $contentID , $this->dump->imageAltText);
		
        return dbQueries::change()->contentInformationComplete(
            $contentID, 
            (isset($this->dump->title) ? $this->dump->title : ''), 
            (isset($this->dump->tags) ? $this->dump->tags : []), 
            (isset($this->dump->description) ? $this->dump->description : ''), 
            (isset($this->dump->private) ? $this->dump->private : -1), 
            (isset($this->dump->adult) ? $this->dump->adult : -1), 
            (isset($this->dump->thumbLink) ? $this->dump->thumbLink : '')    
        );
    }
    
    private function getThumbnailString(){
        return (isset($this->dump->thumbLink)) ? $this->dump->thumbLink : FALSE;
    }
    private function deleteTagsExecute( string $contentID ){
        if(!isset($this->delete->tags))
            return;    
        foreach($this->delete->tags as $val){
            dbQueries::delete()->tagEntryForContentID($val, $contentID);
        }
    }
}
class contentManager {
    private $api = NULL;    
        
    private $contentID = 0;
    private $libraryID = 0;
    private $userID = 0;
    private $groupID = 0;
    
    private $isOnlyPartOf = false;
    private $isOnlyPartOfOneUpload = false;
    private $isAdult = false;
    private $isPrivate = false;
    
    private $lastCheck = NULL;
    private $info = NULL;
    
    private $type = NULL;
    
    private $onDoubleImageChangeToCollection = TRUE;
    
    private $hasDBDump = FALSE;
    private $hasDBTitleUpdate = FALSE;
    private $DBDump = NULL;
    
    function __construct( string $contentID = NULL ){
        if($contentID !== NULL)
           $this->contentID = $contentID;
        
        $this->userID = user::getDBID();
        
        $this->lastCheck = new contentManagerLastCheck();
        $this->api = new contentManagerAPI( $this );
        $this->DBDump = new contentDBDump();
    }  
    public function apiSend(){
        return $this->api;
    }
    public function loadInformationAboutContent(){
        $this->info = new contentManagerInformation($this->contentID);
    }
    public function createNewImage(){
        if(!$this->isUser())
            return FALSE;
            
        $this->createLibraryEntryID();
        if(!$this->contentID)
            return FALSE;
        
        return $this->createLibraryEntry('i');
    }
    public function createNewCollection(){
        if(!$this->isUser())
            return FALSE;    
        $this->createLibraryEntryID();
        if(!$this->contentID)
            return FALSE;
        
        return $this->createLibraryEntry('c');
    }
    public function swapContentTypeImageToCollection(){
        // check if content info loaded
        if($this->getInfoType() === FALSE)
            return FALSE;
        if($this->getInfoType() == 'i'){
            // mark image as collection upload    
                dbQueries::change()->contentInformationIsCollectionUpload( $this->contentID, TRUE );
            // create collection
            $new = new contentManager();
                $new->createNewCollection();
                $new->addImageContent($this->contentID);
                $new->setThumbnailLikeContentID($this->contentID);
                $new->doDBDump();
                
            $this->contentID = $new->getContentID();
            $this->type = $new->getType(); 
            $this->libraryID = $new->getLibraryID();  

            return TRUE;
        }
        return FALSE;
    }
    public function addImageContent( string $imageID ){
        if(!$this->isUserPermission())
            return FALSE;
        
        if($this->onDoubleImageChangeToCollection){
            $this->swapContentTypeImageToCollection();
        }
        
        // save data
            if(!dbQueries::add()->elementToMatchItems( $this->contentID , [(object)['type' => 'i', 'contentID' => $imageID]]))
                return FALSE;
        // set image as colUplaod
            dbQueries::change()->contentInformationIsCollectionUpload( $imageID, TRUE );
        // increase Media Incrementor
            dbQueries::change()->counterElementsPost($this->contentID, 1);
            
        return ;
    }
    public function setThumbnailLikeContentID( string $contentID ){
        $res = dbQueries::get()->thumbURL( $contentID );
        $this->DBDump->addThumbnailLink($res[0]->thumbnailLink);
    }
    public function isUserPermission(int $userID = NULL){
        if($this->contentID == -1){
			$this->lastCheck->setStatus( FALSE );
			return $this->lastCheck->getStatus();
		}	     
        $userID = ($userID !== NULL) ? $userID : (($this->userID > 0) ? $this->userID : user::getDBID());
        if($this->lastCheck->getContentID() != $this->contentID || $this->lastCheck->getUserID() != $userID){
            $this->lastCheck->setStatus( security::isUserAbleToEditContent($this->contentID, $userID) );
            $this->lastCheck->setContentID( $this->contentID );
            $this->lastCheck->setUserID( $userID );
        }
        return $this->lastCheck->getStatus();
    }
    public function setTitle( string $title ){
        $this->DBDump->addTitle($title);
        
        // check todo datyabase stuff
        $this->hasDBTitleUpdate = TRUE;
    }
    public function setDescription( string $description ){
        $this->DBDump->addDescription($description);
    }
	public function setImageAltText( string $text ){
		$this->hasDBDump = TRUE;	
		$this->DBDump->addImageAltText($text);
	}
    public function setTags( $tagList ){
        $this->hasDBDump = TRUE;
        if(!is_array( $tagList )){
            $tagList = security::getTagStringArrayHarmonized( $tagList );
        }
        $this->DBDump->addTags( $tagList );
    }
    public function setTagsDeletion($tagList){
        $this->hasDBDump = TRUE;
        if(!is_array( $tagList )){
            $tagList = security::getTagStringArrayHarmonized( $tagList );
        }
        $this->DBDump->deleteTags( $tagList );
    }
    public function doDBDump(){
        if($this->DBDump->hasDump() === TRUE)
            return TRUE;
        $s = $this->DBDump->execute( $this->contentID );
        $this->addToSearchTablesIfPossible();
        
        $this->DBDump = new contentDBDump();
		return $s;
    }
    public function setUserID( int $userID ){
        $this->userID = $userID;
        $this->DBDump->addUserID( $userID );
    }
    public function setAsPartOfContent( bool $status ){
        $this->isOnlyPartOf = $status;
    }
    public function setAsPartOfOneUpload( bool $status ){
        $this->isOnlyPartOfOneUpload = $status;
    }
    public function setAsAdult( bool $status ){
        $this->isAdult = $status;
        $this->DBDump->addAdult( $status );
    }
    public function setAsPrivate( bool $status ){
        $this->isPrivate = $status;
        $this->DBDump->addPrivate( $status );
    }
    public function getContentID(){
        return $this->contentID;
    }
    public function getType(){
        return $this->type;
    }
    public function getLibraryID(){
        return $this->libraryID;
    }
    
    #
    #
    #
    
    private function addToSearchTablesIfPossible(){
        $this->getInfoType( TRUE );
        
        // check possibilities @ frontpageAll
            if(
                !$this->isSubImageOfSomething() && 
                $this->hasDBTitleUpdate && 
                !$this->info->getPrivate()
            ){
            // if post is the latest build one @ frontpage 4 ALL
                $res = dbQueries::get()->allElementsFromStartPageALLWithUserIDTypeContentID( $this->userID );
                $timestamp = FALSE;
                if(isset($res[0]->dateCreated))
                    $timestamp = strtotime( $res[0]->dateCreated );
                if(
                    empty($res) ||  // first post in search table ALL
                    $timestamp < strtotime( $this->info->getDateCreatedString() )
                ){
                // add post to table 4 ALL
                    $this->addToSearchTableFrontpageALL();
                }
            } else if(
                $this->isSubImageOfSomething() && 
                $this->hasDBTitleUpdate && 
                !$this->info->getPrivate()
            ){
            // update the database only in relation to this content type
            
            // get post date of underlying
                $res = dbQueries::get()->allElementsFromStartPageALLWithUserIDAndTypeContentID( $this->userID , $this->info->getType());    
                $timestamp = FALSE;
                if(isset($res[0]->dateCreated))
                    $timestamp = strtotime( $res[0]->dateCreated );
                if(
                    empty($res) ||  // first post in search table ALL
                    $timestamp < strtotime( $this->info->getDateCreatedString() )
                ){
                // add post to table 4 ALL
                    $this->addToSearchTableFrontpageALLSpecificType( $this->info->getType() );
                }   
            }
        // check possibilities @ frontpageCollections
            if(
                $this->info->getType() == 'c' && 
                $this->hasDBTitleUpdate && 
                !$this->info->getPrivate()
            ){
            // if post is the latest build one @ frontpage 4 Collections
                $res = dbQueries::get()->allElementsFromStartPageCollectionsWithUserIDTypeContentID( $this->userID );
                $timestamp = FALSE;
                if(isset($res[0]->dateCreated))
                    $timestamp = strtotime( $res[0]->dateCreated );
                if(
                    empty($res) ||  // first post in search table Collections
                    $timestamp < strtotime( $this->info->getDateCreatedString() )
                ){
                // add post to table 4 collections    
                    $this->addToSearchTableFrontpageCollections();    
                }
                dbQueries::add()->libraryElementToSearch_contentCollections( $this->info->getLibraryID() );    
        }
        // check possibilities @ frontpageImages
            if(
                $this->info->getType() == 'i' && 
                $this->hasDBTitleUpdate && 
                !$this->info->getPrivate()
            ){
            // if post is the latest build one @ frontpage 4 Images
                $res = dbQueries::get()->allElementsFromStartPageImagesWithUserIDTypeContentID( $this->userID );
                $timestamp = FALSE;
                if(isset($res[0]->dateCreated))
                    $timestamp = strtotime( $res[0]->dateCreated );
                if(
                    empty($res) ||  // first post in search table Images
                    $timestamp < strtotime( $this->info->getDateCreatedString() )
                ){
                // add post to table 4 Images    
                    $this->addToSearchTableFrontpageImages();    
                }    
        }  
    }
    private function addToSearchTableFrontpageALLSpecificType( string $type ){
        // remove all entries from start page
            dbQueries::delete()->allElementsFromStartPageALLWithUserIDAndType( $this->userID , time() - 300, $type);
        // add this Content to start page
            dbQueries::add()->libraryElementToSearch_contentAllFrontpage( $this->info->getLibraryID() );
    }
    private function addToSearchTableFrontpageALL(){
        // remove all entries from start page
            dbQueries::delete()->allElementsFromStartPageALLWithUserID( $this->userID , time() - 300);
        // add this Content to start page
            dbQueries::add()->libraryElementToSearch_contentAllFrontpage( $this->info->getLibraryID() );
    }
    private function addToSearchTableFrontpageCollections(){
        // remove all entries from start page for collections
            dbQueries::delete()->allElementsFromStartPageCollectionsWithUserID( $this->userID );
        // add this Content to start page for collections    
            dbQueries::add()->libraryElementToSearch_contentCollectionsFrontpage( $this->info->getLibraryID() );
    }
    private function addToSearchTableFrontpageImages(){
        // remove all entries from start page for images
            dbQueries::delete()->allElementsFromStartPageImagesWithUserID( $this->userID );
        // add this Content to start page for images    
            dbQueries::add()->libraryElementToSearch_contentImagesFrontpage( $this->info->getLibraryID() );
    }
    private function isSubImageOfSomething(){
        return (
            //$this->isOnlyPartOfOneUpload &&
            ( (bool)$this->info->isPartOfCollection() )
        );
    }
    private function createLibraryEntryID(){
        if(($this->contentID = contentCreation::getItemToLibraryListID()) === FALSE){
            api::getTemplateObject()->addError('cant create contentID');
            return;
        }
    }
    private function createLibraryEntry( string $type ){
        $cItem = new cCreation();
            $cItem->setUserID( $this->userID );
            $cItem->setType( $type );
            $cItem->setContentID( $this->contentID );
            $cItem->setIsPrivate( 0 );
            $cItem->setIsAdult( 0 );
        
        if(!$this->libraryID = contentCreation::createLibraryContentPost( $cItem )){
            // set newly created contentID to is_deleted == 1
            dbQueries::delete()->libraryListIDEntrie( $cItem->getContentID() );
            api::getTemplateObject()->addError('cant create libraryID');
            
            return FALSE;
        }
        $this->type = $type;
        return TRUE;
    }
    private function getInfoType( bool $forced = FALSE){
        if($this->info === NULL || $forced)
            $this->loadInformationAboutContent();
        return $this->info->getType();
    }
    private function isUser(){
        return(
            $this->userID !== FALSE &&
            is_numeric( $this->userID ) &&
            $this->userID > 0
        );
    }
}

?>
