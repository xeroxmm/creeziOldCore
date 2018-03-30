<?php
class contentThumbnail {
    private $ID = 0;
    private $url = '';
    private $size = array();
    private $dateCreated;
    private $mime;
    private $hash;
    
    private $userID;
    private $userNick;
    private $userURL;
    
    private $title;
    private $titleShort;
    private $score;
    private $link;
    private $storeDir;
    private $storeFile;
    
    private $isThumbCreated = FALSE;
    private $picColLinks = [];
    private $thumbnailLink = FALSE;
	private $items = 0;
    
    function __construct($picArray){
        if(!isset($picArray->dateCreated))
            return;
        //print_r($picArray); die();
        $this->ID = $picArray->contentID;
        
        $this->userID = $picArray->userID;
        $this->dateCreated = $picArray->dateCreated;
        
        if(isset($picArray->score))
            $this->score = $picArray->score;
        if(isset($picArray->mime))
            $this->mime = $picArray->mime;
        if(isset($picArray->nick))
            $this->userNick = $picArray->nick;
        
        $this->userURL = $picArray->userURL;
        
        if(isset($picArray->imgTitle))
            $this->title = $picArray->imgTitle;
        
        $this->titleShort = $picArray->shortTitle;
        
        if(isset($picArray->thumbnailLink) && $picArray->type == 'si' )
            $this->link = $picArray->thumbnailLink;
        else 
            $this->link = $picArray->link;
        
        if(isset($picArray->linkStored)){
            $this->storeDir = $picArray->linkStored;
            $this->storeFile = $picArray->linkFilename;
            $this->isThumbCreated = (bool)$picArray->isMOZupdated;
        }
        if(isset($picArray->thumbnailLink)){
            $this->picColLinks = [$picArray->thumbnailLink];
            $this->storeDir = $picArray->thumbnailLink;
            $this->thumbnailLink = $picArray->thumbnailLink;
        }
		if(isset($picArray->mediaIn)){
			$this->items = (int)$picArray->mediaIn;
		}
    }
	public function getItemAmount(){
		return $this->items;
	}
    public function getColLinks(){
        return $this->picColLinks;
    }
    public function getDuration(){
        return ;
    }
    public function getLinkOfDeepSite(){
        return LINK_imagePageSingle;
    }
    public function getLinkHot(){
        if($this->mime == 'jpeg');
            $this->mime = 'jpg';

        $file = uploadTempDirUserUpload.'/'.uploadDirThumbnails.'/'.$this->storeDir.".".$this->mime;

        if(!$this->thumbnailLink){
            if(file_exists($file))
                $m = 1;
                //var_dump($this->updateFileMOZInfo());
            else
                return PIC_HOST.'/'.$this->storeDir."_.".$this->mime;
        }
        
        return THUMB_HOST.'/'.$this->thumbnailLink.".".$this->mime;
    }
    private function updateFileMOZInfo(){
        $databaseName = DBTableNameContentImages;   
        $sql = new dbObj();
        $sql->setTypeUPDATE();
        $sql->setDatabase($databaseName);
        $sql->setUpdatedFieldValueInteger('isMOZupdated', 1, $databaseName);
        $sql->setConditionStringEqual('imageID', $this->ID, $databaseName);
        //echo $sql->getQueryString();
        return db::query($sql);
    }
    public function getLinkOfPictureSite(){
        return $this->getLinkOfElementSite();
    }
    public function getLinkOfElementSite(){
        return $this->ID.'/'.$this->link;
    }
    public function getTitle(){
        return $this->title;
    }
    public function getTitleShort(){
        return $this->titleShort;
    }
    public function getTimeStampHumanReadable(){
        $date = new dateAndTime($this->dateCreated);    
        return $date->getTimeDiffToNowAsString();
    }
    public function getTimeStamp(){
        $date = new dateAndTime($this->dateCreated);    
        return $date->getTimeStamp();
    }
}

class contentPicture {
    private $ID;
    private $IDToCheck = NULL;
    private $title;
    private $titleToCheck = NULL;
    private $link;
    private $dbLink;
    private $linkStored;
    private $linkStoredDBEntry;
    private $linkStoredDBFileName;
    private $url;
    private $dimension;
    private $tags = array();
    private $user;
    private $categories = array();
    private $views;
    private $thumbnail;
    private $unixDate;
    private $comments;
    private $type;
    private $mime;
    private $description;
    private $hash;
    private $imageAltText = NULL;
	
