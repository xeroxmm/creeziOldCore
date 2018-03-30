<?php
class upload {
	private static $lastUpload = NULL;
	private static $errors = array();
	private static $confirmedUploadInfoString = NULL;
	
	public static function isPOSTRequest(){
		//$_POST['title'] = 'TEST Bild';
		if(!isset($_GET['do']) || !isset($_POST['aUpload']))
			return FALSE;
		
		$_POST['title'] = "unset";
		return TRUE;
	}
	
	public static function getLastErrorArray(){
		return self::$errors;
	}
	
	public static function getLastUpdateInfoString(){
		return self::$confirmedUploadInfoString;
	}
	
	public static function setLastUpdateValue($string){
		self::$confirmedUploadInfoString = $string;
	}
	
	public static function getcontentID($type){
		// Build Thumbnail Entrie
		if($type == 'image')
			$DBNameList = 'content_images_IDList';
		else if($type == 'video')
			$DBNameList = 'content_videos_IDList';
		else if($type == 'collection')
			$DBNameList = 'content_collections_IDList';
		else
			die('not such content type');
		
		$sql = new dbObj();
		$sql->presetGetNextAutoIncrementValueOfTable($DBNameList);
		
		$res = db::query($sql);
		
		if(!isset($res[0]->nextID)){
			echo "next value not set " . print_r($res,TRUE);
			echo $sql->getQueryString();	
			return '0';
		}

		$databaseID = base_convert($res[0]->nextID, 10, 36);
		$imageID = $databaseID;

		$v = 1;
		if($databaseID == 'thumb'){
			$databaseID = base_convert($res[0]->nextID + $v, 10, 36);
			$imageID = $databaseID;
			$v++;
		}
		
		$do = true; $i = 0; $errorI = false;
		$databaseName = $DBNameList;
		while( $do ){
			$sql = new dbObj();
			$sql->setTypeINSERT();
			$sql->setDatabase($databaseName);
			$sql->setInsertFieldValueNULL('ID', $databaseName);

			$sql->setInsertFieldValueString('ident', $imageID, $databaseName);
			
			if(db::query($sql)){
				$do = false;

			} else if($i > 10){
				$do = false;
				$errorI = true;
				
				return 0;
			} else {
				$databaseID = base_convert(((int)$res[0]->nextID) + $v, 10, 36);
				$imageID = $databaseID;
				$v++;
			}
				
			$i++;
		}

		return $imageID;
	}
	
