<?php
class api {
	private static $isForced = false;	
	private static $template = null;
	private static $templateType = 0;
	
	private static $core = null;
	
	private static $status = 200;
	private static $statusString = 'OK';
	
	private static $version = '1.0.0.1';
	
	public static function forceOn(){
		self::$isForced = true;
	}
	public static function forceOff(){
		self::$isForced = false;
	}
	public static function isForced(){
		return self::$isForced;
	}
	public static function loadLoginTemplate(){
		self::$template = new apiLogin();
		self::$templateType = apiTemplateEnum::LOGIN;
	}
	public static function loadInfoTemplate(){
		self::$template = new apiInfo();
		self::$templateType = apiTemplateEnum::TIME;
	}
	public static function loadPublishPostsTemplate(){
		self::$template = new apiPublishPosts();
		self::$templateType = apiTemplateEnum::TIME;
	}
	
	public static function loadStatusUserTemplate(){
		self::$template = new apiStatusUser();
		self::$templateType = apiTemplateEnum::statusUSER;
	}
	public static function setLoginStatus($bool){
		if(self::$templateType != apiTemplateEnum::LOGIN)
			self::loadLoginTemplate();
		
		self::$template->setStatus($bool);
	}
	
	public static function setLoginKey($key){
		if(self::$templateType != apiTemplateEnum::LOGIN)
			self::loadLoginTemplate();
		
		self::$template->setKey($key);
	}
	
	public static function setLoginType($key){
		if(self::$templateType != apiTemplateEnum::LOGIN)
			self::loadLoginTemplate();
		
		self::$template->setType($key);
	}
	
	public static function send403(){
		self::$status = 403;
		self::$statusString = 'Access Denied.';
		
		self::sendArray();
		
		exit;
	}
	
	public static function send200(){
		self::$status = 200;
		self::$statusString = 'OK';
		
		self::sendArray();
		
		exit;
	}
	
	public static function setUploadTyp($value){

	}
		
	public static function loadUploadTemplate(){
		self::$template = new apiUpload();
		self::$templateType = apiTemplateEnum::UPLOAD;
	} 
	public static function loadAJAXTemplate(){
		self::$template = new AJAXUpload();
		self::$templateType = apiTemplateEnum::UPLOAD;
	} 
	public static function getTemplateObject(){
		return self::$template;
	}
	private static function checkCrossDomain(){
		if (!isset($_SERVER['HTTP_ORIGIN'])) {
		    // This is not cross-domain request
		    return;
		}
		
		$wildcard = FALSE; // Set $wildcard to TRUE if you do not plan to check or limit the domains
		$credentials = TRUE; // Set $credentials to TRUE if expects credential requests (Cookies, Authentication, SSL certificates)
		$allowedOrigins = array('https://86.106.113.42');
		if (!in_array($_SERVER['HTTP_ORIGIN'], $allowedOrigins) && !$wildcard) {
		    // Origin is not allowed
		    return;
		}
		$origin = ($wildcard && !$credentials) ? '*' : $_SERVER['HTTP_ORIGIN'];
		
		header("Access-Control-Allow-Origin: " . $origin);
		if ($credentials) {
		    header("Access-Control-Allow-Credentials: true");
		}
		header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
		header("Access-Control-Allow-Headers: Origin");
		header('P3P: CP="CAO PSA OUR"'); // Makes IE to support cookies
		
		// Handling the Preflight
		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { 
		    return;
		}
		
		// Response
		return;

	}
	private static $globalErrors = [];
	public static function addGlobalError($string){
		self::$globalErrors[] = $string;
	}
	private static function sendArray(){
		header('Content-Type: application/json');
		self::checkCrossDomain();
		if(self::$template !== NULL)
			$data = self::$template->getDataArray();
		else
			$data = [];
        
        $dc = (object)[];
        if(isset($_POST['shake']))
            $temp = @json_decode($_POST['shake']);
        if(!empty($temp))
            $dc = $temp;
        
		if(empty(self::$globalErrors))
			echo json_encode(array(
				'status' => &self::$status,
				'statusCode' => &self::$statusString,
				'type' => &self::$templateType,
				'data' => $data,
				'shake' => $dc
			));
		else
			echo json_encode(array(
				'status' => &self::$status,
				'statusCode' => &self::$statusString,
				'type' => &self::$templateType,
				'error' => implode(',',self::$globalErrors),
				'data' => $data,
				'shake' => $dc
			));
	}
	private static $apiListener = NULL;
	public static function loadAPIListener(){
		self::$apiListener = new apiListener();
		self::$apiListener->init();
	}
	public static function getAPIListener():apiListener{
		return self::$apiListener;
	}
}

class apiLogin{
	private $type = null;
	private $key = null;
	private $status = false;
	
	public function __construct(){
		
	}
	public function setStatus($bool){
		$this->status = $bool;
	}
	public function setKey($string){
		$this->key = $string;
	}
	public function setType($string){
		$this->type = $string;
	}
	public function getDataArray(){
		return array(
				'type' => &$this->type,
				'key' => &$this->key,
				'status' => &$this->status
			);
	}
}
class AJAXUpload {
	private $status = 200;
	private $contentData = [];
	private $contentUser = [];
    
