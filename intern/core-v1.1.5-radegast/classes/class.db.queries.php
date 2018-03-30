<?php
class dbQueries{
    private static $delete = NULL;
    private static $add = NULL;
    private static $change = NULL;
    private static $get = NULL;
    
    public static function delete(){
        if(self::$delete === NULL)
            self::$delete = new dbDelete();
        return self::$delete;
        return new dbDelete();
    }
    public static function add(){
        if(self::$add === NULL)
            self::$add = new dbAdd();
        return self::$add;
        return new dbAdd();
    }
    public static function change(){
        if(self::$change === NULL)
            self::$change = new dbChange();
        return self::$change;
        return new dbChange();
    }
    public static function get(){
        if(self::$get === NULL)
            self::$get = new dbGet();
        return self::$get;
        return new dbGet();
    }
}
class dbGet {
	public function contentInformationByContentID($ID){
		$databaseName = DBTableNameContentAll;
		
		$sql = new dbObj();
		$sql->setTypeSELECT();
		
		$sql->setDatabase($databaseName);
		$sql->setSELECTField('userID', $databaseName);
		$sql->setSELECTField('title', $databaseName);
		$sql->setSELECTField('dateCreated', $databaseName);
		$sql->setSELECTField('contentID', $databaseName);
		$sql->setSELECTField('ID', $databaseName);
		$sql->setSELECTField('thumbnailLink', $databaseName);
		
		$sql->setConditionIntegerEqual('ID', (int)$ID, $databaseName);
		
		return db::query($sql);
	}	
	public function videoInformationByVideoID($ID){
		$databaseName = DBTableNameSrcVideos;
		
		$sql = new dbObj();
		$sql->setTypeSELECT();
		
		$sql->setDatabase($databaseName);
		$sql->setSELECTField('hoster', $databaseName);
		$sql->setSELECTField('specificHosterID', $databaseName);
		$sql->setSELECTField('userID', $databaseName);
		
		$sql->setConditionIntegerEqual('videoID', (int)$ID, $databaseName);
		
		return db::query($sql);
	}
	public function imageInformationByImageIDRange(int $minID, int $maxID){
        $databaseName = DBTableNameSrcImages;
        $databaseName2 = DBTableNameSrcOrigin;

        $sql = new dbObj();
        $sql->setTypeSELECT();

        $sql->setDatabase($databaseName);
        $sql->setDatabase($databaseName2);

        $sql->setSELECTField('linkStored', $databaseName);
        $sql->setSELECTField('src', $databaseName2);
        $sql->setSELECTField('srcID', $databaseName2);

        $sql->setDBonLeftJoinEqualToColumn('imageID', $databaseName,'srcID', $databaseName2);

        $sql->setConditionNotNULL('src',$databaseName2);
        $sql->setConditionIntegerLargerEqual('imageID',$minID,$databaseName,'AND');
        $sql->setConditionIntegerLowerEqual('imageID',$maxID,$databaseName,'AND');

        return db::query($sql);
    }
    public function imageInformationByImageID($ID){
    	$databaseName = DBTableNameSrcImages;
		
		$sql = new dbObj();
		$sql->setTypeSELECT();
		
		$sql->setDatabase($databaseName);
		$sql->setSELECTField('linkStored', $databaseName);
		$sql->setSELECTField('mime', $databaseName);
		
		$sql->setConditionIntegerEqual('imageID', (int)$ID, $databaseName);
		
		return db::query($sql);
    }	
    public function untaggedElements(int $userID, string $type, int $limit){
    	$databaseName = DBTableNameContentAll;
		$databaseName2= DBTableNameContentMatchTagsID;
		$databaseName3= DBTableNameAITagged;
		$databaseName4= DBTableNameMatchElements;
		$databaseName5= DBTableNameContentAll;
		
		$sql = new dbObj();
		$sql->setTypeSELECT();
		
		$sql->setDatabase($databaseName);
		$sql->setDatabase($databaseName2);
		$sql->setDatabase($databaseName3);
		// $sql->setDatabase($databaseName4);
		// $sql->setDatabase($databaseName5);
		
		$sql->setSELECTField('contentID', $databaseName);
		$sql->setSELECTField('thumbnailLink', $databaseName);
		
		$sql->setSELECTField('tagID', $databaseName2);
		$sql->setSELECTField('dateLastCrawl86', $databaseName3);
		
		$sql->setDBonLeftJoinEqualToColumn('contentID', $databaseName2, 'contentID', $databaseName);
		$sql->setDBonLeftJoinEqualToColumn('contentID', $databaseName3, 'contentID', $databaseName);
		
		$sql->setConditionIsNULL('tagID', $databaseName2);
		$sql->setConditionStringEqual('type', $type, $databaseName, 'AND');
		$sql->setConditionIntegerEqual('is_private', 0, $databaseName, 'AND');
		
		$sql->setConditionIsNULL('dateLastCrawl86', $databaseName3, 'AND');
		// $sql->setConditionIntegerEqual('statusLastCrawl86', 0, $databaseName2);
		
		if($userID > 0){
			$sql->setConditionIntegerEqual('userID', $userID, $databaseName, 'AND');
		}
		
		$sql->setOrderByField('ID', $databaseName, 'DESC');
		
		$sql->setLimit($limit);
		/*if(isset($_GET['a']) && $_GET['a'] == "1") {
			$sql->setDBonLeftJoinEqualToColumn('contentIDSub', $databaseName4, 'contentID', $databaseName);
			$sql->setDBonLeftJoinEqualToColumn('contentID', $databaseName, 'contentID', $databaseName4);	
			$sql->setSELECTField('is_private', $databaseName);
			
			echo $sql->getQueryString(); die();
		}*/
		return db::query($sql);
    }	