	public static function checkAndDoUploadRequest($type = 0){
		$type = (int)$type;
		$_POST['folderName'] = 'new-upload';
		$_POST['category'] = 'empty-category';
		
		if(!isset($_POST['aUpload']) || (int)$_POST['aUpload'] == 0){
			self::$errors[] = 'aUpload';	
			return FALSE;
		}
		
		if(!security::isLoggedIn() && uploadOnlyLoggedIn){
			self::$errors[] = 'login';	
			return FALSE;
		}
		
		if(!isset($_POST['folderName'])){
			self::$errors[] = 'folderName';	
			return FALSE;
		}
		
		if(!isset($_POST['category'])){
			self::$errors[] = 'categorieName';	
			return FALSE;
		}
		
		$crawl = false;
		switch((int)$_POST['aUpload']){
			case 3:
				if(!isset($_POST['do']) || !isset($_POST['val']) || !isset($_POST['aText'])){
					self::$errors[] = 'T3 - PostOne';	
					return FALSE;
				}
				$do = (int)$_POST['do'];
				$value = filter_var($_POST['val'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW );
				
				switch($do){
					case 1:
						if(strlen($value) < 2){
							self::$errors[] = 'T3 -StrLen';	
							return FALSE;
						}
						if(uploadImageInformation::isImageEditable($_POST['aText'])){
							if(uploadImageInformation::changeImageInformationTitle($_POST['aText'], $value)){
								self::$confirmedUploadInfoString = ($value);
								return TRUE;
							} else {
								self::$errors[] = 'T3 - changeInfo';
								return FALSE;
							}	
						}
						self::$errors[] = 'T3 - imageEdit';
						return FALSE;
						break;
						// TITLE	
					case 2:	
						// Split up the tag string
						
						$tagString = security::getTagStringHarmonized($value);
						$tagArray = security::getTagStringArrayHarmonized($tagString);
						
						if(count($tagArray) < 1){
							self::$errors[] = 'T2 - tagCount';
							return FALSE;
						}
						
						if(uploadImageInformation::isImageEditable($_POST['aText'])){
							if(uploadImageInformation::changeImageInformationTags($_POST['aText'], $tagArray)){
								$tagString = implode(',',$tagArray);
								self::$confirmedUploadInfoString = $tagString;
								return TRUE;
							} else {
								self::$errors[] = 'T2 - changeTags';
								return FALSE;
							}
						}
						self::$errors[] = 'T2 - imageEdit';
						return FALSE;
						break;
						// TAGS
					case 3:
						return uploadImageInformation::changeImageInformationDescription($_POST['aText'], $_POST['val']);
						break;
						// DESCRIPTION	
					case 4:
						$res = uploadImageInformation::changeImageStatusToPublished($_POST['val']);
						
						if(count($res) > 0){
							$imageIDs = '';	
							foreach($res as $val){
								$imageIDs .= $val[0].',';
							}	
							$imageIDs = substr($imageIDs, 0,-1);
							
							self::$confirmedUploadInfoString = $imageIDs;
							return TRUE;
						}
						
						self::$errors[] = 'T4 - Default - '.$_POST['val'];
						return FALSE;
						break;
					case 0:
					default: 
						self::$errors[] = 'T3 - Default';
						return FALSE;
						break;
				}
				
				$value = parse_str($str);
				break;	
			case 2:
				if(!isset($_POST['url']) || empty($_POST['url'])){
					self::$errors[] = 'url of extern';		
					return FALSE;
				}

				$picS = new crawlImage($_POST['url']);
				$_FILES['file'] = $picS->getLastImageAsTempFile();
				$picS->deleteLastImage();
				$crawl = true;
			case 1:
				$picUp = new uploadImage($crawl);
				
				if(!$picUp->getStatus()){
					self::$errors[] = 'status -> '.implode(',',$picUp->getErrors());	
					return FALSE;
				}
				if(!$picUp->getDone()){
					self::$errors[] = 'statusDone -> '.implode(',',$picUp->getErrors());	
					return FALSE;
				}
				
				$yt = false;
				if(isset($picS))
					$yt = $picS->getYoutubeObj();
				
				if(!$picUp->process($yt)){
					self::$errors[] = 'process -> '.implode(',',$picUp->getErrors());
					return FALSE;
				}
				
				if(isset($picS) && $picS->isYoutubePost())
					$picS->getYoutubeObj()->buildDBEntries($picUp->getImageID());
				
				
				if(($newContentID = contentCreation::getItemToLibraryListID()) === FALSE){
					self::$errors[] = 'cant create contentID';	
					continue;
				}
				
				$picUp->setLinkURL($newContentID);
				
				$cItem = new cCreation();
					$cItem->setUserID( user::getDBIDCloaked());
					$cItem->setType('i');
					$cItem->setContentID( $newContentID );
					$cItem->setSrcID( $picUp->getImageID() );
					$cItem->setTitle( '' );
					$cItem->setLink( security::getNormalizedLinkURL( '' ));
					$cItem->setShortTitle( uploadSanitizer::getStringShortend( '' ));
					$cItem->setIsPrivate( 0 );
					$cItem->setIsAdult( 0 );
					$cItem->setThumbLink( $picUp->getThumbStoreURL() );
					//$cItem->setCountElements();

				if(!$lastLibraryID = contentCreation::createLibraryContentPost( $cItem )){
					// set newly created contentID to is_deleted == 1
					dbQueries::delete()->libraryListIDEntrie( $cItem->getContentID() );
					self::$errors[] = 'cant create libraryID';
					continue;
				}
				/*
					// Add item to searcable Content Images
						if(!$hidden && !dbQueries::add()->libraryElementToSearch_contentImages($lastLibraryID))
							self::$errors[] = 'no add to searchable content';
					
					
					// Add item to searchable content All 
						if(!$hidden)				
							dbQueries::add()->libraryElementToSearch_contentAll($lastLibraryID);
				*/
				// Add item to matchElementsTable
					if(!dbQueries::add()->elementToMatchItems($newContentID, [(object)['type' => 'si', 'contentID' => $newContentID,'imageID' => $picUp->getImageID(), 'ID' => $picUp->getLastIDContentLibrary()]]))
						self::$errors[] = 'no add to matchElementsTable';
	
				// Add item to sourceTable
					$src = NULL;
					if(isset($_POST['url']) && stristr($_POST['url'], '86.106.113.42') === FALSE){
						$src = $_POST['url'];
						
						if(!dbQueries::add()->elementToSrcTable((object)['type' => 'web', 'userID' => user::getDBIDCloaked(), 'srcID' => $picUp->getImageID(), 'src' => $src]))
							self::$errors[] = 'no add to sourceTable';
					}

				if(isset($_POST['colID']))	
					dbQueries::add()->elementToMatchItems($_POST['colID'], [(object)['type' => 'i', 'contentID' => $newContentID, 'iStart' => 1]]);
				
				self::$lastUpload = $picUp;
				unset($picUp);

				return TRUE;
				break;
			case 0:
			default:
				self::$errors[] = 'aUploadCode';	
				
				return FALSE;
		}
	}
	public static function getLastUpload(){
		if(!is_a(self::$lastUpload, 'uploadImage'))	
			self::$lastUpload = new uploadImage(TRUE, TRUE);
		
		return self::$lastUpload;
	}
	public static function addErrorMsg($text,$hook){
		self::$errors[] = '>> '.$text.' by: '.$hook;
	}
}
class uploadVideoInformation {
	public static function changeInformationTitle($ID_DB, $insertValueString){
		$databaseName = DBTableNameContentVideos;
		$conditionValueString = $ID_DB;
		
		$sql = new dbObj();
		$sql->setTypeUPDATE();
		$sql->setDatabase($databaseName);
		$sql->setConditionStringEqual('videoID', $conditionValueString, $databaseName);
		$sql->setUpdatedFieldValueString('title', $insertValueString, $databaseName);
		$sql->setUpdatedFieldValueString('isNew', 0, $databaseName);

		if(!db::query($sql))
			return FALSE;
		
		$databaseName = DBTableNameContentAll;
		$sql = new dbObj();
		$sql->setTypeUPDATE();
		$sql->setDatabase( $databaseName );
		$sql->setConditionStringEqual('videoID', $conditionValueString, $databaseName);
		$sql->setUpdatedFieldValueString('isNew', 0, $databaseName);
		$sql->setUpdatedFieldValueString('shortTitle', uploadSanitizer::getStringShortend($insertValueString), $databaseName);

		return db::query($sql);
	}
	public static function changeInformationDescription($ID_DB, $description){
		$databaseName = DBTableNameContentVideos;
		$conditionValueString = $ID_DB;
		
		$insertValueString = $description;
		
		$sql = new dbObj();
		$sql->setTypeUPDATE();
		$sql->setDatabase($databaseName);
		$sql->setConditionStringEqual('videoID', $conditionValueString, $databaseName);
		$sql->setConditionIntegerEqual('userID', user::getDBIDCloaked(), $databaseName, 'AND');
		
		$sql->setUpdatedFieldValueString('description', $insertValueString, $databaseName);

		return db::query($sql);
	}
	public static function changeInformationTags($ID_DB, $tagArray){
		if(!is_array($tagArray)){
			upload::addErrorMsg( 'no Array' , 'changeTagArray' );
			return FALSE;
		}
		
		$databaseName = DBTableNameContentVideos;
		$conditionValueString = $ID_DB;
		
		$insertValueString = implode(',',$tagArray);
		
		$sql = new dbObj();
		$sql->setTypeUPDATE();
		$sql->setDatabase($databaseName);
		$sql->setConditionStringEqual('videoID', $conditionValueString, $databaseName);
		$sql->setUpdatedFieldValueString('tagString', $insertValueString, $databaseName);

		return db::query($sql);
	}
	public static function changeInformationComplete($ID_DB, $titleStringORnull, $tagStringORnull, $descriptionStringORnull){
		$databaseName = DBTableNameContentVideos;
		$conditionValueString = $imageID_DB;
		
		$sql = new dbObj();
		$sql->setTypeUPDATE();
		$sql->setDatabase($databaseName);
		
		$sql->setConditionStringEqual('videoID', $conditionValueString, $databaseName);
		$sql->setConditionIntegerEqual('userID', user::getDBIDCloaked(), $databaseName, 'AND');
		
		$sql->setUpdatedFieldValueString('isNew', 0, $databaseName);
		if(is_string($titleORnull)){
			$sql->setUpdatedFieldValueString('title', $insertValueString, $databaseName);
			$sql->setUpdatedFieldValueString('link', security::getNormalizedLinkURL($insertValueString), $databaseName);
			
			if( !self::changeImageInformationTitleAtMainDB($ID_DB, $titleORnull) )
				return FALSE;
		}
		if(is_string($tagsORnull)){
			$sql->setUpdatedFieldValueString('description', $insertValueString, $databaseName);
		}
		if(is_string($descriptionORnull)){
			$sql->setUpdatedFieldValueString('tagString', $insertValueString, $databaseName);
		}
		
		return db::query($sql);
	}
}
class uploadImageInformation {
	public static function getImageInformationDB($imageID_DB){
		$sql = new dbObj();
		
		$databaseName = 'content_images';
		$conditionValueString = $imageID_DB;

		$sql->setTypeSELECT();
		$sql->setDatabase($databaseName);
		$sql->setConditionStringEqual('imageID', $conditionValueString, $databaseName);
		$sql->setSELECTField('userID', $databaseName);
		$sql->setSELECTField('isNew', $databaseName);
		
		$res = db::query($sql);	

		return $res;
	}
	public static function isImageEditable($imageID_DB){
		$res = self::getImageInformationDB($imageID_DB);

		if(isset($res[0]->userID) && $res[0]->userID == user::getDBIDCloaked())
			return TRUE;
		else {
			upload::addErrorMsg('user: '.$res[0]->userID.' ? '.user::getDBIDCloaked(), 'isImageEdit');
			return FALSE;
		}
	}
	public static function changeImageInformationTitle($imageID_DB, $insertValueString){
		$databaseName = DBTableNameContentAll;
		$conditionValueString = $imageID_DB;
		
		$link = security::getNormalizedLinkURL($insertValueString);
		
		$sql = new dbObj();
		$sql->setTypeUPDATE();
		
		$sql->setDatabase($databaseName);
		
		$sql->setConditionStringEqual('contentID', $conditionValueString, $databaseName);
		$sql->setConditionStringEqual('type', 'i', $databaseName, 'AND');
		$sql->setUpdatedFieldValueString('title', $insertValueString, $databaseName);
		$sql->setUpdatedFieldValueInteger('isNew', 0, $databaseName);
		//$sql->setUpdatedFieldValueString('link', $link , $databaseName);

		return db::query($sql);		
	}

	public static function addImageInformationAPISource(cCreation $ob, $apiSource){
		$databaseName = DBTableNameSrcOrigin;
		
		$temp = str_replace('http://','',$apiSource);
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
		
		$sql->setConditionStringEqual('srcID', $ob->getSrcID(), $databaseName);
		$sql->setConditionIntegerEqual('userID', user::getDBIDCloaked(), $databaseName, 'AND');
		$sql->setInsertFieldValueString('src', $apiSource, $databaseName);
		$sql->setInsertFieldValueString('srcType', 'api', $databaseName);
		$sql->setInsertFieldValueDATE_ADD_TO_NOW('dateCreated', 0, $databaseName);
		
		if(!empty($hoster))
			$sql->setInsertFieldValueString('hoster', $hoster, $databaseName);
		if(!empty($file))
			$sql->setInsertFieldValueString('file', $file, $databaseName);

		return db::query($sql);
	}
	public static function changeImageInformationTags($imageID_DB, $tagArray){
		if(!is_array($tagArray)){
			upload::addErrorMsg( 'no Array' , 'changeTagArray' );
			return FALSE;
		}
		
		$databaseName = DBTableNameContentImages;;
		$conditionValueString = $imageID_DB;
		
		$insertValueString = implode(',',$tagArray);
		
		$sql = new dbObj();
		$sql->setTypeUPDATE();
		$sql->setDatabase($databaseName);
		$sql->setConditionStringEqual('imageID', $conditionValueString, $databaseName);
		$sql->setUpdatedFieldValueString('tagString', $insertValueString, $databaseName);

		return db::query($sql);
	}

	public static function changeImageInformationTitleAtMainDB(cCreation $ob, $titleString){
		$databaseName = DBTableNameContentAll;
		$sql = new dbObj();
		$sql->setTypeUPDATE();
		$sql->setDatabase( $databaseName );
		$sql->setConditionStringEqual('contentID', $ob->getContentID(), $databaseName);
		//$sql->setUpdatedFieldValueString('isNew', 0, $databaseName);
		$sql->setUpdatedFieldValueString('shortTitle', uploadSanitizer::getStringShortend($titleString), $databaseName);

		return db::query($sql);
	}

