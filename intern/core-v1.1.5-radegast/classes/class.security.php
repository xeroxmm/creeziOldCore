<?php

	/**
	 * Security class
	 *
	 * @package AdSocials
	 * @since 1.0.0
	 */

class security{
	private static $isInit = false;
	private static $thisUser = null;
	private static $theRequestURL = null;
	
	private static $backendObject = null;
	private static $theSession = null;
	
	public static function init(){
		if(self::$isInit)
			return;
		
		self::initSession();
		self::loadRequestData();
		#self::buildBackendObject();
		self::$isInit = true;
	}
	
	public static function getRequestURL(){
		return self::$theRequestURL;
	}
	public static function isUserAbleToEditContent($contentID, $userID = NULL){
		$status = FALSE;
		
		if(user::getUserObject()->getUserLevel() >= userLevelEnum::SUPERADMIN)
			return TRUE;
		
		$res = dbQueries::get()->userOwnerOfPost( ( ($userID !== NULL) ? $userID : user::getDBID() ), $contentID);
		if(isset($res[0]->ID))
			return TRUE;
		
		return $status;
	}
	public static function getNormalizedLinkURL($string){
		$link = str_replace( "&nbsp;", " ", strtolower($string) );
		$link = str_replace(' ','-',$link);
	    
	    $link = mb_convert_case($link, MB_CASE_LOWER, "UTF-8"); //convert to lowercase
	    $link = preg_replace("#[^a-zA-Z0-9]+#", "-", $link); //replace everything non an with dashes
	    $link = preg_replace("#(-){2,}#", "$1", $link); //replace multiple dashes with one
	    $link = trim($link, "-."); //trim dashes from beginning and end of string if any
			    
		if(strlen($link) > 100)
			$link = substr($link,0,95).'-more';
			    
		return $link;
	}
	
	public static function loadUserLevel($userLevelEnum){
		security::forceInt($userLevelEnum);	
		self::checkInit();

		if(self::$thisUser->getUserLevel() < $userLevelEnum)
			html::send403();
		
		return true;
	}
	
	public static function forceInt(&$int){
		return ((int)$int);
	}
	public static function getHashSimple(){
		return md5(mt_rand(0, 99999999999).mt_rand(0, 99999999999));
	}
	public static function getTagArrayFromRequestString($urlObj){
		if(!is_array($urlObj) || count($urlObj) < 2)
			output::force404();
			
		$ret = [];
		foreach($urlObj as $val){
			if(	$val == LINK_tagPageSingle || 
				$val == LINK_searchPage || 
				$val == 'act' || 
				$val == 'track' || 
				$val == 'images' || 
				$val == 'videos'|| 
				$val == 'gifs' || 
				$val == 'articles'|| 
				$val == 'links'|| 
				$val == 'collections')
				continue;
			
			if(!empty($val) && strlen($val) > 1){
				if(isset($ret[$val]))
					output::force404();
					
				$ret[count($ret)] = $val;
			}
		}
		if(count($ret) < 1)
			output::force404();
		
		return $ret;
	}
	
	public static function getTagStringHarmonized($text){
		$tagString = '';	
		for($i = 0; $i < mb_strlen($text, "UTF-8"); $i++){	
			$char = mb_substr($text,$i,1,"UTF-8");
			if(ord($char) < 32 || ord($char) > 166 || in_array($char, array('!','"','·','#','$','~','ª','º','\\','%','&','/','(',')','=','?','\'','¿','¡','^','`','[',']','*','+','{','}','´',';',':','_','.','<','>','@','|')))
				$char = ',';
			$tagString .= $char;
		}
		
		return $tagString;
	}
	public static function getTagNameHarmonized($text){
		$tagString = '';	
		for($i = 0; $i < mb_strlen($text, "UTF-8"); $i++){	
			$char = mb_substr($text,$i,1,"UTF-8");
			if(ord($char) < 32 || ord($char) > 166 || in_array($char, array('!','"','·','#','$','~','ª','º','\\','%','&','/','(',')','=','?','\'','¿','¡','^','`','[',']','*','+','{','}','´',';',':','_','.','<','>','@','|')))
				$char = '';
			$tagString .= $char;
		}
		
		$tagString = str_replace( "&nbsp;", " ", strtolower($tagString) );
	    
	    $tagString = preg_replace("#[^a-zA-Z0-9 ]+#", "-", $tagString); //replace everything non an with dashes
	    $tagString = preg_replace("#( ){2,}#", "$1", $tagString); //replace multiple dashes with one
	    $tagString = trim($tagString, "-. "); //trim dashes from beginning and end of string if any
		return $tagString;
	}
	public static function getTagStringArrayHarmonized($text){
		$text = str_replace( array('#',',',';','.',"\r","\n") , "," , $text );   
		$arr = explode(",", $text);
		for($i = 0; $i < count($arr); $i++){
			if(strlen($arr[$i]) < 2)
				unset($arr[$i]);
		}
		$arr = array_values($arr);
		
		$arr2 = array();
		foreach($arr as $val){
			$arr2[$val] = $val;
		}
		
		return $arr2;
	}
	