	private $error = [];
	private $errorMsg = [];
	private $type = 0;
	
    function __construct(){
        $this->contentData = (object)[];
        $this->contentUser = (object)[];
    }
    
	public function setErrorAsInfo($msg){
		$this->errorMsg = $msg;
	}
	public function addStringNewContentElement($id,$type,$string){
		if($string === NULL)
			return;
				
		$obj = new apiUpload();
		$obj->setImageID($id);
		$obj->setType($type);
		$obj->setConfirmedInfoString($string);
		
		$this->contentData->elements[] = $obj->getDataArray();
	}
    
	public function addContentID(string $ID){
	    $this->contentData->id = $ID;
	}
    public function addContentType(string $type){
        $this->contentData->type = $type;
    }
    public function addContentElement(stdClass $element){
        if(!isset($this->contentData->elements))
            $this->contentData->elements = [];
            
        $this->contentData->elements[] = $element;
    }
    public function addContentThumbURL(string $url){
        $this->contentData->thumbURL = $url;
    }
    public function addContentInfo(string $info){
        $this->contentData->info = $info;
    }
    public function addError( int $error = 0, string $string ){
        $this->error[] = $error; $this->errorMsg[] = $string;
    }
	public function setStatus($status){
		$this->status = (bool)$status;
	}
    
	public function setType($t){
		$this->type = (int)$t;
	}
	public function getDataArray(){
		return array(
			'type' => 'AJAX',
			'status' => &$this->status,
			'error' => &$this->error,
			'errorMsg' => &$this->errorMsg,
			'content' => &$this->contentData,
			'user' => &$this->contentUser,
		);
	}
}
class apiUpload {
	private $type = null;
	private $status = false;
	private $url = '';
	private $thumb = '';
	private $error = '0';
	private $id = NULL;
	private $info = NULL;
	
	public function __construct(){
		
	}
	public function setStatus($bool){
		$this->status = $bool;
		return $this;
	}
	public function setType($string){
		$this->type = $string;
		return $this;
	}
	public function setUrl($url){
		$this->url = $url;
		return $this;
	}
	public function setThumb($thumbUrl){
		$this->thumb = $thumbUrl;
		return $this;
	}
	public function setImageID($id){
		$this->id = $id;
		return $this;
	}
	public function getDataArray(){
		return array(
				'type' => &$this->type,
				'status' => &$this->status,
				'url' => &$this->url,
				'thumbUrl' => &$this->thumb,
				'error' => &$this->error,
				'errorMsg' => &$this->errorMsg,
				'id' => &$this->id,
				'lastInfo' => &$this->info
			);
	}
	public function setErrorOnUpload($msg){
		$this->error = '100';
		$this->errorMsg = $msg;
	}
	public function setErrorAsInfo($msg){
		$this->errorMsg = $msg;
	}
	public function setConfirmedInfoString($infoString){
		$this->info = $infoString;
		return $this;
	}
}
class apiInfo {
	private $type = NULL;
	private $key = null;
	private $status = false;
	private $time = NULL;
	private $errors = [];
	
	public function __construct(){

	}
	public function setKey($string){
		$this->key = $string;
	}
	public function setType($string){
		$this->type = $string;
	}
	public function setTime($string){
		$this->time = $string;
	}
	public function addError($string){
		$this->errors[] = $string;
	}
	public function getDataArray(){
		return array(
				'type' => &$this->type,
				'info' => &$this->key,
				'time' => &$this->time,
				'error' => &$this->errors
			);
	}
}
class apiStatusUser {
	private $type = 'user';
	private $key = null;
	private $status = false;
	private $time = NULL;
	
	private $name;
	private $urlProfile;
	private $email;
	
	public function __construct(){

	}
	public function setKey($string){
		$this->key = $string;
	}
	public function setStatus($string){
		$this->status = $string;
	}
	public function setTime($string){
		$this->time = $string;
	}
	public function setUserName($string){
		$this->name = $string;
	}
	public function setUserURLProfile($string){
		$this->urlProfile = $string;
	}
	public function setUserEmail($string){
		$this->email = $string;
	}
	public function setSignature($string){
		$this->info = $string;
	}
	public function getDataArray(){
		return array(
				'status' => &$this->status,
				'info' => &$this->info,
				'time' => &$this->time,
				'name' => &$this->name,
				'urlProfile'=> &$this->urlProfile,
				'email'=> &$this->email
			);
	}
}
class apiPublishPosts{
	private $type = NULL;
	private $amount = 0;
	private $files = [];
	private $time = NULL;
	private $errors = [];
	
	public function __construct(){

	}
	public function addAmountPlusOne(){
		$this->amount++;
	}
	public function addFiles($array){
		$this->files[] = $array;
	}
	public function setTime($string){
		$this->time = $string;
	}
	public function addError($string){
		$this->errors[] = $string;
	}
	public function getDataArray(){
		return array(
				'amount' => &$this->amount,
				'files' => &$this->files,
				'time' => &$this->time,
				'error' => &$this->errors
			);
	}
}
class apiErrors {
	public static function notPermissionUser(){
		return 'no user permission';
	}
	public static function notStorage(){
		return 'could not store data';
	}
}
?>