	public static function changeImageStatusToPublished(cCreation $ob, $imageArray){
		$completed = array();	
		$img = explode(',',$imageArray,999999);
		$array = array();
		foreach($img as $val){
			if(strlen($val) > 0)	
				$array[$val] = $val;
		}
		
		if(count($array) < 1)
			upload::addErrorMsg("Array Empty", 'imageUpload');
		
		$databaseName = DBTableNameContentAll;
		$userID = $ob->getUserID();
		
		foreach($array as $val){
			$conditionValueString = $val;	

			$sql = new dbObj();
			$sql->setTypeUPDATE();
			$sql->setDatabase($databaseName);
			$sql->setConditionStringEqual('imageID', $conditionValueString, $databaseName);
			$sql->setUpdatedFieldValueInteger('is_private', 0, $databaseName);
		
			if(db::query($sql))
				$completed[count($completed)] = array($conditionValueString);
		}
		
		return $completed;
	}
}
class uploadSanitizer {
	public static function getStringShortend($string){
		$i = 45;
		$string = str_replace(array("\n\r", "\n", "\r", "\t"), ' ', $string);
		$temp = explode(' ',$string, 999);
		$ret = ''; $z = 0;
		for($j = 0; $j < count($temp); $j++){
			$z += strlen($temp[$j])+1	;
			if($z < $i || count($temp) == 1)	
				$ret .= $temp[$j].' ';
			else {
				$ret .= '&hellip; ';
				break;
			}
		}
		$ret = substr($ret,0,-1);
		return $ret;
	}
	
}
class crawlYoutubeVideo {
	public $status = FALSE;
	
	public $id = NULL;
	public $officialVideoID = 0;
	public $officialName = '';
	public $officialDuration = 0;
	public $officialEmbedURL = '';
	public $officialGenres = '';
	public $officialTags = [];
	public $officialDimensions = ['x' => 0, 'y' => 0];
	public $officialDescription = NULL;
	public $officialTrackList = NULL;
	public $officialThumbimage = NULL;
	public $officialChanelID = NULL;
	
	public $lastCrawlContent = NULL;
	public $lastCrawlURL = NULL;
	public $lastCurlHTTPResponse = NULL;
	
	public $userID = 0;
	public $thumbnailLink = '0/1866';
	
	public function setUserID($ID){
		$this->userID = $ID;
	}
	
	function __construct( $id ){
		if($id === NULL)
			return;
		
		$url = 'https://www.youtube.com/watch?v='.$id;
		$header = [];
		$header[0] = "Accept: text/html,text/xml,application/xml,application/xhtml+xml"; 
		$header[0] .= ";q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5"; 
		$header[] = "Cache-Control: max-age=0"; 
		$header[] = "Connection: keep-alive"; 
		$header[] = "Keep-Alive: 20"; 
		$header[] = "Accept-Charset: utf-8;q=0.7,*;q=0.7"; 
		$header[] = "Accept-Language: en-us,en;q=0.5";
			
		$ch = curl_init ($url);	
		curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
		
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_ENCODING ,"");
		curl_setopt($ch,CURLOPT_BINARYTRANSFER,true);
	    
	    $this->lastCrawlContent = curl_exec($ch);
	    $this->lastCrawlURL = $url;
	    
		$this->lastCurlHTTPResponse = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
	    curl_close ($ch);
		
		$t = explode('</head>',$this->lastCrawlContent,2);
		$tHeader = explode('<meta ',$t[0],2);
		$tMedium = explode('itemtype="http://schema.org/VideoObject"',$t[1],2);
		$tMedium = explode('<h1 class="yt watch-title-container"',$tMedium[1],2);
		//print_r($this->lastCrawlContent);
		$this->tHeader = $tHeader[1];
		
		if(isset($tMedium[0]))
			$this->tMedium = $tMedium[0];
		
		$this->tLast = NULL;
		if(isset($tMedium[1]))
			$this->tLast = $tMedium[1];
		
		if($this->lastCurlHTTPResponse == 200)
			$this->status = TRUE;
		else
			return;
		
		if(!$this->loadVideoInfos())
			$this->status = FALSE;
	}
	
	public function buildDBEntries($videoID = 0){
		$this->videoID = $videoID;	

		$this->insertNewVideoIDtoLibrary();

		if(!$this->insertNewVideoID())
			return FALSE;
		
		//if(!$this->insertNewVideoIDtoLibrary())
		//	return FALSE;
		
		//$this->insertNewVideoPlaylist();
		$this->insertNewVideoTags();
		
		return TRUE;
	}
	
	public $shortTitle;
	private function addMediaToLibrary(){
		$databaseName = 'content_library';	
		$sql = new dbObj();
		$sql->setTypeINSERT();
		$sql->setDatabase($databaseName);
		
		$sql->setInsertFieldValueInteger('private', 0, $databaseName);
		$sql->setInsertFieldValueInteger('isNew', 0, $databaseName);
		$sql->setInsertFieldValueDATE_ADD_TO_NOW('dateCreated', 0, $databaseName); 
		$sql->setInsertFieldValueInteger('userID', user::getDBIDCloaked(), $databaseName);
		$sql->setInsertFieldValueString('videoID', $this->videoID, $databaseName);
		$sql->setInsertFieldValueString('shortTitle', uploadSanitizer::getStringShortend($this->officialName), $databaseName);
		
		return(db::query($sql));
	}
	
	public $videoID;
	public $linkURL;
	private function insertNewVideoID(){
		dbQueries::add()->newVideoToSourceTableByObject($this);
		dbQueries::change()->videoSrcInformationByObject($this);
	}
	
	private function insertNewVideoIDtoLibrary(){
		$this->contentID = contentCreation::getItemToLibraryListID();
	}
	
	private function insertNewVideoPlaylist(){
		$databaseName = 'content_videos_playlist';	
		
		$sql = new dbObj();
		$sql->setTypeINSERT();
		$sql->setDatabase($databaseName);
		
		foreach($this->officialTrackList as $val){
			$sql->setInsertFieldValueNULL('ID', $databaseName);
			$sql->setInsertFieldValueString('videoID', $this->videoID, $databaseName);
			$sql->setInsertFieldValueInteger('timeStampSec', $val['start'], $databaseName);
			$sql->setInsertFieldValueString('fullString', $val['name'], $databaseName);
			
			if(!isset($val['name1']) || $val['name1'] === NULL)
				$sql->setInsertFieldValueString('firstPart', NULL, $databaseName);
			else
				$sql->setInsertFieldValueString('firstPart', $val['name1'], $databaseName);
			if(!isset($val['name2']) || $val['name2'] === NULL)
				$sql->setInsertFieldValueString('secondPart', NULL, $databaseName);
			else
				$sql->setInsertFieldValueString('secondPart', $val['name2'], $databaseName);
		}

		db::query($sql);
	}
	public $lastTagLinks = [];
	private function insertNewVideoTags(){
		if(is_array($this->officialTags))
			foreach($this->officialTags as $key => $val){
				$this->officialTags[$key] = security::getTagNameHarmonized($val);
			}
		if(is_array($this->officialTags) && count($this->officialTags) > 0){
			contentCreation::addTagsToImage($this->contentID, $data->tags);
		}
	}

	private function insertNewVideoTagsMatch(){
		$sql = new dbObj();
		$databaseName = 'match_videos_tags';
		
		$sql->setTypeINSERT();
		$sql->setDatabase($databaseName);
		
		$tags = array();
		
		// TEST Folders
		$tags = $this->lastTagLinks;
		
		foreach($tags as $intTag){
			$link = str_replace(' ', '-', strtolower($intTag));
			
			$sql->setInsertFieldValueNULL('ID', $databaseName);
			$sql->setInsertFieldValueString('videoID', $this->videoID, $databaseName);
			$sql->setInsertFieldValueString('tagLink', $link, $databaseName);
			$sql->setInsertFieldValueString('hash', $this->videoID.'_'.$intTag, $databaseName);
		}
		
		db::query($sql);
	}