	public static function doLogoutProcess(){
		# Delete Cookie from 2nd-Party
			self::deleteSecondPartyAuthKeyFromDB(self::$thisUser);
				
		# Delete Session
			self::$theSession->delete();
		
		# Delete Cookies
			cookie::deleteAll();
		
		# Delete Cookie in DB
			cookie::deleteDBEntry();
	}
	private static function deleteSecondPartyAuthKeyFromDB(userObj $userIDinDB){
		$userID = (int)$userIDinDB->getDatabaseID();
		
		if($userID < 1)
			return;
		$res = NULL;
		if(!isset($res[0]->networkID))
			return;
		
		switch ($res[0]->authMethod){
			case 'google':
				if(!isset($res[0]->longLiveToken) || empty($res[0]->longLiveToken))
					return;
				
				/*$client = new Google_Client();
				$client->setApplicationName("The Folder - Logout");
				$client->setClientId(googlePlusKey);
				$client->setClientSecret(googlePlusSecret);
				#$client->setRedirectUri(HTTP_HOST);
				$client->setDeveloperKey(googlePlusServerKey);
				#$client->addScope("https://www.googleapis.com/auth/userinfo.email");
				
				print_r($client->revokeToken($res[0]->longLiveToken));
				*/
				#$url = 'https://accounts.google.com/o/oauth2/revoke?token='.$res[0]->longLiveToken;

				#@file_get_contents($url);
				
				break;
		}
		
		return;
	}
	public static function doLoginProcess(){
		$sendToURL = FALSE;	

		if(!isset($_GET['code']) && !isset($_GET['loginType'])){
			if(cookie::isUserCookieValid()){	
				html::sendHome();
				exit;
			} else {	
				html::send403();
				exit;
			}
		}
		api::loadLoginTemplate();
		
		if($_GET['loginType'] == 'ggl'){
			if(!self::loginByGoogle())
				api::send403();
			
			$sendToURL = $_SERVER['HTTP_HOST'];
		} else if($_GET['loginType'] == 'fbk'){		
			if(!self::loginByFacebook())
				api::send403();
			
			$sendToURL = $_SERVER['HTTP_HOST'];
			
		} else if($_GET['loginType'] == 'stm'){	
			if(!self::loginBySteam())
				api::send403();
			
			$sendToURL = $_SERVER['HTTP_HOST'];
		} else {
			api::send403();
			exit;
		}

		cookie::setUserCookie(self::$thisUser);
		
		$databaseName = DBTableNameUser;
		$sql = new dbObj();
		
		$sql->setTypeSELECT();
		$sql->setDatabase(DBTableNameUser);		
		
		$sql->setSELECTField('ID', $databaseName);
		$sql->setSELECTField('userURL', $databaseName);
		
		$sql->setConditionIntegerEqual('ID', self::$thisUser->getDatabaseID(), $databaseName);
		
		$res = db::query($sql);

		if(isset($res[0]->userURL)){
			self::$thisUser->setUserURL($res[0]->userURL);
		}
		self::setUserSessionByUserObject(self::$thisUser);
		
		if(!$sendToURL){
			api::setLoginStatus(true);
			api::send200();
		} else {
			html::sendHome();
		}
	}
	
	private static function setUserSessionByUserObject(userObj $userObject){
		self::$theSession->setKeyValue('userID', $userObject->getDatabaseID());
		self::$theSession->setKeyValue('userName', $userObject->getNickname());
		self::$theSession->setKeyValue('userLevel', $userObject->getUserLevel());
		self::$theSession->setKeyValue('userAvatarURL', $userObject->getAvatarURL());
		self::$theSession->setKeyValue('userURL', $userObject->getUserURL());
	}
	
	public static function backendSite(){
		return self::$backendObject;
	}
	public static function getSession(){
		return self::$theSession;
	}
	public static function getLastURL(){
		return self::$theSession->getLastURL();
	}
    public static function isLoggedIn(){
        if(self::$thisUser === NULL)
            return false;
        return self::$thisUser->isLoggedIn();
    }
	public static function getRandomString($length){
		$keySet = '0123456789qwertzuiop+asdfghjkl#yxcvbnm=QWERTZUIOP*ASDFGHJKLYXCVBNM;:';
		$maxLength = strlen($length) - 1;
		$random = '';
		
		for($i = 0; $i < $maxLength; $i++){
			$random .= $keySet[mt_rand(0, $maxLength)];
		}
		
		return $random;
	}
	#
	#	--------------------------------------
	#
	private static function initSession(){
		self::$theSession = new session();		
	}
	
	private static function checkInit(){
		if(!self::$isInit)
			self::init();
	}
	private static function buildBackendObject(){
		self::$backendObject = new pathMap();
		//self::$backendObject->setNamespace(backendURL);
	}
	private static function loadRequestData(){
		# TODO Fill laodReQuestData-Function
		
		# Try to load USER by SESSION
			if(self::isUserLoadedBySession()){
				return;
		# Try to load USER by COOKIES
			} else if(self::isUserLoadedByCookies())
				return;
		
		self::$thisUser = new userObj(FALSE, authMethodEnum::NONE);
	}
	
	private static function isUserLoadedBySession(){
		if( self::$theSession->isKeySet('userID') && 
			self::$theSession->isKeySet('userName') && 
			self::$theSession->isKeySet('userLevel') &&
			self::$theSession->isKeySet('userAvatarURL') && 
			self::$theSession->isKeySet('userURL')){
			
			self::$thisUser = new userObj(self::$theSession->getKeyValue('userName'),authMethodEnum::SESSION);
			
			self::$thisUser->setDatabaseID(self::$theSession->getKeyValue('userID'));
			self::$thisUser->setAvatarURL(self::$theSession->getKeyValue('userAvatarURL'));
			self::$thisUser->setUserLevel(self::$theSession->getKeyValue('userLevel'));
			self::$thisUser->setUserURL(self::$theSession->getKeyValue('userURL'));
			//print_r($_SESSION);
			return TRUE;
		} else
			return FALSE;
	}
	