    public function tagIDByContentIDAndTagLinkS( string $contentIDString, string $tagLinkS ){
        $databaseName = DBTableNameContentMatchTags;
        $databaseName2 = DBTableNameContentTag;     
        
        $sql = new dbObj();
        $sql->setTypeSELECT();
        $sql->setDatabase($databaseName);
        $sql->setDatabase($databaseName2);
        
        $sql->setSELECTField('ID', $databaseName2);
        $sql->setConditionStringEqual('contentID', $contentIDString, $databaseName);
        $sql->setConditionStringEqual('tagLinkS', $tagLinkS, $databaseName, 'AND');
        $sql->setDBonLeftJoinEqualToColumn('tagLinkS', $databaseName2, 'tagLinkS', $databaseName);
        
        return db::query($sql);
    }
    public function allImagesWithThumbnailLink( string $thumbnailLink , int $limit = 100){
        $databaseName = DBTableNameContentAll;
        $sql = new dbObj();
        $sql->setTypeSELECT();
        $sql->setDatabase($databaseName);
        
        $sql->setSELECTField('contentID', $databaseName);
        
        $sql->setConditionStringEqual('thumbnailLink', $thumbnailLink, $databaseName);
        
        $sql->setOrderByField('views', $databaseName, false);
        
        if($limit > 0)
            $sql->setLimit( $limit );
        
        return db::query($sql);
    }
    public function allCollectionsOfImageRAW( string $contentIDSub , string $thumbnailLink ){
        // SELECT all images with thumbnail link    
            $r = dbQueries::get()->allImagesWithThumbnailLink( $thumbnailLink );
            if(empty($r))
                return FALSE;
        
        // iterate through images
        $databaseName = DBTableNameMatchElements;    
        $databaseName2 = DBTableNameContentAll;

        
        $sql = new dbObj();
        $sql->setTypeSELECT();
        $sql->setDatabase($databaseName);
        $sql->setDatabase($databaseName2);
        
        $sql->setSELECTField('contentID', $databaseName);
        $sql->setSELECTField('shortTitle', $databaseName2);
        $sql->setSELECTField('link', $databaseName2);
        $sql->setSELECTField('thumbnailLink', $databaseName2);
        $sql->setSELECTField('mediaIn', $databaseName2);

        foreach($r as $val){
            $sql->setConditionStringEqual('contentIDSub', $val->contentID, $databaseName, 'OR', 2);
        };
        
        $sql->setConditionStringEqual('type', 'c', $databaseName2, 'AND', 1);
        $sql->setConditionIntegerEqual('is_private',0,$databaseName2, 'AND', 3);
        
        $sql->setDBonLeftJoinEqualToColumn('contentID', $databaseName2, 'contentID', $databaseName);
        
        $sql->setOrderByField('mediaIn', $databaseName2, false);
        
        $sql->setLimit( 10 );
        //echo $sql->getQueryString(); exit();
        return db::query($sql);
    } 
    public function allCollectionsOfImage( string $contentIDSub ){
        $databaseName = DBTableNameMatchElements;    
        $databaseName2 = DBTableNameContentAll;
        
        $sql = new dbObj();
        $sql->setTypeSELECT();
        $sql->setDatabase($databaseName);
        $sql->setDatabase($databaseName2);
        
        $sql->setSELECTField('contentID', $databaseName);
        $sql->setSELECTField('shortTitle', $databaseName2);
        $sql->setSELECTField('link', $databaseName2);
        $sql->setSELECTField('thumbnailLink', $databaseName2);
        $sql->setSELECTField('mediaIn', $databaseName2);
        
        $sql->setConditionStringEqual('contentIDSub', $contentIDSub, $databaseName);
        $sql->setConditionStringEqual('type', 'c', $databaseName2, 'AND');
        
        $sql->setDBonLeftJoinEqualToColumn('contentID', $databaseName2, 'contentID', $databaseName);
        
        $sql->setOrderByField('mediaIn', $databaseName2, false);
        
        $sql->setLimit( 10 );

        return db::query($sql);
    }
    public function allImagesOfUser( int $userID ){
        $databaseName = DBTableNameContentAll;    
        $databaseName2 = DBTableNameContentMatchTags;
        $databaseName3 = DBTableNameSrcImages;
        
        $sql = new dbObj();
        $sql->setTypeSELECT();
        $sql->setDatabase($databaseName);
        $sql->setDatabase($databaseName2);
        $sql->setDatabase($databaseName3);
        
        $sql->setSELECTField('title', $databaseName);
        $sql->setSELECTField('contentID', $databaseName);
        $sql->setSELECTField('thumbnailLink', $databaseName);
        $sql->setSELECTField('dateCreated', $databaseName);
        
        $sql->setSELECTField('tagLinkS', $databaseName2);
        
        $sql->setSELECTField('dimensionX', $databaseName3);
        $sql->setSELECTField('dimensionY', $databaseName3);
        $sql->setSELECTField('mime', $databaseName3);
        
        $sql->setDBonLeftJoinEqualToColumn('contentID', $databaseName2, 'contentID', $databaseName);
        $sql->setDBonLeftJoinEqualToColumn('linkStored', $databaseName3, 'thumbnailLink', $databaseName);
        
        $sql->setConditionIntegerEqual('userID', $userID, $databaseName);
        $sql->setConditionNotNULL('thumbnailLink', $databaseName, 'AND');
        $sql->setConditionIsNULL('tagLinkS', $databaseName2, 'AND');
        $sql->setConditionStringEqual('type', 'i', $databaseName, 'AND');

        return db::query($sql);
    }   
    public function allElementsFromStartPageImagesWithUserIDTypeContentID( int $userID ){
        $databaseName = DBTableNameSearchContentFrontpageImages;
        $databaseName2 = DBTableNameContentAll;
        
        $sql = new dbObj();
        $sql->setTypeSELECT();
        $sql->setDatabase($databaseName);
        $sql->setDatabase($databaseName2);
        
        $sql->setSELECTField('ID', $databaseName2);
        $sql->setSELECTField('type', $databaseName2);
        $sql->setSELECTField('contentID', $databaseName2);
        $sql->setSELECTField('userID', $databaseName2);
        $sql->setSELECTField('dateCreated', $databaseName2);
        
        $sql->setDBonLeftJoinEqualToColumn('ID', $databaseName2, 'libraryListID', $databaseName);
        $sql->setConditionIntegerEqual('userID', $userID, $databaseName2);

        return db::query($sql);
    }    
    public function allElementsFromStartPageCollectionsWithUserIDTypeContentID( int $userID ){
        $databaseName = DBTableNameSearchContentFrontpageCollections;
        $databaseName2 = DBTableNameContentAll;
        
        $sql = new dbObj();
        $sql->setTypeSELECT();
        $sql->setDatabase($databaseName);
        $sql->setDatabase($databaseName2);
        
        $sql->setSELECTField('ID', $databaseName2);
        $sql->setSELECTField('type', $databaseName2);
        $sql->setSELECTField('contentID', $databaseName2);
        $sql->setSELECTField('userID', $databaseName2);
        $sql->setSELECTField('dateCreated', $databaseName2);
        
        $sql->setDBonLeftJoinEqualToColumn('ID', $databaseName2, 'libraryListID', $databaseName);
        $sql->setConditionIntegerEqual('userID', $userID, $databaseName2);

        return db::query($sql);
    }
    public function allElementsFromStartPageALLWithUserIDAndTypeContentID(int $userID , string $type ){
        $databaseName = DBTableNameSearchContentFrontpageAll;
        $databaseName2 = DBTableNameContentAll;
        
        $sql = new dbObj();
        $sql->setTypeSELECT();
        $sql->setDatabase($databaseName);
        $sql->setDatabase($databaseName2);
        
        $sql->setSELECTField('ID', $databaseName2);
        $sql->setSELECTField('type', $databaseName2);
        $sql->setSELECTField('contentID', $databaseName2);
        $sql->setSELECTField('userID', $databaseName2);
        $sql->setSELECTField('dateCreated', $databaseName2);
        
        $sql->setDBonLeftJoinEqualToColumn('ID', $databaseName2, 'libraryListID', $databaseName);
        $sql->setConditionIntegerEqual('userID', $userID, $databaseName2);
        $sql->setConditionDateTimeHigher('dateCreated', $time, $databaseName, 'AND');
        $sql->setConditionStringEqual('type', $type, $databaseName2, 'AND');
        
        $sql->setOrderByField('dateCreated', $databaseName, 'DESC');

        return db::query($sql);
    }    
    public function allElementsFromStartPageALLWithUserIDTypeContentID( int $userID , int $time = 0, $type = NULL){
        $databaseName = DBTableNameSearchContentFrontpageAll;
        $databaseName2 = DBTableNameContentAll;
        
        $sql = new dbObj();
        $sql->setTypeSELECT();
        $sql->setDatabase($databaseName);
        $sql->setDatabase($databaseName2);
        
        $sql->setSELECTField('ID', $databaseName2);
        $sql->setSELECTField('type', $databaseName2);
        $sql->setSELECTField('contentID', $databaseName2);
        $sql->setSELECTField('userID', $databaseName2);
        $sql->setSELECTField('dateCreated', $databaseName2);
        
        $sql->setDBonLeftJoinEqualToColumn('ID', $databaseName2, 'libraryListID', $databaseName);
        $sql->setConditionIntegerEqual('userID', $userID, $databaseName2);
        $sql->setConditionDateTimeHigher('dateCreated', $time, $databaseName, 'AND');
        
        if($type !== NULL)
            $sql->setConditionStringEqual('type', $type, $databaseName2, 'AND');
        
        $sql->setOrderByField('dateCreated', $databaseName, 'DESC');

        return db::query($sql);
    }
    public function thumbURL( string $contentIDString ){
        $databaseName = DBTableNameContentAll;
        $sql = new dbObj();
        $sql->setTypeSELECT();
        $sql->setDatabase($databaseName);
        
        $sql->setSELECTField('ID', $databaseName);
        $sql->setSELECTField('type', $databaseName);
        $sql->setSELECTField('thumbnailLink', $databaseName);
        $sql->setConditionStringEqual('contentID', $contentIDString, $databaseName);
        
        return db::query($sql);
    }    
    public function allTagLinkSByContentID( string $contentIDString ){
        $databaseName = DBTableNameContentTag;
        $sql = new dbObj();
        $sql->setTypeSELECT();
        
        $sql->setDatabase($databaseName);
        $sql->setSELECTField('ID', $databaseName);
        
        $sql->setConditionStringEqual('contentID', $contentIDString, $databaseName);
        $sql->setOrderByField('tagLinkS', $databaseName, 'ASC');
        
        return db::query($sql);
    }   
    public function typeOfContentIDTitleByContentID( string $contentIDString )  {
        $databaseName = DBTableNameContentAll;
        $sql = new dbObj();
        $sql->setTypeSELECT();
        $sql->setDatabase($databaseName);
        
        $sql->setSELECTField('ID', $databaseName);
        $sql->setSELECTField('type', $databaseName);
        $sql->setSELECTField('title', $databaseName);
        $sql->setSELECTField('dateCreated', $databaseName);
        $sql->setSELECTField('is_private', $databaseName);
        $sql->setSELECTField('is_colUpload', $databaseName);
        
        $sql->setConditionStringEqual('contentID', $contentIDString, $databaseName);
        
        return db::query($sql);
    }
    public function userOwnerOfPost(int $userID, string $contentID){
        $databaseName = DBTableNameContentAll;
        $sql = new dbObj();
        $sql->setTypeSELECT();
        $sql->setDatabase($databaseName);
        
        $sql->setSELECTField('ID', $databaseName);
        $sql->setConditionIntegerEqual('userID', $userID, $databaseName);
        $sql->setConditionStringEqual('contentID', $contentID, $databaseName, 'AND');
        
        return db::query($sql);
    }
    public function allTagLinkS(){
        $databaseName = DBTableNameContentTag;
        $sql = new dbObj();
        $sql->setTypeSELECT();
        
        $sql->setDatabase($databaseName);
        $sql->setSELECTField('tagLinkS', $databaseName);
        
        $sql->setOrderByField('tagLinkS', $databaseName, 'ASC');
        
        return db::query($sql);
    }
    public function tagIDByTagLinkS( string $tagLinkS ){
        $databaseName = DBTableNameContentTag;
        $sql = new dbObj();
        $sql->setTypeSELECT();
        
        $sql->setDatabase($databaseName);
        $sql->setSELECTField('tagLinkS', $databaseName);
        $sql->setSELECTField('ID', $databaseName);
        $sql->setSELECTField('label', $databaseName);
        $sql->setSELECTField('counter', $databaseName);
        
        $sql->setConditionStringEqual('tagLinkS', $tagLinkS, $databaseName);
        
        return db::query($sql);
    }   
    public function oneImageInformationByContentID( $contentIDString ){
        $databaseName = DBTableNameMatchElements;
        $databaseName3 = DBTableNameSrcImages;
        
        $sql = new dbObj();
        $sql->setTypeSELECT();
        
        $sql->setDatabase($databaseName);
        $sql->setDatabase($databaseName3);
        
        $sql->setSELECTField('ID', $databaseName);
        $sql->setSELECTField('linkStored', $databaseName3);
        $sql->setSELECTField('mime', $databaseName3);
        $sql->setSELECTField('imageID', $databaseName3);
        
        $sql->setDBonLeftJoinEqualToColumn('imageID', $databaseName3, 'imageID', $databaseName);
        
        $sql->setConditionStringEqual('contentID', $contentIDString, $databaseName);
        $sql->setConditionStringEqual('type', 'si', $databaseName, 'AND');
        
        return db::query($sql);
    }   
    public function allItemsOfCollection($colContentIDString){
        $databaseName = DBTableNameMatchElements;
            
        $sql = new dbObj();
        $sql->setTypeSELECT();
        
        $sql->setDatabase($databaseName);
        
        $sql->setSELECTField('ID', $databaseName);
        $sql->setSELECTField('contentIDSub', $databaseName);
        $sql->setConditionStringEqual('contentID', $colContentIDString, $databaseName);
        $sql->setOrderByField('position', $databaseName, 'ASC');
        $sql->setConditionStringEqual('type', 'i', $databaseName, 'AND');
        
        return db::query($sql); //echo $sql->getQueryString();
    }   
    public function allPublicCollections(){
        $databaseName = DBTableNameContentAll;
        $databaseName2= DBTableNameContentImages;
        //$databaseName2= 'content_collections';
        
        $sql = new dbObj();
        $sql->setTypeSELECT();
        
        $sql->setDatabase($databaseName);

        $sql->setSELECTField('contentID', $databaseName);
        $sql->setSELECTField('link', $databaseName);
    
        $sql->setConditionIntegerEqual('is_adult', 0, $databaseName);
        $sql->setConditionIntegerEqual('is_private', 0, $databaseName, 'AND');
        $sql->setConditionStringEqual('type', 'c', $databaseName, 'AND');
        
        return db::query($sql);
    }
	public function allPublicDescriptions(){
		$databaseName = DBTableNameMatchElements;
        $databaseName2= DBTableNameSrcText;
		
		$sql = new dbObj();
        $sql->setTypeSELECT();
        
        $sql->setDatabase($databaseName);
        $sql->setDatabase($databaseName2);
		
		$sql->setSELECTField('contentID', $databaseName);
		$sql->setSELECTField('text', $databaseName2);
		
		$sql->setDBonLeftJoinEqualToColumn('textID', $databaseName2, 'textID', $databaseName);
		
		$sql->setConditionStringEqual('type', 'td', $databaseName2);
		$sql->setConditionNotNull('textID', $databaseName, 'AND');
		
		return db::query($sql);
	}
	public function allPublicDescriptionsAndTexts(){
		$databaseName = DBTableNameMatchElements;
        $databaseName2= DBTableNameSrcText;
		
		$sql = new dbObj();
        $sql->setTypeSELECT();
        
        $sql->setDatabase($databaseName);
        $sql->setDatabase($databaseName2);
		
		$sql->setSELECTField('contentID', $databaseName);
		$sql->setSELECTField('text', $databaseName2);
		
		$sql->setDBonLeftJoinEqualToColumn('textID', $databaseName2, 'textID', $databaseName);
		
		$sql->setConditionStringEqual('type', 'td', $databaseName2, 1);
		$sql->setConditionStringEqual('type', 'ta', $databaseName2,'OR', 1);
		$sql->setConditionNotNull('textID', $databaseName, 'AND', 2);
		
		return db::query($sql);
	}
    public function allPublicPosts($int = 0){
        $int = (int)$int;	
        $databaseName = DBTableNameContentAll;
        $databaseName2= DBTableNameSrcImages;
        
        $sql = new dbObj();
        $sql->setTypeSELECT();
        
        $sql->setDatabase($databaseName);
        $sql->setDatabase($databaseName2);

        $sql->setSELECTField('contentID', $databaseName);
        $sql->setSELECTField('link', $databaseName);
        $sql->setSELECTField('thumbnailLink', $databaseName);
        $sql->setSELECTField('mime', $databaseName2);
		$sql->setSELECTField('title', $databaseName);
    
        $sql->setConditionIntegerEqual('is_adult', 0, $databaseName);
        $sql->setConditionIntegerEqual('is_private', 0, $databaseName, 'AND');
        $sql->setConditionStringEqual('type', 'i', $databaseName, 'AND');
        $sql->setConditionNotNULL('thumbnailLink', $databaseName, 'AND');
        
        $sql->setDBonLeftJoinEqualToColumn('linkStored', $databaseName2, 'thumbnailLink', $databaseName);
        $sql->setOrderByField('dateCreated', $databaseName, TRUE);        
        
		$k = 45000;
		if($int > 0){
			$sql->setLimit($k,($int-1)*$k);
		}
		
        return db::query($sql);
    }   
    public function loginUserAPILoginCredentials($user = null, $password = null, $cloaked = FALSE){
        $databaseName = DBTableNameUser;
        $databaseName2= DBTableNameUserAPI; 
        
        $sql = new dbObj();
        $sql->setTypeSELECT();
        $sql->setDatabase($databaseName);
        $sql->setDatabase($databaseName2);
        
        $sql->setSELECTField('ID', $databaseName);
        //$sql->setSELECTField('email', $databaseName);
        $sql->setSELECTField('nick', $databaseName);
        $sql->setSELECTField('userURL', $databaseName);
        $sql->setSELECTField('userLevel', $databaseName);
        $sql->setSELECTField('apiUser', $databaseName2);
        
        $sql->setConditionStringEqual('apiKey', $password, $databaseName2);
        $sql->setConditionStringEqual('apiUser', $user, $databaseName2, 'AND');
        
        $sql->setDBonLeftJoinEqualToColumn('userID', $databaseName2, 'ID', $databaseName);
        
        return db::query($sql); 
    }
    public function userIDfromUserNick( $NICKNAME ){
        $databaseName = DBTableNameUser;    
            
        $sql = new dbObj();
        $sql->setTypeSELECT();
        $sql->setDatabase($databaseName);
        
        $sql->setSELECTField('ID', $databaseName);
        $sql->setConditionStringEqual('nick', $NICKNAME, $databaseName);
        
        return db::query($sql);
    }
}
class dbDelete {
    public function allElementsFromStartPageImagesWithUserID( int $userID ){
        $res = dbQueries::get()->allElementsFromStartPageImagesWithUserIDTypeContentID( $userID );
        if(!is_array( $res ))
            return;
        
        $databaseName = DBTableNameSearchContentFrontpageImages;
        
        foreach($res as $val){
            if($val->userID == $userID){    
                $sql = new dbObj();
                $sql->setTypeDELETE();
                $sql->setDatabase($databaseName);
                
                $sql->setConditionStringEqual('libraryListID', $val->ID, $databaseName);
                
                db::query($sql);
            }
        }
    }    
    public function allElementsFromStartPageCollectionsWithUserID( int $userID ){
        $res = dbQueries::get()->allElementsFromStartPageCollectionsWithUserIDTypeContentID( $userID );
   
        $databaseName = DBTableNameSearchContentFrontpageCollections;
        
        foreach($res as $val){
            if($val->userID == $userID){    
                $sql = new dbObj();
                $sql->setTypeDELETE();
                $sql->setDatabase($databaseName);
                
                $sql->setConditionStringEqual('libraryListID', $val->ID, $databaseName);
                
                db::query($sql);
            }
        }
    }
    public function allElementsFromStartPageALLWithUserIDAndType( int $userID , int $allowedTime = 0, string $type) {
        $res = dbQueries::get()->allElementsFromStartPageALLWithUserIDTypeContentID( $userID , $allowedTime, $type);
   
        $databaseName = DBTableNameSearchContentFrontpageAll;
        
        foreach($res as $val){
            if($val->userID == $userID){    
                $sql = new dbObj();
                $sql->setTypeDELETE();
                $sql->setDatabase($databaseName);
                
                $sql->setConditionStringEqual('libraryListID', $val->ID, $databaseName);
                                
                db::query($sql);
            }
        }
    }    
    public function allElementsFromStartPageALLWithUserID( int $userID , int $allowedTime = 0){
        $res = dbQueries::get()->allElementsFromStartPageALLWithUserIDTypeContentID( $userID , $allowedTime);
   
        $databaseName = DBTableNameSearchContentFrontpageAll;
        
        foreach($res as $val){
            if($val->userID == $userID){    
                $sql = new dbObj();
                $sql->setTypeDELETE();
                $sql->setDatabase($databaseName);
                
                $sql->setConditionStringEqual('libraryListID', $val->ID, $databaseName);
                                
                db::query($sql);
            }
        }
    }
    public function elementFromSearchTableImages($libraryListInt){
        $databaseName = DBTableNameSearchContentImages;
            
        $sql = new dbObj();
        $sql->setTypeDELETE();
        $sql->setDatabase($databaseName);
        
        $sql->setConditionStringEqual('libraryListID', $libraryListInt, $databaseName);
        
        db::query($sql);
    }
    public function tagEntryForContentByID( int $ID , string $contentIDString){
        // Delete all TagLinkIDs
            $databaseName = DBTableNameContentMatchTagsID;
            
            $sql = new dbObj();
            $sql->setTypeDELETE();
            $sql->setDatabase($databaseName);
            
            $sql->setConditionStringEqual('contentID', $contentIDString, $databaseName);
            $sql->setConditionIntegerEqual('tagID', $ID, $databaseName, 'AND');
            
            db::query($sql);
    }
    public function tagEntryForContentByTagLinkS( string $tagLinkS, string $contentIDString){
        // Delete all matching to TagLinkS
            $databaseName = DBTableNameContentMatchTags;
            
            $sql = new dbObj();
            $sql->setTypeDELETE();
            $sql->setDatabase($databaseName);
            
            $sql->setConditionStringEqual('contentID', $contentIDString, $databaseName);
            $sql->setConditionStringEqual('tagLinkS', $tagLinkS, $databaseName, 'AND');
            
            db::query($sql);
    }
    public function tagEntryForContentID( string $tagLinkS, string $contentIDString ){
        // check if tag is set for contentID
            $r = dbQueries::get()->tagIDByContentIDAndTagLinkS( $contentIDString,$tagLinkS );
            
            if(!isset($r[0]->ID))
                return TRUE;
            
            dbQueries::delete()->tagEntryForContentByID($r[0]->ID, $contentIDString);
            dbQueries::delete()->tagEntryForContentByTagLinkS($tagLinkS, $contentIDString);
           
        // update counter
            dbQueries::change()->counterTagsDatabase($tagLinkS, -1);
    }
    public function allTagEntriesForContentID($contentIDString){
        // Select all tags that are in DB
            $databaseName = DBTableNameContentMatchTags;
                
            $sql = new dbObj();
            $sql->setTypeSELECT();
            $sql->setDatabase($databaseName);
            
            $sql->setSELECTField('tagLinkS', $databaseName);
            $sql->setConditionStringEqual('contentID', $contentIDString, $databaseName);
            
            $r = db::query($sql);
            
            // update counter
            if(isset($r[0])){
                $databaseName = DBTableNameContentTag;  
                $sql = new dbObj();
                $sql->setTypeINSERT();
                $sql->setDatabase($databaseName);   
                foreach($r as $tag){
                    $sql->setInsertFieldValueString('tagLinkS', $tag->tagLinkS, $databaseName);
                    $sql->setOnDuplicateFieldValueAddIntegerToColumn('counter', -1, 'counter');
                }
                db::query($sql);
            }
        // Delete all matching to TagLinkS
            $databaseName = DBTableNameContentMatchTags;
            
            $sql = new dbObj();
            $sql->setTypeDELETE();
            $sql->setDatabase($databaseName);
            
            $sql->setConditionStringEqual('contentID', $contentIDString, $databaseName);
            
            db::query($sql);
            
        // Delete all TagLinkIDs
            $databaseName = DBTableNameContentMatchTagsID;
            
            $sql = new dbObj();
            $sql->setTypeDELETE();
            $sql->setDatabase($databaseName);
            
            $sql->setConditionStringEqual('contentID', $contentIDString, $databaseName);
            
            db::query($sql);
    }   
    public function libraryListIDEntrie($ID){
        $databaseName = DBTableNameContentAllID;
        $sql = new dbObj();
        $sql->setTypeUPDATE();
        
        $sql->setDatabase($databaseName);
        //$sql->setUpdatedFieldValueInteger('is_deleted', 1, $databaseName);
        //$sql->setUpdatedFieldValueDATE_ADD_TO_NOW('dateDeleted', 0, $databaseName);
        
        $sql->setConditionIntegerEqual('contentID', $ID, $databaseName);
        $sql->setUpdatedFieldValueInteger('is_deleted', 1, $databaseName);
        $sql->setUpdatedFieldValueDATE_ADD_TO_NOW('dateDeleted', 0, $databaseName);
        
        return(db::query($sql));
    }
    public function srcListEntrieByID( $ID ){
        $databaseName = DBTableNameSrcListID;
        $sql = new dbObj();
        $sql->setTypeUPDATE();
        
        $sql->setDatabase($databaseName);
        
        $sql->setConditionIntegerEqual('ID', $ID, $databaseName);
        $sql->setUpdatedFieldValueInteger('is_deleted', 1, $databaseName);
        $sql->setUpdatedFieldValueDATE_ADD_TO_NOW('dateDeleted', 0, $databaseName);
        
        return db::query($sql);
    }
    public function elementFromContent($cID, $elementID){
        $databaseName = DBTableNameMatchElements;
        
        $sql = new dbObj();
        $sql->setTypeDELETE();
        $sql->setDatabase($databaseName);
        
        $sql->setConditionStringEqual('contentID', $cID, $databaseName);
        $sql->setConditionStringEqual('contentIDSub', $elementID, $databaseName, 'AND');
        
        // check if thumbnail is deleted pic
        if(db::query($sql)){
            $databaseName = DBTableNameContentAll;  
            $sql = new dbObj();
            $sql->setTypeSELECT();
            $sql->setDatabase($databaseName);
            
            $sql->setSELECTField('thumbnailLink', $databaseName);
            $sql->setSELECTField('contentID', $databaseName);
            $sql->setConditionStringEqual('contentID', $cID, $databaseName);
            $sql->setConditionStringEqual('contentID', $elementID, $databaseName, 'OR');
            
            $res = db::query($sql);
            
            if(isset($res[0]->contentID) ){
                if(isset($res[1])){
                    if($res[0]->thumbnailLink == $res[1]->thumbnailLink){
                        // update thumbnail
                        $databaseName = DBTableNameMatchElements;
                        $databaseName2 = DBTableNameContentAll; 
                        $sql = new dbObj();
                        $sql->setDatabase($databaseName);
                        $sql->setDatabase($databaseName2);
                        
                        $sql->setSELECTField('thumbnailLink', $databaseName2);
                        $sql->setConditionStringEqual('contentID', $cID, $databaseName);
                        $sql->setConditionNotNULL('contentIDSub', $databaseName, 'AND');
                        
                        $sql->setDBonLeftJoinEqualToColumn('contentID', $databaseName2, 'contentIDSub', $databaseName);
                        $sql->setOrderByField('position', $databaseName);
                        $sql->setLimit(1);
                        
                        $rty = db::query($sql);
                        
                        if(isset($rty[0]->thumbnailLink)){
                            $sql = new dbObj();
                            $sql->setDatabase($databaseName2);
                            $sql->setTypeUPDATE();
                            $sql->setUpdatedFieldValueString('thumbnailLink', $rty[0]->thumbnailLink,$databaseName2);
                            $sql->setConditionStringEqual('contentID', $cID, $databaseName2);
                            
                            return db::query($sql);
                        } else {
                            // set thumbnaillink 0
                            $sql = new dbObj();
                            $sql->setDatabase($databaseName2);
                            $sql->setTypeUPDATE();
                            $sql->setUpdatedFieldValueNULL('thumbnailLink', $databaseName2);
                            $sql->setConditionStringEqual('contentID', $cID, $databaseName2);
                            
                            return db::query($sql);
                        }
                    }
                }
                return TRUE;
            }
        };
        //echo $sql->getQueryString();  
        return FALSE;
    }
}
class dbAdd {
	public static function newVideoToSourceTableByObject(crawlYoutubeVideo $cObj){			
		$databaseName = DBTableNameContentAll;	
		$sql = new dbObj();
		$sql->setTypeINSERT();
		$sql->setDatabase($databaseName);
		
		$sql->setInsertFieldValueNULL('ID', $databaseName);
		$sql->setInsertFieldValueString('videoID', $cObj->videoID, $databaseName);
		$sql->setInsertFieldValueString('type', LINK_videoPageSingle, $databaseName);
		
		$sql->setInsertFieldValueString('title', Encoding::toUTF8($cObj->officialName), $databaseName);
		$sql->setInsertFieldValueString('link', $cObj->linkURL, $databaseName);
		$sql->setInsertFieldValueString('shortTitle', uploadSanitizer::getStringShortend(Encoding::toUTF8($cObj->officialName)), $databaseName);
		$sql->setInsertFieldValueInteger('is_private', 0, $databaseName);
		$sql->setInsertFieldValueInteger('contentID', $cObj->contentID, $databaseName);
		$sql->setInsertFieldValueString('thumbnailLink', $cObj->thumbnailLink, $databaseName);
		
		$sql->setInsertFieldValueDATE_ADD_TO_NOW('dateCreated', 0, $databaseName);
		
		$sql->setInsertFieldValueInteger('userID', $cObj->userID, $databaseName);

		return db::query( $sql );
	}
	public function libraryElementVideo(cCreation $ob){
		$databaseName = DBTableNameSrcVideos;
			
		$sql = new dbObj();
		$sql->setTypeINSERT();
		
		// obligatory
			$sql->setDatabase($databaseName);
			$sql->setInsertFieldValueInteger('videoID', $ob->getSrcID(), $databaseName);
			$sql->setInsertFieldValueDATE_ADD_TO_NOW('dateCreated', 0, $databaseName);
			$sql->setInsertFieldValueString('hash', $ob->getHoster().'-'.$ob->getHosterID(), $databaseName);
			$sql->setInsertFieldValueString('url', $ob->getLink(), $databaseName);
			$sql->setInsertFieldValueString('hoster', $ob->getHoster(), $databaseName);
			$sql->setInsertFieldValueInteger('userID', $ob->getUserID(), $databaseName);
			$sql->setInsertFieldValueString('specificHosterID', $ob->getHosterID(), $databaseName);
		
		// checkList
		
		return db::query($sql);
	}
	public function imageMetaStats(int $imageID, int $resX, int $resY, int $size){
		$databaseName = DBTableNameMetaImageStats;
		
		$sql = new dbObj();
		$sql->setTypeINSERT();
		
		$sql->setDatabase($databaseName);
			
		$sql->setInsertFieldValueInteger('imageID', $imageID, $databaseName);
		$sql->setInsertFieldValueInteger('resX', $resX, $databaseName);
		$sql->setInsertFieldValueInteger('resY', $resY, $databaseName);
		$sql->setInsertFieldValueInteger('size', $size, $databaseName);
		
		return db::query($sql);;
	}	
	public function imageMetaFingerPrint(int $imageID, int $sum, string $fingerPrint, string $colourPrint){
		$databaseName = DBTableNameMetaImageFingerprints;
		
		$sql = new dbObj();
		$sql->setTypeINSERT();
		
		$sql->setDatabase($databaseName);
			
		$sql->setInsertFieldValueInteger('imageID', $imageID, $databaseName);
		$sql->setInsertFieldValueInteger('sumDigit', $sum, $databaseName);
		$sql->setInsertFieldValueString('fingerprintColoursHEX', $fingerPrint, $databaseName);
		$sql->setInsertFieldValueString('colourDistributionHEX', $colourPrint, $databaseName);
		
		return db::query($sql);;
	}	
	public function errorAPIImageCrawl(int $userID, int $errorNr, string $text, string $url ){
		$databaseName = DBTableNameErrorAPICrawlImage;
		
		$sql = new dbObj();
		$sql->setTypeINSERT();
		
		$sql->setDatabase($databaseName);
		
		$sql->setInsertFieldValueDATE_ADD_TO_NOW('dateCreated', 0, $databaseName);		
		$sql->setInsertFieldValueString('crawlErrorText', $text, $databaseName);
		$sql->setInsertFieldValueString('crawlURL', $url, $databaseName);
		$sql->setInsertFieldValueInteger('crawlErrorNumber', $errorNr, $databaseName);
		$sql->setInsertFieldValueInteger('userID', $userID, $databaseName);
		
		return db::query($sql);;
	}
	