	public function getThumbnailURL(){
		return $this->officialThumbimage;
	}
	public function getID(){
		return $this->officialVideoID;
	}
	public $officialDescriptionNode;
	private function loadVideoInfos(){		
		$this->officialDescription = $this->getDescriptionOfCode();
		
	    $description = $this->getMetaPropertyContent($this->tHeader, 'og:description');
	    $this->officialVideoID = $this->getMetaItempropContent($this->tMedium, 'videoId');
	    
		$this->officialName = $this->getMetaPropertyContent($this->tHeader, 'og:title');
		$this->officialDuration = $this->getDurationInSeconds($this->getMetaItempropContent($this->tMedium, 'duration'));
		$this->officialEmbedURL = NULL;
		$this->officialGenres = $this->getMetaItempropContent($this->tMedium, 'genre');
		
		$this->officialTags = $this->getMetaTags($this->tHeader);
		
		$this->officialDimensions['x'] = $this->getMetaPropertyContent($this->tHeader, 'og:video:width');
		$this->officialDimensions['y'] = $this->getMetaPropertyContent($this->tHeader, 'og:video:height');
		$this->officialThumbimage = $this->getMetaPropertyContent($this->tHeader, 'og:image');
		$this->officialChanelID = $this->getMetaItempropContent($this->tMedium, 'channelId');

		//$this->officialTrackList = $this->getOfficialTracklistArray();
		
		return TRUE;
	}
	private function getDescriptionOfCode(){
		$t = explode('<p id="eow-description"', $this->tLast, 2);
		if(!isset($t[1]))
			return NULL;
		
		$tu = explode('</p>', $t[1],2);
		$f = explode('>', $tu[0],2);
		
		if(isset($f[1]))
			return $f[1];
		else
			return NULL;
	}
	private function getMetaTags($string){
		$regex = '#<meta property="og:video:tag" content="(.*?)">#s';	
		preg_match_all($regex, $string, $found);
		if(isset($found[1]))
			return $found[1];
		else 
			return NULL;
	}
	private function getMetaItempropContent($string, $name){
		$pattern = '/<meta itemprop="'.$name.'" content="(.*)">/';
	    preg_match($pattern, $string, $matches);
	    if(isset($matches[1]))
	    	return $matches[1];
		else
			return NULL;
	}

	private function getMetaPropertyContent($string, $name){
		$pattern = '/<meta property="'.$name.'" content="(.*)">/';
	    preg_match($pattern, $string, $matches);
	    if(isset($matches[1]))
	    	return $matches[1];
		else
			return NULL;
	}

	private function getOfficialTracklistArray(){			
		$tracklist = []; $z = 0;
		
		$track = array();
		$track['start'] = 0;
		$track['end'] = 0;
		$track['name'] = '';
		$track['name1'] = NULL;
		$track['name2'] = NULL;

		$innerHTML = str_replace(array('<br>','<br />'),'<br/>', $this->officialDescription);
		
		$codeSplit = explode('<br/>', $innerHTML, 9999);
		
		foreach($codeSplit as $val){
			if(strpos($val, 'yt.www.watch.player.seekTo(') !== FALSE){
				$fL = $this->getSecondsBySeekToString( $val );
				
				if(isset($fL[0]) && isset($fL[1]) && count($fL[0]) == count($fL[1]) && count($fL[1]) >= 1){
					$track = array();
					$track['start'] = 0;
					
					$timeT = explode(':',$fL[1][0],5);
					$timeT = array_reverse($timeT);
					
					$time = 0; $faktor = array(1 , 60 , 3600, 86400);
					for($i = 0; $i < count($timeT); $i++){
						$time += ((int)$timeT[$i])*$faktor[$i];
					}
					$track['start'] = $time;

					$track['end'] = 0;
					if(isset($fL[1][1])){
						$timeT = explode(':',$fL[1][1],5);
						$timeT = array_reverse($timeT);
						
						$time = 0; $faktor = array(1 , 60 , 3600, 86400);
						for($i = 0; $i < count($timeT); $i++){
							//echo $i."-".$time."-".$timeT[$i]."-".$faktor[$i]."\n";	
							$time += ((int)$timeT[$i])*$faktor[$i];
						}
						$track['end'] = $time;
					}
					
					$innerA = $val;
					foreach($fL[0] as $valAS){
						$innerA = str_replace($valAS, '', $innerA);
					}
					
					$track['name'] = Encoding::stripAllKnownSpecialChars(trim(str_replace(' – ', ' - ', str_replace(array('()','[]','{}', '(or)', '( or )'), '', $innerA) ) , '.1234567890.-:; #'));
					
					$temp = explode(' - ', $track['name'], 2);
					
					if(isset($temp[1])){
						$track['name1'] = $temp[0];
						$track['name2'] = $temp[1];
					}

					$tracklist[] = $track;
					
					$z++;
				}
			}
		}

		return $tracklist;
	}
	private function getSecondsBySeekToString($string){
		$regex = '#<\s*?a\b[^>]*>(.*?)</a\b[^>]*>#s';	
		preg_match_all($regex, $string, $found);
		
		return $found;
	}
	private function getDurationInSeconds($string){
		if(strpos($string, 'PT') === FALSE)
			return 0;
			
		$dv = new DateIntervalEnhanced($string);
		return $dv->to_seconds();
	}
	private function getGenreArray( $string ){
		$t = explode(' ',$string,99);
		return $t;
	}
}
class DateIntervalEnhanced extends DateInterval { 
	public function to_seconds(){ 
        return ($this->y * 365 * 24 * 60 * 60) + 
               ($this->m * 30 * 24 * 60 * 60) + 
               ($this->d * 24 * 60 * 60) + 
               ($this->h * 60 * 60) + 
               ($this->i * 60) + 
               $this->s; 
   	} 
      
	public function recalculate(){ 
        $seconds = $this->to_seconds(); 
        $this->y = floor($seconds/60/60/24/365); 
        $seconds -= $this->y * 31536000; 
        $this->m = floor($seconds/60/60/24/30); 
        $seconds -= $this->m * 2592000; 
        $this->d = floor($seconds/60/60/24); 
        $seconds -= $this->d * 86400; 
        $this->h = floor($seconds/60/60); 
        $seconds -= $this->h * 3600; 
        $this->i = floor($seconds/60); 
        $seconds -= $this->i * 60; 
        $this->s = $seconds; 
	} 
} 
class crawlImage {
	private $lastCrawlURL = NULL;
    private $lastCrawlContent = NULL;
    private $lastError = false;
    
    private $isYoutubeVideo = FALSE;
    private $youtubeID = NULL;
    private $youtubeObj = NULL; 
        
    function __construct($url){ 
        $this->lastCrawlURL = trim($url);
        
        if(stristr($this->lastCrawlURL, '/usr/local/lsws/CREEZI/urkraft/public_html/sebCollections')){
            $this->lastCrawlContent = 1;
            return;
        }
            
        if($this->isYoutubeVideoURL() || 1 > 2){
            $this->isYoutubeVideo = TRUE;   
            $this->youtubeObj = new crawlYoutubeVideo($this->youtubeID);
            $this->lastCrawlURL = $this->youtubeObj->getThumbnailURL();
        }

        $ch = curl_init ($this->lastCrawlURL);  
        curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($ch, CURLOPT_REFERER, "https://anjalorenz.wordpress.com/");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $this->lastCrawlContent = curl_exec($ch);
        
        if(curl_errno($ch)){
            $this->lastError = curl_errno($ch).' - '.curl_error($ch);
            echo curl_errno($ch).' - '.curl_error($ch)."\n";
        }
        curl_close ($ch);
    }
    function isLastCrawlAnError(){
        if($this->lastCrawlContent !== FALSE)
            return FALSE;
        else {
            return $this->lastError;
        }
    }
	function isYoutubePost(){
		return $this->isYoutubeVideo;
	}
	function getYoutubeObj(){
		return $this->youtubeObj;
	}
	function isYoutubeVideoURL(){
		if(strpos($this->lastCrawlURL, 'ttps://i.ytimg.com/vi/') > 0 ){
			$t = explode('/',str_replace('https://i.ytimg.com/vi/', '', $this->lastCrawlURL),2);	
			if(strlen($t[0]) > 5 && strlen($t[0]) < 20){
				$this->youtubeID = $t[0];	
				return TRUE;
			}
		}
		return FALSE;
	}
	
	public function getLastImageAsTempFile(){
		$temp = explode('.',$this->lastCrawlURL,9999);
		if(count($temp) == 0)
			$last = '';
		
		$last = '.img';
			
		$tmpDir = sys_get_temp_dir().'/xeroxCore_ms'.crc32($this->lastCrawlURL).$last;
		
		if(file_exists($tmpDir)){
	        unlink($tmpDir);
	    }
		
		if(stristr($this->lastCrawlURL, '/usr/local/lsws/CREEZI/urkraft/public_html/sebCollections')){
			if(!copy($this->lastCrawlURL, $tmpDir))
				return FALSE;
		} else {		
	    	$fp = fopen($tmpDir,'x');
	    	fwrite($fp, $this->lastCrawlContent);
	    	fclose($fp);
		}

		$file = array(
			'name' => $this->lastCrawlURL,
			'type' => 'image',
			'tmp_name' => $tmpDir,
			'error' => 0,
			'size' => filesize($tmpDir)
		);
		
		if(!file_exists($tmpDir))
			die();

		return $file;
	}
	
