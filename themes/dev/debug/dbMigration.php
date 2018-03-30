<?php
echo "DB Migrating \n\n";
die();
$databaseName = DBTableNameSearchContentImages;
$sql = new dbObj();
$sql->setDatabase($databaseName);
$sql->setSELECTField('libraryListID', $databaseName);

$res = db::query($sql);

foreach($res as $val) {
	$databaseName = DBTableNameSearchContentAll;
	$sql = new dbObj();
	$sql->setDatabase($databaseName);
	$sql->setTypeINSERT();
	$sql->setInsertFieldValueInteger('libraryListID', $val->libraryListID, $databaseName);
	
	if(db::query($sql))
		echo $val->libraryListID." inserted\r\n";
}
die();
checkDBIntegCollection();
die();
// load all images
	// from contentLibrary
		$res = loadAllImagesFromDB1(); //print_r($res); die();
			
		checkIfFilesExist($res);
		parseDoubledStoredLink($res);
		$dups = parseDoubledHash($res);		// print_r($dups); die();

	// Create srcListIDs from unique pictures
		createSrcListIDsEntries($res, $dups, FALSE);
	
	// load existing collection and ImageSubPages
		$res = loadExistingColAndImgSites();	// print_r($res); die();
	
	// getColsOutOfAllSubPages
		$cols = getArrayWithCollectionIDs($res);  // print_r($cols); die();
	
	// getImagesOfAllSubpages
		$imgs = getArrayWithImageIDs($res);  // print_r($imgs); die();
	
	// build contentIDs by last result
		$contentIDList = createCollectionAndImagesLibraryListIDs($cols, $imgs, $dups); // print_r($contentIDList); die();
	
	// expand contentIDs with collectionMatchingImages
		expandContentIDListWithMatchingCollectionImages($contentIDList, $dups);
		
	// insert contentIDs into database
		createContentListIDsEntries($contentIDList, FALSE);
	
	// insert content into library List
		createContentListEntries($contentIDList, $dups, 0);
	
	// insert content into searchableAll
		createSearchEntriesAll($contentIDList, 0);
	
	// insert matching items into table
		createContentMatchEntriesImages($contentIDList, $dups, 0);
		createContentMatchEntriesCollections($contentIDList, $dups, 0);
	
	// insert tags to create matching table
		$tags = getArrayWithTagsOfAvailableCollections($contentIDList, $dups);
		
		die();