    private $voteUp;
    private $voteDown;
    private $score;
    
    private $isPicture = FALSE;
    private $isThumbnail = FALSE;
    private $isPrivate = FALSE;
    private $isPictureSite = FALSE;
    private $isAdult = FALSE;
    private $relatedItemsArray = NULL;
    private $relatedItemsUpdate = NULL;
        
    private $errors = array();
    
    private $userURL;
    private $userAvatar;
    private $userID;
    private $nick;
    
    private $status = FALSE;
    private $dbLastQueryResult = NULL;
    
    private $source;
    private $collectionsIn = NULL;
    
	private $resolution = NULL;
	private $fileSize = 0;
	private $fingerprintColours = '';
	
    function __construct($picLink = NULL){
        $this->getLinkInfo($picLink);   
        $this->setAsPictureSite();
        
        if($this->status != 1)
            return;
        
        $this->loadPictureInfoFromDB();
        
        if(empty($this->dbLastQueryResult)){
            $this->setStatus(0);
            return;
        }

        if($this->dbLastQueryResult[0]->is_private == 1){
            $this->setStatus(0);
            return;
        }

        $this->loadCollectionConstructFromDB();

        if($this->titleToCheck !== NULL && $this->titleToCheck != $this->dbLastQueryResult[0]->link){
            $this->setStatus(2);
            $this->setLink();
            return;
        }
        
        $this->loadImageTags();
        $this->setPictureInfos();
        
        return;
    }
	public function getImageAltText(){
		return $this->imageAltText;
	}
    public function getCollectionsIn(){
        if($this->collectionsIn == NULL){
            $res = dbQueries::get()->allCollectionsOfImage( $this->IDToCheck );
            $this->collectionsIn = (isset($res[0]->contentID)) ? $res : [];
        }
        $object = [];
        foreach($this->collectionsIn as $val){
            $link = (!empty($val->link)) ? '/'.$val->link : '';    
            
            if($val->mediaIn == 0){
                if(($c = dbQueries::change()->updateMediaInOfContentID( $val->contentID )) > 0)
                    $val->mediaIn = $c;
            }
            
            $object[] = (object)[
                            'title' => $val->shortTitle,
                            'descr' => $val->mediaIn,
                            'link'  => '/c/'.$val->contentID.$link,
                            'thumb' => $val->thumbnailLink.'.jpg'
                        ];
        }
        
        return $object;
    }
    private $collectionsInRaw = NULL;
    public function getCollectionsInPictureData(){
        if($this->collectionsInRaw == NULL){
            $res = dbQueries::get()->allCollectionsOfImageRAW( $this->IDToCheck, (string)$this->thumbnail );
            $this->collectionsInRaw = (isset($res[0]->contentID)) ? $res : [];
        }
        $object = [];
        foreach($this->collectionsInRaw as $val){
            $link = (!empty($val->link)) ? '/'.$val->link : '';    
            if($val->mediaIn == 0){
                if(($c = dbQueries::change()->updateMediaInOfContentID( $val->contentID )) > 0)
                    $val->mediaIn = $c;
            }
            
            $object[] = (object)[
                            'title' => $val->shortTitle,
                            'descr' => $val->mediaIn,
                            'link'  => '/c/'.$val->contentID.$link,
                            'thumb' => $val->thumbnailLink.'.jpg'
                        ];
        }
        return $object;
    }
    public function getSource(){
        return $this->source;
    }
    public function getUserID(){
        return $this->userID;
    }
    public function isPicture(){
        return $this->isPicture;
    }
    public function getTitle(){
        return $this->title;
    }
    public function getLinkStored(){
        return $this->linkStored;
    }
    public function getLinkOfPictureSite(){
        return $this->link;
    }
    public function getLinkHot(){
        return PIC_HOST.'/'.$this->linkStoredDBEntry.".".$this->mime;
    }
    public function getLinkHotThumb(){
        return THUMB_HOST.'/'.$this->hash.".".$this->mime;
    }
    public function getLinkHotThumbMed(){
        if($this->mime == 'jpeg')
            $this->mime = 'jpg';
        
        return THUMB_HOST.'/'.$this->linkStoredDBEntry."-med.".$this->mime;
    }
    public function getErrors(){
        return $this->errors;
    }
    