	public function deleteLastImage(){
		$this->lastCrawlContent = NULL;
	}
}

class uploadImageCalculator {
	private $origWidth = 0;
	private $origHeight = 0;
		
	private $maxWidth = 0;
	private $minWidth = 0;
	private $maxHeight = 0;
	private $minHeight = 0;	
	
	private $lastApectRatio = 0;
	private $newIsSquare = false;
	
	private $isInnerCrop = true;
	
	private $origPictureDir = '';
	private $newPictureDir = '';
	
	public function setOrigPictureDir($string){
		$this->origPictureDir = $string;
	}
	public function setNewPictureDir($string){
		$this->newPictureDir = $string;
	}
	public function setNewRatioSquare(){
		$this->newIsSquare = true;
	}
	public function setInnerCrop(){
		$this->isInnerCrop = true;
	}
	public function setOuterCrop(){
		$this->isInnerCrop = false;
	}
	public function setMaxWidth($int){
		$this->maxWidth = (int)$int;
	}
	public function setMinWidth($int){
		$this->minWidth = (int)$int;
	}
	public function setMaxHeight($int){
		$this->maxHeight = (int)$int;
	}
	public function setMinHeight($int){
		$this->minHeight = (int)$int;
	}
	public function setHeightDynamic(){
		$this->maxHeight = 0;
	}
	public function setWidthDynamic(){
		$this->maxWidth = 0;
	}
	public function setOriginalWidth($int){
		$this->origWidth = (int)$int;
	}
	public function setOriginalHeight($int){
		$this->origHeight = (int)$int;
	}
	
	public function getAspectRatioOrig(){
		$this->lastApectRatio = $this->origWidth / $this->origHeight;	
		return $this->lastApectRatio;
	}
	
	public function isAspectPortrait(){
		$status = false;
		if($this->getAspectRatioOrig() < 1)
			$status = TRUE;
		
		return $status;
	}
	public function isAspectLandscape(){
		$status = false;
		if($this->getAspectRatioOrig() >= 1)
			$status = TRUE;
		
		return $status;
	}
}

class uploadImage {
	private $file = NULL;
	private $filename = '';
	private $filenameStored = '';
	private $filenameDir = '';
	private $fileLocation = '';
	private $filenameLink = '';
	private $filesize = 0;
	private $fileURL = '';
	private $mimeByExtension = NULL;
	private $sha = NULL;
	
	private $isDone = false;
	private $status = false;
	
	private $errors = array();
	private $image = NULL;
	private $linkURL = NULL;
	private $imageID = 0;
	
	private $isCrawledFile = false;
	private $pictureMetaClass = NULL;
	private $lastIDContentLibrary = 0;
	
	private $originalFileName = '';
	
	function __construct($is_crawled = false, $dummy = false){
		if($dummy === TRUE)
			return;
			
		if(is_bool($is_crawled))
			$this->isCrawledFile = $is_crawled;
		
		require_once 'intern/2nd-party/pictureUpload_Bulletproof/bulletproof.php';
		require_once 'intern/2nd-party/pictureUpload_Bulletproof/utils/func.image-crop.php';
		
		$this->image = new Bulletproof\Image($_FILES);
		
		if($this->image["file"]){
			$upload = $this->image->upload(); 
		
		    if($upload){
		        $this->isDone = true;
				$this->filesize = $this->image->getSize();
				$this->filename = $this->image->getName();
				$this->mimeByExtension = $this->image->getMime();
				$this->fileLocation = $this->image->getFullPath();
				$this->status = true;
				
				$fileInfo = $this->image->getFiles();

				if(isset($fileInfo['name']) && strlen($fileInfo['name']) > 1){
					if(strlen($fileInfo['name']) < 245)	
						$this->originalFileName = $fileInfo['name'];
					else
						$this->originalFileName = substr($fileInfo['name'], 0 , 244).'_.'.$this->image->getMime();
				} else
					$this->originalFileName = 'noFileName_'.time().$this->image->getMime();
		    } else {
		        $this->addError($this->image["error"]);
				$this->isDone = false;
		    }
		} else
			$this->addError('noFILES');
	}
	public function setLinkURL( $url ){
		$this->linkURL = HTTP_HOST.'/i/'.$url;
	}
	public function getLastIDContentLibrary(){
		return (int)$this->lastIDContentLibrary;
	}
	private function addError($error){
		$this->errors[] = $error;
	}
	
	public function getErrors(){
		return $this->errors;
	}
	
	private function getSha1Hash(){
		if($this->sha === NULL)
			$this->sha = sha1_file($this->fileLocation);
		
		return $this->sha;
	}
	private $isYoutubeHandle = false;
	private $youtubeObj = NULL;
	
	public function process($isYoube = false){
		if(is_a($isYoube,'crawlYoutubeVideo')){
			$this->isYoutubeHandle = TRUE;
			$this->youtubeObj = $isYoube;
		}
		if(!$this->status || $this->image === NULL){
			$this->addError('statusImage');	
			return FALSE;
		}
		$hash = ((int)($this->filesize / 100)).'-'.$this->getSha1Hash();
		$this->hashSum = $hash;
		$dir = uploadDirRoot.'/'.uploadDirImages;
		$rel = '/';
		$moveToTempNEU = SERVER_ROOT.'/neu/';
		$dirThumb = uploadDirRoot.'/'.uploadDirThumbnails;
		
		if(!$this->buildDatabaseEntrieCounter()){
			$this->addError('db: Entries C');	
			return FALSE;
		}
        
        $imageCounter = $this->imageID;
        $imageDir = (int)($imageCounter / 10000);
        $imageFileName = $imageCounter - ($imageDir * 10000);
        $moveToTempNEU .= $imageCounter.'.'.$this->mimeByExtension;
        
        $rel .= $imageDir;
        $this->filenameDir = $rel;
        $this->filenameStored = $imageDir.'/'.$imageFileName;
        $this->filenameLink = $imageFileName;
        $picDir = '/'.$this->filenameLink;
         
        if(!fileHandler::createFolder("./".$dir.$rel)){
            $this->addError('createFolder "'.$dir.$rel.'"');    
            return FALSE;
        }
        if(!fileHandler::createFolder($dirThumb.$rel)){
            $this->addError('createFolderThumb "'.$dirThumb.$rel.'"');  
            return FALSE;
        }
        
        if(imageProcessExtern)
            $this->buildImageExtern( $imageDir );
        else
            $this->buildImageIntern( $imageDir );
		
		if(!$this->buildDatabaseEntries()){ // its possible that $this->imageIDchanged is true
			$this->addError('db: Entries');	
			return FALSE;
		}

        if(!$this->imageIDchanged){
			$origFile = originalFilesUploadDir.$imageDir;
			fileHandler::createFolder($origFile);
			$origFile .='/'.$imageFileName.'.'.$this->mimeByExtension;
			$this->image->copyUploadedFile($this->fileLocation, $origFile);
			
			if(!$this->isYoutubeHandle && ! 1 > 2){
				require_once 'class.process.picture.php';
				$this->pictureMetaClass = new picture();
				$this->pictureMetaClass->setLoadPath($this->fileLocation);
				$this->pictureMetaClass->copyPictureIntoBuffer();
			}
			
			if(!$this->isCrawledFile && !$this->image->moveUploadedFile($this->fileLocation, $this->filenameStoredInFileSystem)){
				$this->addError('moveUploadedImageToFolder "'.$this->fileLocation.' to '.$moveToTempNEU.'"');	
				
				return FALSE;
			} else if($this->isCrawledFile && !$this->image->moveCrawledFile($this->fileLocation, $this->filenameStoredInFileSystem)){
				$this->addError('moveImageToFolder "'.$this->fileLocation.' to '.$moveToTempNEU.'"');	
				return FALSE;
			}
			
			//if($this->mimeByExtension != 'png'){	
				// create 0byte file
			fileHandler::createFolder(SERVER_ROOT.'/neu/');
			$datei = fopen($moveToTempNEU,"w");
			fclose($datei);	
			//}
			
			if(!$this->isYoutubeHandle && 1 > 2){
				$this->pictureMetaClass->loadPicture();
				$this->pictureMetaClass->cleanBuffer();
				$this->pictureMetaClass->buildColorClusterMap();
				
				$this->buildDatabaseEntriesPictureMeta();
			}
            
            if(!imageProcessExtern)
                $this->buildImageThumb( $imageDir );
		}

		$this->fileLocation = $rel;        

		return TRUE;
	}
    private function buildImageExtern( string $imageDir ){        
        $this->filenameStoredInFileSystem = SERVER_ROOT.'/'.VH_ROOT.'/userUploads/images/'.$imageDir.'/'.imageProcessURIFilePrefix.$this->filenameLink.imageProcessURIFilePostfix.'.'.$this->mimeByExtension;
       
    }
    private function buildImageIntern( string $imageDir ){
        $this->filenameStoredInFileSystem = SERVER_ROOT.'/'.VH_ROOT.'/userUploads/images/'.$imageDir.'/'.$this->filenameLink.'.'.$this->mimeByExtension;
    }
	private function buildImageThumb( string $imageDir ){
        $this->filenameStoredInFileSystemThumbMed = uploadTempDirUserUpload.'/images/'.$imageDir.'/'.$this->filenameLink.'-med.'.$this->mimeByExtension; 
        $crop = Bulletproof\resize_crop_image(SizeThumbnailMedium_X, 0, $this->filenameStoredInFileSystem, $this->filenameStoredInFileSystemThumbMed);    
        
        
        $this->filenameStoredInFileSystemThumb = uploadTempDirUserUpload.'/thumbnails/'.$imageDir.'/'.$this->filenameLink.'.'.$this->mimeByExtension;
        $crop = Bulletproof\resize_crop_image(SizeThumbnailSmall_X, SizeThumbnailSmall_Y, $this->filenameStoredInFileSystem, $this->filenameStoredInFileSystemThumb);
        
        $this->filenameStoredInFileSystem = str_replace('/thumbnails/', '/images/', $this->filenameStoredInFileSystem);
	}
	private function buildDatabaseEntriesPictureMeta(){
		$databaseName = DBTableNameImageMetaInfo;
		$cluster = $this->pictureMetaClass->getColorPaletteSub();
		$main = $this->pictureMetaClass->getColorPaletteMain();
			
		$sql = new dbObj();
		$sql->setTypeINSERT();
	
		$sql->setDatabase($databaseName);
		
		$sql->setInsertFieldValueNULL('ID', $databaseName);
		$sql->setInsertFieldValueString('imageID', $this->imageID, $databaseName);
		
		for($i = 0; $i < 5; $i++){
			if(isset($cluster[$i])){
				$sql->setInsertFieldValueInteger('cluster'.($i + 1).'_number', $cluster[$i]['cluster'], $databaseName);
				$sql->setInsertFieldValueFloat('cluster'.($i + 1).'_var', $cluster[$i]['var'], $databaseName);
			}
			if(isset($main[$i]))
				$sql->setInsertFieldValueString('main'.($i + 1).'_color', $main[$i]['color'], $databaseName);
		}
		
		if(db::query($sql)){
			$databaseName = DBTableNameContentImages;
				
			$sql = new dbObj();
			$sql->setTypeUPDATE();
			
			$sql->setDatabase($databaseName);
			$sql->setConditionStringEqual('imageID', $this->imageID, $databaseName);
			$sql->setUpdatedFieldValueInteger('isMETAupdated', 1, $databaseName);
			
			db::query($sql);
		}
		
		return;
	}
	