	private static function isUserLoadedByCookies(){
		# Load UserID from Cookie and check with DB
		//echo "<!-- cooking -->";
		if(!cookie::isUserCookieValid())
			return FALSE;
		// echo "<!-- valid -->";
		$lastRes = cookie::getLastCookieDBObject();
		if($lastRes === NULL || !isset($lastRes->ID) || (int)$lastRes->ID < 1)
			return FALSE;
		//echo "<!-- lastCookie -->";
		$databaseName = DBTableNameUser;
		$sql = new dbObj();
		$sql->setTypeSELECT();
		
		$sql->setDatabase($databaseName);
		
		$sql->setSELECTField('nick', $databaseName);
		$sql->setSELECTField('avatarHTML', $databaseName);
		$sql->setSELECTField('userLevel', $databaseName);
		$sql->setSELECTField('ID', $databaseName);
		$sql->setSELECTField('userURL', $databaseName);
		
		$sql->setConditionIntegerEqual('ID', $lastRes->userID, $databaseName);
		
		$res = db::query($sql);
		
		if(!isset($res[0]->ID))
			return FALSE;
		//echo "<!-- query -->";
		self::$thisUser = new userObj($res[0]->nick,authMethodEnum::COOKIE);
		self::$thisUser->setAvatarURL($res[0]->avatarHTML);
		self::$thisUser->setUserLevel((int)$res[0]->userLevel);
		//self::$thisUser->setEmail($res[0]->email);
		self::$thisUser->setDatabaseID($res[0]->ID);
		self::$thisUser->setUserURL($res[0]->userURL);
		
		self::setUserSessionByUserObject(self::$thisUser);
		
		return TRUE;
	}
	private static function isDBUser($user = null, $password = null){
		if($user === NULL || $password === NULL)
			return false;
		
		# TODO login via Database
		
		return true;
	}
	public static function loginUserAPI($user, $password, $cloaked = FALSE){
		$res = dbQueries::get()->loginUserAPILoginCredentials( $user, $password, $cloaked );

		if(isset($res[0]->nick)){
			self::$thisUser = new userObj($res[0]->nick, authMethodEnum::API);
			self::$thisUser->setUserLevel((int)$res[0]->userLevel);
			//self::$thisUser->setEmail($res[0]->email);
			self::$thisUser->setDatabaseID($res[0]->ID);
			self::$thisUser->setUserURL($res[0]->userURL);
			
			if($cloaked !== FALSE && is_string($cloaked) && $res[0]->userLevel > 9)
				self::$thisUser->setDBIDCloaked($cloaked);
			
			$signature = sha1(rand($res[0]->ID,$res[0]->ID+1999999).$res[0]->nick.$res[0]->ID.rand(0,900000));
			self::$thisUser->setAPISignatureKey($signature);
			
			/*$databaseName = DBTableNameUserAPI;
			$sql = new dbObj();
			$sql->setTypeUPDATE();
			$sql->setDatabase($databaseName);
			
			$sql->setConditionIntegerEqual('userID', $res[0]->ID, $databaseName);

			$sql->setUpdatedFieldValueString('signature', $signature, $databaseName);
			
			return db::query($sql);*/
			
			return TRUE;
		}
		return FALSE;
	}

