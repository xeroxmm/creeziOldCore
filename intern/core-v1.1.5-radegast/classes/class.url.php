<?php
	/**
	 * URL class
	 *
	 * @package AdSocials
	 * @since 1.0.0
	 */

class urlObj {
	private $path = array();
	
	private $isStandardFile = false;
	private $isFile = false;
	private $file = null;
	private $status = 200;
	
	private $isLogin = false;
	private $isHome = true;
	private $isBackend = false;
	private $isFrontend = false;
	private $isInstallation = false;
	private $isLogout = false;
	private $isVirtual = false;
    
	private $isTagPage = FALSE;
	private $isCollectionTag = FALSE;
	private $isImageTag = FALSE;
    private $isVideoTag = FALSE;
	
	private $isHTML = true;
	private $isAJAX = false;
	private $isAPI = false;
		
	public function __construct( string $url = NULL){
		if($url === NULL && isset($_GET[urlGETvar])){
			$this->splitURL();
		} else if($url === NULL && isset($_GET['doInstall'])){
			$this->setInstallation();
		} else if($url !== NULL){
            $this->splitURL($url);
            $this->isVirtual = TRUE;
        }
	}
	public function isVirtual(){
	    return $this->isVirtual;
	}
	public function getRequestedURI(){
		return $_SERVER['REQUEST_URI'];
	}
	public function getRequestedURL(){
		return HTTP_HOST.strtok($_SERVER['REQUEST_URI'],'?');
	}
	public function getCode(){
		return $this->status;
	}
	
	public function isHome(){
		return $this->isHome;
	}
	public function isLogin(){
		return $this->isLogin;
	}
	public function isBackend(){
		return $this->isBackend;
	}
	public function isCollectionPage(){
		return $this->isCollectionTag;
	}
    public function isImagePage(){
        return $this->isImageTag;
    }
	public function isVideoPage(){
		return $this->isVideoTag;
	}
	public function setVideoPage($bool = TRUE){
		$this->isVideoTag = (bool)$bool;
	}
	public function setCollectionPage($bool = TRUE){
		$this->isCollectionTag = (bool)$bool;
	}
	public function isTagPage(){
		return $this->isTagPage;
	}
	public function setTagPage($bool = TRUE){
		$this->isTagPage = (bool)$bool;
	}
	public function isFrontend(){
		return $this->isFrontend;
	}
	
	public function isFile(){
		return $this->isFile;
	}
	public function isStandardFile(){
		return $this->isStandardFile;
	}
	public function getPathArray(){
		return $this->path;
	}
	public function isInstallation(){
		return $this->isInstallation;
	}
	
	public function isOn(){
		#echo $this->status;	
		if($this->status == 200)
			return true;
		else
			return false;
	}
	
	public function isHTML(){
		return $this->isHTML;
	}
	
	public function isAJAX(){
		return $this->isAJAX;
	}
	
	public function isAPI(){
		return $this->isAPI;
	}
	
	public function isLogout(){
		return $this->isLogout;
	}
	
	#
	#	-----------------------------
	#
	