	private function buildDatabaseEntrieCounter(){
		// 1st - try to use old ID
			/*$magic = security::getRandomString(10);
			
			$databaseName = DBTableNameSrcListID;
			
			// UPDATE Particular Field
			
			
			$sql = new dbObj();
			$sql->setTypeSELECT();
			
			$sql->setDatabase($databaseName);
			
			$sql->setSELECTField('srcID', $databaseName);
			$sql->setConditionIntegerEqual('magic', $magic, $databaseName);
			$sql->setConditionIntegerEqual('is_blocked', 1, $databaseName);
			
			$sql->setLimit(1);
			
			$res = db::query($sql);
			
			if(isset($res[0]->srcID)){
				$this->imageID = $res[0]->srcID;
				return TRUE;
			}
			*/
		// 2nd - add new entry into srcIDList
			
		
		// 2nd - add ID to Object
			$this->imageID = contentCreation::getItemToSrcListID();
		
		return TRUE;
	}
	private $imageDatabaseID = 0;
	private $imageIDchanged = FALSE;
	private function buildDatabaseEntries(){
		$this->imageIDchanged = FALSE;	
		// 1st - create Entry for image in database
			$sql = new dbObj();
			$databaseName = DBTableNameSrcImages;

			$sql->setTypeINSERT();
			$sql->setDatabase($databaseName);
			$sql->setInsertFieldValueNULL('ID', $databaseName);
			$sql->setInsertFieldValueInteger('dimensionX', $this->image->getHeight(), $databaseName);
			$sql->setInsertFieldValueInteger('dimensionY', $this->image->getWidth(), $databaseName);
			$sql->setInsertFieldValueInteger('userID', user::getDBIDCloaked(), $databaseName);
			$sql->setInsertFieldValueInteger('imageID', $this->imageID, $databaseName);
			$sql->setInsertFieldValueDATE_ADD_TO_NOW('dateCreated', NULL, $databaseName);
			$sql->setInsertFieldValueString('linkStored', $this->filenameStored, $databaseName);
			$sql->setInsertFieldValueString('linkFilename', $this->filenameLink, $databaseName);
			$sql->setInsertFieldValueString('hash', $this->hashSum, $databaseName);
			$sql->setInsertFieldValueString('mime', $this->mimeByExtension, $databaseName);
			
			$this->IDThumbLinkURL = $this->filenameStored;
			
			if(!db::query($sql)){	
				$sql = new dbObj();
				$sql->setTypeSELECT();
				
				$sql->setDatabase($databaseName);
				
				$sql->setSELECTField('imageID', $databaseName);
				$sql->setSELECTField('linkStored', $databaseName);
				$sql->setConditionStringEqual('hash', $this->hashSum, $databaseName);
				
				$res = db::query($sql);
				
				// Delete recently used srcID
					deleteSrcInformation::srcList($this->imageID);
				
				if(!isset($res[0]->imageID)){
					$this->addError('no imageID res');	
					return FALSE;
				}
				
				// Delete recent SrcEntrie
					dbQueries::delete()->srcListEntrieByID( $this->imageID);
				
				$this->imageID = $res[0]->imageID;
				$this->imageIDchanged = TRUE;
				$this->IDThumbLinkURL = $res[0]->linkStored;
			}
		
			$databaseName = DBTableNameSrcUserList;
			$sql = new dbObj();
			$sql->setTypeINSERT();
			
			$sql->setDatabase($databaseName);
			
			$sql->setInsertFieldValueNULL('ID', $databaseName);
			$sql->setInsertFieldValueInteger('userID', user::getDBIDCloaked(), $databaseName);
			$sql->setInsertFieldValueInteger('srcID', $this->imageID, $databaseName);
			$sql->setInsertFieldValueDATE_ADD_TO_NOW('dateCreated', 0, $databaseName);
			$sql->setInsertFieldValueString('hash', user::getDBIDCloaked().'-'.$this->imageID, $databaseName);
			$sql->setInsertFieldValueString('fileName', $this->originalFileName, $databaseName);
			
			db::query($sql);
		
		// 2nd - create thumbnailEntry to image
			if(!$this->imageIDchanged){
				$databaseName = DBTableNameSrcThumbnails;
				$sql = new dbObj();
				
				$sql->setTypeINSERT();
				$sql->setDatabase($databaseName);
				$sql->setInsertFieldValueNULL('ID', $databaseName);
				$sql->setInsertFieldValueInteger('thumbnailID', $this->imageID ,$databaseName);
				$sql->setInsertFieldValueInteger('imageID', $this->imageID, $databaseName);
				$sql->setInsertFieldValueString('link', $this->filenameStored, $databaseName);
				$sql->setInsertFieldValueString('mime', $this->mimeByExtension, $databaseName);
				
				if(!db::query($sql)){
					$this->addError('thumbsDB '.$sql->getQueryString().' -> '.implode(',',$sql->getErrors()));	
					return FALSE;
				}
			}
		/*
		if(!$this->isYoutubeHandle){
			$sql = new dbObj();
			$databaseName = DBTableNameSrcImagesThumbnails;
			
			$sql->setTypeINSERT();
			$sql->setDatabase($databaseName);
			
			$tags = array();

			foreach($tags as $intTag){
				$sql->setInsertFieldValueNULL('ID', $databaseName);
				$sql->setInsertFieldValueString('imageID', $this->imageID, $databaseName);
				$sql->setInsertFieldValueInteger('tagID', $intTag, $databaseName);
				$sql->setInsertFieldValueString('hash', $this->imageID.'_'.$intTag, $databaseName);
			}
			
			db::query($sql);
		}*/
		return TRUE;
		return $this->addMediaToLibrary();
	}
	