	private static function isUserCreatedByMail($userEmail){
		if(!is_string($userEmail) || strlen($userEmail) < 7)
			return FALSE;
			
		#$sql = 'SELECT `ID`, `authMethod` FROM `userBase` WHERE `email` = \''.db::harm($userEmail).'\';';	
		die('email ?');
		$sql = new dbObj();
		
		$sql->setDatabase('userBase');
		
		$sql->setSELECTField('ID', 'userBase');
		$sql->setSELECTField('authMethod', 'userBase');
		
		$sql->setConditionStringEqual('email', $userEmail, 'userBase');
		
		$res = db::query($sql);
		
		if(isset($res[0]->ID))
			return TRUE;
		else
			return FALSE;
	}
	private static function getUserIDByNetworkID($userID, $type = 'steam'){
		if(!is_string($userID) || strlen($userID) < 3)
			return FALSE;
			
		#$sql = 'SELECT `ID`, `authMethod` FROM `userBase` WHERE `networkID` = \''.db::harm($userID).'\';';	
		
		$databaseName2 = DBTableNameUser;
		switch($type){
			case 'steam':
				$databaseName = DBTableNameUserNetworkSteam;
				break;
			default:
				return FALSE;
				break;
		}
		
		$sql = new dbObj();
		$sql->setTypeSELECT();
		
		$sql->setDatabase( $databaseName );
		$sql->setDatabase( $databaseName2 );
		
		$sql->setConditionStringEqual('steamID', $userID, $databaseName);
		
		$sql->setSELECTField('userID', $databaseName);
		$sql->setSELECTField('userLevel', $databaseName2);
		
		$sql->setDBonLeftJoinEqualToColumn('userID', $databaseName, 'ID', $databaseName2);
		#echo($sql->getQueryString().' --- > '.$userID);
		$res = db::query($sql);
		
		if(isset($res[0]->userID) && (int)$res[0]->userID > 0)
			return $res[0];
		else{ //echo $sql->getQueryString(); die();
			return FALSE;}
	}
	private static function createUserByObject($authMethod, $userObject, $googleToken = NULL){
		$status = false;	
		
		$harmedEmail = NULL;
		$harmedNick = NULL;
		$harmedFirst = NULL;
		$harmedSur = NULL;
		$harmedNick = NULL;
		$harmedAvatar = NULL;
		$harmedLocale = NULL;
		$harmedLongLiveToken = NULL;
		$harmedNetworkID = NULL;
		$harmedVerified = 0;
		$harmedRefreshToken = NULL;
		
		switch ($authMethod){
			case 'google':	
				if(!is_a($userObject,'Google_Service_Plus_Person') || $googleToken === NULL)
					return FALSE;
				
				if(FALSE)
					$userObject = new Google_Service_Plus_Person();

				if(is_string($userObject->getEmails()) && strlen($userObject->getEmails()) > 8)
					$harmedEmail = $userObject->getEmails();
				else if(is_array($userObject->getEmails()) && count($userObject->getEmails()) >= 1){
					$arr = $userObject->getEmails();
					$arr = $arr[0]->getValue();
					if(is_string($arr) && strlen($arr) > 8)
						$harmedEmail = $arr;
				}
				
				if(is_string($userObject->getDisplayName()))
					$harmedNick = $userObject->getDisplayName();
				
				if(is_string($userObject->getName()->givenName))
					$harmedFirst = $userObject->getName()->givenName;
				
				if(is_string($userObject->getName()->familyName))
					$harmedSur = $userObject->getName()->familyName;
				
				if(is_string($userObject->getImage()->url))
					$harmedAvatar = $userObject->getImage()->url;
				
				if(is_string($userObject->getLanguage()))
					$harmedLocale = $userObject->getLanguage();
				
				if(is_string($userObject->getVerified()))
					$harmedVerified = (strtolower($userObject->getVerified())) == 'true' ? 1 : 0;
				
				$harmedLongLiveToken = $googleToken['access_token'];
				$harmedRefreshToken = $googleToken['refresh_token'];
				
				$status = true;
				$harmedAuth = $authMethod;
				
				$harmedNetworkID = $userObject->getId();
								
				break;
			case 'facebook':
				if(is_string($userObject['email']) && strlen($userObject['email']) > 8)
					$harmedEmail = $userObject['email'];
				if(is_string($userObject['name']))
					$harmedNick = $userObject['name'];
				if(is_string($userObject['first_name']))
					$harmedFirst = $userObject['first_name'];
				if(is_string($userObject['last_name']))
					$harmedSur = $userObject['last_name'];
				
				$harmedAvatar = 'http://graph.facebook.com/'.$userObject['id'].'/picture';
				
				if(is_string($userObject['locale']))
					$harmedLocale = substr($userObject['locale'],0,2);
				if((int)($userObject['verified']) == 1)
					$harmedVerified = 1;
				else
					$harmedVerified = 0;
				
				$harmedLongLiveToken = self::getSession()->getKeyValue('longLivedAccessToken');
				
				$harmedNetworkID = $userObject['id'];
				
				$status = true;
				$harmedAuth = $authMethod;
				
				break;
			case 'steam':	
				
				if(is_string($userObject->personaname))
					$harmedNick = $userObject->personaname;
				
				$harmedAvatar = $userObject->avatar;
				
				$harmedVerified = 0;
				
				$harmedNetworkID = $userObject->steamid;
				
				$status = true;
				$harmedAuth = $authMethod;
				
				return self::addNewUserToDBSteam($harmedNick, $harmedFirst, $harmedSur, $harmedAvatar, $harmedLocale, $harmedNetworkID);
				
				break;
		}
	}

	private static function getNewUserURLRandomized(){
		$id = '';
		for($i = 0; $i < 21; $i++){
			$id .= mt_rand(0, 9);
		}
		return $id;
	}

	private static function addNewUserToDBInfo($nick, $first, $sur, $avatar, $locale, $sqlID){
		$databaseName = DBTableNameUser;
		
		$sql = new dbObj();
		$sql->setDatabase($databaseName);
		$sql->setTypeINSERT();
		
		$sql->setInsertFieldValueNULL('ID', $databaseName);
		$sql->setInsertFieldValueString('nick', $nick, $databaseName);
		$sql->setInsertFieldValueString('firstname', $first, $databaseName);
		$sql->setInsertFieldValueString('surname', $sur, $databaseName);
		$sql->setInsertFieldValueString('avatarHTML', $avatar, $databaseName);
		$sql->setInsertFieldValueString('locale', $locale, $databaseName);
		$sql->setInsertFieldValueNULL('expireDate', DBTableNameUser);
		$sql->setInsertFieldValueString('userURL', 'user-'.$sqlID, $databaseName);

		if(db::query($sql))
			return db::getLastID();
		
		return 0;
	}

	private static function updateUserURL($id){
		$userURL = $id * 8 + mt_rand(0, 7);
		
		$databaseName = DBTableNameUser;
		
		$sql = new dbObj();
		$sql->setTypeUPDATE();
		$sql->setDatabase($databaseName);
		
		$sql->setUpdatedFieldValueString('userURL', 'user-'.$userURL, $databaseName);
		$sql->setConditionIntegerEqual('ID', $id, $databaseName);
		
		return db::query($sql);
	}

	private static function addNewUserToDBSteam($nick, $first, $sur, $avatar, $locale, $steamID){
		$sqlID = self::getNewUserURLRandomized();	
		$id = self::addNewUserToDBInfo($nick, $first, $sur, $avatar, $locale, $sqlID);

		if($id === FALSE || $id < 1)
			return FALSE;
		
		self::updateUserURL($id);
		
		$databaseName = DBTableNameUserNetworkSteam;
		
		$sql = new dbObj();
		$sql->setTypeINSERT();
		$sql->setDatabase($databaseName);
		
		$sql->setInsertFieldValueInteger('userID', $id, $databaseName);
		$sql->setInsertFieldValueString('steamID', $steamID, $databaseName);
		$sql->setInsertFieldValueNOW('dateCreated', $databaseName);
		
		if(!db::query($sql))
			return FALSE;
		
		self::$thisUser->setDatabaseID( db::getLastID() );
		
		return TRUE;
	}

