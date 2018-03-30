<?php
/*die();
$databaseName = DBTableNameContentImages;
$sql = new dbObj();
$sql->setTypeSELECT();
$sql->setDatabase($databaseName);
$sql->setSELECTField('imageID', $databaseName);
$sql->setConditionIntegerHigher('ID', 5132, $databaseName);

$res = db::query($sql);
//print_r($res);
// print_r($res = db::query($sql));
$databaseName = 'match_collection_images'; $databaseName2 = DBTableNameContentAll;
$sql = new dbObj(); $sql2 = new dbObj();
$sql->setTypeINSERT(); $sql2->setTypeDELETE();
$sql->setDatabase($databaseName); $sql2->setDatabase($databaseName2);
$i = 1;
foreach($res as $val){
	$sql->setInsertFieldValueString('collectionID', '7y', $databaseName);
	$sql->setInsertFieldValueString('imageID', $val->imageID, $databaseName);
	$sql->setInsertFieldValueInteger('position', $i, $databaseName);
	$sql->setInsertFieldValueString('hash', '7y-'.$val->imageID, $databaseName);
	$i++;
	if($i == 2)
	$sql2->setConditionStringEqual('imageID', $val->imageID, $databaseName2);
	else
	$sql2->setConditionStringEqual('imageID', $val->imageID, $databaseName2,'OR');
}
//var_dump(db::query($sql));
var_dump(db::query($sql2));
echo $sql2->getQueryString();
die();*/

$wallpaperDir = '/usr/local/lsws/CREEZI/urkraft/public_html/sebCollections';
$url = 'https://creezi.com/sebs-wallpaper-function';

//	Check if there is any POST parameter for a specific SubFolder
	if(!isset($_POST['folder'])){
		//	If not, read Folder
			$subDirs = fileHandler::getSubFolderAsArray( $wallpaperDir );
			if(empty($subDirs) || count($subDirs) < 1)
				exit();
			
			foreach($subDirs as $dir){
				//  check, if folder is already craswled  //  if, skip folder
					if(fileHandler::isFileInFolder($dir, 'crawled.txt'))
						continue;
				//  if not, crawl folder
					$asynch = new asynchronCall(); // ::;
					$getArray_keyName_value = ['folder' => $dir];
					
					if(!$asynch->simplePOSTCall($url, $getArray_keyName_value))
						echo '<span style="color: red">error: '.$url." _ ".implode(': ', $getArray_keyName_value).'</span><br />';
					else
						echo '<span style="color: green">called: '.$url." _ ".implode(': ', $getArray_keyName_value).'</span><br />';
			}
		exit();
	} else {
		//  check if POST Folder exists
			if(!stristr($_POST['folder'], $wallpaperDir) || !fileHandler::isFolder($_POST['folder']))
				exit();
		//  if so, read folder and check if already crawled
			if(fileHandler::isFileInFolder($_POST['folder'], 'crawled.txt'))
				exit();
		//  crawl Folder
			$files = fileHandler::getSubFilesAsArray($_POST['folder']);
			$info = [];
			
		//	if file _info.txt exits and minimum 1 extra file then conjtinue crawling	
			if(in_array('_info.txt', $files) && count($files) > 1){
				// read _info.txt
				$handle = fopen($_POST['folder'].'/_info.txt', 'r');
				if ($handle) {
					$i = 0;
				    while (($line = fgets($handle)) !== false) {
				        if($i > 1)
							break;
						$info[] = $line;
						$i++;
				    }
				
				    fclose($handle);
				} else 
					exit();
			} else
				exit();
		//  check info credentials
			$title = 'this collection needs a title...';
			$tags = [];
			if(isset($info[0]) &&  strlen($info[0]) > 3)
				$title = str_replace(array("\r","\n"),'',$info[0]);
			if(isset($info[1]) &&  strlen($info[1]) > 1)
				$tags = str_replace(', ',',',$info[1]);
			
		//  create collection json for parsing to function
			$json = (object)['time' => 123, 'userAlias' => 'Bastian', 'files' => NULL, 'coltitle' => $title, 'coltags' => $tags];
			$json->files = [];
			
			$i = 0;
			foreach($files as $val){
				if($i > 5000)
					break;	
				if($val == '_info.txt')
					continue;
					
				$size = @getimagesize($_POST['folder'].'/'.$val);
				if(!isset($size[0]) || !isset($size[1]) || $size[0] < 1920 || $size[1] < 1080)
					continue;
				$newPic = (object)['title' => '_', 'url' => $_POST['folder'].'/'.$val];
				$json->files[] = $newPic;
				$i++;
			}
			//print_r($json);
		
		//  send collection json expression to apiFunction to create collection the "vanilla way"
			$array = [
						'usersignature' => '123456dfgdfgdfgdfg653656tegh',
						'time' => '123456',
						'user' => 'xeroxmm',
						'amount' => count($json->files),
						'data' => json_encode($json)
					];
			$asynch = new asynchronCall();
			if($asynch->simplePOSTCall('https://creezi.com/api/publish/collection', $array))
				fileHandler::createFile($_POST['folder'], 'crawled.txt', date("Y-m-d H:i:s"));
			
		//  DONE
		exit();
	}
?>