function loadAllImagesFromDB1(){
	$databaseName2 = 'content_images';
	$sql = new dbObj();
	
	$sql->setTypeSELECT();
	$sql->setDatabase($databaseName2);
	
	$sql->setSELECTField('userID', $databaseName2);
	$sql->setSELECTField('imageID', $databaseName2);
	$sql->setSELECTField('dateCreated', $databaseName2);
	$sql->setSELECTField('title', $databaseName2);
	$sql->setSELECTField('link', $databaseName2);
	$sql->setSELECTField('dimensionX', $databaseName2);
	$sql->setSELECTField('dimensionY', $databaseName2);
	$sql->setSELECTField('linkStored', $databaseName2);
	$sql->setSELECTField('hash', $databaseName2);
	$sql->setSELECTField('description', $databaseName2);
	$sql->setSELECTField('apiSrc', $databaseName2);
	$sql->setSELECTField('mime', $databaseName2);

	$sql->setOrderByField('ID', $databaseName2);

	return($res = db::query($sql));
}		
function checkIfFilesExist($res){
	foreach($res as $val){	
		if(!file_exists('/usr/local/lsws/CREEZI/urkraft/public_html/userUploads/images/'.$val->linkStored.'.'.$val->mime)){
			echo $val->imageID."\n";
		}
	}
}
function parseDoubledStoredLink($src){
	$dup = []; $dups = [];
	foreach($src as $key => $val){
		if(!isset($dup[$val->linkStored])){	
			$dup[$val->linkStored] = $val;
		} else {
			if(!isset($dups[$val->linkStored])){
				$dups[$val->linkStored] = [];
				$dups[$val->linkStored][] = $dup[$val->linkStored]->imageID.' - '.$dup[$val->linkStored]->title;
			}
			$dups[$val->linkStored][] = $val->imageID;
			unset($src[$key]);
		}
	}
	if(count($dups) > 0){
		echo "dups\n";
		print_r($dups);
	}
}
function parseDoubledHash($src){
	$dup = []; $dups = []; $copied = []; $map = [];
	foreach($src as $key => $val){
		if(!isset($dups[$val->hash]))
			$dups[$val->hash] = ['real' => $val, 'list' => [0 => $val]];
		else {
			// look if title is longer
			if(strlen($val->title) > strlen($dups[$val->hash]['real']->title)){
				// add real entrie to map
					$dups[$val->hash]['list'][] = $dups[$val->hash]['real'];
				// change real entrie
					$dups[$val->hash]['real'] = $val;
			} else {
				// add entrie directly to map
					$dups[$val->hash]['list'][] = $val;
			}
		}
		
	}
	/*
	foreach($dups as $val){
		if(count($val['list']) > 1){
			echo $val['real']->imageID.' - '.$val['real']->title."\n\n";
			foreach($val['list'] as $entrie){
				echo "\t".$entrie->imageID.' - '.$entrie->title."\n";
			}
			echo '________________________________________'."\n";
		}
	}
	*/
	return $dups;
}
function createSrcListIDsEntries($res, &$dups, $status){
	// build src array by file ID
		$src = [];
		foreach($res as $val){
			if(isset($dups[$val->hash]) && $dups[$val->hash]['real']->imageID == $val->imageID){
				$src[(int)str_replace('0/','',$val->linkStored)] = TRUE;
				$dups[$val->hash]['real']->srcID = (int)str_replace('0/','',$val->linkStored);
			}
		}
		ksort($src);
		
		end($src);
		$key = key($src);

	if(!$status){
		echo "skipping inserting new srcListIds... \n\n";	
		return;
	}
	
	$databaseName = 'content_srcListID';
		
	$sql = new dbObj2();
	$sql->setTypeINSERT();
	$sql->setDatabase($databaseName);
	
	for($i = 1; $i <= $key; $i++){
		if(!isset($src[$i])){
			$sql->setInsertFieldValueInteger('is_deleted', 1, $databaseName);
			$sql->setInsertFieldValueNOW('dateDeleted', $databaseName);
		} else {
			$sql->setInsertFieldValueInteger('is_deleted', 0, $databaseName);
			$sql->setInsertFieldValueNULL('dateDeleted', $databaseName);
		}
	}

###		
	echo "inserting new srcListIds with status: ".(int)db2::query($sql)."\n\n";	
}
function loadImagesCollectionDependencies(){
	// from collectionMatchingTable
		$databaseName = 'match_collection_images';
		$databaseName2 = 'content_images';
		$databaseName3 = 'content_library';
		
		$sql = new dbObj();
		$sql->setTypeSELECT();
		$sql->setDatabase($databaseName3);
		$sql->setDatabase($databaseName);
		$sql->setDatabase($databaseName2);
		//print_r($collections); die();
		
		$sql->setSELECTField('ID', $databaseName3);
		
		$sql->setSELECTField('title', $databaseName2);
		$sql->setSELECTField('link', $databaseName2);
		$sql->setSELECTField('dimensionX', $databaseName2);
		$sql->setSELECTField('dimensionY', $databaseName2);
		$sql->setSELECTField('userID', $databaseName2);
		$sql->setSELECTField('dateCreated', $databaseName2);
		$sql->setSELECTField('linkStored', $databaseName2);
		$sql->setSELECTField('hash', $databaseName2);
		$sql->setSELECTField('mime', $databaseName2);
		$sql->setSELECTField('apiSrc', $databaseName2);
		$sql->setSELECTField('imageID', $databaseName2);
		$sql->setSELECTField('collectionID', $databaseName);
		
		$sql->setDBonLeftJoinEqualToColumn('collectionID', $databaseName, 'collectionID', $databaseName3);
		$sql->setDBonLeftJoinEqualToColumn('imageID', $databaseName2, 'imageID', $databaseName);
		
		$sql->setConditionNotNULL('collectionID', $databaseName3);
		$sql->setOrderByField('ID', $databaseName);
		
		$res = db::query($sql);
	
		return ($res);
}
function loadExistingColAndImgSites(){
	$databaseName = 'content_library';
	$databaseName2 = 'content_images';
	$databaseName3 = 'content_collections';
	$sql = new dbObj();
	
	$sql->setTypeSELECT();
	$sql->setDatabase($databaseName);
	$sql->setDatabase($databaseName2);
	$sql->setDatabase($databaseName3);
	
	$sql->setSELECTField('dateCreated', $databaseName);
	$sql->setSELECTField('imageID', $databaseName);
	$sql->setSELECTField('collectionID', $databaseName);
	$sql->setSELECTField('userID', $databaseName);
	$sql->setSELECTField('hash', $databaseName);
	$sql->setSELECTField('shortTitle', $databaseName);
	$sql->setSELECTField('dateCreated', $databaseName);

	$sql->setSELECTField('title', $databaseName2, 'imgT');
	$sql->setSELECTField('link', $databaseName2, 'imgL');
	$sql->setSELECTField('dimensionX', $databaseName2);
	$sql->setSELECTField('dimensionY', $databaseName2);
	$sql->setSELECTField('linkStored', $databaseName2);
	$sql->setSELECTField('hash', $databaseName2);
	$sql->setSELECTField('tagString', $databaseName2);
	$sql->setSELECTField('description', $databaseName2);
	$sql->setSELECTField('apiSrc', $databaseName2);
	$sql->setSELECTField('mime', $databaseName2);
	
	$sql->setSELECTField('title', $databaseName3,'colT');
	$sql->setSELECTField('link', $databaseName3, 'colL');
	$sql->setSELECTField('thumbnailLinks', $databaseName3);
	$sql->setSELECTField('hash', $databaseName3, 'colHash');
	$sql->setSELECTField('description', $databaseName3, 'colDesc');
	$sql->setSELECTField('elementsIn', $databaseName3);

	$sql->setDBonLeftJoinEqualToColumn('imageID', $databaseName2, 'imageID', $databaseName);
	$sql->setDBonLeftJoinEqualToColumn('collectionID', $databaseName3, 'collectionID', $databaseName);

	$sql->setOrderByField('ID', $databaseName);
	$sql->setLimit(100000);

	return ($res = db::query($sql));
}
function getArrayWithCollectionIDs($res){
	$cols = [];	
	foreach($res as $key => $val){
		/*if(!empty($val->imageID)){
			$thisID = base_convert($val->imageID, 36, 10);
			$thisType = 1;
			$val->title = $val->imgT;
			$val->link = $val->imgL;
		} else */
		if(!empty($val->collectionID)){
			$thisID = base_convert($val->collectionID, 36, 10);
			$thisType = 2;
			$val->title = $val->colT;
			$val->link = $val->colL;
			$val->contentID = $val->collectionID;
			
			$cols[(int)base_convert($val->collectionID, 36, 10)] = $val;
		} else 
			continue;
	}
	ksort($cols);
	return $cols;
}
function getArrayWithImageIDs($res){
	$imgs = [];	
	foreach($res as $key => $val){
		if(!empty($val->imageID)){
			$thisID = base_convert($val->imageID, 36, 10);
			$thisType = 1;
			$val->title = $val->imgT;
			$val->link = $val->imgL;
			$val->contentID = $val->imageID;
			
			$imgs[(int)base_convert($val->imageID, 36, 10)] = $val;
		} else /*
		if(!empty($val->collectionID)){
			$thisID = base_convert($val->collectionID, 36, 10);
			$thisType = 2;
			$val->title = $val->colT;
			$val->link = $val->colL;
			
			$cols[(int)base_convert($val->collectionID, 36, 10)] = $val;
		} else */
			continue;
	}
	ksort($imgs);
	return $imgs;
}
function createCollectionAndImagesLibraryListIDs($cols, $imgs, &$dupsOrig){
	reset($dupsOrig);
	$first_key = key($dupsOrig);	
	
	print_r($dupsOrig[$first_key]);
	
	//print_r($imgs); die();
	
	$contentIDList = []; $lostIDList = [];
	
	// copy dips list
		$dups = $dupsOrig;
	
	// add collections to contentIDList
		foreach($cols as $base10ID => $val){
			$contentIDList[$base10ID] = $val;
			$contentIDList[$base10ID]->type = 1;
		}
	
	// try to add imageID to contentIDList
		foreach($imgs as $base10ID => $val){
			$val->type = 2;
			if(!isset($contentIDList[$base10ID])){	
				$contentIDList[$base10ID] = $val;
				$dupsOrig[$val->hash]['real']->base10ID = $base10ID;
			} else {
				// imgID already useb by contentPost
				// add imgID to lostItemList
				$lostIDList[$base10ID] = $val;
			}
			
			// remove image from hashArray
				//unset($dups[$val->hash]);
		}
	// sort contentIDList
		ksort($contentIDList);
		
		end($contentIDList);
		$key = key($contentIDList); echo "key: ".$key."\n";
		
	// create new contentId for lostItems
		$z = 1;//print_r($contentIDList); print_r($lostIDList);die();
		foreach($lostIDList as $lostRealImg){
			$dupsOrig[$val->hash]['real']->base10ID = $key + $z;
			$lostRealImg->contentID = base_convert(($key + $z), 10, 36);
			$contentIDList[($key + $z)] = $lostRealImg; 
			
			$z++;
		}
		
		return $contentIDList;
	// create new contentIDs for all the pics that were not crawled yet
		foreach($dupsOrig as $hash => $hashImg){
			// when base10ID already set, the pic doesnt have to be crawlen	
			if(isset($hashImg['real']->base10ID))
				continue;
			
			$contentIDList[($key + $z)] = $hashImg['real'];
			$dupsOrig[$hash]['real']->base10ID = $key + $z;
			
			$z++;
		}
		print_r($contentIDList); die();
	}