	private function addMediaToLibrary(){
		if($this->isYoutubeHandle)
			return TRUE;
			
		$databaseName = 'content_library';	
		$sql = new dbObj();
		$sql->setTypeINSERT();
		$sql->setDatabase($databaseName);
		
		$sql->setInsertFieldValueInteger('private', 0, $databaseName);
		$sql->setInsertFieldValueDATE_ADD_TO_NOW('dateCreated', 0, $databaseName); 
		$sql->setInsertFieldValueInteger('userID', user::getDBIDCloaked(), $databaseName);
		$sql->setInsertFieldValueString('imageID', $this->imageID, $databaseName);
		
		if(db::query($sql)){
			$this->lastIDContentLibrary = db::getLastID();
			return TRUE;
		}
		return FALSE;
	}
	
	public function getStatus(){
		return $this->status;
	}
	public function getDone(){
		return $this->isDone;
	}
	public function getLinkDir(){
		return $this->fileLocation;
	}
	public function getThumbURL(){
		return $this->linkURL.'-thumb';
	}
	private $IDThumbLinkURL = NULL;
	public function getThumbStoreURL(){
		return $this->IDThumbLinkURL;
	}
	public function getLinkURL(){
		return $this->linkURL;
	}
	public function getLinkStored(){
		return $this->filenameStored;
	}
	public function getImageID(){
		return $this->imageID;
	}
	public function getImageDatabaseID(){
		return $this->imageDatabaseID;
	}
}
class cCreation {
	private $titel = '';
	private $userID = NULL;
	private $type = NULL;
	private $isPrivate = 0;
	private $isAdult = 0;
	private $thumbnail = NULL;
	private $contentID = NULL;
	private $thumbLink = NULL;
	private $text = NULL;
	private $textID = NULL;
	private $scrID = NULL;
	private $srcType = NULL;
	private $link = NULL;
	private $title = NULL;
	private $sTitle = NULL;
	private $elementsIn = 0;
	private $hosterID = '';
	private $hoster = '';
	
	public function setHoster($string){
		$this->hoster = $string;
	}
	public function setHosterID($string){
		$this->hosterID = $string;
	}
	public function setText($string){
		$this->text = $string;
	}
	public function setType($string){
		$this->type = $string;
	}
	public function setUserID($int){
		$this->userID = (int)$int;
	}
	public function setTitle($string){
		$this->title = $string;
	}
	public function setShortTitle($string){
		$this->sTitle = $string;
	}
	public function setIsPrivate($bool){
		$this->isPrivate = (int)((bool) $bool);
	}
	public function setIsAdult($bool){
		$this->isAdult = (int)((bool) $bool);
	}
	public function setContentID($string){
		$this->contentID = substr($string,0,9);
	}
	public function setThumbLink($link){
		$this->thumbLink = substr($link, 0, 19);
	}
	public function setTextID($int){
		$this->textID = (int)$int;
	}
	public function setSrcID($int){
		$this->scrID = (int)$int;
	}
	public function setSrcType($string){
		$this->srcType = $string;
	}
	public function setLink( $link ){
		$this->link = $link;
	}
	public function setCountElements( $int ){
		$this->elementsIn = (int)$int;
	}
	