	private static function loginByPOST(){
		if(!isset($_POST['login']) || !isset($_POST['password']) || !isset($_POST['rCaptcha']))
			return false;
		
		if(self::isDBUser($_POST['login'], $_POST['password']))
			return true;
		
		return false;
	}
	private static function loginByCookie(){
		# TODO loginByCookie
		
		return false;
	}
	private static function loginByGoogle(){
		if(!isset($_GET['code']))
			return false;

		$client = new Google_Client();

		$client->setApplicationName('TheFolder');
		$client->setClientId(googlePlusKey);
		$client->setClientSecret(googlePlusSecret);
		$client->setDeveloperKey(googlePlusServerKey);
		$client->setRedirectUri(HTTP_HOST.'/'.LINK_Login_Script.'?loginType=ggl');
		#$client->setAccessType("offline");
		$client->setScopes(['profile','email','openid']);
		
		#$client->authenticate($_GET['code']);
		$authToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);# die();
		
		#$authToken = $client->getAccessToken();
		
		if(!isset($authToken['access_token']) || !isset($authToken['expires_in'])){# || !isset($authToken['refresh_token'])){
			print_r($authToken);	
			return FALSE;
		}
		#print_r(
		#$client->authorize();#); die();
		#print_r($authToken);die();
		
		$plus = new Google_Service_Plus($client);
		#echo date('y-m-d H:i:s',$authToken['created']);die();
		$person = @$plus->people->get('me');

		if((int)$person->getId() < 1)
			return false;			
		
		#$return->theCoreToken = $authToken['access_token'];
		
		$thisID = self::getUserIDByNetworkID($person->getId());
		
		if(FALSE === $thisID){
			if(!self::createUserByObject('google', $person, $authToken))
				return FALSE;
		} else {
			/*$sql = 'UPDATE 
						`userBase` 
					SET 
						`longLiveToken` = '.db::harmAndString($authToken['access_token']).',  
						`expireDate` = DATE_ADD(NOW(), INTERVAL '.(int)$authToken['expires_in'].' SECOND),
						`lastLogin` = NOW() 
					WHERE `ID` = '.(int)$thisID.';';*/
			
			$sql = new dbObj();
			
			$sql->setTypeUPDATE();
			
			$sql->setDatabase('userBase');
			$sql->setUpdatedFieldValueString('longLiveToken', $authToken['access_token'], 'userBase');
			$sql->setUpdatedFieldValueDATE_ADD_TO_NOW('expireDate', $authToken['expires_in'], 'userBase');
			$sql->setUpdatedFieldValueNOW('lastLogin', 'userBase');
			
			$sql->setUpdatedFieldValueInteger('ID', $thisID, 'userBase');
			
			db::query($sql);
			self::$thisUser->setDatabaseID($thisID);
		}
		
		$arr = $person->getEmails();
		$arr = $arr[0]->getValue();
		if(is_string($arr) && strlen($arr) > 8)
			self::$thisUser->setEmail($arr);				
		
		self::$thisUser->setNickName($person->getDisplayName());
		self::$thisUser->setAvatarURL($person->getImage()->url);

		return true;		
	}
	private static function loginByFacebook(){
		if(!isset($_GET['code']))
			return false;
		
		$urlCheck = 'https://graph.facebook.com/me';
		
		$fb = new Facebook\Facebook([
				  'app_id' => facebookAppId,
				  'app_secret' => facebookAppSecret,
				  'default_graph_version' => facebookDefaultGraphVersion
			  ]);
		if(!self::getSession()->isKeySet('longLivedAccessToken')){
			$helper = $fb->getRedirectLoginHelper();
			
			$accessToken = $helper->getAccessToken();
			$oAuth2Client = $fb->getOAuth2Client();
		
			$longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
			#$longLivedAccessTokenExpireDate = $oAuth2Client->get;
		
			self::getSession()->setKeyValue('longLivedAccessToken', $longLivedAccessToken);
		} else
			$longLivedAccessToken = self::getSession()->getKeyValue('longLivedAccessToken');
		
		$fb->setDefaultAccessToken($longLivedAccessToken);
		
		$response = $fb->get('me?fields=email,id,name,first_name,last_name,age_range,link,gender,locale,timezone,updated_time,verified');
  		$userArray = $response->getDecodedBody();

		#print_r($userArray);

		if(!isset($userArray['email']))
			return false;

		if(!isset($userArray['name']))
			return false;
	
		$thisID = self::getUserIDByNetworkID($userArray['id']);
	
		if(FALSE === $thisID){
			if(!self::createUserByObject('facebook', $userArray))
				return FALSE;
		} else {
			self::$thisUser->setDatabaseID($thisID);
		}
							
		self::$thisUser->setEmail($userArray['email']);
		self::$thisUser->setNickName($userArray['name']);
		self::$thisUser->setAvatarURL('http://graph.facebook.com/'.$userArray['id'].'/picture');
		
		return true;
	}
	private static function loginBySteam(){
		$openid = new LightOpenID(HTTP_HOST.'/login?loginType=stm');
		
		if($openid->validate()){
            $id = $openid->identity;
            
            $ptn = "/^http:\/\/steamcommunity\.com\/openid\/id\/(7[0-9]{15,25}+)$/";
            preg_match($ptn, $id, $matches);
            
            $url = "http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=".steamApiKey."&steamids=$matches[1]";
            
			$conn = new cConnection();
			$conn->setIP($url);
			$conn->establish();
			$json_decoded = $conn->getReturnByJSON();
			
            /*$json_object= file_get_contents($url);
            $json_decoded = json_decode($json_object);
			            
			print_r($matches); print_r($json_decoded);   */         
			            
			if($matches[1] != $json_decoded->response->players[0]->steamid)
				return FALSE;
 
			$res = self::getUserIDByNetworkID($json_decoded->response->players[0]->steamid, 'steam');
			
			if(FALSE === $res){
				if(!self::createUserByObject('steam', $json_decoded->response->players[0]))
					return FALSE;
				self::$thisUser->setUserLevel(1);	
			} else {
				self::$thisUser->setUserLevel($res->userLevel);	
				self::$thisUser->setDatabaseID($res->userID);
			}
					
			self::$thisUser->setEmail(NULL);
			self::$thisUser->setNickName($json_decoded->response->players[0]->personaname);
			self::$thisUser->setAvatarURL($json_decoded->response->players[0]->avatarmedium);
			
			return true;
        } else
			return false;
	}
	
	public static function getUserObject(){
		if(!self::$isInit){
			self::checkInit();
			if(!self::$isInit)
				return NULL;
		}

		if(is_a(self::$thisUser,'userObj')){	
			return self::$thisUser;
		} else
			return NULL;
	}
}
class session {
	private $lastURL = null;	
	private $set = true;
	public function __construct(){
		if(session_status() === FALSE)
	        session_start();
		
		$this->sessionStandards();
	}
	
