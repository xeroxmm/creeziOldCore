<?php
class contentCollection{
	private $status = 0;
	private $IDToCheck = 0;	
	private $titleToCheck = '';	
	private $isCollectionSite = FALSE;
	private $dbLastQueryResult = NULL;
	private $dbLastQueryAllImagesResult = NULL;
	private $dbLastQueryResultTags = NULL;
	private $link = '';
	private $userID = 0;
	
	private $collectionItems = [];
	private $collectionTitle = NULL;
	private $collectionDescription = NULL;
	private $collectionTags = [];
	private $relatedItemsUpdate = NULL;
	private $relatedItemsArray = NULL;
	private $collectionHasPaginationLink = FALSE;
	private $isAdult = FALSE;
	
	private $countElements = 0;
	
	function __construct($colLink = NULL){
		$this->getLinkInfo($colLink);	
		$this->setAsCollectionSite();

		if($this->status != 1)
			return;
		
		$this->loadCollectionInfoFromDB();
		if(!isset($this->dbLastQueryResult[0]->dateCreated)){
			$this->setStatus(0);
			return;
		}
		if($this->titleToCheck !== NULL && $this->titleToCheck != $this->dbLastQueryResult[0]->link){
			$this->setStatus(2);
			$this->setLink();
			return;
		}
		
		$this->loadCollectionImagesFromDB(); // print_r($this->dbLastQueryAllImagesResult);
		
		if(count($this->dbLastQueryAllImagesResult) < 1){
			$this->setStatus(0);
			return;
		}
		$this->setLink();
		//print_r($this->getItems());
		if($this->colObj->page > 1 && isset($this->colObj->infScroll) && $this->colObj->infScroll == TRUE){
			$this->setCollectionInfos();    
			$obj = new stdClass();
            $obj->items = $this->getItems();
            $obj->userID = $this->userID;
            $obj->colObj = new stdClass();
            $obj->colObj->startImage = $this->colObj->startImage;
            $obj->title = $this->getTitle();
            $obj->collectionHasPaginationLink = $this->collectionHasPaginationLink;
            
			snippetLEGO::echoCollectionImagesInfScroll( $obj );
			exit();
		}
		
		$this->loadCollectionTags();
			
		$this->setCollectionInfos();
		
		return;
	}
	public function getContentID(){
		return $this->IDToCheck;
	}
	public function getTagsAsString(){
		$strings = ',';	
		foreach($this->collectionTags as $val){
			$strings .= $val[1].',';
		}
		return trim($strings,',');
	}
	public function isPaginated(){
		return $this->collectionHasPaginationLink;
	}
	public function getPaginationLink(){
		return pagination::getPaginationNextLink();
	}
	private function loadCollectionTags(){
		$databaseName = DBTableNameContentMatchTagsID;
		$databaseTag = DBTableNameContentTag;
		
		$sql = new dbObj();
		$sql->setTypeSELECT();
		$sql->setDatabase($databaseName);
		$sql->setDatabase($databaseTag);
		
		$sql->setSELECTField('label', $databaseTag);
		$sql->setSELECTField('counter', $databaseTag);
		$sql->setSELECTField('tagLinkS', $databaseTag);
		
		$sql->setConditionStringEqual('contentID', $this->IDToCheck, $databaseName);
		$sql->setDBonLeftJoinEqualToColumn('ID', $databaseTag, 'tagID', $databaseName);
		
		$sql->setOrderByField('counter', $databaseTag, FALSE);
		// echo $sql->getQueryString();
		$this->dbLastQueryResultTags = db::query($sql);

		if(isset($this->dbLastQueryResultTags[0]->counter)){
			foreach($this->dbLastQueryResultTags as $tag){
				$this->collectionTags[] = array($tag->label, $tag->tagLinkS);
			}
		}
	}
	private function getLinkInfo($link){
		if($link === NULL){	
			$urlObj = core::getURLObj()->getPathArray();
			if(isset($urlObj[1]))
				$this->IDToCheck = $urlObj[1];
			else
				return;
				
			if(isset($urlObj[2]))
				$this->titleToCheck = $urlObj[2];
		}
		
		$this->setStatus(1);
	}
	private function setStatus($int = 0){
		$this->status = $int;
	}
	private function setAsCollectionSite(){
		$this->isCollectionSite = TRUE;
	}
	private function loadCollectionInfoFromDB(){
		$databaseUser 		= DBTableNameUser;
		$databaseName		= DBTableNameContentAll;
			
		$sql = new dbObj();
		
		$sql->setTypeSELECT();
		
		$sql->setDatabase($databaseName);
		$sql->setDatabase($databaseUser);
		
		$sql->setSELECTField('title', $databaseName);
		$sql->setSELECTField('link', $databaseName);

		$sql->setSELECTField('userID', $databaseName);
		$sql->setSELECTField('views', $databaseName);
		$sql->setSELECTField('contentID', $databaseName);
		$sql->setSELECTField('dateCreated', $databaseName);
		
		$sql->setSELECTField('is_private', $databaseName);
		$sql->setSELECTField('is_adult', $databaseName);
		
		$sql->setSELECTField('nick', $databaseUser);
		$sql->setSELECTField('avatarHTML', $databaseUser);
		$sql->setSELECTField('userURL', $databaseUser);
		$sql->setSELECTField('thumbnailLink', $databaseName);
		$sql->setSELECTField('mediaIn', $databaseName);
				
		$sql->setConditionStringEqual('contentID', $this->IDToCheck, $databaseName);
		$sql->setConditionStringEqual('type', 'c', $databaseName, 'AND');
		
		$sql->setDBonLeftJoinEqualToColumn('ID', $databaseUser, 'userID', $databaseName);
		
		//echo $sql->getQueryString();
		
		$this->dbLastQueryResult = db::query($sql);
	}
	private $colObj = NULL;
	private function loadCollectionImagesFromDB(){
		$dataBaseMatch		= DBTableNameMatchElements;
		$databaseNameAll	= DBTableNameContentAll;
		$databaseNameText	= DBTableNameSrcText;
			
		$sql = new dbObj();
		
		$sql->setTypeSELECT();
		
		$sql->setDatabase($dataBaseMatch);
		$sql->setDatabase($databaseNameAll);
		$sql->setDatabase($databaseNameText);

		$sql->setSELECTField('title', $databaseNameAll);
		$sql->setSELECTField('thumbnailLink', $databaseNameAll);
		$sql->setSELECTField('type', $databaseNameAll, 'typeC');
		$sql->setSELECTField('type', $databaseNameText);
		$sql->setSELECTField('link', $databaseNameAll);
		$sql->setSELECTField('dateCreated', $databaseNameAll);
		$sql->setSELECTField('is_private', $databaseNameAll);
		$sql->setSELECTField('userID', $databaseNameAll);
		$sql->setSELECTField('contentID', $databaseNameAll);
		$sql->setSELECTField('text', $databaseNameText);
		
		$sql->setConditionStringEqual('contentID', $this->IDToCheck, $dataBaseMatch);
		$sql->setConditionStringEqual('is_private', 0, DBTableNameContentAll, 'AND',1);
		$sql->setConditionIsNULL('is_private', DBTableNameContentAll, 'OR',1);
        
		$sql->setDBonLeftJoinEqualToColumn('contentID', $databaseNameAll,'contentIDSub', $dataBaseMatch);
		$sql->setDBonLeftJoinEqualToColumn('textID', $databaseNameText,'textID', $dataBaseMatch);
		
		$sql->setOrderByField('position', $dataBaseMatch);
		
        $string = $sql->getQueryString();
		$res = db::query($sql);
		
		if(empty($this->dbLastQueryResult[0]->thumbnailLink) && isset($res[0]->thumbnailLink)){
			dbQueries::change()->contentInformationThumbUrl($this->dbLastQueryResult[0]->contentID, $res[0]->thumbnailLink);
		}
		if($this->dbLastQueryResult[0]->mediaIn != count($res)){
			dbQueries::change()->contentInformationMediaIn($this->dbLastQueryResult[0]->contentID, count($res));
		}
		$colObj = pagination::getCollectionObject(); // print_r($colObj);
		$startImage = 0; $endImage = 0;
		if(isset($colObj->contentID)){
			$this->colObj = $colObj;
			$offset = ($colObj->page - 1) * $colObj->limit + ($colObj->page - 1);
		
			if($offset < 0)
				$offset = 0;
			$startImage = $offset;
			$this->colObj->startImage = $startImage;
			$endImage = $offset + $colObj->limit + 1;
		} else {
			$endImage = 24;
			$this->colObj = (object)[];
			$this->colObj->page = 1;
			$this->colObj->startImage = 1;
		}
		//echo $startImage.' - '.$endImage."\n";
		$ct = count($res); $imageCounter = 0;
		
		if($ct > 29){
			$this->dbLastQueryAllImagesResult = [];
			foreach($res as $key => $val){	
				if($val->typeC == 'i'){
					// unset all elements until images count == page expression	
					// unset all elements after images reach counter
					if(($imageCounter < $startImage) || ($imageCounter >= $endImage))
						unset($res[$key]);
					else
						$this->dbLastQueryAllImagesResult[] = $val;
					$imageCounter++;
				} 
				if($val->type == 'td'){
					$this->collectionDescription = $val->text;
				continue;
				}
			}

			// print_r($res);
			if($imageCounter >= $endImage)
				$this->collectionHasPaginationLink = TRUE;

		} else
			$this->dbLastQueryAllImagesResult = $res;
	}
	private function setLink(){
		$this->link = HTTP_HOST.'/'.LINK_collectionPageSingle.'/'.$this->dbLastQueryResult[0]->contentID.'/'.$this->dbLastQueryResult[0]->link;
	}
	
