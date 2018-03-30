<?php
	/**
	 * DEBUG class
	 *
	 * @package AdSocials
	 * @since 1.0.0
	 */
	 
class debug {
	private static $errors = array();
	private static $warnings = array();
	private static $hints = array();
	
	public static function checkAndCopyImageToBotByContentID($cID){
		$databaseName = DBTableNameContentAll;
		
		$sql = new dbObj();
		$sql->setDatabase($databaseName);
		$sql->setTypeDELETE();

		$sql->setConditionStringEqual('contentID', $cID, $databaseName);
		$sql->setConditionIsNULL('thumbnailLink', $databaseName, 'AND');
		$sql->setConditionStringEqual('type', 'i', $databaseName, 'AND');
		$r = db::query($sql);
	}
	
	public static function checkAndCopyImageToBot($i){
		$val = (object)[];
		$val->linkStoredDBEntry = $i;	
		if(!file_exists('/usr/local/lsws/CREEZI/urkraft/public_html/userUploads/thumbnails/'.$val->linkStoredDBEntry.'.jpg')){
			$temp = explode('/',$val->linkStoredDBEntry,999);
			$imageCounter = ($temp[0] * 10000)+$temp[1];
			$moveToTempNEU = SERVER_ROOT.'/neu/';
			
			if(file_exists('/usr/local/lsws/CREEZI/urkraft/public_html/userUploads/images/'.$val->linkStoredDBEntry.'_.jpg')){
				$moveToTempNEU .= $imageCounter.'.jpg';
			} else if(file_exists('/usr/local/lsws/CREEZI/urkraft/public_html/userUploads/images/'.$val->linkStoredDBEntry.'_.png')){
				$moveToTempNEU .= $imageCounter.'.png';
			} else if(file_exists('/usr/local/lsws/CREEZI/urkraft/public_html/userUploads/images/'.$val->linkStoredDBEntry.'_.gif')){
				$moveToTempNEU .= $imageCounter.'.gif';
			} else {
				echo "<!-- not available: ".$val->linkStoredDBEntry."-->\r\n";
				$databaseName = DBTableNameSrcImages;
				$sql = new dbObj();
				$sql->setTypeDELETE();
				$sql->setDatabase($databaseName);
				$sql->setConditionStringEqual('linkStored', $val->linkStoredDBEntry, $databaseName);
				
				db::query($sql);
				
				$databaseName = DBTableNameContentAll;
				$sql = new dbObj();
				$sql->setTypeDELETE();
				$sql->setDatabase($databaseName);
				$sql->setConditionStringEqual('thumbnailLink', $val->linkStoredDBEntry, $databaseName);
				$sql->setConditionStringEqual('type', 'i', $databaseName, 'AND');
				
				db::query($sql);

				return;
			}
			
			fileHandler::createFolder(SERVER_ROOT.'/neu/');
			if(!file_exists($moveToTempNEU)){
				$datei = fopen($moveToTempNEU,"w");
				fclose($datei);
			} else {
				echo "<!-- skip: ".$moveToTempNEU."-->\r\n";
			}
		}
	}
	
	public static function addError($text){
		self::$errors[] = new debugObj($text);
	}
	public static function addWarning($text){
		self::$warnings[] = new debugObj($text);
	}
	public static function addHint($text){
		self::$hints[] = new debugObj($text);
	}
	
	public static function showErrors(){
		print_r(self::$errors);
	}
	public static function showWarnings(){
		print_r(self::$warnings);
	}
	public static function showHints(){
		print_r(self::$hints);
	}
	
	public static function showAll(){
		self::showErrors();
		self::showWarnings();
		self::showHints();
	}
	
	public static function init(){
		if(isDEBUG)
			self::loadDebugger();
		else
			self::hideErrorMessages();
	}
	public static function xray($item){
		var_dump($item);
		echo '<br /><br />';	
		print_r($item);
		
		die();
	}
	
	#
	#	-----------------------------------------
	#
	
	private static function loadDebugger(){
		error_reporting(E_ALL);
		ini_set("display_errors", 1);
		ini_set('error_reporting', E_ALL);
	}
	private static function hideErrorMessages(){
		error_reporting(0);
		ini_set("display_errors", 0);
	}
	
}

class debugObj extends debug {
	public $timestamp = 0;
	public $timeStampScript = 0;
	
	public $file = '';
	public $line = 0;
	
	public $called = '';
	public $text = '';
	
	function __construct($text){
		$this->timestamp = microtime();
		$this->timeStampScript = core::getScriptTimeStampDelta();
		$this->text = $text;
		
		$e = new Exception();
    	$trace = $e->getTrace();
		
		$this->file = $trace[1]['file'];
		$this->line = $trace[1]['line'];
		$this->called = $trace[1]['function'];
	}
}
?>