	public function delete(){
		session_destroy();
		$this->set = FALSE;
	}
	
	public function getLastURL(){
		return ($this->lastURL !== NULL) ? $this->lastURL : $_SERVER['HTTP_HOST'];
	}
	
	public function setKeyValue($keyname, $value){
		$_SESSION[$keyname] = $value;
	}
	
	public function isKeySet($keyname){
		return isset($_SESSION[$keyname]);
	}
	
	public function isKeyValue($keyname, $value){
		if(!isset($_SESSION[$keyname]))
			return FALSE;
		
		if($_SESSION[$keyname] == $value)
			return TRUE;
		else
			return FALSE;
	}
	
	public function getKeyValue($keyname){
		if(isset($_SESSION[$keyname]))
			return $_SESSION[$keyname];
		else
			return NULL;
	}
	public function deleteKey($keyname){
		if(isset($_SESSION[$keyname]))
			unset($_SESSION[$keyname]);
	}
	private function sessionStandards(){
		#debug::xray($_SERVER);
		
		# TODO ignore 404 redirects
		
		# Standard: surfing on site and not(!) login-url
		if(!core::getURLObj()->isLogin()){
			$this->lastURL = $_SERVER['REQUEST_URI'];
			$_SESSION['lastURL'] = $_SERVER['REQUEST_URI'];
		} else {
			# first visit -> direct to loginURL
			if(!isset($_SESSION['lastURL'])){
				$_SESSION['lastURL'] = $_SERVER['HTTP_HOST'];
				$this->lastURL = $_SERVER['HTTP_HOST'];
			} else {
				# not(!) first visit -> redirect to presite
				# -> means: $_SESSION['lastURL'] is equal to $_SESSION['preLastURL'];
				$this->lastURL = $_SESSION['lastURL'];
			}
		}		
	}
}

class pathMap{
	private $child = null;
	private $childIsDynamic = false;
	private $allChildsDynamic = false;
	private $name = false;
	private $level = userLevelEnum::NORMAL;
	private $fileName = null;
	
	private function setNamespace($name){
		$this->name = $name;
	}
	