	private function addThumbnailInformation(){
		$databaseName = DBTableNameSrcImages;
		$sql = new dbObj();
		$sql->setDatabase($databaseName);
		$sql->setSELECTField('dimensionX', $databaseName);
		$sql->setSELECTField('dimensionY', $databaseName);
		$sql->setSELECTField('hash', $databaseName);
		
		if(!isset($this->dbLastQueryAllImagesResult[0]))
			return;
		
		$id = explode('/',$this->dbLastQueryAllImagesResult[0]->thumbnailLink);
		if(!isset($id[1]))
			return;
		
		$sql->setConditionIntegerEqual('imageID', $id[1], $databaseName);
		
		$res = db::query($sql);
		
		if(isset($res[0]->dimensionX)){
			$this->collectionItems[0]->dimension = [$res[0]->dimensionX , $res[0]->dimensionY];
			$this->hash = $res[0]->hash;
		}
	}
	
	private function setCollectionInfos(){	
		// $this->hash = $this->dbLastQueryResult[0]->hash;
		$this->collectionTitle = $this->dbLastQueryResult[0]->title;

		foreach($this->dbLastQueryAllImagesResult as $val){
			$z = (object)[];	
			$z->title = $val->title;
			$z->linkStoredDBEntry = $val->thumbnailLink.'';
			//$z->description = $val->description;
			//$z->dimension = array($val->dimensionX, $val->dimensionY);
			//$z->mime = $val->mime;
			$z->linkContent = $val->typeC.'/'.$val->contentID.'/'.$val->link;
			$z->contentID = $val->contentID;
			if($val->type == 'td'){
				$this->collectionDescription = $val->text;
				continue;
			}
			$this->collectionItems[] = $z;
			
			if($val->typeC == 'i' || $val->typeC == 'v')
				$this->countElements++;
		}
		
		$this->user = $this->dbLastQueryResult[0]->userID;
		//$this->views = $this->dbLastQueryResult[0]->views;
		$this->unixDate = $this->dbLastQueryResult[0]->dateCreated;
		//$this->comments = $this->dbLastQueryResult[0]->comments;
		$this->isPrivate = (bool)$this->dbLastQueryResult[0]->is_private;
		//$this->voteDown = $this->dbLastQueryResult[0]->votesDown;
		//$this->voteUp = $this->dbLastQueryResult[0]->votesUp;
		//$this->score = $this->dbLastQueryResult[0]->score;
		
		$this->userID = $this->dbLastQueryResult[0]->userID;
		$this->userURL = $this->dbLastQueryResult[0]->userURL;
		$this->userAvatar = $this->dbLastQueryResult[0]->avatarHTML;
		$this->nick = $this->dbLastQueryResult[0]->nick;
		
		$this->isAdult = (bool)$this->dbLastQueryResult[0]->is_adult;
		
		// load first image of the collection (ThumbNailInformation)
			$this->addThumbnailInformation();
		/*
		$dbl = [];
		foreach($this->dbLastQueryResult as $val){
			if(empty($val->label) || empty($val->tagLinkS) || isset($dbl[trim($val->label)]))
				continue;	
			$this->collectionTags[] = 
			$dbl[trim($val->label)] = TRUE;
		}*/
	}
	/**
	 * 
	 * 
	 */
	public function getItems(){
	    $z = count($this->collectionItems);
        if($z < 24)
            $z = 0; 
		pagination::setElementCount( $z );    
		return $this->collectionItems;
	}
	public function getCountElements(){
		return $this->countElements;
	}
	public function getStatus(){
	 	return $this->status;
	}
	public function getLinkOfPictureSite(){
		return $this->link;
	}
	public function getDescription(){
		return $this->collectionDescription;
	}
	public function getTags(){
		return $this->collectionTags;
	}
	public function getUserURL(){
		return $this->userURL;
	}
	public function getUserNick(){
		return $this->nick;
	}
	public function getUserID(){
		return $this->userID;
	}
	public function getUserAvatar(){
		return $this->userAvatar;
	}
	public function getTitle(){
		return $this->collectionTitle;
	}
	public function isAdult(){
		return $this->isAdult;
	}
	private $mime = 'jpg';
	public function getLinkHot(){
		return PIC_HOST.'/'.$this->collectionItems[0]->linkStoredDBEntry.".".$this->mime;
	}
	public function isPrivate(){
		return $this->isPrivate;
	}
	public function getDimensionX(){
		if(!isset($this->collectionItems[0]->dimension[0]))
			return 0;	
		return (int)$this->collectionItems[0]->dimension[0];
	}
	public function getDimensionY(){
		if(!isset($this->collectionItems[0]->dimension[1]))
			return 0;
		return (int)$this->collectionItems[0]->dimension[1];
	}
	public function imageIsInThisCollection($id){
		foreach($this->dbLastQueryAllImagesResult as $val){
			if($val->contentID == $id)
				return TRUE;
		}
		return FALSE;
	}
	public function getRelatedItems(){
		$update = FALSE;
		
		if(!is_array($this->relatedItemsArray) && is_array($this->collectionTags) && count($this->collectionTags) > 0){
			$update = FALSE;

			$databaseName = 'match_images_tags';
			$databaseName2 = 'match_collection_tags';
			$databaseName = DBTableNameContentMatchTags;

			$sql = new dbObj();
			$sql->setTypeSELECT();
			$sql->setDatabase($databaseName);
			//$sql->setDatabase($databaseName2);
			
			$sql->setSELECTField('contentID', $databaseName);
			
			foreach($this->collectionTags as $val)
				$sql->setConditionStringEqual('tagLinkS', $val[1], $databaseName, 'OR', 123);
			
			// $sql->setConditionStringEqual('contentID', $this->IDToCheck, $databaseName, 'AND', NULL, '!=');
			
			$sql->setLimit(5000);
			//$sql->setOrderByField('dateCreated', $databaseName);
			
			$res = db::query($sql);
			// echo '<!--'.$sql->getQueryString().'-->'; return;
			$matches = FALSE;
			if(isset($res[0])){
				$matches = [];
				$this->relatedItemsArray = [];	
				foreach($res as $val){
					if($val->contentID == $this->IDToCheck || $this->imageIsInThisCollection($val->contentID))
						continue;
						
					if(!isset($matches[$val->contentID])){
						$matches[$val->contentID] = 0;
						$this->relatedItemsArray[$val->contentID] = 0;
					}
					$matches[$val->contentID]++;
					$this->relatedItemsArray[$val->contentID]++;
				}
				//arsort($this->relatedItemsArray);
				// print_r($this->relatedItemsArray);
				$z = 0;
				$databaseName = DBTableNameContentAll;
				$sql = new dbObj();
				$sql->setTypeSELECT();
				$sql->setDatabase($databaseName);
				// $sql->setDatabase('content_collections');
				
				$sql->setSELECTField('title', $databaseName);
				// $sql->setSELECTField('title', 'content_collections', 'colTitle');
				// $sql->setSELECTField('link', 'content_collections', 'colLink');
				$sql->setSELECTField('link', $databaseName);
				$sql->setSELECTField('type', $databaseName);
				$sql->setSELECTField('contentID', $databaseName);
				$sql->setSELECTField('thumbnailLink', $databaseName);
				
				foreach($this->relatedItemsArray as $key => $val){
					$sql->setConditionStringEqual('contentID', $key, $databaseName, 'OR', 1);	
					if($z > 250){
						break;
					}
					$z++;
				}
				$sql->setConditionStringEqual('is_private', 0, $databaseName, 'AND', 2);
				// $sql->setDBonLeftJoinEqualToColumn('collectionID', 'content_collections', 'inCollectionColID', $databaseName);
				$sql->setLimit(250);
				$res = db::query($sql);  //echo $sql->getQueryString();
				
				$alreadInCollection = [];
				
				if(isset($res[0])){
					$n = 0;	
					foreach($res as $val){
						if(isset($alreadInCollection[$val->contentID]) && $val->contentID !== NULL)
							continue;
						
						$alreadInCollection[$val->contentID] = TRUE;
						
						if($val->type == 'c'){
							$this->relatedItemsArray[$val->contentID] = [$val->contentID, $val->title, $val->link, $val->thumbnailLink, $val->type, $val->contentID, $val->link, $val->title];
						} else
							$this->relatedItemsArray[$val->contentID] = [$val->contentID, $val->title, $val->link, $val->thumbnailLink];
						
						$n++;
						if($n > 25)
							break;
					}
				}
				/*echo '<!--';
				print_R($this->relatedItemsArray);
				echo '-->';*/
			} else
				$this->relatedItemsArray = NULL;
			
		}
		
		if(is_array($this->relatedItemsArray)){
			foreach($this->relatedItemsArray as $key => $val){
				if(!is_array($val))
					unset($this->relatedItemsArray[$key]);
			}
		}
		return $this->relatedItemsArray;
		if(is_array($this->relatedItemsArray) && count($this->relatedItemsArray) > 0 && isset($this->relatedItemsArray[0]->type)){
			if(DateIntervalEnhanced::createFromDateString($this->relatedItemsUpdate)->to_seconds() - time() < 7200)
				$this->updateRelatedContent();
			return $this->relatedItemsArray;
		}
		if($update)
			$this->updateRelatedContent();
	}
	private function updateRelatedContent(){
		
	}
}
?>