    public function getTags(){
        return $this->tags;
    }
    
    public function getDescription(){
        return $this->description;
    }
    
    public function isAdult(){
        return $this->isAdult;
    }
    
    public function isPrivate(){
        return $this->isPrivate;
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
    
    private function setAsPictureSite(){
        $this->isPictureSite = TRUE;
    }
    
    private function setStatus($int = 0){
        $this->status = $int;
    }
    private function loadCollectionConstructFromDB(){
        $dataBaseMatch      = DBTableNameMatchElements;
        $databaseNameAll    = DBTableNameSrcImages;
        $databaseNameText   = DBTableNameSrcText;
        $databaseNameSrc    = DBTableNameSrcOrigin;
        $databaseFingerPrint= DBTableNameMetaImageFingerprints;
		$databaseMetaStats	= DBTableNameMetaImageStats;
		    
        $sql = new dbObj();
        
        $sql->setTypeSELECT();
        
        $sql->setDatabase($dataBaseMatch);
        $sql->setDatabase($databaseNameAll);
        $sql->setDatabase($databaseNameText);
        $sql->setDatabase($databaseNameSrc);
		$sql->setDatabase($databaseFingerPrint);
		$sql->setDatabase($databaseMetaStats);

        $sql->setSELECTField('linkStored', $databaseNameAll);
        $sql->setSELECTField('imageID', $databaseNameAll);
        $sql->setSELECTField('dimensionX', $databaseNameAll);
        $sql->setSELECTField('dimensionY', $databaseNameAll);
        $sql->setSELECTField('linkFilename', $databaseNameAll);
        $sql->setSELECTField('is_adult', $databaseNameAll);
        $sql->setSELECTField('is_private', $databaseNameAll);
        $sql->setSELECTField('userID', $databaseNameAll);
        $sql->setSELECTField('mime', $databaseNameAll);
        $sql->setSELECTField('text', $databaseNameText);
        $sql->setSELECTField('type', $databaseNameText);
        $sql->setSELECTField('src', $databaseNameSrc);
        $sql->setSELECTField('hoster', $databaseNameSrc);
        
		$sql->setSELECTField('colourDistributionHEX', $databaseFingerPrint);
		
		$sql->setSELECTField('resX', $databaseMetaStats);
		$sql->setSELECTField('resY', $databaseMetaStats);
		$sql->setSELECTField('size', $databaseMetaStats);
		
        $sql->setConditionStringEqual('contentID', $this->IDToCheck, $dataBaseMatch);
        
        $sql->setDBonLeftJoinEqualToColumn('imageID', $databaseNameAll,'imageID', $dataBaseMatch);
        $sql->setDBonLeftJoinEqualToColumn('textID', $databaseNameText,'textID', $dataBaseMatch);
        $sql->setDBonLeftJoinEqualToColumn('srcID', $databaseNameSrc,'imageID', $databaseNameAll);
        
		$sql->setDBonLeftJoinEqualToColumn('imageID', $databaseFingerPrint, 'imageID', DBTableNameSrcImages);
		$sql->setDBonLeftJoinEqualToColumn('imageID', $databaseMetaStats, 'imageID', DBTableNameSrcImages);
		
        $this->dbLastQueryAllImagesResult = db::query($sql);
        
        if(is_array($this->dbLastQueryAllImagesResult)){
        	/*if(isset($_GET['abc'])){
        		print_r($this->dbLastQueryAllImagesResult);
				
        	}	*/
        	
            foreach($this->dbLastQueryAllImagesResult as $val){
                if(isset($val->linkStored)){
					if(empty($val->colourDistributionHEX)){
                        //echo (int)$this->dbLastQueryAllImagesResult[0]->imageID;
                        $call = new asynchronCall();
						$url = 'https://creezi.com/picMeNow';
						$call->simplePOSTCall($url, ['id'=>((int)$this->dbLastQueryAllImagesResult[0]->imageID)]);
		
		        	} else {
		        		$this->resolution = ['x' => $val->resX, 'y' => $val->resY];
						$this->fileSize = $val->size;
						$this->fingerprintColours = $val->colourDistributionHEX;
		        	}	
					
                    $this->linkStoredDBEntry = $val->linkStored;
                    $this->mime = $val->mime;
                    $this->linkStoredDBFileName = $val->linkFilename;
                    $this->linkStored = uploadDirRoot.'/'.uploadDirImages.'/'.$this->linkStoredDBEntry.'.'.$val->mime;
                    $this->dimension = array($val->dimensionX, $val->dimensionY);
                    $this->isPrivate = (bool)$val->is_private;
                    $this->isAdult = (bool)$val->is_adult;
                    $this->imageID = $val->imageID;
                    if(strlen($val->hoster) > 3){
                        $hoster = explode('.',$val->hoster);
                        $c = count($hoster);
                        if($c > 1){
                            $hoster = $hoster[$c-2].'.'.$hoster[$c-1];
                        } else {
                            $hoster = $c[0];
                        }
                        $this->source = [$val->src, $hoster];
                    }
                                        
                }
                if($val->type == 'td'){
                    $this->description = $val->text;
                }
				if($val->type == 'ta'){
                    $this->imageAltText = $val->text;
                }
            }
        }
    }
	public function getResolution(){
		return $this->resolution;
	}
	public function getFileSizeKB(){
		return ((int)($this->fileSize/102.4)/10);
	}
	public function getColourFingerPrintString(){
		$kernel = 2;
		$temp = str_split($this->fingerprintColours, 6);
		$s = '<div class="xcColourCircle xcColourPrint xcColourFoot">';
		
		foreach($temp as $val){
			$s .= '<p class="xcColourPoint" style="background-color:#'.$val.'">&nbsp;</p>';			
		}
		
		$s .= '</div>';
		return $s;
	}
    private function loadPictureInfoFromDB(){
        $databaseUser       = DBTableNameUser;
        $databaseName       = DBTableNameContentAll;
        
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
        $sql->setSELECTField('thumbnailLink', $databaseName);
        
        $sql->setSELECTField('is_private', $databaseName);
        $sql->setSELECTField('is_adult', $databaseName);
        
        $sql->setSELECTField('nick', $databaseUser);
        $sql->setSELECTField('avatarHTML', $databaseUser);
        $sql->setSELECTField('userURL', $databaseUser);
                
        $sql->setConditionStringEqual('contentID', $this->IDToCheck, $databaseName);
        $sql->setConditionStringEqual('type', 'i', $databaseName, 'AND');
        
        $sql->setDBonLeftJoinEqualToColumn('ID', $databaseUser, 'userID', $databaseName);
				        
        //echo $sql->getQueryString();
        
        $this->dbLastQueryResult = db::query($sql);
    }
    
    public function getStatus(){
        return $this->status;
    }
    
    private function setLinkCollection(){
        $this->link = HTTP_HOST.'/'.LINK_collectionPageSingle.'/'.$this->dbLastQueryResult[0]->inCollectionColID.'/'.$this->dbLastQueryResult[0]->colLink;
    }
    private function setLink(){
        $this->link = HTTP_HOST.'/'.LINK_imagePageSingle.'/'.$this->IDToCheck.'/'.$this->dbLastQueryResult[0]->link;
    }
    private function loadImageTags(){
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
                $this->tags[] = array($tag->label, $tag->tagLinkS);
            }
            //print_r($this);
            //print_r($this->tags);
        }// else echo $sql->getQueryString();
    }
    private function setPictureInfos(){
        $this->setLink();
        
        $this->hash = NULL;
        $this->title = str_replace('_','',$this->dbLastQueryResult[0]->title);
        
        //$this->linkStoredDBEntry = $this->dbLastQueryResult[0]->linkStored;
        // = $this->dbLastQueryResult[0]->linkFilename;
        
        if($this->isThumbnail)
            $this->linkStored = uploadDirRoot.'/'.uploadDirThumbnails.'/'.$this->dbLastQueryResult[0]->linkStored.'/'.$this->dbLastQueryResult[0]->hash.'.'.$this->dbLastQueryResult[0]->mime;
        
        //$this->tags = array();
        $this->user = $this->dbLastQueryResult[0]->userID;
        $this->categories = array();
        $this->views = $this->dbLastQueryResult[0]->views;
        $this->unixDate = $this->dbLastQueryResult[0]->dateCreated;
        
        $this->voteDown = 0;
        $this->voteUp = 0;
        $this->score = 100;
        
        // $this->relatedItemsArray = core::getJSONasObject($this->dbLastQueryResult[0]->relatedItems);
        // $this->relatedItemsUpdate = $this->dbLastQueryResult[0]->relatedItemsUpdate;
        
        $this->userURL = $this->dbLastQueryResult[0]->userURL;
        $this->userAvatar = $this->dbLastQueryResult[0]->avatarHTML;
        $this->nick = $this->dbLastQueryResult[0]->nick;
        $this->userID = $this->dbLastQueryResult[0]->userID;
        
        $this->isPrivate = (bool)$this->dbLastQueryResult[0]->is_private;
        $this->isAdult = (bool)$this->dbLastQueryResult[0]->is_adult;
        $this->thumbnail = $this->dbLastQueryResult[0]->thumbnailLink;
        
        //if($this->isPictureSite)
            //$this->mime = $this->dbLastQueryResult[0]->mime;
        
        $dbl = [];
        foreach($this->dbLastQueryResult as $val){
            if(empty($val->label) || empty($val->tagLinkS) || isset($dbl[trim($val->label)]))
                continue;   
            $this->tags[] = array($val->label, $val->tagLinkS);
            $dbl[trim($val->label)] = TRUE;
        }
    }
    public function getContentID(){
        return $this->IDToCheck;
    }
    public function getTagsAsString(){
        $string = '';
        foreach($this->tags as $val)
            $string .= $val[1].','; 
        return trim($string,' ,');
    }
    public function getTimeStampHumanReadable(){
        $date = new dateAndTime($this->unixDate);   
        return $date->getTimeDiffToNowAsString();
    }
    public function sendHeaderToBrowser(){
        $mime = 'Content-type: image/'.$this->mime; 
        header($mime);
    }

    public function sendFileToBrowser(){
        readfile($this->linkStored);
        exit;
    }
    
    public function getDimensionX(){
        return (int)$this->dimension[0];
    }
    
    public function getDimensionY(){
        return (int)$this->dimension[1];
    }
    public function getUserURL(){
        return $this->userURL;
    }
    public function getUserNick(){
        return $this->nick;
    }
    public function getUserAvatar(){
        return $this->userAvatar;
    }
    public function getRelatedItems(){
        $update = FALSE;
        
        if(!is_array($this->relatedItemsArray) && is_array($this->tags) && count($this->tags) > 0){
            $update = FALSE;

            $databaseName = DBTableNameContentMatchTags;

            $sql = new dbObj();
            $sql->setTypeSELECT();
            $sql->setDatabase($databaseName);
            
            $sql->setSELECTField('contentID', $databaseName);
            
            foreach($this->tags as $val)
                $sql->setConditionStringEqual('tagLinkS', $val[1], $databaseName, 'OR', 123);
            
            $sql->setLimit(5000);
            
            $res = db::query($sql);

            $matches = FALSE;
            if(isset($res[0])){
                $matches = [];
                $this->relatedItemsArray = [];  
                foreach($res as $val){
                    if($val->contentID == $this->IDToCheck)
                        continue;
                        
                    if(!isset($matches[$val->contentID])){
                        $matches[$val->contentID] = 0;
                        $this->relatedItemsArray[$val->contentID] = 0;
                    }
                    $matches[$val->contentID]++;
                    $this->relatedItemsArray[$val->contentID]++;
                }
                arsort( $matches );
                arsort( $this->relatedItemsArray );
                
                $z = 0;
                $databaseName = DBTableNameContentAll;
                $sql = new dbObj();
                $sql->setTypeSELECT();
                $sql->setDatabase($databaseName);
                
                $sql->setSELECTField('title', $databaseName);
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
                    }
                }
            } else
                $this->relatedItemsArray = NULL;
            
        }
        
        if(is_array($this->relatedItemsArray)){
            $n = 0;
            foreach($this->relatedItemsArray as $key => $val){
                if(!is_array($val) || $n > 25)
                    unset($this->relatedItemsArray[$key]);
                $n++;
            }
        }
        return $this->relatedItemsArray;
    }
    private function updateRelatedContent(){
        core::selectAsynchronRequest()->doUpdateRelatedContentItems($this->ID, 'images');
    }
}
class contentPictureType {
    private $ending;
    private $type;
    private $isMovie = false;
}

?>