	function __construct($init){
		if($init !== FALSE)
			$this->setNamespace($init);
	}
	public function setDynamic($bool = true){
		if($bool)
			$this->childIsDynamic = true;
		else
			$this->childIsDynamic = false;
		return $this;
	}
	public function setDynamicChilds($bool = true){
		$this->allChildsDynamic = TRUE;
		return $this;
	}
	public function isChildDynamicAll(){
		return $this->allChildsDynamic;
	}
	public function isChildDynamic(){
		return $this->childIsDynamic;
	}
	public function setChild($childName){
		if($this->childIsDynamic)
			$childName = 0;	
			
		if($this->child === NULL)
			$this->child = array();
		
		if(!isset($this->child[$childName])){
			$this->child[$childName] = new pathMap($childName);
			$this->child[$childName]->setLevel($this->level);
		}

		return $this->child[$childName];
	}
	public function setFile($fileName){
		$this->fileName = $fileName;
		return $this;
	}
	public function getFile(){
		return $this->fileName;
	}
	public function setLevel($userLevelEnum){
		$this->level = (int)$userLevelEnum;
		return $this;
	}
	public function getChild($childName){
		if(isset($this->child[$childName]))
			return $this->child[$childName];
		else if($this->childIsDynamic)
			return $this->child[0];
		else
			return false;
	}
	
}
class Encoding {
  const ICONV_TRANSLIT = "TRANSLIT";
  const ICONV_IGNORE = "IGNORE";
  const WITHOUT_ICONV = "";
  protected static $win1252ToUtf8 = array(
        128 => "\xe2\x82\xac",
        130 => "\xe2\x80\x9a",
        131 => "\xc6\x92",
        132 => "\xe2\x80\x9e",
        133 => "\xe2\x80\xa6",
        134 => "\xe2\x80\xa0",
        135 => "\xe2\x80\xa1",
        136 => "\xcb\x86",
        137 => "\xe2\x80\xb0",
        138 => "\xc5\xa0",
        139 => "\xe2\x80\xb9",
        140 => "\xc5\x92",
        142 => "\xc5\xbd",
        145 => "\xe2\x80\x98",
        146 => "\xe2\x80\x99",
        147 => "\xe2\x80\x9c",
        148 => "\xe2\x80\x9d",
        149 => "\xe2\x80\xa2",
        150 => "\xe2\x80\x93",
        151 => "\xe2\x80\x94",
        152 => "\xcb\x9c",
        153 => "\xe2\x84\xa2",
        154 => "\xc5\xa1",
        155 => "\xe2\x80\xba",
        156 => "\xc5\x93",
        158 => "\xc5\xbe",
        159 => "\xc5\xb8"
  );
    protected static $brokenUtf8ToUtf8 = array(
        "\xc2\x80" => "\xe2\x82\xac",
        "\xc2\x82" => "\xe2\x80\x9a",
        "\xc2\x83" => "\xc6\x92",
        "\xc2\x84" => "\xe2\x80\x9e",
        "\xc2\x85" => "\xe2\x80\xa6",
        "\xc2\x86" => "\xe2\x80\xa0",
        "\xc2\x87" => "\xe2\x80\xa1",
        "\xc2\x88" => "\xcb\x86",
        "\xc2\x89" => "\xe2\x80\xb0",
        "\xc2\x8a" => "\xc5\xa0",
        "\xc2\x8b" => "\xe2\x80\xb9",
        "\xc2\x8c" => "\xc5\x92",
        "\xc2\x8e" => "\xc5\xbd",
        "\xc2\x91" => "\xe2\x80\x98",
        "\xc2\x92" => "\xe2\x80\x99",
        "\xc2\x93" => "\xe2\x80\x9c",
        "\xc2\x94" => "\xe2\x80\x9d",
        "\xc2\x95" => "\xe2\x80\xa2",
        "\xc2\x96" => "\xe2\x80\x93",
        "\xc2\x97" => "\xe2\x80\x94",
        "\xc2\x98" => "\xcb\x9c",
        "\xc2\x99" => "\xe2\x84\xa2",
        "\xc2\x9a" => "\xc5\xa1",
        "\xc2\x9b" => "\xe2\x80\xba",
        "\xc2\x9c" => "\xc5\x93",
        "\xc2\x9e" => "\xc5\xbe",
        "\xc2\x9f" => "\xc5\xb8"
  );
  protected static $utf8ToWin1252 = array(
       "\xe2\x82\xac" => "\x80",
       "\xe2\x80\x9a" => "\x82",
       "\xc6\x92"     => "\x83",
       "\xe2\x80\x9e" => "\x84",
       "\xe2\x80\xa6" => "\x85",
       "\xe2\x80\xa0" => "\x86",
       "\xe2\x80\xa1" => "\x87",
       "\xcb\x86"     => "\x88",
       "\xe2\x80\xb0" => "\x89",
       "\xc5\xa0"     => "\x8a",
       "\xe2\x80\xb9" => "\x8b",
       "\xc5\x92"     => "\x8c",
       "\xc5\xbd"     => "\x8e",
       "\xe2\x80\x98" => "\x91",
       "\xe2\x80\x99" => "\x92",
       "\xe2\x80\x9c" => "\x93",
       "\xe2\x80\x9d" => "\x94",
       "\xe2\x80\xa2" => "\x95",
       "\xe2\x80\x93" => "\x96",
       "\xe2\x80\x94" => "\x97",
       "\xcb\x9c"     => "\x98",
       "\xe2\x84\xa2" => "\x99",
       "\xc5\xa1"     => "\x9a",
       "\xe2\x80\xba" => "\x9b",
       "\xc5\x93"     => "\x9c",
       "\xc5\xbe"     => "\x9e",
       "\xc5\xb8"     => "\x9f"
    );
  static function toUTF8($text){
  /**
   * Function \ForceUTF8\Encoding::toUTF8
   *
   * This function leaves UTF8 characters alone, while converting almost all non-UTF8 to UTF8.
   *
   * It assumes that the encoding of the original string is either Windows-1252 or ISO 8859-1.
   *
   * It may fail to convert characters to UTF-8 if they fall into one of these scenarios:
   *
   * 1) when any of these characters:   ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÚÛÜÝÞß
   *    are followed by any of these:  ("group B")
   *                                    ¡¢£¤¥¦§¨©ª«¬­®¯°±²³´µ¶•¸¹º»¼½¾¿
   * For example:   %ABREPRESENT%C9%BB. «REPRESENTÉ»
   * The "«" (%AB) character will be converted, but the "É" followed by "»" (%C9%BB)
   * is also a valid unicode character, and will be left unchanged.
   *
   * 2) when any of these: àáâãäåæçèéêëìíîï  are followed by TWO chars from group B,
   * 3) when any of these: ðñòó  are followed by THREE chars from group B.
   *
   * @name toUTF8
   * @param string $text  Any string.
   * @return string  The same string, UTF8 encoded
   *
   */
    if(is_array($text))
    {
      foreach($text as $k => $v)
      {
        $text[$k] = self::toUTF8($v);
      }
      return $text;
    } 
    
    if(!is_string($text)) {
      return $text;
    }
       
    $max = self::strlen($text);
  
    $buf = "";
    for($i = 0; $i < $max; $i++){
        $c1 = $text{$i};
        if($c1>="\xc0"){ //Should be converted to UTF8, if it's not UTF8 already
          $c2 = $i+1 >= $max? "\x00" : $text{$i+1};
          $c3 = $i+2 >= $max? "\x00" : $text{$i+2};
          $c4 = $i+3 >= $max? "\x00" : $text{$i+3};
            if($c1 >= "\xc0" & $c1 <= "\xdf"){ //looks like 2 bytes UTF8
                if($c2 >= "\x80" && $c2 <= "\xbf"){ //yeah, almost sure it's UTF8 already
                    $buf .= $c1 . $c2;
                    $i++;
                } else { //not valid UTF8.  Convert it.
                    $cc1 = (chr(ord($c1) / 64) | "\xc0");
                    $cc2 = ($c1 & "\x3f") | "\x80";
                    $buf .= $cc1 . $cc2;
                }
            } elseif($c1 >= "\xe0" & $c1 <= "\xef"){ //looks like 3 bytes UTF8
                if($c2 >= "\x80" && $c2 <= "\xbf" && $c3 >= "\x80" && $c3 <= "\xbf"){ //yeah, almost sure it's UTF8 already
                    $buf .= $c1 . $c2 . $c3;
                    $i = $i + 2;
                } else { //not valid UTF8.  Convert it.
                    $cc1 = (chr(ord($c1) / 64) | "\xc0");
                    $cc2 = ($c1 & "\x3f") | "\x80";
                    $buf .= $cc1 . $cc2;
                }
            } elseif($c1 >= "\xf0" & $c1 <= "\xf7"){ //looks like 4 bytes UTF8
                if($c2 >= "\x80" && $c2 <= "\xbf" && $c3 >= "\x80" && $c3 <= "\xbf" && $c4 >= "\x80" && $c4 <= "\xbf"){ //yeah, almost sure it's UTF8 already
                    $buf .= $c1 . $c2 . $c3 . $c4;
                    $i = $i + 3;
                } else { //not valid UTF8.  Convert it.
                    $cc1 = (chr(ord($c1) / 64) | "\xc0");
                    $cc2 = ($c1 & "\x3f") | "\x80";
                    $buf .= $cc1 . $cc2;
                }
            } else { //doesn't look like UTF8, but should be converted
                    $cc1 = (chr(ord($c1) / 64) | "\xc0");
                    $cc2 = (($c1 & "\x3f") | "\x80");
                    $buf .= $cc1 . $cc2;
            }
        } elseif(($c1 & "\xc0") == "\x80"){ // needs conversion
              if(isset(self::$win1252ToUtf8[ord($c1)])) { //found in Windows-1252 special cases
                  $buf .= self::$win1252ToUtf8[ord($c1)];
              } else {
                $cc1 = (chr(ord($c1) / 64) | "\xc0");
                $cc2 = (($c1 & "\x3f") | "\x80");
                $buf .= $cc1 . $cc2;
              }
        } else { // it doesn't need conversion
            $buf .= $c1;
        }
    }
    return $buf;
  }
  static function toWin1252($text, $option = self::WITHOUT_ICONV) {
    if(is_array($text)) {
      foreach($text as $k => $v) {
        $text[$k] = self::toWin1252($v, $option);
      }
      return $text;
    } elseif(is_string($text)) {
      return static::utf8_decode($text, $option);
    } else {
      return $text;
    }
  }
  static function toISO8859($text) {
    return self::toWin1252($text);
  }
  static function toLatin1($text) {
    return self::toWin1252($text);
  }
  static function fixUTF8($text, $option = self::WITHOUT_ICONV){
    if(is_array($text)) {
      foreach($text as $k => $v) {
        $text[$k] = self::fixUTF8($v, $option);
      }
      return $text;
    }
    $last = "";
    while($last <> $text){
      $last = $text;
      $text = self::toUTF8(static::utf8_decode($text, $option));
    }
    $text = self::toUTF8(static::utf8_decode($text, $option));
    return $text;
  }
  static function UTF8FixWin1252Chars($text){
    // If you received an UTF-8 string that was converted from Windows-1252 as it was ISO8859-1
    // (ignoring Windows-1252 chars from 80 to 9F) use this function to fix it.
    // See: http://en.wikipedia.org/wiki/Windows-1252
    return str_replace(array_keys(self::$brokenUtf8ToUtf8), array_values(self::$brokenUtf8ToUtf8), $text);
  }
  static function removeBOM($str=""){
    if(substr($str, 0,3) == pack("CCC",0xef,0xbb,0xbf)) {
      $str=substr($str, 3);
    }
    return $str;
  }
  protected static function strlen($text){
    return (function_exists('mb_strlen') && ((int) ini_get('mbstring.func_overload')) & 2) ?
           mb_strlen($text,'8bit') : strlen($text);
  }
  public static function normalizeEncoding($encodingLabel)
  {
    $encoding = strtoupper($encodingLabel);
    $encoding = preg_replace('/[^a-zA-Z0-9\s]/', '', $encoding);
    $equivalences = array(
        'ISO88591' => 'ISO-8859-1',
        'ISO8859'  => 'ISO-8859-1',
        'ISO'      => 'ISO-8859-1',
        'LATIN1'   => 'ISO-8859-1',
        'LATIN'    => 'ISO-8859-1',
        'UTF8'     => 'UTF-8',
        'UTF'      => 'UTF-8',
        'WIN1252'  => 'ISO-8859-1',
        'WINDOWS1252' => 'ISO-8859-1'
    );
    if(empty($equivalences[$encoding])){
      return 'UTF-8';
    }
    return $equivalences[$encoding];
  }
  public static function encode($encodingLabel, $text)
  {
    $encodingLabel = self::normalizeEncoding($encodingLabel);
    if($encodingLabel == 'ISO-8859-1') return self::toLatin1($text);
    return self::toUTF8($text);
  }
  protected static function utf8_decode($text, $option)
  {
    if ($option == self::WITHOUT_ICONV || !function_exists('iconv')) {
       $o = utf8_decode(
         str_replace(array_keys(self::$utf8ToWin1252), array_values(self::$utf8ToWin1252), self::toUTF8($text))
       );
    } else {
       $o = iconv("UTF-8", "Windows-1252" . ($option == self::ICONV_TRANSLIT ? '//TRANSLIT' : ($option == self::ICONV_IGNORE ? '//IGNORE' : '')), $text);
    }
    return $o;
  }
  public static function stripAllKnownSpecialChars($string){
  	return str_replace(
		  		array('â¦'), 
		  		array('♦' ), 
		  		$string
			);
  }
}
?>