    public function aiTagCrawlDate( string $contentID ){
		$databaseName = DBTableNameAITagged;
		
		$sql = new dbObj();
		$sql->setTypeINSERT();
		
		$sql->setDatabase($databaseName);
		
		$sql->setInsertFieldValueDATE_ADD_TO_NOW('dateLastCrawl86', 0, $databaseName);		
		$sql->setInsertFieldValueString('contentID', $contentID, $databaseName);
		
		db::query($sql);
		
		return db::getLastID();
	}
	public function elementToSearchTableVideos($libraryListInt){
		$databaseName = DBTableNameSearchContentVideos;
        $sql = new dbObj();
        $sql->setDatabase($databaseName);
        $sql->setTypeINSERT();
        $sql->setInsertFieldValueInteger('libraryListID', $libraryListInt, $databaseName);
        
        if(!db::query($sql)){
            echo $sql->getQueryString();     
            return FALSE;
        }

        return db::getLastID();
	}	
    public function elementToSearchTableImages($libraryListInt){
        $databaseName = DBTableNameSearchContentImages;
        $sql = new dbObj();
        $sql->setDatabase($databaseName);
        $sql->setTypeINSERT();
        $sql->setInsertFieldValueInteger('libraryListID', $libraryListInt, $databaseName);
        
        if(!db::query($sql)){
            // echo $sql->getQueryString();     
            return FALSE;
        }

        return db::getLastID();
    }   
    public function newElementToSrcTable(){
        $databaseName = DBTableNameSrcListID;
        $sql = new dbObj();
        $sql->setTypeINSERT();
        
        $sql->setDatabase($databaseName);
        
        $sql->setInsertFieldValueNULL('ID', $databaseName);
        
        if(!db::query($sql)){   
            return FALSE;
        }

        return db::getLastID();
    }   
    public function elementToSrcTable($ob){
        if(!isset($ob->srcID) || !isset($ob->userID) || !isset($ob->src) || !isset($ob->type))  
            return FALSE;
        
        $databaseName = DBTableNameSrcOrigin;
        
        $temp = str_replace('http://','',$ob->src);
        $temp = str_replace('https://','',$temp);
        
        $file = '';
        $hoster = '';
        
        $t = explode('/',$temp,2);
        if(isset($t[0])){
            $hoster = $t[0];
        }
        if(isset($t[1])){
            $t = explode('?',$t[1],2);
            $file = '/'.$t[0];
        }
        
        $sql = new dbObj();
        $sql->setTypeINSERT();
        $sql->setDatabase($databaseName);
        
        $sql->setInsertFieldValueInteger('srcID', $ob->srcID, $databaseName);
        $sql->setInsertFieldValueInteger('userID', $ob->userID, $databaseName);
        $sql->setInsertFieldValueString('src', $ob->src, $databaseName);
        $sql->setInsertFieldValueString('srcType', $ob->type, $databaseName);
        $sql->setInsertFieldValueDATE_ADD_TO_NOW('dateCreated', 0, $databaseName);
        $sql->setInsertFieldValueString('hoster', $hoster, $databaseName);
        $sql->setInsertFieldValueString('file', $file, $databaseName);
        //echo $sql->getQueryString();
        return db::query($sql);
    }   
    public function elementToMatchItems($contentID, $array){
        $databaseName = DBTableNameMatchElements;
        $sql = new dbObj();
        $sql->setTypeUpdate();
        $sql->setDatabase($databaseName);
        
        $sql->setUpdatedFieldValueIntegerPlus('position', count($array), $databaseName);
        $sql->setConditionStringEqual('contentID', $contentID, $databaseName);

        db::query($sql);

        $i = 1;

        $databaseName = DBTableNameMatchElements;
        $sql = new dbObj();
        $sql->setTypeINSERT();
        $sql->setDatabase($databaseName);

        // 
        foreach($array as $pic){
            $sql->setInsertFieldValueString('contentID', $contentID, $databaseName);
            
            if($pic->type == 'i' || $pic->type == 'v')
                $sql->setInsertFieldValueString('contentIDSub', $pic->contentID, $databaseName);
            else if($pic->type == 'si')
                $sql->setInsertFieldValueInteger('imageID', $pic->imageID, $databaseName);
            else if($pic->type == 'st')
                $sql->setInsertFieldValueInteger('textID', $pic->textID, $databaseName);
            else if($pic->type == 'vs')
                $sql->setInsertFieldValueInteger('videoID', $pic->videoID, $databaseName);
			
            $sql->setInsertFieldValueInteger('position', $i, $databaseName);
            $sql->setInsertFieldValueString('type', $pic->type, $databaseName);
            $i++;
        }

        return db::query($sql);
    }   
    public function libraryElementToSearch_contentImages(int $libraryID){
        $databaseName = DBTableNameSearchContentImages;
        $sql = new dbObj();
        $sql->setTypeINSERT();
        
        $sql->setDatabase($databaseName);
        
        $sql->setInsertFieldValueInteger('libraryListID', $libraryID, $databaseName);
        
        return db::query($sql);
    }
	public function libraryElementToSearch_contentVideosFrontpage($libraryID){
		$databaseName = DBTableNameSearchContentFrontpageVideos;
        $sql = new dbObj();
        $sql->setTypeINSERT();
        
        $sql->setDatabase($databaseName);
        
        $sql->setInsertFieldValueInteger('libraryListID', $libraryID, $databaseName);
        
        return db::query($sql);
	}
    public function libraryElementToSearch_contentImagesFrontpage(int $libraryID){
        $databaseName = DBTableNameSearchContentFrontpageImages;
        $sql = new dbObj();
        $sql->setTypeINSERT();
        
        $sql->setDatabase($databaseName);
        
        $sql->setInsertFieldValueInteger('libraryListID', $libraryID, $databaseName);
        
        return db::query($sql);
    }
    public function libraryElementToSearch_contentAllFrontpage( int $libraryID ){
        $databaseName = DBTableNameSearchContentFrontpageAll;
        $sql = new dbObj();
        $sql->setTypeINSERT();
        
        $sql->setDatabase($databaseName);
        
        $sql->setInsertFieldValueInteger('libraryListID', $libraryID, $databaseName);
        
        return db::query($sql);
    }
    public function libraryElementToSearch_contentCollectionsFrontpage( int $libraryID ){
        $databaseName = DBTableNameSearchContentFrontpageCollections;
        $sql = new dbObj();
        $sql->setTypeINSERT();
        
        $sql->setDatabase($databaseName);
        
        $sql->setInsertFieldValueInteger('libraryListID', $libraryID, $databaseName);
        
        return db::query($sql);
    }
    public function libraryElementToSearch_contentAll( int $libraryID ){
        $databaseName = DBTableNameSearchContentAll;
        $sql = new dbObj();
        $sql->setTypeINSERT();
        
        $sql->setDatabase($databaseName);
        
        $sql->setInsertFieldValueInteger('libraryListID', $libraryID, $databaseName);
        
        return db::query($sql);
    }
    public function libraryElementToSearch_contentCollections($libraryID){
        $databaseName = DBTableNameSearchContentCollections;
        $sql = new dbObj();
        $sql->setTypeINSERT();
        
        $sql->setDatabase($databaseName);
        
        $sql->setInsertFieldValueInteger('libraryListID', $libraryID, $databaseName);
        
        return db::query($sql);
    }
    public function libraryElementText(cCreation $ob){
        $databaseName = DBTableNameSrcText;
        $sql = new dbObj();
        $sql->setTypeINSERT();
        
        $sql->setDatabase($databaseName);
        
        $sql->setInsertFieldValueInteger('textID', $ob->getTextID(), $databaseName);
        $sql->setInsertFieldValueInteger('userID', $ob->getUserID(), $databaseName);
        $sql->setInsertFieldValueDATE_ADD_TO_NOW('dateCreated', 0, $databaseName);
        $sql->setInsertFieldValueString('text', $ob->getText(), $databaseName);
        $sql->setInsertFieldValueString('type', $ob->getType(), $databaseName);
        $sql->setInsertFieldValueInteger('is_adult', $ob->getIsAdult(), $databaseName);
        $sql->setInsertFieldValueInteger('is_private', $ob->getIsPrivate(), $databaseName);

        //$sql->setInsertFieldValueFloat('wordScore', 0.0, $databaseName);
        //echo $sql->getQueryString();
        return db::query($sql);
    }
    public function itemToTextSrcListID(){
        $databaseName = DBTableNameSrcListIDText;
        $sql = new dbObj();
        $sql->setTypeINSERT();
        
        $sql->setDatabase($databaseName);
        
        $sql->setInsertFieldValueNULL('ID', $databaseName);
        //$sql->setInsertFieldValueCounterAutoIncrement('textID', $databaseName);
        
        if(!db::query($sql)){
            //echo $sql->getQueryString();
            return FALSE;
        }
    
        return db::getLastID();
    }
    public function tagEntryForContentID( string $tag, string $contentIDString, string $label = ''){
        $label = strtolower( $label );    
        if($label == '')
            $label = NULL;

        // is tag already set for post
            $r = dbQueries::get()->tagIDByContentIDAndTagLinkS($contentIDString,$tag);
            
            if(isset($r[0]->ID))
                return TRUE;
        
        // check if tag is in Database
            $r = dbQueries::get()->tagIDByTagLinkS( $tag );
            
        // if not in DB
            if(!isset($r[0]->ID)){
                // add to DB
                $ID = dbQueries::add()->tagEntry( $tag, $label );
                if(!is_numeric($ID) || $ID < 1)
                    return FALSE;
            } else
                $ID = $r[0]->ID;
            
        // add one to counter
            dbQueries::change()->counterTagsDatabase($tag, 1);
            
        // add Tag to match table
            dbQueries::add()->tagToMatchContentByString($tag, $contentIDString);
            dbQueries::add()->tagToMatchContentByID($ID, $contentIDString);
    }
    public function tagEntry( string $tag, $label = NULL ){
        $databaseName = DBTableNameContentTag;
        $sql = new dbObj();
        $sql->setTypeINSERT();
        $sql->setDatabase($databaseName);
        
        $sql->setInsertFieldValueString('tagLinkS', $tag, $databaseName);
        if($label === NULL || empty($label))
            $sql->setInsertFieldValueString('label', $tag, $databaseName);
        else
            $sql->setInsertFieldValueString('label', $label, $databaseName);
        
        db::query($sql);
        
        return db::getLastID();
    }
    public function tagToMatchContentByString( string $tagLinkS, string $contentID){
        /// Add Tag to MatchContentWithTAGLinkS
            $databaseName = DBTableNameContentMatchTags;
            
            $sql = new dbObj();
            $sql->setTypeINSERT();
            $sql->setDatabase($databaseName);

            $sql->setInsertFieldValueString('contentID', $contentID, $databaseName);
            $sql->setInsertFieldValueString('tagLinkS', $tagLinkS, $databaseName);
            $sql->setInsertFieldValueString('hash', $contentID.'-'.$tagLinkS, $databaseName);

            
            return db::query($sql);
    }
    public function tagToMatchContentByID( string $tagID, string $contentID){
        /// Add Tag to MatchContentWithTAGID
            $databaseName = DBTableNameContentMatchTagsID;
            
            $sql = new dbObj();
            $sql->setTypeINSERT();
            $sql->setDatabase($databaseName);
            
            $sql->setInsertFieldValueString('contentID', $contentID, $databaseName);
            $sql->setInsertFieldValueString('tagID', $tagID, $databaseName);
            $sql->setInsertFieldValueString('hash', $contentID.'-'.$tagID, $databaseName);
            
            return db::query($sql);
    }
    public function tagsToAllDatabases(cCreation $ob, $tagArray){
        if(!is_array($tagArray))
            return TRUE;
        
        /// Insert Tag to TagLibrary if necdessary
            $databaseName = DBTableNameContentTag;  
            foreach($tagArray as $key => $tag){
                $tag = trim($tag);  
                $tagLink = security::getNormalizedLinkURL($tag);
                
                if(empty($tagLink) || strlen($tagLink) < 2)
                    continue;   
                
                $sql = new dbObj();
                $sql->setTypeINSERT();
                $sql->setDatabase($databaseName);
                $sql->setInsertFieldValueString('label', $tag, $databaseName);
                $sql->setInsertFieldValueString('tagLinkS', $tagLink, $databaseName);
                $sql->setOnDuplicateFieldValueAddIntegerToColumn('counter', 1, 'counter');
                
                db::query($sql);
            }
        
        /// Add Tag to MatchContentWithTAGLinkS
            $databaseName = DBTableNameContentMatchTags;
            
            $sql = new dbObj();
            $sql->setTypeINSERT();
            $sql->setDatabase($databaseName);
            
            $tagLinkArray = []; $g = 0;
            foreach($tagArray as $key => $tag){     
                $tagLink = security::getNormalizedLinkURL($tag);
                $tagLinkArray[] = $tagLink;
                
                if(empty($tagLink))
                    continue;   
                
                $g++;   
                $sql->setInsertFieldValueString('contentID', $ob->getContentID(), $databaseName);
                $sql->setInsertFieldValueString('tagLinkS', $tagLink, $databaseName);
                $sql->setInsertFieldValueString('hash', $ob->getContentID().'-'.$tagLink, $databaseName);
            }
            if($g > 0)
                db::query($sql);
            else                
                return TRUE;
            
        /// Select IDs from TagLinkLibrary
            $databaseName = DBTableNameContentTag;
            $sql = new dbObj();
            $sql->setTypeSELECT();
            $sql->setDatabase($databaseName);
            $sql->setSELECTField('ID', $databaseName);
            
            foreach($tagLinkArray as $tagLink){
                if(empty($tagLink) || strlen($tagLink) < 2)
                    continue;
                $sql->setConditionStringEqual('tagLinkS', $tagLink, $databaseName, 'OR');
            }
            $res = db::query($sql);
        
        /// If IDs availba add IDs to contentMatchTagList
            if(isset($res[0]->ID)){
                $databaseName = DBTableNameContentMatchTagsID;
            
                $sql = new dbObj();
                $sql->setTypeINSERT();
                $sql->setDatabase($databaseName);
                
                foreach($res as $val){
                    $sql->setInsertFieldValueString('contentID', $ob->getContentID(), $databaseName);
                    $sql->setInsertFieldValueInteger('tagID', $val->ID, $databaseName);
                    $sql->setInsertFieldValueString('hash', $ob->getContentID().'-'.$val->ID, $databaseName);
                }
                
                return db::query($sql);
            }
            return FALSE;
    }
}
class dbChange {
	public function videoSrcInformationByObject($ID, crawlYoutubeVideo $cObj){
		$databaseName = DBTableNameSrcVideos;	
		$sql = new dbObj();
		$sql->setTypeUPDATE();
		$sql->setDatabase($databaseName);

		//$sql->setInsertFieldValueString('externID', $this->officialVideoID, $databaseName);
		$sql->setUpdatedFieldValueString('channel', $cObj->officialChanelID, $databaseName);
		///$sql->setInsertFieldValueString('externEmbedURL', $this->officialEmbedURL, $databaseName);
		//$sql->setInsertFieldValueString('tagString', implode(',',$this->officialTags), $databaseName);
		//$sql->setInsertFieldValueString('descriptionExtern', $this->officialDescription, $databaseName);
		
		//$sql->setInsertFieldValueString('hash', contentTypeVideoYoutube.'-'.$this->officialVideoID, $databaseName);
		
		$sql->setUpdatedFieldValueInteger('duration', $cObj->officialDuration, $databaseName);
		$sql->setUpdatedFieldValueInteger('is_parsed', 1, $databaseName);
		$sql->setUpdatedFieldValueString('genre', $cObj->officialGenres, $databaseName);
		$sql->setUpdatedFieldValueInteger('dimX', $cObj->officialDimensions['x'], $databaseName);
		$sql->setUpdatedFieldValueInteger('dimY', $cObj->officialDimensions['y'], $databaseName);

		$sql->setConditionIntegerEqual('videoID', $ID, $databaseName);

		db::query($sql);
	}	
	public function aiTagInfoText86( $rID, string $rTags){
		$databaseName = DBTableNameAITagged;
		
		$sql = new dbObj();
		$sql->setTypeUPDATE();
		$sql->setDatabase($databaseName);
		
		$sql->setUpdatedFieldValueString('infoLastCrawl86', $rTags, $databaseName);
		$sql->setUpdatedFieldValueInteger('statusLastCrawl86', 1, $databaseName);
		$sql->setConditionStringEqual('ID', $rID, $databaseName);
		
		db::query($sql);
	}
    public function updateMediaInOfContentID( string $contentID ){
        $databaseName = DBTableNameMatchElements;
        
        $sql = new dbObj();
        $sql->setTypeSELECT();
        $sql->setDatabase($databaseName);
        
        $sql->setSELECTCount('ID', $databaseName, 'c');
        
        $sql->setConditionStringEqual('contentID', $contentID, $databaseName);
        $sql->setConditionNotNULL('contentIDSub', $databaseName, 'AND');

        $c = db::query($sql);
        if(isset($c[0]->c) && $c[0]->c > 0){
            dbQueries::change()->contentInformationMediaIn( $contentID, $c[0]->c );
            return $c[0]->c;
        }
        return 0;
    }
    public function contentInformationMediaIn( string $contentID, int $c ){
        $databaseName = DBTableNameContentAll;
        $sql = new dbObj();
        $sql->setTypeUPDATE();
        $sql->setDatabase($databaseName);
        
        $sql->setUpdatedFieldValueInteger('mediaIn', $c, $databaseName);
        $sql->setConditionStringEqual('contentID', $contentID, $databaseName);;
        
        db::query($sql);
    }    
    public function counterTagsDatabase($tagLinkS, int $counter){
        $databaseName = DBTableNameContentTag;
        $sql = new dbObj();
        $sql->setTypeUPDATE();
        $sql->setDatabase($databaseName);
        
        $sql->setUpdatedFieldValueIntegerPlus('counter', $counter, $databaseName);
        $sql->setConditionStringEqual('tagLinkS', $tagLinkS, $databaseName);

        return db::query($sql);
    }    
    public function contentInformationThumbUrl(string $contentIDString, string $contentThumbString){
        $databaseName = DBTableNameContentAll;
        
        $sql = new dbObj();
        $sql->setTypeUPDATE();
        
        $sql->setDatabase($databaseName);
        
        $sql->setUpdatedFieldValueString('thumbnailLink', $contentThumbString, $databaseName);
        $sql->setConditionStringEqual('contentID', $contentIDString, $databaseName);

        return db::query($sql);
    }
    public function contentInformationIsCollectionUpload( string $contentIDString, bool $status ){
        $databaseName = DBTableNameContentAll;
        
        $sql = new dbObj();
        $sql->setTypeUPDATE();
        
        $sql->setDatabase($databaseName);
        
        $sql->setUpdatedFieldValueInteger('is_colUpload', $status, $databaseName);
        $sql->setConditionStringEqual('contentID', $contentIDString, $databaseName);

        return db::query($sql);
    }
    public function counterElementsPost(string $contentIDString, int $i){
        $databaseName = DBTableNameContentAll;    
            
        $sql = new dbObj();
        $sql->setTypeUPDATE();
        
        $sql->setDatabase($databaseName);
        
        $sql->setUpdatedFieldValueIntegerPlus('mediaIn', $i, $databaseName);
        $sql->setConditionStringEqual('contentID', $contentIDString, $databaseName);
        
        return db::query($sql);
    }    
    public function contentInformationType(string $contentIDString, string $contentTypeString){
        if($contentIDString == NULL)
            return FALSE;
        if(!in_array($contentTypeString, ['i','c']))
            return FALSE;
        
        $databaseName = DBTableNameContentAll;
            
        $sql = new dbObj();
        $sql->setTypeUPDATE();
        
        $sql->setDatabase($databaseName);
        
        $sql->setUpdatedFieldValueString('type', $contentTypeString, $databaseName);
        $sql->setConditionStringEqual('contentID', $contentIDString, $databaseName);
        
        return db::query($sql);
    }
	public function contentInformationAltTag( string $contentIDString, string $altInfo ){
		if(strlen($altInfo) > 4){
    // check if there is already a $altInfo
            $databaseName2 = DBTableNameSrcText;
            $databaseName  = DBTableNameMatchElements;
            $sql = new dbObj();
            $sql->setTypeSELECT();
            $sql->setDatabase($databaseName);
            $sql->setDatabase($databaseName2);
            
            $sql->setSELECTField('ID', $databaseName2);
            $sql->setConditionStringEqual('type', 'ta', $databaseName2);
            $sql->setConditionStringEqual('contentID', $contentIDString, $databaseName, 'AND');
            
            $sql->setDBonLeftJoinEqualToColumn('textID', $databaseName2, 'textID', $databaseName);

            $res = db::query($sql);
            
            if(isset($res[0]->ID)){
                // update ta string
                $ob = new cCreation();
                    $ob->setUserID( user::getDBIDCloaked() );
                    $ob->setTextID( $res[0]->ID );
                    $ob->setText( $altInfo );
    
                    return $this->srcTextTextForced( $ob );
            } else {
                return contentCreation::addTextToContentItem($contentIDString, $altInfo, 'ta');
            }
        }
		return FALSE;
	}
    public function contentInformationComplete(string $contentIDString, string $titleString, array $tagArray, string $descriptionString, int $isPrivateInt, int $isAdultInt, string $thumbLink = ''){
        if($contentIDString == NULL)
            return FALSE;
        
        if($isPrivateInt < 0)
            $isPrivateInt = NULL;
        if($isAdultInt < 0)
            $isAdultInt = NULL;
        
        if(strlen($thumbLink) < 3)
            $thumbLink = NULL;
        
        // Add title
        if(strlen($titleString) > 2 || $isPrivateInt != NULL || $thumbLink !== NULL){
            $databaseName = DBTableNameContentAll;
            
            $sql = new dbObj();
            $sql->setTypeUPDATE();
            
            $sql->setDatabase($databaseName);
            
            if(strlen($titleString) > 2){
                $sql->setUpdatedFieldValueString('title', $titleString, $databaseName);
                $sql->setUpdatedFieldValueString('link', security::getNormalizedLinkURL($titleString), $databaseName);
                $sql->setUpdatedFieldValueString('shortTitle', uploadSanitizer::getStringShortend($titleString), $databaseName);
            }
            if($isPrivateInt != NULL)
                $sql->setUpdatedFieldValueInteger('is_private', $isPrivateInt, $databaseName);
            
            if($thumbLink !== NULL)
                $sql->setUpdatedFieldValueInteger('thumbnailLink', $thumbLink, $databaseName);
            
            $sql->setConditionStringEqual('contentID', $contentIDString, $databaseName);
            //echo "<!--".$sql->getQueryString()."-->";
            if(!db::query($sql))
                return FALSE;
        }

        if(strlen($descriptionString) > 4){
            // check if there is already a description
            $databaseName2 = DBTableNameSrcText;
            $databaseName  = DBTableNameMatchElements;
            $sql = new dbObj();
            $sql->setTypeSELECT();
            $sql->setDatabase($databaseName);
            $sql->setDatabase($databaseName2);
            
            $sql->setSELECTField('ID', $databaseName2);
            $sql->setConditionStringEqual('type', 'td', $databaseName2);
            $sql->setConditionStringEqual('contentID', $contentIDString, $databaseName, 'AND');
            
            $sql->setDBonLeftJoinEqualToColumn('textID', $databaseName2, 'textID', $databaseName);
            
            $res = db::query($sql);
            
            if(isset($res[0]->ID)){
                // update td string
                $ob = new cCreation();
                    $ob->setUserID( user::getDBIDCloaked() );
                    $ob->setTextID( $res[0]->ID );
                    $ob->setText( $descriptionString );
                    
                    $this->srcTextTextForced( $ob );
            } else {
                contentCreation::addTextToContentItem($contentIDString, $descriptionString);
            }
        }
        
        $rTy = dbQueries::get()->typeOfContentIDTitleByContentID($contentIDString);
        //print_r($tagArray);print_r($isPrivateInt); var_dump($rTy[0]->title);
        
        // if not private && not adult add to searchable database respnsoibble to type
            if($isPrivateInt != 1 && $isAdultInt != 1 && strlen($rTy[0]->title) > 2){
                if($rTy[0]->type == 'i'){
                    dbQueries::add()->elementToSearchTableImages($rTy[0]->ID);
                }
                dbQueries::add()->libraryElementToSearch_contentAll($rTy[0]->ID);
            }
        
        if(is_array($tagArray) && count($tagArray) > 0){
            // tags
             foreach($tagArray as $label){
                $tag = security::getNormalizedLinkURL( $label );
                dbQueries::add()->tagEntryForContentID($tag, $contentIDString, $label);
             }  
        } else if(strlen($rTy[0]->title) < 3 && empty(dbQueries::get()->allTagLinkSByContentID( $contentIDString ))){
            if($rTy[0]->type == 'i')    
                dbQueries::delete()->elementFromSearchTableImages( $rTy[0]->ID );
        }
        return TRUE;
    }   
    public function contentInformationStatusPrivate(string $contentID, int $is_private){
        $p = (int)((bool) $is_private);
        $databaseName = DBTableNameContentAll;
        
        $sql = new dbObj();
        $sql->setTypeUPDATE();
        
        $sql->setDatabase($databaseName);
        
        $sql->setUpdatedFieldValueInteger('is_private', $p, $databaseName);
        $sql->setConditionStringEqual('contentID', $contentID, $databaseName);

        return db::query($sql);
    }   
    public function contentInformationDescription(cCreation $ob){
        // check if contentDescription is already availabel changeContentInformationDescription
            $databaseName = DBTableNameSrcText;
            $databaseName2 = DBTableNameMatchElements;
            $sql = new dbObj();
            $sql->setTypeSELECT();
            
            $sql->setDatabase($databaseName);
            $sql->setDatabase($databaseName2);
            
            $sql->setSELECTField('textID', $databaseName);
            $sql->setDBonLeftJoinEqualToColumn('textID', $databaseName, 'textID', $databaseName2);
            $sql->setConditionStringEqual('type', 'td', $databaseName);
            $sql->setConditionStringEqual('contentID', $ob->getContentID(), $databaseName2, 'AND');
            
            $res = db::query($sql);
            
            if(isset($res[0]->textID))
                $textID = $res[0]->textID;
            else
                $textID = FALSE;
        
        if(!$textID){
            $ob->setType( 'td' );
            dbQueries::add()->libraryElementText( $ob );
        } else {
            $ob->setTextID( $textID );  
            $this->srcTextText( $ob );
        }
        
    }
	public function srcTextTextForced(cCreation $cObj){
        $databaseName = DBTableNameSrcText;
        $sql = new dbObj();
        
        $sql->setTypeUPDATE();
        
        $sql->setDatabase($databaseName);
        
        $sql->setUpdatedFieldValueString('text', $cObj->getText(), $databaseName);
        $sql->setConditionIntegerEqual('ID', $cObj->getTextID(), $databaseName);
        $sql->setUpdatedFieldValueDATE_ADD_TO_NOW('dateModified', 0, $databaseName);

        return db::query($sql);
    }
    public function srcTextText(cCreation $cObj){
        $databaseName = DBTableNameSrcText;
        $sql = new dbObj();
        
        $sql->setTypeUPDATE();
        
        $sql->setDatabase($databaseName);
        
        $sql->setUpdatedFieldValueString('text', $cObj->getText(), $databaseName);
        $sql->setConditionIntegerEqual('textID', $cObj->getTextID(), $databaseName);
        $sql->setConditionIntegerEqual('user', $cObj->getUserID(), $databaseName);
        $sql->setUpdatedFieldValueDATE_ADD_TO_NOW('dateModified', 0, $databaseName);
        
        return db::query($sql);
    }
}
?>