	private function splitURL( string $url = NULL){
		if($url === NULL)    
            $this->path = explode('/',$_GET[urlGETvar]);
        else
            $this->path = explode('/',$url);

		if(count($this->path) == 1 && empty($this->path[0])){
			// is home front ...
		} else if(count($this->path) < maxFolderInstances){
			$this->unsetHome();
			
			# 0. StandardFiles (*.css, *.js, *.ico)
			# 1. Frontend
			# 2. LoginURL
			# 3. BackEnd
			if($this->isAJAXRequest())
				$this->setAJAX();
			else if($this->isAPIRequest())
				$this->setAPI();
			else if($this->isStandardFileR())
				$this->setStandardFile();
			else if($this->isFrontendURL())
				$this->setFrontend();
			else if($this->isLoginURL())
				$this->setLogin();
			else if($this->isBackendURL())
				$this->setBackend();				
			else if($this->isLogoutURL())
				$this->setLogout();
			else
				$this->set404();
		} else 
			$this->set404();
	}
	private function isAPIRequest(){
		if(moduleAPI && useAPI && count($this->path) > 0 && ($this->path[0] == LINK_API) && isset($_POST[API_OBLIGATORY]))
			return true;
		
		return false;
	}
	private function isStandardFileR(){
		if(	count($this->path) > 1 && 
			$this->path[1] == frontendLayoutDIR && 
			file_exists($_SERVER['REQUEST_URI']))
			return true;
		else
			return false;
			
	}
	private function isAJAXRequest(){
		if(count($this->path) > 0 && ($this->path[0] == LINK_AJAX) && isset($_POST[AJAX_OBLIGATORY]))
			return true;
		
		return false;
	}
	private function setAJAX(){
		$this->isAJAX = true;
	}
	private function setAPI(){
		$this->isAPI = TRUE;
	}
	private function setStandardFile(){
		$this->isStandardFile = true;
		$this->isFrontend = false;
		$this->isBackend = false;
		$this->isLogin = false;
		$this->isInstallation = false;
		$this->isLogout = false;
	}
	private function isFrontendURL(){	
		if((count($this->path) > 0 && ($this->path[0] == LINK_Login_Script || $this->path[0] == LINK_Logout)))
			return false;
		
		if($this->path[0] == LINK_tagPageSingle){
			$this->setTagPage();
		} else if($this->path[0] == LINK_collectionPageSingle){
			$this->setCollectionPage();
		} else if($this->path[0] == LINK_imagePageSingle){
		    $this->setImagePage();
		} else if($this->path[0] == LINK_videoPageSingle){
			$this->setVideoPage();
		}

		return true;
	}
    private function setImagePage($bool = TRUE){
        $this->isImageTag = (bool)$bool;
    }
	private function isFrontendFile(){
		if((count($this->path) == 1 && $this->path[0] != LINK_Login_Script) || (file_exists('themes/'.frontendLayoutDIR.'/'.$this->path[0].'.html')
			&& $this->path[0] != 'footer' && $this->path[0] != 'header' && $this->path[0] != 'landing'))
			return true;
		else
			return false;
	}
	
	private function isBackendURL(){
		if($this->path[0] == backendURL)
			return true;
		else
			return false;
	}
	
	private function isLoginURL(){
		if(isset($this->path[0]) && $this->path[0] == LINK_Login_Script && count($this->path) == 1)
			return true;
		else
			return false;
	}
	
	private function isLogoutURL(){
		if(isset($this->path[0]) && $this->path[0] == LINK_Logout && count($this->path) == 1)
			return true;
		else
			return false;
	}
	private $isCronjob = NULL;
	public function isCronjob(){
		if($this->isCronjob === NULL && isset($this->path[0]) && $this->path[0] == LINK_CRONJOB && count($this->path) > 1){
			$this->isCronjob = true;
			return TRUE;
		}
		return FALSE;
	}
	private function is404Redirect(){
		if($_GET[urlGETvar] == '404'){
			$this->set404();
			return true;
		}
		return false;
	}
	
	private function set404(){
		$this->path = null;
		$this->isFile = false;
		$this->file = null;
		$this->isHome = false;
		$this->status = 404;
		$this->isStandardFile = false;
		$this->isInstallation = false;
		$this->isLogout = false;
	}
	
	private function unsetHome(){
		$this->isHome = false;
	}
	
	private function setInstallation(){
		$this->unsetHome();
			
		$this->isBackend = false;
		$this->isFrontend = false;
		$this->isLogin = false;
		$this->isStandardFile = false;
		$this->isInstallation = true;
		$this->isLogout = false;
	}
	private function setBackend(){
		$this->isBackend = true;
		$this->isFrontend = false;
		$this->isLogin = false;
		$this->isStandardFile = false;
		$this->isInstallation = false;
		$this->isLogout = false;
	}
	private function setLogin(){
		$this->isBackend = false;
		$this->isFrontend = false;
		$this->isLogin = true;
		$this->isStandardFile = false;
		$this->isInstallation = false;
		$this->isLogout = false;
	}
	private function setFrontend(){
		$this->isFrontend = true;
		$this->isBackend = false;
		$this->isLogin = false;
		$this->isStandardFile = false;
		$this->isInstallation = false;
		$this->isLogout = false;
	}
	private function setLogout(){
		$this->isFrontend = false;
		$this->isBackend = false;
		$this->isLogin = false;
		$this->isStandardFile = false;
		$this->isInstallation = false;
		$this->isLogout = true;
	}
}
?>