function expandContentIDListWithMatchingCollectionImages(&$contentIDList, &$dups){
	$databaseName = 'match_collection_images';
	$databaseName2 = 'content_images';
	$databaseName3 = 'content_library';
	
	$sql = new dbObj();
	$sql->setTypeSELECT();
	$sql->setDatabase($databaseName3);
	$sql->setDatabase($databaseName);
	$sql->setDatabase($databaseName2);
	//print_r($collections); die();
	
	$sql->setSELECTField('ID', $databaseName3);
	
	$sql->setSELECTField('title', $databaseName2);
	$sql->setSELECTField('link', $databaseName2);
	$sql->setSELECTField('dimensionX', $databaseName2);
	$sql->setSELECTField('dimensionY', $databaseName2);
	$sql->setSELECTField('userID', $databaseName2);
	$sql->setSELECTField('dateCreated', $databaseName2);
	$sql->setSELECTField('linkStored', $databaseName2);
	$sql->setSELECTField('hash', $databaseName2);
	$sql->setSELECTField('mime', $databaseName2);
	$sql->setSELECTField('apiSrc', $databaseName2);
	$sql->setSELECTField('imageID', $databaseName2);
	
	$sql->setDBonLeftJoinEqualToColumn('collectionID', $databaseName, 'collectionID', $databaseName3);
	$sql->setDBonLeftJoinEqualToColumn('imageID', $databaseName2, 'imageID', $databaseName);
	
	$sql->setConditionNotNULL('collectionID', $databaseName3);
	
	$res = db::query($sql);  // print_r($contentIDList);
	
	end($contentIDList);
	$key = key($contentIDList); // echo "key: ".$key."\n";
	
	$z = 1;
	foreach($res as $val){
		if(empty($val->dateCreated))
			continue;
		
		if(isset($dups[$val->hash]['real']->base10ID)){
			echo "hash: ".$val->imageID." _ ".$dups[$val->hash]['real']->imageID."\n";
		} else {
			$do = TRUE;
			while($do){
				if(!isset($contentIDList[$z])){
					$val->contentID = base_convert($z, 10, 36);
					$val->type = 3;
					$contentIDList[$z] = $val;
					$do = FALSE;
				}
				$z++;
			}
		}
	}
	ksort($contentIDList); echo count($contentIDList);
	return ;
	print_r($contentIDList); die();
}
function createContentListIDsEntries($contentIDList, $status){
	$databaseName = 'content_libraryListID';
	$sql = new dbObj2();
	$sql->setTypeINSERT();
	$sql->setDatabase($databaseName);
	
	end($contentIDList);
	$last = key($contentIDList);
	
	if(!$status){
		echo "skip contentID creation... "."\n";
		return;
	}
	
	for($i = 1; $i <= $last; $i++){
		if(!isset($contentIDList[$i])){
			$sql->setInsertFieldValueString('contentID', base_convert($i, 10, 36), $databaseName);
			$sql->setInsertFieldValueInteger('is_deleted', 1, $databaseName);
			$sql->setInsertFieldValueNOW('dateDeleted', $databaseName);
		} else {
			$sql->setInsertFieldValueString('contentID', base_convert($i, 10, 36), $databaseName);
			$sql->setInsertFieldValueInteger('is_deleted', 0, $databaseName);
			$sql->setInsertFieldValueNULL('dateDeleted', $databaseName);
		}
	}
#####	
	// Build libraryIDs
		echo "contentIDs created: ".var_dump(db2::query($sql))."\n";
}
function createContentListEntries($contentIDList, &$hash ,$status){
	$databaseName = 'content_libraryList';
	// print_r($contentIDList); die();
		
	$sql = new dbObj2();
	$sql->setTypeINSERT();
	$sql->setDatabase($databaseName);
	$max = 0; $thisPics = []; //print_r($hash); die();
	$thumbsS = []; $v = 0;
	foreach($contentIDList as $key => $val){
		if(isset($val->shortTitle) && strlen($val->shortTitle) > 50)
			$val->shortTitle = substr($val->shortTitle,0,47).'[&hellip;]';
		
		$title = NULL;
		if(strlen($val->title) > 6)
			$title = $val->title;
		
		if($title === NULL){
			$sql->setInsertFieldValueNULL('title', $databaseName);
			$sql->setInsertFieldValueNULL('link', $databaseName);
			$sql->setInsertFieldValueNULL('shortTitle', $databaseName);
		} else {
			$sql->setInsertFieldValueString('title', $val->title, $databaseName);
			$sql->setInsertFieldValueString('link', substr(str_replace('--', '-', $val->link),0,45), $databaseName);
			$sql->setInsertFieldValueString('shortTitle', uploadSanitizer::getStringShortend($val->title), $databaseName);
			//echo uploadSanitizer::getStringShortend($val->title)."\t ".strlen(uploadSanitizer::getStringShortend($val->title))."\n";
		}
		
		if($val->type == 1)
			$type = 'c';
		else
			$type = 'i';
		
		$sql->setInsertFieldValueInteger('userID', $val->userID, $databaseName);
		$sql->setInsertFieldValueString('type', $type, $databaseName);
		$sql->setInsertFieldValueString('dateCreated', $val->dateCreated, $databaseName);
		$sql->setInsertFieldValueString('contentID', base_convert($key, 10, 36), $databaseName);
		
		if($val->type == 1){
			$thumb = explode(',',$val->thumbnailLinks.',');
			if(strlen($thumb[0]) < 2)
				$sql->setInsertFieldValueNULL('thumbnailLink', $databaseName);
			else{
				$thumbsS[$thumb[0]] = TRUE;	
				$sql->setInsertFieldValueString('thumbnailLink', $thumb[0], $databaseName);
			}
		} else{
			$sql->setInsertFieldValueString('thumbnailLink', $hash[$val->hash]['real']->linkStored, $databaseName);
			$thumbsS[$hash[$val->hash]['real']->linkStored] = TRUE;
			$thisPics[$val->hash] = $hash[$val->hash]['real'];
			$hash[$val->hash]['real']->base10IDi = $key;
		}
		$v++;
		//if($v > 35)
		//	die;
	}
#####
	//print_r($thisPics);
	// echo $sql->getQueryString();
	
	if(!$status){
		echo "skip contentCreation in library ... "."\n";
		return;
	}
	
	 createimageListEntries($thisPics);
	 createImageThumbListEntries($thisPics);
	echo "content library entries created: ".(int)(db2::query($sql))."\n";
	echo db2::getLastError();
	
	return;
	
	$databaseName = 'content_srcThumbnails';
	$sql = new dbObj2();
	$sql->setDatabase($databaseName);
	
	$sql->setSELECTField('link', $databaseName);
	
	$res = db2::query($sql);
	
	$s = [];
	foreach($res as $val){
		$s[$val->link] = TRUE;
	}
	
	foreach($thumbsS as $key => $val){
		if(!isset($s[$key]))
			echo $key."\n";
	}
	die();
}
function createImageThumbListEntries($thisPics){
	$databaseName = 'content_srcThumbnails';
	
	$sql = new dbObj2();
	$sql->setTypeINSERT();
	$sql->setDatabase($databaseName);
	
	foreach($thisPics as $key => $val){
		$sql->setInsertFieldValueInteger('imageID', $val->srcID, $databaseName);
		$sql->setInsertFieldValueInteger('thumbnailID', $val->srcID, $databaseName);
		$sql->setInsertFieldValueString('link', $val->linkStored, $databaseName);
		$sql->setInsertFieldValueString('mime', $val->mime, $databaseName);
	}
	
	echo "content thumbs entries created: ".(int)(db2::query($sql))."\n";
}
function createimageListEntries($thisPics){
	$databaseName = 'content_srcImages';
		
	$sql = new dbObj2();
	$sql->setTypeINSERT();
	$sql->setDatabase($databaseName);
	
	foreach($thisPics as $key => $val){
		$sql->setInsertFieldValueInteger('imageID', $val->srcID, $databaseName);
		$sql->setInsertFieldValueInteger('dimensionX', $val->dimensionX, $databaseName);
		$sql->setInsertFieldValueInteger('dimensionY', $val->dimensionY, $databaseName);
		$sql->setInsertFieldValueString('linkStored', $val->linkStored, $databaseName);
		$sql->setInsertFieldValueString('linkFilename', $val->srcID.'.'.$val->mime, $databaseName);
		$sql->setInsertFieldValueInteger('userID', $val->userID, $databaseName);
		$sql->setInsertFieldValueString('hash', $key, $databaseName);
		$sql->setInsertFieldValueString('mime', $val->mime, $databaseName);
		$sql->setInsertFieldValueString('dateCreated', $val->dateCreated, $databaseName);
		$sql->setInsertFieldValueInteger('statusMozed', 1, $databaseName);
		$sql->setInsertFieldValueInteger('statusMetaed', 0, $databaseName);
	}
	//echo $sql->getQueryString();
#####
	echo "content src library entries created: ".(int)(db2::query($sql))."\n";
}
function createSearchEntriesAll($contentIDList, $status){
	if(!$status){
		echo "skip searchEntries ALL in library ... "."\n";
		return;
	}
	//print_r($contentIDList); die();
	
	$databaseName = 'search_contentAll';
	$databaseName2 = 'search_contentCollections';
	$databaseName3 = 'search_contentImages';

	$sql = new dbObj2();
	$sql->setTypeINSERT();
	$sql->setDatabase($databaseName);
	
	$sql2 = new dbObj2();
	$sql2->setTypeINSERT();
	$sql2->setDatabase($databaseName2);
	
	$sql3 = new dbObj2();
	$sql3->setTypeINSERT();
	$sql3->setDatabase($databaseName3);
	
	$max = 0;
	foreach($contentIDList as $key => $val){
		if($val->type != 3)	
			$sql->setInsertFieldValueString('libraryListID', $key, $databaseName);
		if($val->type == 2)
			$sql3->setInsertFieldValueString('libraryListID', $key, $databaseName3);
		else if($val->type == 1)
			$sql2->setInsertFieldValueString('libraryListID', $key, $databaseName2);
	}
#####
	var_dump(db2::query($sql));
	var_dump(db2::query($sql2));
	var_dump(db2::query($sql3));
}
function createContentMatchEntriesImages($contentIDList, $hash,$status){
	// print_r($contentIDList); die();
	
	if(!$status){
		echo "skip matching images to pic in library ... "."\n";
		return;
	}
	
	$databaseName = 'content_matchElements';
	
	$sql = new dbObj2();
	$sql->setTypeINSERT();
	$sql->setDatabase($databaseName);
	
	$pos = 0;
	foreach($contentIDList as $val){
		if($val->type == 1)
			continue;
		
		$sql->setInsertFieldValueString('contentID', $val->contentID, $databaseName);
		$sql->setInsertFieldValueNULL('contentIDSub', $databaseName);
		$sql->setInsertFieldValueInteger('imageID', $hash[$val->hash]['real']->srcID, $databaseName);
		$sql->setInsertFieldValueInteger('position', 1, $databaseName);
		$sql->setInsertFieldValueString('type', 'si', $databaseName);
	}
	echo "matching Pics to Images created: ".(int)(db2::query($sql))."\n";
	echo db2::getLastError();
}
function createContentMatchEntriesCollections($contentIDList, $dups, $status){
	if(!$status){
		echo "skip matching images to collections in library ... "."\n";
		return;
	}
	$res = loadImagesCollectionDependencies();
	
	$cols = [];
	
	foreach($res as $val){	
		if(!isset($cols[$val->collectionID]))
			$cols[$val->collectionID] = [];	
		
		if(!isset($val->dateCreated))
			continue;
		
		$cols[$val->collectionID][] = $val->hash;
	}
	
	foreach($cols as $key => $val){
		if(count($val) < 1){
			#	DELETE THIS ENTRIE
			echo " OHO: ".$key."\n";
		}
	}
	
	$databaseName = 'content_matchElements';
	
	$sql = new dbObj2();
	$sql->setTypeINSERT();
	$sql->setDatabase($databaseName);
	
	$pos = 0;
	foreach($cols as $key => $val){
		$pos = 0;	
		foreach($val as $hashString){
			$pos++;
			//print_r($dups[$hashString]['real']); die();
			$sql->setInsertFieldValueString('contentID', $key, $databaseName);
			$sql->setInsertFieldValueString('contentIDSub', base_convert($dups[$hashString]['real']->base10IDi, 10, 36),$databaseName);
			$sql->setInsertFieldValueNULL('imageID', $databaseName);
			$sql->setInsertFieldValueInteger('position', $pos, $databaseName);
			$sql->setInsertFieldValueString('type', 'i', $databaseName);
		}
	}
	echo "matching Collections and Images created: ".(int)(db2::query($sql))."\n";
	echo db2::getLastError();
}
function getArrayWithTagsOfAvailableCollections($contentIDList, $hash){
	$conListImgID = [];	
	$conListCntID = [];
	foreach($contentIDList as $val){
		if($val->type == 1)
			continue;
			
		$conListImgID[$val->imageID] = $val->contentID;
		$conListCntID[$val->contentID] = $val->imageID;
	}	
	print_r($conListCntID);

	$databaseName = 'match_collection_tags';
	
	$sql = new dbObj();
	$sql->setDatabase($databaseName);
	
	$sql->setSELECTField('collectionID', $databaseName);
	$sql->setSELECTField('tagLink', $databaseName);
	
	$sql->setOrderByField('tagLink', $databaseName);
	$res = db::query($sql);
	
	$tagsByColID = [];
	$tagsByTagLink = [];
	
	foreach($res as $val){
		if(!isset($tagsByColID[$val->collectionID]))
			$tagsByColID[$val->collectionID] = [];
		if(!isset($tagsByTagLink[$val->tagLink]))
			$tagsByTagLink[$val->tagLink] = [];
		
		$tagsByColID[$val->collectionID][$val->tagLink] = $val->collectionID.'-'.$val->tagLink;
		$tagsByTagLink[$val->tagLink][$val->collectionID] = $val->collectionID.'-'.$val->tagLink;
	}
	
	$databaseName = 'match_images_tags';
	
	$sql = new dbObj();
	$sql->setDatabase($databaseName);
	
	$sql->setSELECTField('imageID', $databaseName);
	$sql->setSELECTField('tagLink', $databaseName);
	
	$sql->setOrderByField('tagLink', $databaseName);
	$res = db::query($sql);
	
	foreach($res as $val){
		if(!isset($conListImgID[ $val->imageID ]))
			continue;
		
		$newCntID = $conListImgID[ $val->imageID ];
			
		if(!isset($tagsByColID[$newCntID]))
			$tagsByColID[$newCntID] = [];
		if(!isset($tagsByTagLink[$val->tagLink]))
			$tagsByTagLink[$val->tagLink] = [];
		
		$tagsByColID[$newCntID][$val->tagLink] = $newCntID.'-'.$val->tagLink;
		$tagsByTagLink[$val->tagLink][$newCntID] = $newCntID.'-'.$val->tagLink;
	}
	
	print_r($tagsByTagLink);
	
	$databaseName = 'content_libraryTags';
	$sql = new dbObj2();
	$sql->setTypeINSERT();
	$sql->setDatabase($databaseName);
	
	foreach($tagsByTagLink as $key => $val){
		if(strlen($key)>40){
			echo $key."\n";
			die();
		}
		$sql->setInsertFieldValueString('label', str_replace('-',' ',$key), $databaseName);
		$sql->setInsertFieldValueString('tagLinkS', $key, $databaseName);
		$sql->setInsertFieldValueInteger('counter', count($val), $databaseName);
	}
	
	// echo "tags in tag table created: ".(int)(db2::query($sql))."\n";
	// echo db2::getLastError();
	
	$sql = new dbObj2();
	$sql->setDatabase($databaseName);
	$sql->setSELECTField('ID', $databaseName);
	$sql->setSELECTField('tagLinkS', $databaseName);
	
	$sql->setOrderByField('ID', $databaseName);
	
	$res = db2::query($sql);
	
	$tagIDs = [];
	foreach($res as $val){
		$tagIDs[$val->tagLinkS] = $val->ID;
	}
	
	// insert into matching table
	$databaseName = 'content_matchTags';
	$sql = new dbObj2();
	$sql->setTypeINSERT();
	$sql->setDatabase($databaseName);
	
	foreach($tagsByTagLink as $key => $val){
		if(strlen($key)>40){
			echo $key."\n";
			die();
		}
		foreach($val as $ID => $hash){
			$sql->setInsertFieldValueString('contentID', $ID, $databaseName);
			$sql->setInsertFieldValueString('tagLinkS', $key, $databaseName);
			$sql->setInsertFieldValueString('hash', $hash, $databaseName);
		}
	}

	// echo "tags in tag table created: ".(int)(db2::query($sql))."\n";
	// echo db2::getLastError();
	
	$databaseName = 'content_matchTagsID';
	$sql = new dbObj2();
	$sql->setTypeINSERT();
	$sql->setDatabase($databaseName);
	
	foreach($tagsByTagLink as $key => $val){
		if(strlen($key)>40 || !isset($tagIDs[$key])){
			echo $key."\n";
			die();
		}
		foreach($val as $ID => $hash){
			$sql->setInsertFieldValueString('contentID', $ID, $databaseName);
			$sql->setInsertFieldValueInteger('tagID', $tagIDs[$key], $databaseName);
			$sql->setInsertFieldValueString('hash', $ID.'-'.$tagIDs[$key], $databaseName);
		}
	}

	// echo "tags in tag table created: ".(int)(db2::query($sql))."\n";
	// echo db2::getLastError();
}