	public function getText(){
		return $this->text;
	}
	public function getType(){
		return $this->type;
	}
	public function getUserID(){
		return $this->userID;
	}
	public function getTitle(){
		return $this->title;
	}
	public function getShortTitle(){
		return $this->sTitle;
	}
	public function getIsPrivate(){
		return $this->isPrivate;
	}
	public function getIsAdult(){
		return $this->isAdult;
	}
	public function getContentID(){
		return $this->contentID;
	}
	public function getThumbLink(){
		return $this->thumbLink;
	}
	public function getTextID(){
		return $this->textID;
	}
	public function getSrcID(){
		return $this->scrID;
	}
	public function getSrcType(){
		return $this->srcType;
	}
	public function getLink(){
		return $this->link;
	}
	public function getHoster(){
		return $this->hoster;
	}	
	public function getHosterID(){
		return $this->hosterID;
	}
	public function getCountElements(){
		return $this->elementsIn;
	}
}
class contentCreation{
	public static function createVideoRawEntryForLibraryPost($videoID, $url, $userID, $hoster, $hosterID){
		$ob = new cCreation();
		$ob->setSrcID($videoID);
		$ob->setLink($url);
		$ob->setUserID($userID);
		$ob->setHoster($hoster);
		$ob->setHosterID($hosterID);
			
		return dbQueries::add()->libraryElementVideo($ob);
	}	
	public static function addTagsToImage($contentID, $tagArray){
		if(!is_array($tagArray))
			return TRUE;
		
		// build tagsEntrie
			$ob = new cCreation();
				$ob->setContentID( $contentID );
			
		// do databaseQuery
			return dbQueries::add()->tagsToAllDatabases($ob, $tagArray);
			
	}	 
	public static function addTextToContentItem($contentID, $textString, $type = 'td'){
		if(!isset($textString) || strlen($textString) < 2 )
			return TRUE;
			
		// build srcTextEntrie
			if(($ID = dbQueries::add()->itemToTextSrcListID()) !== FALSE){
				$ob = new cCreation();
					$ob->setTextID( $ID );
					$ob->setUserID( security::getUserObject()->getDatabaseIDCloaked() );
					$ob->setText( $textString );
					$ob->setType( $type );
		// add text to textSrc with recent Entrie ID
				If(!dbQueries::add()->libraryElementText( $ob ))
					return FALSE;
				
		// add text to elementMatchTable
				if(dbQueries::add()->elementToMatchItems($contentID, [(object)['type' => 'st', 'textID' => $ob->getTextID()]]))
					return TRUE;
			}
		
		return FALSE;
	}	
	public static function getItemToSrcListID(){
		return dbQueries::add()->newElementToSrcTable();
	}
	public static function getItemToLibraryListID(){
		$databaseName = DBTableNameContentAllID;
		$sql = new dbObj();
		$sql->setTypeINSERT();
		
		$sql->setDatabase($databaseName);
		
		$sql->setInsertFieldValueNULL('ID', $databaseName);
		$sql->setInsertFieldValueNULL('contentID', $databaseName);
		$sql->setInsertFieldValueInteger('is_deleted', 0, $databaseName);
		//$sql->setInsertFieldValueCounterAutoIncrementBase36('contentID', $databaseName);
		
		if(!db::query($sql)){
			//echo $sql->getQueryString(); die();		
			return FALSE;
		}
		//echo "ook"; die();
		$ID = db::getLastID();
		$base = base_convert($ID, 10, 36);
		
		$sql = new dbObj();
		$sql->setTypeUPDATE();
		$sql->setDatabase($databaseName);
		
		$sql->setUpdatedFieldValueString('contentID', $base, $databaseName);
		$sql->setConditionIntegerEqual('ID', $ID, $databaseName);
		
		if(db::query($sql))
			return $base;
		
		return FALSE;
	}
	public static function updateLibraryContentPost(cCreation $cCreation){
	    $databaseName = DBTableNameContentAll;
        $sql = new dbObj();
        $sql->setTypeUPDATE();
        
        $sql->setDatabase($databaseName);
        
        $sql->setUpdatedFieldValueInteger('userID', $cCreation->getUserID(), $databaseName);
        $sql->setUpdatedFieldValueString('type', $cCreation->getType(), $databaseName);
        
        if(!empty($cCreation->getTitle()))
            $sql->setUpdatedFieldValueString('title', $cCreation->getTitle(), $databaseName);
        
        if(!empty($cCreation->getTitle()))
            $sql->setUpdatedFieldValueString('link', security::getNormalizedLinkURL($cCreation->getTitle()), $databaseName);
        
        if(!empty($cCreation->getTitle()))
            $sql->setUpdatedFieldValueString('shortTitle', uploadSanitizer::getStringShortend($cCreation->getTitle()), $databaseName);
        
        $sql->setUpdatedFieldValueInteger('is_private', $cCreation->getIsPrivate(), $databaseName);
        $sql->setUpdatedFieldValueInteger('is_adult', $cCreation->getIsAdult(), $databaseName);
        $sql->setConditionStringEqual('contentID', $cCreation->getContentID(), $databaseName);
        
        if(!empty($cCreation->getThumbLink()))
            $sql->setUpdatedFieldValueString('thumbnailLink', $cCreation->getThumbLink(), $databaseName);
        
        // $sql->setInsertFieldValueInteger('elementsIn', $cCreation->getCountElements(), $databaseName);
        // $s = $sql->getQueryString();
        
        return db::query($sql);
	}
	public static function createLibraryContentPost(cCreation $cCreation){
		$databaseName = DBTableNameContentAll;
		$sql = new dbObj();
		$sql->setTypeINSERT();
		
		$sql->setDatabase($databaseName);
		
		$sql->setInsertFieldValueNULL('ID', $databaseName);
		$sql->setInsertFieldValueInteger('userID', $cCreation->getUserID(), $databaseName);
		$sql->setInsertFieldValueString('type', $cCreation->getType(), $databaseName);
		
		if(empty($cCreation->getTitle()))
			$sql->setInsertFieldValueNULL('title', $databaseName);
		else
			$sql->setInsertFieldValueString('title', $cCreation->getTitle(), $databaseName);
		
        if(empty($cCreation->getTitle()))
            $sql->setInsertFieldValueNULL('link', $databaseName);
        else
            $sql->setInsertFieldValueString('link', security::getNormalizedLinkURL($cCreation->getTitle()), $databaseName);
		
        if(empty($cCreation->getTitle()))
            $sql->setInsertFieldValueNULL('shortTitle', $databaseName);
        else
            $sql->setInsertFieldValueString('shortTitle', uploadSanitizer::getStringShortend($cCreation->getTitle()), $databaseName);
		
		$sql->setInsertFieldValueDATE_ADD_TO_NOW('dateCreated', 0, $databaseName);
		$sql->setInsertFieldValueInteger('is_private', $cCreation->getIsPrivate(), $databaseName);
		$sql->setInsertFieldValueInteger('is_adult', $cCreation->getIsAdult(), $databaseName);
		$sql->setInsertFieldValueString('contentID', $cCreation->getContentID(), $databaseName);
		
        if(empty($cCreation->getThumbLink()))
            $sql->setInsertFieldValueNULL('thumbnailLink', $databaseName);
        else
            $sql->setInsertFieldValueString('thumbnailLink', $cCreation->getThumbLink(), $databaseName);
		
		// $sql->setInsertFieldValueInteger('elementsIn', $cCreation->getCountElements(), $databaseName);

		if(db::query($sql))
			return db::getLastID();

		return FALSE;
	}	
	public static function addCollectionDBAndReturnColID($colTitle, $colDescription,$elements){
		// Build Collection
		$collID = upload::getcontentID('collection');
		//echo $collID."\n";
		if($collID == '0' || $collID === NULL){
			//echo "next ColID 0 - ".$collID;	
			return;
		}
		//$this->collID = $collID;
		$databaseName = 'content_collections';
		
		$elementLinks = '';
		foreach($elements as $val){
			$elementLinks .= $val->link.',';	
		}
		$elementLinks = substr($elementLinks, 0, -1);
		
		// Insert String into collDB
		$sql = new dbObj();
		$sql->setTypeINSERT();
		$sql->setDatabase($databaseName);
		
		$sql->setInsertFieldValueNULL('ID', $databaseName);
		$sql->setInsertFieldValueString('title', $colTitle, $databaseName);
		$sql->setInsertFieldValueString('link', security::getNormalizedLinkURL($colTitle), $databaseName);
		$sql->setInsertFieldValueInteger('userID', user::getDBIDCloaked(), $databaseName);
		$sql->setInsertFieldValueString('collectionID', $collID, $databaseName);
		$sql->setInsertFieldValueDATE_ADD_TO_NOW('dateCreated', 0, $databaseName);
		$sql->setInsertFieldValueInteger('elementsIn', count($elements), $databaseName);
		$sql->setInsertFieldValueString('hash', count($elements).'-'.md5(user::getDBIDCloaked().$colTitle), $databaseName);
		$sql->setInsertFieldValueString('thumbnailLinks', $elementLinks, $databaseName);

		$sql->setInsertFieldValueString('description', $colDescription, $databaseName);
		
		$sql->setOnDuplicateFieldValueDATE_ADD_TO_NOW('dateCreated', 1);
		
		if(!db::query($sql)){
			//echo $sql->getQueryString();	
			return;
		}
		
		$sql = new dbObj();
		$databaseName = DBTableNameContentAll;
		$sql->setTypeINSERT();
		$sql->setDatabase($databaseName);
		$sql->setInsertFieldValueNULL('ID', $databaseName);
		$sql->setInsertFieldValueString('collectionID', $collID, $databaseName);
		$sql->setInsertFieldValueDATE_ADD_TO_NOW('dateCreated', 0, $databaseName);
		$sql->setInsertFieldValueInteger('userID', user::getDBIDCloaked(), $databaseName);
		$sql->setInsertFieldValueInteger('private', 0, $databaseName);
		$sql->setInsertFieldValueInteger('isNew', 0, $databaseName);
		$sql->setInsertFieldValueString('shortTitle', uploadSanitizer::getStringShortend($colTitle), $databaseName);
		
		if(!db::query($sql)){
			//echo $sql->getQueryString();
		};
		
		return $collID;
	}	
	public static function addCollectionMatchDBImages($dbTableIDListEditableFiles, $colID){
		$databaseName = 'match_collection_images';
		
		// Insert String into collDB
		$sql = new dbObj();
		$sql->setTypeINSERT();
		$sql->setDatabase($databaseName);
		
		$i = 0;
		foreach($dbTableIDListEditableFiles as $file){
			$i++;	
			$sql->setInsertFieldValueNULL('ID', $databaseName);
			$sql->setInsertFieldValueString('collectionID', $colID, $databaseName);
			$sql->setInsertFieldValueString('imageID', $file->imageID, $databaseName);
			$sql->setInsertFieldValueInteger('position', $i, $databaseName);
			$sql->setInsertFieldValueString('hash', $colID.'-'.$colID, $databaseName);
		}
		if(!db::query($sql))
			//echo $sql->getQueryString();
			$i = 1;
	}	
	public static function addCollectionMatchDBTags($tagString, $colID){
		$databaseName = 'match_collection_tags';
		$tagArray = security::getTagStringArrayHarmonized($tagString);
		$databaseName2 = 'content_tag';	

		if(count($tagArray) > 0){
			// Insert String into collDB
			$sql = new dbObj();
			$sql->setTypeINSERT();
			$sql->setDatabase($databaseName);
			
			$sql2 = new dbObj();
			$sql2->setTypeINSERT();
			$sql2->setDatabase($databaseName2);
			
			$i = 0;
			foreach($tagArray as $tag){
				$i++;	
				$sql->setInsertFieldValueNULL('ID', $databaseName);
				$sql->setInsertFieldValueString('collectionID', $colID, $databaseName);
				$sql->setInsertFieldValueString('tagLink', security::getNormalizedLinkURL($tag), $databaseName);
				$sql->setInsertFieldValueString('hash', $colID.'-'.security::getNormalizedLinkURL($tag), $databaseName);
				
				$sql2->setInsertFieldValueString('label', $tag, $databaseName2);
				$sql2->setInsertFieldValueString('tagLinkS', security::getNormalizedLinkURL($tag), $databaseName2);
				$sql2->setOnDuplicateFieldValueAddIntegerToColumn('counter', 1, 'counter');
			}
			if(!db::query($sql) || !db::query($sql2))
				//echo $sql->getQueryString()."\n";
				$i = 1;
		}
	}	
	public static function deleteOldContentFromDB($dbTableIDListEditableFiles, $colID){
		$databaseName = DBTableNameContentAll;
		
		$sql = new dbObj();
		$sql->setTypeDELETE();
		$sql->setDatabase($databaseName);
		
		$z = 0;
		foreach($dbTableIDListEditableFiles as $entry){
			if($z > 0)	
				$sql->setConditionStringEqual('ID', $entry->ID, $databaseName, 'OR');
			else
				$sql->setConditionStringEqual('ID', $entry->ID, $databaseName);
			
			$sql2 = new dbObj();
			$sql2->setDatabase(DBTableNameContentImages);
			$sql2->setTypeUPDATE();
			$sql2->setUpdatedFieldValueString('inCollectionColID', $colID, DBTableNameContentImages);
			$sql2->setConditionStringEqual('imageID', $entry->imageID, DBTableNameContentImages);
			
			db::query($sql2);
			
			$z++;
		}
		if(!db::query($sql)){
			//echo $sql->getQueryString();	
			return;
		}
	}
}
class deleteSrcInformation{
	public static function srcList($srcID){
		$databaseName = DBTableNameSrcListID;
		$sql = new dbObj();
		$sql->setTypeDELETE();
		
		$sql->setDatabase($databaseName);
		//$sql->setUpdatedFieldValueInteger('is_deleted', 1, $databaseName);
		//$sql->setUpdatedFieldValueDATE_ADD_TO_NOW('dateDeleted', 0, $databaseName);
		
		$sql->setConditionIntegerEqual('srcID', $srcID, $databaseName);
		
		db::query($sql);
	}
}
?>