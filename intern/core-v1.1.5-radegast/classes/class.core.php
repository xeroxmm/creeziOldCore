<?php
	/**
	 * Core class
	 *
	 * @package AdSocials
	 * @since 1.0.0
	 */
	 
class core {
	private static $isInit = false;
	private static $initTimestamp = 0;
	
	private static $objTheme;
	private static $objAjax;
	private static $objAPI;
	
	private static $urlRequest = null;
	
	public static function init(){
		self::loadEssentials();
		self::loadURLRequest();
		self::loadInfoUser();
		
		self::switchRessourcesByURL();
	}
		
	public static function getScriptTimeStampStart(){
		return self::$initTimestamp;
	}
	public static function getScriptTimeStampDelta(){
		return (microtime(TRUE) - self::$initTimestamp);
	}
    public static function setURLObj( urlObj $urlObj ){
        self::$urlRequest = $urlObj;
    }
	public static function getURLObj(){
		if(self::$urlRequest === NULL)
			self::loadURLRequest();
			
		return self::$urlRequest;
	}
	#
	#	-------------------------------------------------
	#
	
	private static function loadEssentials(){
		# Start Script-Timer
		self::$initTimestamp = microtime(TRUE);
		date_default_timezone_set('UTC');
		
		# Load & Start Debug- and Core-Modul
		self::initDebug();
		
		# Load DB-Modul
		self::loadDBMain();
					
		# Load & Start Security- and URL-Modul
		self::initSecurity();
		
		# Load Output-Modul
		self::initOutput();
	}
	
	private static function initDebug(){
		# Load Enumerations
		require_once 'intern/'.CORE_VERSION.'/classes/class.enum.php';
		require_once 'intern/'.CORE_VERSION.'/enums/enum.debug.php';
		
		# Load main CORE-Configurations	
		require_once 'intern/'.CORE_VERSION.'/config/conf.core.php';
		
		# Load Debug-Class
		require_once 'intern/'.CORE_VERSION.'/classes/class.debug.php';
		
		# Load other stuff
		require_once 'intern/'.CORE_VERSION.'/classes/class.filehandling.php';
	}
	
	private static function loadDBMain(){
		# Load Enumerations
		require_once 'intern/'.CORE_VERSION.'/enums/enum.userScopes.php';
		require_once 'intern/'.CORE_VERSION.'/enums/enum.db.php';
		
		# Load main DB-Configurations	
		require_once 'intern/'.CORE_VERSION.'/config/conf.mysql.php';
		
		# Load DB-Class
		require_once 'intern/'.CORE_VERSION.'/classes/class.db.php';
		require_once 'intern/'.CORE_VERSION.'/classes/class.db.queries.php';
	}
	
	private static function initSecurity(){
		# Load Enumerations
		require_once 'intern/'.CORE_VERSION.'/enums/enum.security.php';
		require_once 'intern/'.CORE_VERSION.'/classes/class.time.php';
		require_once 'intern/'.CORE_VERSION.'/classes/class.pagination.php';
		
		# Load main Security-Configurations	
		require_once 'intern/'.CORE_VERSION.'/config/conf.security.php';
		require_once 'intern/'.CORE_VERSION.'/config/conf.links.php';
		require_once 'intern/'.CORE_VERSION.'/config/conf.upload.php';
		//echo phpinfo();
		//die();
		# Load Security-Class
		require_once 'intern/'.CORE_VERSION.'/classes/class.security.php';
		require_once 'intern/'.CORE_VERSION.'/classes/class.cookies.php';
		require_once 'intern/'.CORE_VERSION.'/classes/class.user.php';
		require_once 'intern/'.CORE_VERSION.'/classes/class.route.php';
		
		# Load URL-Class
		require_once 'intern/'.CORE_VERSION.'/classes/class.url.php';
		require_once 'intern/'.CORE_VERSION.'/classes/class.asynchron.php';
		
		# Load Com-Classes
		require_once 'intern/'.CORE_VERSION.'/classes/class.connection.php';
		require_once 'intern/'.CORE_VERSION.'/classes/class.upload.php';
		require_once 'intern/'.CORE_VERSION.'/classes/class.content.manager.php';
        
		# Load 2nd Parties
		//require_once 'intern/2nd-party/facebook/autoload.php';
		require_once 'intern/2nd-party/lightOpenID/lightOpenID.php';
		//require_once 'intern/2nd-party/google-api-php-client-2.0.0-RC4/vendor/autoload.php';
		
		security::init();
	}

	private static function initOutput(){
		# Load Output-Classes (HTML, API, AJAX)
		require_once 'intern/'.CORE_VERSION.'/classes/class.snippet.php';
		require_once 'intern/'.CORE_VERSION.'/classes/class.output.php';
		require_once 'intern/'.CORE_VERSION.'/classes/class.api.php';
		require_once 'intern/'.CORE_VERSION.'/classes/class.ajax.php';
		require_once 'intern/'.CORE_VERSION.'/classes/class.output.picture.php';
		require_once 'intern/'.CORE_VERSION.'/classes/class.output.video.php';
		require_once 'intern/'.CORE_VERSION.'/classes/class.output.collection.php';
		require_once 'intern/'.CORE_VERSION.'/classes/class.output.tag.php';
		require_once 'intern/'.CORE_VERSION.'/classes/class.cronjobHook.php';
		
		if(moduleAPI)
			require_once 'intern/'.CORE_VERSION.'/classes/class.api.listener.php';
	}

	private static function loadURLRequest(){
		if(self::$urlRequest === NULL)	
			self::$urlRequest = new urlObj();
	}
	private static function loadInfoUser(){
		
	}
	private static function switchRessourcesByURL(){	
		if(is_object(self::$urlRequest) && self::$urlRequest->isOn()){
			if(self::$urlRequest->isAJAX()){
				if(output::loadAJAX()){
					security::loadUserLevel(userLevelEnum::NONE);
					
					api::send200();	
				} else{
					html::send404();
				}
			} else if(self::$urlRequest->isAPI()){
				api::loadAPIListener();
				api::send403();
				exit;
			} else if(self::$urlRequest->isBackend()){
				output::loadBackendTemplate();	
				output::loadSite();
				security::loadUserLevel(userLevelEnum::NORMAL);
				
				html::send200();
			} else if(self::$urlRequest->isLogin()){
				 security::doLoginProcess();
				 html::sendToLoginURL(); 
			} else if(self::$urlRequest->isInstallation()){
	 			exit ('alreadyInstalled');	
	 			require_once 'intern/'.CORE_VERSION.'/install/routine.install.php';
				die();
			} else if(self::$urlRequest->isLogout()){
				security::doLogoutProcess();
				html::sendHome();
			} else if(self::$urlRequest->isCronjob()){
				output::loadCronjob();	
				cronjobHook::analyzeRequest();
				
				die();
			} else if(self::$urlRequest->isHome() || self::$urlRequest->isFrontend()){
				output::loadFrontendTemplate();
				output::loadSite();
								
				html::send200();
			} else{
				html::send404();
			}	
		} else {
			html::send404();
		}
	}
	private static $asynchronFunctions = NULL;
	public static function selectAsynchronRequest(){
		if(self::$asynchronFunctions === NULL)
			self::loadAsynchronFunctions();
		
		return self::$asynchronFunctions;
	}
	public static function getJSONasObject($JSON){
		$json = json_decode($JSON);
		if($json !== FALSE)
			return $json;
		
		return NULL;
	}
	private static function loadAsynchronFunctions(){
		self::$asynchronFunctions = new asynchronCall();
	}
}

?>