function checkDBIntegCollection(){
	$databaseName = 'content_collections';
	$sql = new dbObj2();
	$sql->setTypeSELECT();
	$sql->setDatabase($databaseName);
	$sql->setSELECTField('collectionID', $databaseName);
	$sql->setSELECTField('title', $databaseName);
	$sql->setOrderByField('collectionID', $databaseName,'ASC');
	
	$res2 = db2::query($sql); echo count($res2);
	// print_r($res2);
	
	$databaseName = DBTableNameContentAll;
	$sql = new dbObj();
	$sql->setTypeSELECT();
	$sql->setDatabase($databaseName);
	$sql->setSELECTField('contentID', $databaseName);
	$sql->setSELECTField('title', $databaseName);
	$sql->setConditionStringEqual('type', 'c', $databaseName);
	$sql->setOrderByField('contentID', $databaseName,'ASC');
	
	$res = db::query($sql);
	
	$byID = []; $byTitle = []; $notSet = []; $notTitle = [];
	
	foreach($res as $old){
		$byID[ $old->contentID ] = $old->title;
	}
	
	// print_r($byID);
	foreach($res2 as $new){
		if(!isset($byID[ $new->collectionID ]))
			$notSet[ $new->collectionID ] = $new->title;
		else{
			if($byID[ $new->collectionID ] != $new->title)
				$notTitle[ $new->collectionID ] = $new->title;
		}
	}
	print_r($notSet);
	print_r($notTitle);
}
?>