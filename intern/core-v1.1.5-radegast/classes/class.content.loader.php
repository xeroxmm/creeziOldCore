<?php
class contentLoader {
    private $type = FALSE;
    private $allowedTypes = [1];
    private $status = FALSE;
    private $loadURL = FALSE;
    private $html = FALSE;
    
    function __construct(){
        
    }
    function loadType( int $type ){
        if(!in_array($type, $this->allowedTypes))
            return FALSE;
           
        $this->type = $type;
        $this->status = TRUE;
    }
    function addURL( string $url ){
        if(!$this->status)
            return FALSE;
        
        $this->loadURL = $url;
    }
    function execute(){
        if(!$this->status)
            return FALSE;
        
        switch($this->type){
            case FALSE:
                return FALSE;
                break;
            case 1:
                return $this->loadURLIntoDOM( true );
                break;
            default:
                return FALSE;
                break;
        }
    }
    private function loadURLIntoDOM( bool $strictMode ){
        $urlObj = new contentLoaderURLHandler( $strictMode );
        $urlObj->parseURL( $this->loadURL );
        if(($this->html = $urlObj->loadContentTypeObject()) !== FALSE)
            $this->sendAPIHTML();
    }
    private function sendAPIHTML(){
        api::getTemplateObject()->addContentID( 0 );
        $obj = new stdClass();
        $obj->html = $this->html;
        $obj->title= 'a title';
        
        api::getTemplateObject()->addContentInfo( json_encode($obj) );
    }
}
class contentLoaderURLHandler {
    private $path = FALSE;
    private $modeStrict = TRUE;
    private $isURLParsed = FALSE;
    private $urlType = FALSE;
    private $uObj = FALSE;
    
    function __construct( bool $strictMode ){
        $this->modeStrict = $strictMode;
    }
    function parseURL( string $url ){
        if($this->modeStrict){
            $temp = explode(NOPROTOCOL_HOST.'/', $url, 2);
            $temp2 = explode('//', $url, 2);
            // HOSTER IN URL
                if(is_array($temp) && isset($temp[1]))
                    $url = '/'.$temp[1];
            // URL STARTS WITH // but there is no hoster
                else if(is_array($temp2) && isset($temp2[1]) || $url[0] != '/'){
                    return FALSE;
                }
        }
        if($url[0] == '/') 
            $url = ltrim($url,'/');
        
        if(!$this->analyzeURLPattern( $url ))
            return FALSE;
        
        $this->isURLParsed = TRUE;
    }
    
    function loadContentTypeObject(){
        if(!$this->isURLParsed || !$this->uObj)
            return FALSE;
        switch($this->urlType){
            case FALSE:
                return FALSE;
                break;
            case LINK_imagePageSingle:
			case LINK_videoPageSingle:
                output::loadFrontendTemplate();
                output::loadSite( $this->uObj );
                
                $string = html::getBody('content-image');
                
                return $string;
                break;
			/*case LINK_videoPageSingle:
                output::loadFrontendTemplate();
                output::loadSite( $this->uObj );
                
                $string = html::getBody('content-image');
                
                return $string;
                break;*/
            case LINK_collectionPageSingle:
                output::loadFrontendTemplate();
                output::loadSite( $this->uObj );
                
                $string = html::getBody('content-collection');
                
                return $string;
                break;
        }
    }
    
    private function analyzeURLPattern( $url ){
        $this->uObj = new urlObj( $url );
        if($this->uObj->isImagePage())
            $this->urlType = LINK_imagePageSingle;
        else if($this->uObj->isCollectionPage())
            $this->urlType = LINK_collectionPageSingle;
		else if($this->uObj->isVideoPage())
            $this->urlType = LINK_videoPageSingle;
        else 
            return FALSE;
        
        return TRUE;
    }
}
?>