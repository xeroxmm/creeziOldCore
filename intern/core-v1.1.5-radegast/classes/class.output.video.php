<?php
class contentVideoThumb{
	private $ID;
	private $link;
	private $storeDir;
	private $mime;
	private $title;
	private $dateCreated;
	private $titleShort;
	
	function __construct($array){
		if(!isset($array->dateCreated))
			return;
		
		$ix = base_convert($array->vidImageID, 36,10);
		
		$this->ID = $array->videoID;
		$this->link = $array->videoLink;
		$this->storeDir = ((int)($ix / 10000)).'/'.$ix;
		$this->mime = 'jpg';
		$this->title = $array->vidTitle;
		$this->titleShort = $array->shortTitle;
		$this->dateCreated = $array->dateCreated;
		$this->duration = $array->duration;
	}
	
	public function getLinkOfDeepSite(){
		return LINK_videoPageSingle;
	}
	public function getLinkOfElementSite(){
		return $this->ID.'/'.$this->link;
	}
	public function getLinkHot(){
		return THUMB_HOST.'/'.$this->storeDir.".".$this->mime;
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
	public function getDuration(){
		$time 	= $this->duration;
		$hour 	= (int)($time/3600);
		$min 	= (int)(($time - 3600 * $hour)/60);
		$sec 	= (int)($time - 3600 * $hour - 60 * $min);
		if($hour > 0)
			$hour .= ':';
		else
			$hour = '';
		
		if($min > 9)
			$min .= ':';
		else 
			$min = '0'.$min.':';
			
		if($sec < 10)
			$sec = '0'.$sec;
		
		return $hour.$min.$sec;
	}
}
class contentVideo {
	private $title;
	private $titleToCheck = NULL;
	private $ID;
	private $IDToCheck = NULL;
	private $url;
	private $views;
	private $unixDate;
	private $tags = array();
	private $user;
	private $categories = array();
	private $folders = array();
	private $duration;
	private $thumbnailURL;
	private $thumbnailID;
	private $comments;
	private $status = FALSE;
	private $playlist = [];
	
	private $isVideoSite = FALSE;
	private $videoID = 0;
	private $description = '';
	private $collectionsIn = NULL;
	private $dimension = [0,0];
	private $relatedItemsArray = NULL;
	private $hoster = '';
	private $hosterID = '';
	private $userID = 0;
	private $linkStoredDBEntry = 0;
	
