<?php
class user extends security{
	private static $init = false;
	private static $uploadingFormID = 0;
	private static $userScopes = array();
	private static $userHash = NULL;
	private static $isLoadedUserHashFromSession = FALSE;
	private static $uploadingFormIDClosed = [];
	
	private static function isInit(){
		if(!is_a(parent::getUserObject(),'userObj'))
			return FALSE;

		return TRUE;
	}
	public static function generateUploadingFormID(){
		self::$uploadingFormID = security::getHashSimple();
		security::getSession()->setKeyValue('uploadFormValue', self::$uploadingFormID);
	}
	public static function isValidUploadForm(){
		if(isset(self::$uploadingFormIDClosed[self::$uploadingFormID]))
			return FALSE;
		
		return TRUE;
	}
	public static function closeUploadingFormular(){
		self::$uploadingFormIDClosed[self::$uploadingFormID] = TRUE;
		security::getSession()->deleteKey('uploadFormValue');
	}
	public static function generateUserIdent($force = false){
		if($force || security::getSession()->getKeyValue('userHash') === NULL){
			self::$userHash = security::getHashSimple();
			security::getSession()->setKeyValue('userHash', self::$userHash);
		}
	}
	public static function loadUserIdentFromSession(){
		self::generateUserIdent();	
		self::$userHash = security::getSession()->getKeyValue('userHash');
		self::$isLoadedUserHashFromSession = TRUE;
	}
	public static function isSetUserIdent(){
		if(!self::$isLoadedUserHashFromSession)
			self::loadUserIdentFromSession();
		
		if(self::$userHash !== NULL)
			return TRUE;
		
		return FALSE;
	}
	public static function getUserIdentHash(){
		self::isSetUserIdent();	
		return self::$userHash;
	}
	public static function getUploadingFormID(){
		return self::$uploadingFormID;
	}
	public static function getEmail(){	
		if(!self::isInit() || !parent::getUserObject()->isLoggedIn() || empty(parent::getUserObject()->getEmail()))		
			return NULL;
		else 
			return parent::getUserObject()->getEmail();
	}
	public static function getID(){	
		if(!self::isInit() || !parent::getUserObject()->isLoggedIn() || empty(parent::getUserObject()->getUserID()))		
			return NULL;
		else 
			return parent::getUserObject()->getUserID();
	}
	public static function getDBID(){	
		if(!self::isInit() || !parent::getUserObject()->isLoggedIn() || empty(parent::getUserObject()->getDatabaseID()))		
			return NULL;
		else 
			return parent::getUserObject()->getDatabaseID();
	}
	public static function getDBIDCloaked(){
		if(!self::isInit() || !parent::getUserObject()->isLoggedIn() || empty(parent::getUserObject()->getDatabaseIDCloaked()))		
			return self::getDBID();
		else 
			return parent::getUserObject()->getDatabaseIDCloaked();
	}
	public static function getNick(){
		if(!self::isInit()|| !parent::getUserObject()->isLoggedIn() || empty(parent::getUserObject()->getNickname()))
			return NULL;
		else 
			return parent::getUserObject()->getNickname();
	}
	
	public static function getAvatarURL(){	
		if(!self::isInit() || !parent::getUserObject()->isLoggedIn() || empty(parent::getUserObject()->getAvatarURL()))
			return HTTP_HOST.'/ressources/'.frontendLayoutDIR.'/images/login.png';
		else 
			return parent::getUserObject()->getAvatarURL();
	}
	public static function getURL(){		
		if(!self::isInit() || !parent::getUserObject()->isLoggedIn() || empty(parent::getUserObject()->getUserURL()))
			#var_dump(parent::getUserObject());
			return NULL;
		else 
			return parent::getUserObject()->getUserURL();
	}
	public static function getAPISignature(){
		if(!parent::getUserObject()->isLoggedIn())
			#var_dump(parent::getUserObject());
			return NULL;
		else 
			return parent::getUserObject()->getAPISignatureKey();
	}
	public static function setRequiredUserScope($enumUserScope){
		self::$userScopes[$enumUserScope];
	}
	private static $isActiveUserPage = false;
	private static $isActiveUserPageID = 0;
	public static function getUserFromDBByUserURL($url){
		if(!is_string($url))
			return FALSE;
		
		$databaseName = DBTableNameUser;
		$sql = new dbObj();
		$sql->setTypeSELECT();
		$sql->setDatabase($databaseName);
		
		$sql->setSELECTField('userLevel', $databaseName);
		$sql->setSELECTField('nick', $databaseName);
		$sql->setSELECTField('ID', $databaseName);
		$sql->setSELECTField('avatarHTML', $databaseName);
		
		$sql->setConditionStringEqual('userURL', $url, $databaseName);
		
		$res = db::query($sql);
		if(!isset($res[0]))
			return FALSE;
		
		$obj = new userObj($res[0]->nick, authMethodEnum::DB);
		$obj->setAvatarURL($res[0]->avatarHTML);
		$obj->setDatabaseID($res[0]->ID);
		//$obj->setLoginTimeLast($res[0]->lastLogin);
		$obj->setUserLevel($res[0]->userLevel);
		
		self::$isActiveUserPage = TRUE;
		self::$isActiveUserPageID = $res[0]->ID;
		return $obj;
	}
	public static function isActiveUserPage(){
		return self::$isActiveUserPage;
	}
	public static function getActiveUserDBID(){
		return self::$isActiveUserPageID;
	}
}