	function __construct($vidLink = NULL){
		$this->getLinkInfo($vidLink);	
		$this->setAsVideoSite();
		
		if($this->status != 1)
			return;
		
		$this->loadVideoInfoFromDBStrikt();
				
		if(empty($this->dbLastQueryResult)){
			$this->setStatus(0);
			return;
		}
		
		$this->loadCollectionConstructFromDB();
		
		if($this->titleToCheck !== NULL && $this->titleToCheck != $this->dbLastQueryResult[0]->link){
			$this->setStatus(2);
			$this->setLink();
			return;
		}

		$this->loadVideoTagsFromDB();
		$this->setVideoInfos();

		return;
	}
	public function getLinkHotThumbMed(){
        return THUMB_HOST.'/'.$this->linkStoredDBEntry."-med.jpg";
    }
	public function getDuration(){
		return $this->duration;
	}
	public function getDurationString(){
		$h = (int)($this->duration / 3600);
		$min = (int)(($this->duration - ($h*3600)+0.1)/60);
		$sec = (int)($this->duration - ($h*3600) - ($min*60) + 0.1);
		
		$s = '';
		if($h > 0){
			$s .= $h." h ".sprintf('%02d',$min).' min';	
		} else
			$s = $min.' min';
		
		return $s;
	}
	public function getSource(){
		switch($this->hoster){
			case 'youtube':
				return 'https://www.youtube.com/watch?v='.$this->hosterID;
				break;
		}
	}
	public function getEmbedString(){
		switch($this->hoster){
			case 'youtube':
				$s = '<iframe width="700" height="394" src="https://www.youtube.com/embed/'.$this->hosterID.'" frameborder="0" allowfullscreen></iframe>';
				return $s;
				break;
		}
	}
	public function getImageAltText(){
		return '';
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
                            $this->relatedItemsArray[$val->contentID] = [$val->contentID, $val->title, $val->link, $val->thumbnailLink,$val->type];
                        
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
	public function getResolution(){
		return $this->resolution;
	}
	private function loadCollectionConstructFromDB(){
		$dataBaseMatch      = DBTableNameMatchElements;
        $databaseNameAll    = DBTableNameSrcVideos;
        $databaseNameText   = DBTableNameSrcText;
		    
        $sql = new dbObj();
        
        $sql->setTypeSELECT();
        
        $sql->setDatabase($dataBaseMatch);
        $sql->setDatabase($databaseNameAll);
        $sql->setDatabase($databaseNameText);

        $sql->setSELECTField('duration', $databaseNameAll);
        $sql->setSELECTField('videoID', $databaseNameAll);
        $sql->setSELECTField('dimX', $databaseNameAll);
        $sql->setSELECTField('dimY', $databaseNameAll);
        $sql->setSELECTField('channel', $databaseNameAll);
        $sql->setSELECTField('is_adult', $databaseNameAll);
        $sql->setSELECTField('is_private', $databaseNameAll);
        $sql->setSELECTField('userID', $databaseNameAll);
        $sql->setSELECTField('hoster', $databaseNameAll);
		$sql->setSELECTField('specificHosterID', $databaseNameAll);
        $sql->setSELECTField('text', $databaseNameText);
        $sql->setSELECTField('type', $databaseNameText);

        $sql->setConditionStringEqual('contentID', $this->IDToCheck, $dataBaseMatch);
        
        $sql->setDBonLeftJoinEqualToColumn('videoID', $databaseNameAll,'videoID', $dataBaseMatch);
        $sql->setDBonLeftJoinEqualToColumn('textID', $databaseNameText,'textID', $dataBaseMatch);

        $this->dbLastQueryAllImagesResult = db::query($sql);
        //print_r($this->dbLastQueryAllImagesResult);
        if(is_array($this->dbLastQueryAllImagesResult)){        	
            foreach($this->dbLastQueryAllImagesResult as $val){
                if(isset($val->hoster)){
	        		$this->resolution = ['x' => $val->dimX, 'y' => $val->dimY];

                    $this->dimension = array($val->dimX, $val->dimY);
                    $this->isPrivate = (bool)$val->is_private;
                    $this->isAdult = (bool)$val->is_adult;
                    $this->imageID = $val->videoID;
                    $this->hoster = $val->hoster;
					$this->hosterID = $val->specificHosterID;
					$this->duration = $val->duration;
					$this->channel = $val->channel;
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
	public function getStatus(){
		return $this->status;
	}
	public function getLinkOfVideoSite(){
		return $this->link;
	}
	public function getVideoThumbMed(){
		return $this->link;
	}
	/*public function getLinkHotThumbMed(){
		return PIC_HOST.'/'.$this->thumbnailURL.'-med.'.$this->thumbnailMime;
	}*/
	public function getDescription(){
		return $this->description;
	}
	
	public function getPlayList(){
		$string = NULL;	
		if(count($this->playlist) > 0){
			$z = 0;
			foreach($this->playlist as $val){
				$min = (int)(((int)$val[0])/60);
				$sec = ((int)$val[0])-($min*60);
				
				if(strlen($val[2]) > 2){
					$textString = '<p class="playlist actName"><a href="'.HTTP_HOST.'/'.LINK_searchPage.'/act/'.security::getNormalizedLinkURL($val[2]).'">'.$val[2].'</a></p><p> - </p><p class="playlist songName"><a href="'.HTTP_HOST.'/'.LINK_searchPage.'/track/'.urlencode($val[3]).'">'.$val[3].'</a></p>';
				} else
					$textString = '<p>'.$val[1].'</p>';
				
				if($min < 10)
					$min = '0'.$min;
				if($sec < 10)
					$sec = '0'.$sec;
				$z++;
				$string .= '<div class="playlistentrie"><p class="playlist timeStamp"><a id="fire-'.$z.'" class="fireStarter" href="'.HTTP_HOST.'/'.LINK_videoPageSingle.'/'.$this->ID.'?t='.((int)$val[0]).'" onclick="player.seekTo('.((int)$val[0]).', true);" data-timestamp="'.((int)$val[0]).'" data-href="'.HTTP_HOST.'/'.LINK_videoPageSingle.'/'.$this->ID.'?t='.((int)$val[0]).'" data-title="'.$this->title.' - Time: '.((int)$val[0]).'"  rel="noindex, follow">'.$min.':'.$sec.'</a></p><p>&raquo;</p>'.$textString.'</div>';
			}
		}
		return $string;
	}
	public function isAdult(){
		return $this->isAdult;
	}
	
	public function isPrivate(){
		return $this->isPrivate;
	}
	public function getTitle(){
		return $this->title;
	}
	public function getTags(){
		return $this->tags;
	}
	public function getDimensionX(){
		return (int)$this->dimension[0];
	}
	
	public function getDimensionY(){
		return (int)$this->dimension[1];
	}
	public function getVideoID(){
		return $this->ID;
	}
	public function getVideoIDExtern(){
		return $this->hosterID;
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
	private function setLink(){
		$this->link = HTTP_HOST.'/'.LINK_videoPageSingle.'/'.$this->dbLastQueryResult[0]->contentID.'/'.$this->dbLastQueryResult[0]->link;
	}
	private function setAsVideoSite(){
		$this->isVideoSite = TRUE;
	}
	private function loadVideoTagsFromDB(){
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
        
        $this->dbLastQueryResultTags = db::query($sql);

        if(isset($this->dbLastQueryResultTags[0]->counter)){
            foreach($this->dbLastQueryResultTags as $tag){
                $this->tags[] = array($tag->label, $tag->tagLinkS);
            }
        }
	}
	private function setVideoPlaylistInfo(){
		if(!isset($this->dbLastQueryResult[0]->timeStampSec))
			return;

		foreach($this->dbLastQueryResult as $val){
			$this->playlist[] = array(
				$val->timeStampSec,
				$val->fullString,
				$val->firstPart,
				$val->secondPart
			);
		}
	}
	private function loadVideoPlaylistFromDB(){
		$databaseName 		= DBTableNameContentVideosPlaylist;
		
		$sql = new dbObj();
		$sql->setTypeSELECT();
		$sql->setDatabase($databaseName);
		
		$sql->setSELECTField('timeStampSec', $databaseName);
		$sql->setSELECTField('fullString', $databaseName);
		$sql->setSELECTField('firstPart', $databaseName);
		$sql->setSELECTField('secondPart', $databaseName);
		
		$sql->setConditionStringEqual('videoID', $this->IDToCheck, $databaseName);
		
		$sql->setOrderByField('timeStampSec', $databaseName, TRUE);
		
		$this->dbLastQueryResult = db::query($sql);
	}
	
	private function setVideoTagsInfo(){
		if(!isset($this->dbLastQueryResult[0]->tagLinkS))
			return;
		foreach($this->dbLastQueryResult as $val){
			$this->tags[] = array($val->label, $val->tagLinkS);
		}
	}
	
	private function loadVideoInfoFromDBStrikt(){
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
        $sql->setConditionStringEqual('type', 'v', $databaseName, 'AND');
        
        $sql->setDBonLeftJoinEqualToColumn('ID', $databaseUser, 'userID', $databaseName);
				        
        //echo $sql->getQueryString();
        
        $this->dbLastQueryResult = db::query($sql);
		$this->videoID = $this->dbLastQueryResult[0]->contentID;
		$this->userID = $this->dbLastQueryResult[0]->userID;
		$this->linkStoredDBEntry = $this->dbLastQueryResult[0]->thumbnailLink;
	}

	private function setVideoInfos(){
		$this->setLink();
        
        $this->hash = NULL;
        $this->title = str_replace('_','',$this->dbLastQueryResult[0]->title);

        //if($this->isThumbnail)
        //    $this->linkStored = uploadDirRoot.'/'.uploadDirThumbnails.'/'.$this->dbLastQueryResult[0]->linkStored.'/'.$this->dbLastQueryResult[0]->hash.'.'.$this->dbLastQueryResult[0]->mime;
        
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
        /**
        $dbl = [];
        foreach($this->dbLastQueryResult as $val){
            if(empty($val->label) || empty($val->tagLinkS) || isset($dbl[trim($val->label)]))
                continue;   
            $this->tags[] = array($val->label, $val->tagLinkS);
            $dbl[trim($val->label)] = TRUE;
        }*/
	}
	
	private function loadVideoInfoFromDB(){
		$databaseName 		= DBTableNameContentVideos;
		$dataBaseTags 		= DBTableNameContentTag;
		$dataBaseTagMatch 	= DBTableNameContentVideosTagsMatch;
		$databaseUser 		= DBTableNameContentUser;
		$databasePlayList	= DBTableNameContentVideosPlaylist;
			
		$sql = new dbObj();
		
		$sql->setTypeSELECT();
		
		$sql->setDatabase($databaseName);
		$sql->setDatabase($dataBaseTagMatch);
		$sql->setDatabase($dataBaseTags);
		$sql->setDatabase($databaseUser);
		$sql->setDatabase($databasePlayList);
		
		$sql->setSELECTField('videoID', $databaseName);
		$sql->setSELECTField('imageID', $databaseName);
		$sql->setSELECTField('title', $databaseName);
		$sql->setSELECTField('link', $databaseName);
		$sql->setSELECTField('userID', $databaseName);
		
		$sql->setSELECTField('views', $databaseName);
		$sql->setSELECTField('dateCreated', $databaseName);
		$sql->setSELECTField('votesUp', $databaseName);
		$sql->setSELECTField('votesDown', $databaseName);
		$sql->setSELECTField('score', $databaseName);
		$sql->setSELECTField('private', $databaseName);
		$sql->setSELECTField('linkStored', $databaseName);
		$sql->setSELECTField('linkFilename', $databaseName);
		$sql->setSELECTField('scoreReported', $databaseName);
		
		$sql->setSELECTField('isExtern', $databaseName);
		$sql->setSELECTField('externSource', $databaseName);
		$sql->setSELECTField('externID', $databaseName);
		$sql->setSELECTField('externChanelID', $databaseName);
		$sql->setSELECTField('externEmbedURL', $databaseName);
		
		$sql->setSELECTField('isAdult', $databaseName);
		
		$sql->setSELECTField('comments', $databaseName);
		$sql->setSELECTField('hash', $databaseName);
		
		$sql->setSELECTField('width', $databaseName);
		$sql->setSELECTField('height', $databaseName);
		$sql->setSELECTField('description', $databaseName);
		$sql->setSELECTField('duration', $databaseName);
		$sql->setSELECTField('genre', $databaseName);
		
		$sql->setSELECTField('nick', $databaseUser);
		$sql->setSELECTField('avatarHTML', $databaseUser);
		$sql->setSELECTField('userURL', $databaseUser);
		
		$sql->setSELECTField('label', $dataBaseTags);
		$sql->setSELECTField('tagLinkS', $dataBaseTags);
		$sql->setSELECTField('counter', $dataBaseTags);
		
		$sql->setConditionStringEqual('videoID', $this->IDToCheck, $databaseName);
		
		$sql->setDBonLeftJoinEqualToColumn('videoID', $dataBaseTagMatch, 'videoID', $databaseName);
		$sql->setDBonLeftJoinEqualToColumn('tagLinkS', $dataBaseTags, 'tagLink', $dataBaseTagMatch);
		$sql->setDBonLeftJoinEqualToColumn('ID', $databaseUser, 'userID', $databaseName);
		$sql->setDBonLeftJoinEqualToColumn('videoID', $databasePlayList, 'videoID', $databaseName);
		
		$sql->setOrderByField('counter', $dataBaseTags, FALSE);
		
		echo $sql->getQueryString();
		die();
		$this->dbLastQueryResult = db::query($sql);
	}

	private function setStatus($int = 0){
		$this->status = $int;
	}
}
?>