class userObj {
	private $authMethod = authMethodEnum::NONE;
	private $isLoggedIn = false;
    	
	private $id = null;
	private $databaseID = 0;
	private $databaseIDCloaked = 0;
	private $salt = null;
	private $userLevel = userLevelEnum::NONE;
	
	private $firstName = null;
	private $surName = null;
	private $nickName = null;
	
	private $email = null;
	private $paypal = null;
	
	private $loginTimeLast = null;
	private $loginTimeThis = null;
	
	private $loginRequestURL = null;
	private $avatarURL = null;
	private $apiSig = NULL;
	
	private $userURL = 'user-NULL';
	
	public function __construct($nickName, $authMethodEnum){
		security::forceInt($authMethodEnum);
		if($nickName !== FALSE && $authMethodEnum > authMethodEnum::NONE){
			$this->nickName = $nickName;
			$this->authMethod = $authMethodEnum;
            $this->isLoggedIn = true;
		} else if($nickName === FALSE){
			$this->nickName = 'John Doe';
		}
	}
    public function isLoggedIn(){
        return $this->isLoggedIn;
    }
	public function setAuthMethod($authMethodEnum){
		security::forceInt($authMethodEnum);
		$this->authMethod = $authMethodEnum;
		return $this;
	}
	public function setID($ID){
		security::forceInt($ID);
		if($ID > 0)
			$this->id = (int)$ID;
		else
			$this->id = 0;
		
		return $this;
	}
	public function setSalt($salt){
		$this->salt = $salt;
		return $this;
	}
	public function setUserLevel($userLevelEnum){
		security::forceInt($userLevelEnum);	
		$this->userLevel = $userLevelEnum;
		return $this;
	}
	public function setDBIDCloaked($NICKNAME){
		if(empty($NICKNAME))
			return; 
			
		$res = dbQueries::get()->userIDfromUserNick( $NICKNAME );
		
		if(isset($res[0]->ID))
			$this->databaseIDCloaked = (int)$res[0]->ID;
		else
			$this->databaseIDCloaked = NULL;
		
		return;
	}
	public function setUserURL($url){
		$this->userURL = $url;
		return $this;
	}
	public function setAPISignatureKey($key){
		$this->apiSig = $key;
		return $this;
	}
	public function setAvatarURL($url){
		$this->avatarURL = $url;
		return $url;
	}
	public function setFirstName($string){
		$this->firstName = $string;
		return $this;
	}
	public function setSurName($string){
		$this->surName = $string;
		return $this;
	}
	public function setNickName($string){
		$this->nickName = $string;
		return $this;
	}
	public function setEmail($string){
		$this->email = $string;
		return $this;
	}
	public function setPaypal($string){
		$this->paypal = $string;
	}
	public function setLoginTimeLast($int){
		security::forceInt($int);	
		$this->loginTimeLast = $int;
		return $this;
	}
	public function setLoginTimeThis($int){
		security::forceInt($int);		
		$this->loginTimeThis = $int;
		return $this;
	}
	public function setLoginRequestURL(urlObj $url){
		$this->loginRequestURL = $url->getPathArray();
	}
	public function setDatabaseID($ID){
		security::forceInt($ID);		
		$this->databaseID = abs($ID);
		return $this;
	}
	#
	#	------------------------------------------------
	#
	public function getUserLevel(){
		return $this->userLevel;
	}
	public function getAvatarURL(){
		return $this->avatarURL;
	}
	public function getNickname(){
		return $this->nickName;
	}
	public function getEmail(){
		return $this->email;
	}
	public function getUserID(){
		return $this->id;
	}
	public function getDatabaseID(){
		return $this->databaseID;
	}
	public function getDatabaseIDCloaked(){
		if($this->databaseIDCloaked === NULL || $this->databaseIDCloaked == 0)
			return (int)$this->databaseID;
		return (int)$this->databaseIDCloaked;
	}
	public function getUserURL(){
		return $this->userURL;
	}
	public function getAPISignatureKey(){
		return $this->apiSig;
	}
}
?>