<?php
class html {
	private static $isHeaderFunc = false;	
	private static $htmlHeader = false;
	private static $isBodyFunc = false;
	private static $htmlBody = false;
	private static $htmlFooter = false;
	
	private static $htmlMapID = array();
	private static $htmlMapTAG = array();
    private static $htmlMapCLASS = array();
    
	public static function send404(){
		self::sendHeader404();
		self::sendLayout404();
	}
	
	public static function send403(){
		self::sendHeader403();
		self::sendLayout403();
		exit();
	}
	
	public static function send303($link){
		self::sendHeader303();
		self::sendLayout303($link);
		exit();
	}
	
	public static function sendHome(){
		self::sendHeader200();
		self::sendLayoutHome();
		exit();
	}
	public static function sendToBackend(){
		$url = backendURL;
		if(security::getRequestURL() != NULL)
			$url = security::getRequestURL();
		#echo $url;
		header('Location: http://'.$_SERVER['HTTP_HOST'].'/'.$url);
		exit;
	}
	public static function sendToLoginURL(){
		$url = security::getLastURL();
		header('Location: '.$url);
		exit;
	}
	public static function head(){
		self::isHeaderFuncLoaded();	
		return self::$htmlHeader;
	}
    
	/*
	 * 
	 *	@return htmlTag
	 */
	public static function body($id = NULL, $class = []){
		self::isBodyFuncLoaded($id, $class);
		return self::$htmlBody;
	}
	
	public static function footer(){
		self::isFooterFuncLoaded();
		return self::$htmlFooter;
	}
	public static function getBody(string $ID = NULL){
	    if($ID === NULL)    
	       return self::$htmlBody->broadcastAsString();
        
        return self::$htmlBody->getElementByID( $ID )->broadcastAsString();            
	}
	public static function send200(){
		self::sendHeader200();
		
		self::isHeaderFuncLoaded();
		self::isBodyFuncLoaded();
		
		self::$htmlHeader->broadcast();
		self::$htmlBody->broadcast();
		
		exit();
	}
    public static function setMapClass(htmlTag &$htmlTag){
        $elementID = array();	
        if(count($htmlTag->getClass()) > 0)
            foreach($htmlTag->getClass() as $val){
                if(!isset(self::$htmlMapCLASS[$val]))
                    self::$htmlMapCLASS[$val] = array();
                
                $j = count(self::$htmlMapCLASS[$val]);
                
                self::$htmlMapCLASS[$val][$j] = $htmlTag;
				$elementID[] = $j;
            }
		
		return $elementID;
    }
    public static function setMapTag(htmlTag &$htmlTag){
        if(!isset(self::$htmlMapTAG[$val]))
            self::$htmlMapTAG[$val] = array();
        
        self::$htmlMapTAG[$val][] = $htmlTag;
    }
	public static function setMapID(htmlTag &$htmlTag){
		if($htmlTag->getID() !== NULL)	
			self::$htmlMapID[$htmlTag->getID()] = $htmlTag;
	}
	public static function setMapIDList(htmlTagList &$htmlTagList){
		if($htmlTagList->getID() !== NULL)	
			self::$htmlMapID[$htmlTagList->getID()] = $htmlTagList;
	}
	public static function getElementByID($ID){
		if(isset(self::$htmlMapID[$ID]))
			return self::$htmlMapID[$ID];
		else
			return false;
	}
	#
	#	--------------------------------
	#
	private static function isFooterFuncLoaded(){
		if(!self::$htmlFooter)
			self::$htmlFooter = new htmlFooter();
	}
	private static function isBodyFuncLoaded($id = NULL, $class = []){
		if(!self::$htmlBody)
			self::$htmlBody = new htmlTag('body',$id,$class);
	}
	
	private static function isHeaderFuncLoaded(){
		if(!self::$htmlHeader)
			self::$htmlHeader = new htmlHeader();
	}
	
	private static function sendHeader404(){
		header("HTTP/1.0 404 Not Found");
	}
	private static function sendHeader403(){
		header("HTTP/1.0 403 Access Forbidden");
	}
	private static function sendHeader200(){
		header("HTTP/1.1 200 Ok");
	}
	private static function sendHeader303(){
		header("HTTP/1.0 303 See Other");
	}
	private static function sendLayout303($link){
		$link = "Location: ".$link;	
		header($link);
		exit();
	}
	private static function sendLayout404(){
		/*self::sendFrontendHeader();
		include_once 'themes/'.frontendLayoutDIR.'/404.php';
		self::sendFrontendFooter();
		*/
		echo "404";
		exit();
	}
	private static function sendLayout403(){
		/*self::sendFrontendHeader();
		include_once 'themes/'.frontendLayoutDIR.'/403.php';
		self::sendFrontendFooter();
		*/
		echo "403";
		exit();
	}
	private static function sendLayoutHome(){
		header('Location: http://'.$_SERVER['HTTP_HOST']);
		exit;
	}
	private static function sendFrontendHeader(){
		include_once 'themes/'.frontendLayoutDIR.'/header.php';
	}
	
	private static function sendFrontendFooter(){
		include_once 'themes/'.frontendLayoutDIR.'/footer.php';
	}
}

class htmlTag{
	private $on = TRUE;	
	private $tag = null;
	private $id = null;
	private $class = array();
	private $attr = array();
	private $close = null;
	private $inner = array();
	private $parent = null;
	private $isText = false;
	private $text = '';
	private $usedIDs = array();
    private $htmlClassElementIDs = array();
	private $final = false;
	private $style = array();
	
	public function __construct($type, $id = null, $class = array()){
		if($type === NULL){
			$this->isText = true;
			$this->text = $id;
		} else {
			$this->tag = $type;
			
			if($id !== null && is_string($id)){
				$this->id = $id;
				html::setMapID($this);
			}
            
			if(is_array($class) && count($class) > 0){
				$this->class = $class;
			} else if(is_string($class)){
				$this->class[] = $class;
            }
            
            $this->htmlClassElementIDs = html::setMapClass($this);
            
			$this->setCloseTag();
			if($this->tag === 'body')
				$this->final = true;
		}
	}
	public function isEmptyElement(){
		if($this->isText && $this->text === NULL)
			return TRUE;
		
		return FALSE;
	}
	public function isText(){
		return $this->isText;
	}
    public function getElementByID($id, $n = 1){
        foreach($this->inner as $val){
        	// test if inner element has proper ID	
        	if($val->getID() == $id)
				return $val;
			
			// look if innerS have a element
			$k = $val->getElementByID($id,0);
			
			// if non of the inners and is not the first call -> return FALSE
			if(!is_bool($k))
				return $k;
        }
		if($n == 0)
			return FALSE;
		
        return $this;
		
        return new htmlTag( NULL );
    }
	public function remove(){
		$this->isText = TRUE;
		$this->text = NULL;
		
		return NULL;
	}
	public function reset(){
		$r = $this;	
		while(($t = $r->outer(0)) !== FALSE){
			$r = $t;
		}
		return $r;
		return new htmlTag( NULL );
	}
    public function getAllElementIDs(){
        return $this->usedIDs;
    }
	public function getID(){
		return $this->id;
	}
    public function getClass(){
        return $this->class;
    }
	public function hasParent(){
		if($this->parent === NULL)
			return false;
		else
			return true;
	}
    public function setParent(&$parent){
		$this->parent = $parent;
	}
	public function setIcon($icon){
		$this->removeClass('fa-exclamation');
		$this->addClass('fa-'.$icon);
		return $this;
		return new htmlTag();
	}
	public function setContent($content = null){
		/* @var $this htmlTag */

		if(is_string($content))
			$this->inner[] = new htmlTag(NULL,$content);
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	public function setContentNew($content = null){
		foreach($this->inner as $k => $val){
			if( $val->isText() )
				unset( $this->inner[$k] );
		}	
		if(is_string($content))
			$this->inner[] = new htmlTag(NULL,$content);
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	public function setContentAtBegin($content = null){
		if(is_string($content))
			array_unshift($this->inner, new htmlTag(NULL,$content));
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	public function outer($k = 1){
		if($this->parent !== NULL){
			if(is_a($this->parent, 'htmlTag'))
				return $this->parent;
			else
				return new htmlTag();
		} else {
			if(is_a($this, 'htmlTag') && $k == 1)
				return $this;
			else
				return FALSE;
		}
		return new htmlTag();
	}
	public function in(){
		if(is_a($this->inner(), 'htmlTag'))
			return $this->inner();
		else
			return new htmlTag();	
	}
	public function addElementAtBegin(htmlTag $element){
		$element->setParent($this);
		array_unshift($this->inner, $element);	
		
        foreach($element->getAllElementIDs() as $key => &$val){
             $this->usedIDs[$key] = $val;
        }
				
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	public function inner(){
		if(!empty($this->inner) && $this->close === NULL){	
			if(!end($this->inner)->hasParent())
				end($this->inner)->setParent($this);
			
			return end($this->inner);
		} else
			if(is_a($this, 'htmlTag'))
				return $this;
			else
				return new htmlTag();
	}
	public function addElement($element){
		if($element === NULL)
			return $this;	
		$element->setParent($this);
			
		$this->inner[] = $element;
        foreach($element->getAllElementIDs() as $key => &$val){
             $this->usedIDs[$key] = $val;
        }
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	/*
	 * Creates a DIV element as an html-DOM-element, id anc class not mandatory
	 *
	 * @return htmlTag
	 */
	public function _DIV($id = null, $class = array()){
		$element = new htmlTag('div', $id, $class);
		$this->inner[] = $element;
		
        if($id !== NULL)
            $this->usedIDs[$id] = $element;
        
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	public function _P($id = null, $class = array(), $text = null){		
		$element = new htmlTag('p', $id, $class);
		
		if(is_string($text)){
			$element->inner()->setContent($text);
		}
		
		$this->inner[] = $element;
		if($id !== NULL)
            $this->usedIDs[$id] = $element;

		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	public function _HEADER($id = null, $class = array()){
		 $element = new htmlTag('header', $id, $class);
		$this->inner[] = $element;
		if($id !== NULL)
            $this->usedIDs[$id] = $element;
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	public function _H($level = 2, $id = null, $class = array()){
		$element = new htmlTag('h'.$level, $id, $class);
		$this->inner[] = $element;
		if($id !== NULL)
            $this->usedIDs[$id] = $element;
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	public function _A($link = null, $id = null, $class = array(), $text = null){
		$element = new htmlTag('a', $id, $class);
		
		$link = ($link !== null) ? $link : '#';

		if(useVersionControl){
			$add = '?';
			if(stristr($link, '?') !== FALSE)
				$add = '&';
			
			$link .= $add.LINK_version;
		}
		
		$element->setAttr('href',$link);
        
		if(is_string($text))
			$element->inner()->setContent($text);
				
		$this->inner[] = $element;
		
		if($id !== NULL)
            $this->usedIDs[$id] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	public function _FORM($id = null, $class = array()){
		$element = new htmlTag('form', $id, $class);
		
		$this->inner[] = $element;
		if($id !== NULL)
            $this->usedIDs[$id] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	public function _I($content = null, $id = null, $class = array()){
		$element = new htmlTag('i', $id, $class);
		$element->setContent($content);
		$this->inner[] = $element;
		if($id !== NULL)
            $this->usedIDs[$id] = $element;
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	public function _Input($id = null, $class = null, $type = 'text', $placeholder = NULL, $name = NULL){
		$element = new htmlTag('input', $id, $class);
		$element->isInnerClose();
		
		$element->setAttr('type', $type);
		
		if($placeholder !== NULL && is_string($placeholder))
		$element->setAttr('placeholder', $placeholder);
		
		if($name !== NULL && is_string($name))
			$element->setAttr('name', $name);
		
		$this->inner[] = $element;
		
		if($id !== NULL)
            $this->usedIDs[$id] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	public function _SPAN($content = null, $id = null, $class = array()){
		$element = new htmlTag('span', $id, $class);
		$element->setContent($content);
		$this->inner[] = $element;
		if($id !== NULL)
            $this->usedIDs[$id] = $element;
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	public function _STRONG($content = null, $id = null, $class = array()){
		$element = new htmlTag('strong', $id, $class);
		$element->setContent($content);
		$this->inner[] = $element;
		if($id !== NULL)
            $this->usedIDs[$id] = $element;
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	public function _IFRAME($link = null, $id = null, $class = array(), $type = NULL){
		$element = new htmlTag('iframe', $id, $class);
		
		$link = ($link !== null) ? $link : '#';
		
		$element->setAttr('src',$link);

		if($type == 'yt'){
			$element->setAttr('width', 600);
			$element->setAttr('height', 340);
			$element->setAttr('frameborder', 0);
			$element->setAttr('allowfullscreen');
		}

		$this->inner[] = $element;
		if($id !== NULL)
            $this->usedIDs[$id] = $element;
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();	
		
	}
	public function _IMG($link = null, $id = null, $class = array(), $tag = NULL){
		$element = new htmlTag('img', $id, $class);
		
		$link = ($link !== null) ? $link : '#';
		
		$element->setAttr('src',$link);
		
		if(is_string($tag))
			$element->setAttr('alt',$tag);
		
		$element->isInnerClose();
		$this->inner[] = $element;
		if($id !== NULL)
            $this->usedIDs[$id] = $element;
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	public function _HR($id = null, $class = array()){
		$element = new htmlTag('hr', $id, $class);

		$element->isInnerClose();
		$this->inner[] = $element;
		if($id !== NULL)
            $this->usedIDs[$id] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	public function _UL($id = null, $class = array()){
		$element = new htmlTag('ul', $id, $class);
		$this->inner[] = $element;
		if($id !== NULL)
            $this->usedIDs[$id] = $element;
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	public function _LI($id = null, $class = array()){
		$element = new htmlTag('li', $id, $class);
		$this->inner[] = $element;
		if($id !== NULL)
            $this->usedIDs[$id] = $element;
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	
	#
	#
	#
	#		All the BootPack-related stuff
	#
	#
	#
	public function _BtIcon($name){
		$element = new htmlTag('span', null, ['glyphicon','glyphicon-'.$name]);
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	
	public static function _BtIconS($name){
		$element = new htmlTag('span', null, ['glyphicon','glyphicon-'.$name]);
				
		return $element->broadcastAsString();
	}
	
	public function _BtButton($id = null, $class = null, $text = null, $icon = null, $label = null){
		if(is_array($class)){
			$class[] = 'btn';
			$class[] = 'btn-default';
		} else 
			$class = array($class, 'btn','btn-default');
		
		$element = new htmlTag('button', $id, $class);
		$element->setAttr('type','button');
		
		if($label !== NULL && is_string($label))
			$element->setAttr('aria-label',$label);
		
		if($icon !== NULL && is_string($icon))
			$element->_BtIcon($icon)->in()->setStyle('margin-right', '0.25em');
				
		if($text !== NULL && is_string($text))
			$element->setContent($text);
				
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	
	public function _BtButtonGroup($id = null, $class = null){
		if(is_array($class))
			$class[] = 'btn-group';
		else 
			$class = array($class, 'btn-group');	
		
		$element = new htmlTag('div', $id, $class);
		$element->setAttr('role','group');
		
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	
	public function _BtButtonGroupJustified($id = null, $class = null){
		if(is_array($class)){
			$class[] = 'btn-group';
			$class[] = 'btn-group-justified';
		} else 
			$class = array($class, 'btn-group', 'btn-group-justified');	
		
		$element = new htmlTag('div', $id, $class);
		$element->setAttr('role','group');
		
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	
	public function _BtFieldAlert($id = null, $class = array(), $text = 'Alert!'){
		if(is_array($class)){
			$class[] = 'alert';
			$class[] = 'alert-danger';
		} else 
			$class = array($class, 'alert', 'alert-danger');
		
		$element = new htmlTag('div', $id, $class);
		$element->setAttr('role','alert');
		$element->_BtIcon('exclamation-sign');
		$element->_SPAN('Error: ',null,'sr-only');

		if($text !== NULL && is_string($text))
			$element->setContent($text);
				
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	
	public function _BtFieldInfo($id = null, $class = array(), $text = 'Info'){
		if(is_array($class)){
			$class[] = 'alert';
			$class[] = 'alert-info';
		} else 
			$class = array($class, 'alert', 'alert-info');
		
		$element = new htmlTag('div', $id, $class);
		$element->setAttr('role','alert');
		$element->_BtIcon('exclamation-sign');
		$element->_SPAN('Info: ',null,'sr-only');

		if($text !== NULL && is_string($text))
			$element->setContent($text);
				
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	
	public function _BtFieldSuccess($id = null, $class = array(), $text = 'Success!'){
		if(is_array($class)){
			$class[] = 'alert';
			$class[] = 'alert-success';
		} else 
			$class = array($class, 'alert', 'alert-success');
		
		$element = new htmlTag('div', $id, $class);
		$element->setAttr('role','alert');
		$element->_BtIcon('exclamation-sign');
		$element->_SPAN('Success: ',null,'sr-only');

		if($text !== NULL && is_string($text))
			$element->setContent($text);
				
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	public function _BtFieldWarning($id = null, $class = array(), $text = 'Warning!'){
		if(is_array($class)){
			$class[] = 'alert';
			$class[] = 'alert-warning';
		} else 
			$class = array($class, 'alert', 'alert-warning');
		
		$element = new htmlTag('div', $id, $class);
		$element->setAttr('role','alert');
		$element->_BtIcon('exclamation-sign');
		$element->_SPAN('Warning: ',null,'sr-only');

		if($text !== NULL && is_string($text))
			$element->setContent($text);
				
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	public function _BtDropdown($id, $class, $text, $size = 'default', $isExpanded = false, $isModular = true){
		if(is_array($class)){
			$class[] = 'btn';
			$class[] = 'dropdown-toggle';
		} else 
			$class = array($class, 'btn', 'dropdown-toggle');
		
		$class[] = 'btn-'.$size;
			
		$element = new htmlTag('div', NULL, 'dropdown');
		
		$dropDown = null;
		$dropDown = $element->_BtButton($id, $class, $text, NULL, $text)->inner();

		$dropDown->setAttr('data-toggle','dropdown');
		$dropDown->setAttr('aria-haspopup','true');
		
		if($isExpanded === TRUE)
			$dropDown->setAttr('aria-expanded','true');
		
		$dropDown->setContent('<span class="caret"></span>');
		
		$element->_UL(NULL, ['dropdown-menu'])->inner()->setAttr('aria-labelledby',$id);
		
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	
	public function _BtDropdownEntrySimple($id = null, $class = null, $text, $isDisabled = false){
		if($isDisabled)	
			if(is_array($class))
				$class[] = 'disabled';
			else 
				$class = array($class, 'disabled');	
			
		$element = new htmlTag('li', $id, $class);
		$element->_A('#')->inner()->setContent($text);
		
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	
	public function _BtDropdownEntryHeader($id = null, $class = null, $text){
		if(is_array($class))
			$class[] = 'dropdown-header';
		else 
			$class = array($class, 'dropdown-header');	
		
		$element = new htmlTag('li', $id, $class);
		$element->inner()->setContent($text);
		
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	
	public function _BtDropdownEntryDivider($id = null, $class = null){
		if(is_array($class))
			$class[] = 'divider';
		else 
			$class = array($class, 'divider');	
		
		$element = new htmlTag('li', $id, $class);
		$element->setAttr('role','separator');
		
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	
	public function _BtDropdownFromArray($id = null, $class = null, $text = null, $dropdownArray){
		$this->_BtDropdown($id, $class, $text);
		
		$inBox = $this->inner[count($this->inner) - 1];
		
		if(is_array($dropdownArray) && is_a($dropdownArray[0],'htmlTag')){
			foreach($dropdownArray as $e)	
				$inBox->inner()->_LI()->inner()->addElement($e);
		}

		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	
	public function _BtForm($id = null, $class = null){
		if(is_array($class))
			$class[] = 'bootForm';
		else 
			$class = array($class, 'bootForm');
			
		$element = new htmlTag('form', $id, $class);
		
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
		
	public function _BtInputText($id = null, $class = null, $label = null, $standard = '', $labelLeft = true){
		if(is_array($class))
			$class[] = 'input-group';
		else 
			$class = array($class, 'input-group');	
		
		$element = new htmlTag('div', $id, $class);
		
		if($labelLeft){
			$element->_SPAN($label,null, ['input-group-addon']);
			$element->_Input(null, ['form-control'], 'text', $standard);
		} else {
			$element->_Input(null, ['form-control'], 'text', $standard);
			$element->_SPAN($label,null, ['input-group-addon']);
		}		
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}	
	
	public function _BtInputPassword($id = null, $class = null, $label = null, $standard = '', $labelLeft = true){
		if(is_array($class))
			$class[] = 'input-group';
		else 
			$class = array($class, 'input-group');	
		
		$element = new htmlTag('div', $id, $class);
		
		if($labelLeft){
			$element->_SPAN($label,null, ['input-group-addon']);
			$element->_Input(null, ['form-control'], 'password', $standard);
		} else {
			$element->_Input(null, ['form-control'], 'password', $standard);
			$element->_SPAN($label,null, ['input-group-addon']);
		}		
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}	
	
	public function _BtNavTab($id = null, $class = array()){
		if(is_array($class)){
			$class[] = 'nav';
			$class[] = 'nav-tabs';
		} else 
			$class = array($class, 'nav', 'nav-tabs');	
		
		$element = new htmlTag('ul', $id, $class);
		
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	
	public function _BtNavPills($id = null, $class = array()){
		if(is_array($class)){
			$class[] = 'nav';
			$class[] = 'nav-pills';
		} else 
			$class = array($class, 'nav', 'nav-pills');	
		
		$element = new htmlTag('ul', $id, $class);
		
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	
	public function _BtNavPillsVertStacked($id = null, $class = array()){
		if(is_array($class)){
			$class[] = 'nav';
			$class[] = 'nav-pills';
			$class[] = 'nav-stacked';
		} else 
			$class = array($class, 'nav', 'nav-pills', 'nav-stacked');	
		
		$element = new htmlTag('ul', $id, $class);
		
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	
	public function _BtNavElement($id = null, $class = array()){
		$element = new htmlTag('ul', $id, $class);
		$element->setAttr('role','presentation');
		
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	
	public function _BtNavElementActive($id = null, $class = array()){
		if(is_array($class)){
			$class[] = 'active';
		} else 
			$class = array($class, 'active');	
		
		$element = new htmlTag('ul', $id, $class);
		$element->setAttr('role','presentation');
		
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	
	public function _BtNavElementDropDown($id = null, $class = array()){
		if(is_array($class)){
			$class[] = 'dropdown';
		} else 
			$class = array($class, 'dropdown');	
		
		$element = new htmlTag('ul', $id, $class);
		$element->setAttr('role','presentation');
		
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	
	public function _BtNavbar($id = null, $class = array()){
		if(is_array($class)){
			$class[] = 'navbar';
			$class[] = 'navbar-default';
		} else 
			$class = array($class, 'navbar', 'navbar-default');	
		
		$element = new htmlTag('nav', $id, $class);
		$element->_DIV(null, 'container-fluid');
		
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	
	public function _BtNavbarHeader($id = null, $class = array()){
		if(is_array($class)){
			$class[] = 'navbar-header';
		} else 
			$class = array($class, 'navbar-header');	
		
		$element = new htmlTag('div', $id, $class);
		
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	
	public function _BtNavbarBrandLink($id = null, $class = array(), $linkSrc = '#'){
		if(is_array($class)){
			$class[] = 'navbar-brand';
		} else 
			$class = array($class, 'navbar-brand');	
		
		$element = new htmlTag('a', $id, $class);
		$element->setAttr('src',$linkSrc);
		
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	
	public function _BtNavbarForm($id = null, $class = array(), $role = null){
		if(is_array($class)){
			$class[] = 'navbar-form';
			$class[] = 'navbar-left';
		} else 
			$class = array($class, 'navbar-form','navbar-left');	
		
		$element = new htmlTag('form', $id, $class);	
		
		if(is_string($role))
			$element->setAttr('role',$role);
		
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	
	public function _BtNavbarButton($id = null, $class = array(), $text = null){
		if(is_array($class)){
			$class[] = 'btn';
			$class[] = 'btn-default';
			$class[] = 'navbar-btn';
		} else 
			$class = array($class, 'btn','btn-default', 'navbar-btn');	
		
		$element = new htmlTag('button', $id, $class);	
		
		if(is_string($text))
			$element->setContent($text);
		
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	
	public function _BtNavbarText($text = null){
		$element = new htmlTag('p', null, ['navbar-text']);	
		
		if(is_string($text))
			$element->setContent($text);
		
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	
	public function _BtNavbarSetAlignmentRight(){
		$this->class[] = 'navbar-right';
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	
	public function _BtFormGroup($id = null, $class = array()){
		if(is_array($class)){
			$class[] = 'form-group';
		} else 
			$class = array($class, 'form-group');	
			
		$element = new htmlTag('div', $id, $class);	

		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	 
	public function _BtBreadcrumb($id = null, $class = null){
		if(is_array($class))
			$class[] = 'breadcrumb';
		else 
			$class = array($class, 'breadcrumb');	
		
		$element = new htmlTag('ol', $id, $class);
		
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	} 
	public function _BtBreadcrumbLinkEntry($id = null, $class = null, $text = '', $linkSrc = '#'){
		if(is_array($class))
			$class[] = 'breadcrumbLink';
		else 
			$class = array($class, 'breadcrumbLink');	
		
		$element = new htmlTag('li', $id, $class);
		
		$element->inner()->_A($linkSrc, null, null, $text);
		
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	public function _BtBreadcrumbActiveEntry($id = null, $class = null, $text = ''){
		if(is_array($class))
			$class[] = 'active';
		else 
			$class = array($class, 'active');	
		
		$element = new htmlTag('li', $id, $class);
		$element->setContent($text);
		
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	
	/*
	 * 
	 * TODO create the Pagination
	 * 
	 */
	
	public function _BtLabelDefault($text){
		$element = new htmlTag('span', null, ['label', 'label-default']);
		$element->setContent($text);
		
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	public function _BtLabelPrimary($text){
		$element = new htmlTag('span', null, ['label', 'label-primary']);
		$element->setContent($text);
		
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	public function _BtLabelSuccess($text){
		$element = new htmlTag('span', null, ['label', 'label-success']);
		$element->setContent($text);
		
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	public function _BtLabelInfo($text){
		$element = new htmlTag('span', null, ['label', 'label-info']);
		$element->setContent($text);
		
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	public function _BtLabelWarning($text){
		$element = new htmlTag('span', null, ['label', 'label-warning']);
		$element->setContent($text);
		
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	public function _BtLabelDanger($text){
		$element = new htmlTag('span', null, ['label', 'label-danger']);
		$element->setContent($text);
		
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	
	public function _BtBadge($text){
		$element = new htmlTag('span', null, ['badge']);
		$element->setContent($text);
		
		$this->inner[] = $element;		
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	
	public function _BtProgressBar($id = null, $class = array(), $label = null, $progress = 10, $type = null){
		if(is_array($class))
			$class[] = 'progress-bar';
		else 
			$class = array($class, 'progress-bar');	
		
		switch($type){
			case 'success':
				$class[] = 'progress-bar-success';
			case 'info':
				$class[] = 'progress-bar-info';
			case 'warning':
				$class[] = 'progress-bar-warning';
			case 'danger':
				$class[] = 'progress-bar-danger';
		}
		
		$element = new htmlTag('div', null, ['progress']);
		$element->_DIV($id, $class)->in()->setAttr('role','progressbar')->setAttr('aria-valuemin','0')->setAttr('aria-valuenow',$progress)->setAttr('aria-valuemax',"100")->setAttr('style','width: '.$progress.'%');

		if(is_string($label))
			$element->in()->setContent($label);
		
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	
	public function _BtPanel($id = null, $class = array(), $type = 'default'){
		if(is_array($class)){
			$class[] = 'panel';
			$class[] = 'panel-'.$type;
		} else 
			$class = array($class, 'panel', 'panel-'.$type);
		
		$element = new htmlTag('div', $id, $class);
		
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	
	public function _BtPanelHeader($id = null, $class = array(), $text = ''){
		if(is_array($class)){
			$class[] = 'panel-heading';
		} else 
			$class = array($class, 'panel-heading');
		
		$element = new htmlTag('div', null, $class);
		
		if(!empty($text))
			$element->setContent($text);
		
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	
	public function _BtPanelBody($id = null, $class = array(), $text = ''){
		if(is_array($class)){
			$class[] = 'panel-body';
		} else 
			$class = array($class, 'panel-body');
		
		$element = new htmlTag('div', null, $class);
		
		if(!empty($text))
			$element->setContent($text);
				
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	
	public function _BtPanelFooter($id = null, $class = array(), $text = ''){
		if(is_array($class)){
			$class[] = 'panel-footer';
		} else 
			$class = array($class, 'panel-footer');
		
		$element = new htmlTag('div', null, $class);
		
		if(!empty($text))
			$element->setContent($text);
				
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	
	public function _BtColumnSize_4($id = null, $class = array()){
		if(is_array($class))
			$class[] = 'col-md-4';
		else 
			$class = array($class,'col-md-4');
			
		$element = new htmlTag('div', $id, $class);
		
		if(!empty($text))
			$element->setContent($text);
				
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	
	public function isInnerClose(){
		$this->close = '/';
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	public function setStyle($style_name, $style_value){
		$this->style[] = [$style_name, $style_value];
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	public function setCenter(){
		$this->in()->setStyle('margin', '0 auto');
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	public function setAttr($attr_name = null, $attr_value = null){
		if($attr_name !== null){	    
			if(is_array($attr_name)){
			    foreach($attr_name as $key => $val)
	                $this->attr[$key] = $val;
			} else if($attr_value === null)
				$this->attr[$attr_name] = null;
			else
				$this->attr[$attr_name] = $attr_value;
		}
		
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}

	public function addClass($class){
		if(is_string($class))	
			$this->class[$class] = $class;
		else if(is_array($class))
			foreach($class as $val)
				$this->class[$val] = $val;
			
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	public function removeClass($class){
		if(is_string($class) && isset($this->class[$class]))	
			unset($this->class[$class]);
		else if(is_array($this->class))
			foreach($this->class as $k => $val)
				if($val == $class)	
					unset($this->class[$k]);
			
		if(is_a($this, 'htmlTag'))
			return $this;
		else
			return new htmlTag();
	}
	public function aList($id = null){
		$element = new htmlTagList($id);
		
		$this->inner[] = $element;
		
		if(is_a($this, 'htmlTag'))
			return $this->inner[count($this->inner)-1];
		else
			return new htmlTagList();
	}
	
	public function broadcastAsString(){
		$return = '';
			
		if($this->isText){
			$return .= $this->text;
			return $return;
		}	
		$return .= '<'.$this->tag;
		if($this->id != null)
			$return .= ' id="'.$this->id.'"';
		if(count($this->class) > 0){
			$return .= ' class="';	
			foreach($this->class as $val)
				$return .= $val.' ';
			$return .= '"';
		}
		
		if(count($this->style) > 0){
			$return .= ' style="';
				foreach($this->style as $val)
					$return .= $val[0].':'.$val[1].';';
			$return .= '"';
		}
		
		if(count($this->attr) > 0){
			foreach ($this->attr as $key => $val) {
				if($val === null)
					$return .= ' '.$key;
				else
					$return .= ' '.$key.'="'.$val.'"';
			}
		}
		$return .= $this->close.'>';
		
		if(!empty($this->inner))
			foreach($this->inner as $val)
				$return .= $val->broadcastAsString();
		
		#if($this->tag == 'body')
		#	html::footer()->broadcast();
		
		if($this->close === null)
			$return .= '</'.$this->tag.'>';	
		if($this->final === TRUE)
			$return .= '</html>';
		
		return $return;
	}
	
	public function broadcast(){
		if($this->isText){
			echo $this->text;
			return;
		}	
		echo '<'.$this->tag;
		if($this->id != null)
			echo ' id="'.$this->id.'"';
		if(count($this->class) > 0){
			echo ' class="';	
			foreach($this->class as $val)
				echo $val.' ';
			echo '"';
		}
		
		if(count($this->style) > 0){
			echo ' style="';
				foreach($this->style as $val)
					echo $val[0].':'.$val[1].';';
			echo '"';
		}
		
		if(count($this->attr) > 0){
			foreach ($this->attr as $key => $val) {
				if($val === null)
					echo ' '.$key;
				else
					echo ' '.$key.'="'.$val.'"';
			}
		}
		echo $this->close.'>';
		
		if(!empty($this->inner))
			foreach($this->inner as $val)
				$val->broadcast();
		
		if($this->tag == 'body')
			html::footer()->broadcast();
		
		if($this->close === null)
			echo '</'.$this->tag.'>';	
		if($this->final === TRUE)
			echo '</html>';
	}
	private function setCloseTag(){
		switch($this->tag){
			case 'input':
				$this->close = '/';
				break;
			default:
				break;
		}
	}
}

class htmlTagList extends htmlTag {
	private $on = TRUE;	
	private $tag = null;
	private $id = null;
	private $class = array();
	private $attr = array();
	private $close = null;
	private $inner = array();
	private $parent = null;
	private $text = '';
	private $usedIDs = array();
    private $htmlClassElementIDs = array();
	private $final = false;
	private $style = array();
	
	function __construct($id = null){
		if(is_string($id)){
			$this->id = $id;
			html::setMapIDList($this);
		}
		$this->tag = 'ul';
	}
	
	public function getID(){
		return $this->id;
	}
	
	public function setClass($class){
		if(is_array($class))
			$this->class = array_merge($this->class, $class);
		else if($class !== NULL)
			$this->class[] = (string)$class;
		
		if(is_a($this, 'htmlTagList'))
			return $this;
		else
			return new htmlTagList();
	}
	
	############################################################
	
	public function _LIText($text, $id = null, $class = array()){
		$element = new htmlTag('li', $id, $class);
		$element->setContent($text);
		
		$this->inner[] = $element;
		
		if($id !== NULL)
            $this->usedIDs[$id] = $element;
		
		if(is_a($this, 'htmlTagList'))
			return $this;
		else
			return new htmlTagList();
	}

	public function _LILink($linkSrc, $linkText, $id = null, $class = array(), $linkID = NULL, $linkClass = array()){
		$element = new htmlTag('li', $id, $class);
		$element->_A($linkSrc,$linkID,$linkClass,$linkText);
		
		$this->inner[] = $element;
		
		if($id !== NULL)
            $this->usedIDs[$id] = $element;
		
		if(is_a($this, 'htmlTagList'))
			return $this;
		else
			return new htmlTagList();
	}
	
	public function _LILabelDefault($labelText, $text = null, $id = null, $class = array()){
		$element = new htmlTag('li', $id, $class);
		$element->_BtLabelDefault($labelText);
		
		if(is_string($text))
			$element->setContent($text);
		
		$this->inner[] = $element;
		
		if($id !== NULL)
            $this->usedIDs[$id] = $element;
		
		if(is_a($this, 'htmlTagList'))
			return $this;
		else
			return new htmlTagList();
	}
	
	public function _LILabelSuccess($labelText, $text = null, $id = null, $class = array()){
		$element = new htmlTag('li', $id, $class);
		$element->_BtLabelSuccess($labelText);
		
		if(is_string($text))
			$element->setContent($text);
		
		$this->inner[] = $element;
		
		if($id !== NULL)
            $this->usedIDs[$id] = $element;
		
		if(is_a($this, 'htmlTagList'))
			return $this;
		else
			return new htmlTagList();
	}
	
	public function _LILabelWarning($labelText, $text = null, $id = null, $class = array()){
		$element = new htmlTag('li', $id, $class);
		$element->_BtLabelWarning($labelText);
		
		if(is_string($text))
			$element->setContent($text);
		
		$this->inner[] = $element;
		
		if($id !== NULL)
            $this->usedIDs[$id] = $element;
		
		if(is_a($this, 'htmlTagList'))
			return $this;
		else
			return new htmlTagList();
	}
	
	public function _LILabelAlert($labelText, $text = null, $id = null, $class = array()){
		$element = new htmlTag('li', $id, $class);
		$element->_BtLabelDanger($labelText);
		
		if(is_string($text))
			$element->setContent($text);
		
		$this->inner[] = $element;
		
		if($id !== NULL)
            $this->usedIDs[$id] = $element;
		
		if(is_a($this, 'htmlTagList'))
			return $this;
		else
			return new htmlTagList();
	}
	
	public function _LILabelInfo($labelText, $text = null, $id = null, $class = array()){
		$element = new htmlTag('li', $id, $class);
		$element->_BtLabelInfo($labelText);
		
		if(is_string($text))
			$element->setContent($text);
		
		$this->inner[] = $element;
		
		if($id !== NULL)
            $this->usedIDs[$id] = $element;
		
		if(is_a($this, 'htmlTagList'))
			return $this;
		else
			return new htmlTagList();
	}
	
	public function _LILabelPrimary($labelText, $text = null, $id = null, $class = array()){
		$element = new htmlTag('li', $id, $class);
		$element->_BtLabelPrimary($labelText);
		
		if(is_string($text))
			$element->setContent($text);
		
		$this->inner[] = $element;
		
		if($id !== NULL)
            $this->usedIDs[$id] = $element;
		
		if(is_a($this, 'htmlTagList'))
			return $this;
		else
			return new htmlTagList();
	}
	
	############################################################
	
	public function broadcast(){
		if(is_string($this->id))
			echo '<!-- List: '.$this->id.' -->';
		if(!empty($this->inner)){
			echo '<'.$this->tag;
			if($this->id != null)
				echo ' id="'.$this->id.'"';
			if(count($this->class) > 0){
				echo ' class="';	
				foreach($this->class as $val)
					echo $val.' ';
				echo '"';
			}
			
			if(count($this->style) > 0){
				echo ' style="';
					foreach($this->style as $val)
						echo $val[0].':'.$val[1].';';
				echo '"';
			}
			
			if(count($this->attr) > 0){
				foreach ($this->attr as $key => $val) {
					if($val === null)
						echo ' '.$key;
					else
						echo ' '.$key.'="'.$val.'"';
				}
			}
			echo $this->close.'>';	
			
			foreach($this->inner as $val)
				$val->broadcast();
			
			echo '</ul>';
		}
	}
}

class htmlFooter{
	private $scripts = array();
	
	public function addScript($link,$async = false, $defer = false){
		$string = '<script src="'.$link.'"';
		if($async)
			$string .= ' async';
		
		if($defer)
			$string .= ' defer';
		
		$string .= '></script>';
		$this->scripts[$link] = $string;
	}
	public function removeScript($link){
		if(isset($this->scripts[$link]))
			unset($this->scripts[$link]);
	}
	public function addScriptPiwik( $url ){
		$string = '<script type="text/javascript">var _paq = _paq || [];_paq.push(["setDomains", ["*.creezi.com"]]);_paq.push([\'trackPageView\']);_paq.push([\'enableLinkTracking\']);(function() {var u="//'.$url.'/";_paq.push([\'setTrackerUrl\', u+\'piwik.php\']);_paq.push([\'setSiteId\', 1]);var d=document, g=d.createElement(\'script\'), s=d.getElementsByTagName(\'script\')[0];g.type=\'text/javascript\';g.async=true;g.defer=true;g.src=u+\'piwik.js\';s.parentNode.insertBefore(g,s);})();</script><noscript><p><img src="//'.$url.'/piwik.php?idsite=1" style="border:0;" alt="" /></p></noscript>';
		$this->scripts['piwik'] = $string;
	}
	public function addScriptPiwikPic($url){
		$this->scripts['piwik'] = '<noscript><p><img src="//'.$url.'/piwik.php?idsite=1" style="border:0;" alt="" /></p></noscript>';
	}
	public function broadcast(){
		$this->broadcastScripts();
	}
	public function addScriptYTPlayer($id){
		$string = '<script>var tag = document.createElement(\'script\');tag.src = "https://www.youtube.com/iframe_api";var firstScriptTag = document.getElementsByTagName(\'script\')[0];firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
var player;var jumpTo = false;function onYouTubeIframeAPIReady(){player = new YT.Player(\'youtubePlayer\',{height: \'394\',width: \'700\',videoId: \''.$id.'\',events:{\'onReady\': onPlayerReady,\'onStateChange\': onPlayerStateChange}});}
function onPlayerReady(event){
	if(QueryString.t.length > 0){
		jumpTo = parseInt(QueryString.t);	
		player.seekTo(jumpTo);
		event.target.startVideo();}}
var done = false;function onPlayerStateChange(event){if (event.data == YT.PlayerState.PLAYING && !done && 1 > 2){setTimeout(stopVideo, 6000);done = true;}}
function stopVideo(){player.stopVideo();}</script>';

		$this->scripts['ytplayer'] = $string;
	}
	private function broadcastScripts(){
		if(!empty($this->scripts))
			foreach($this->scripts as $val)
				echo $val;	
	}
}
class htmlHeader{
	private $DOCType = '<!DOCTYPE html>';
	private $language = '<html lang="en">';
	private $meta = null;
	
	private $title = null;
	
	private $favicon = null;
	private $stylesheets = array();
	private $scripts = array();
	private $IEComments = array();
	private $styles = [];
	private $robots = null;
	
	private $headerLinks = array();
	
	private $description = NULL;
	
	public function __construct(){
		$this->meta = new htmlMeta();
	}
	public function addScriptPiwik( $url ){
		$string = '<script type="text/javascript">var _paq = _paq || [];_paq.push(["setDomains", ["*.creezi.com"]]);_paq.push([\'trackPageView\']);_paq.push([\'enableLinkTracking\']);(function() {var u="//'.$url.'/";_paq.push([\'setTrackerUrl\', u+\'piwik.php\']);_paq.push([\'setSiteId\', 1]);var d=document, g=d.createElement(\'script\'), s=d.getElementsByTagName(\'script\')[0];g.type=\'text/javascript\';g.async=true;g.defer=true;g.src=u+\'piwik.js\';s.parentNode.insertBefore(g,s);})();</script>';
		html::footer()->addScriptPiwikPic($url);
		
		$this->scripts['piwik'] = $string;
	}
	public function addScriptYTPlayer($id){
    	$string = '<script>var player = null;var tag = document.createElement(\'script\');tag.src = "https://www.youtube.com/iframe_api";var firstScriptTag = document.getElementsByTagName(\'script\')[0];firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
var player;var jumpTo = false;function onYouTubeIframeAPIReady(){player = new YT.Player(\'youtubePlayer\',{height: \'394\',width: \'700\',videoId: \''.$id.'\',events:{\'onReady\': onPlayerReady,\'onStateChange\': onPlayerStateChange}});}
function onPlayerReady(event){
	document.getElementById("single-box").className =
    document.getElementById("single-box").className.replace(/\bvideoContainer\b/,\'\');
	setTimeout(function () {
		$("#youtubePlayerPic").addClass("hidden");
		console.log("fired");
	}, 500);	
	/*if(QueryString.t.length > 0){
		jumpTo = parseInt(QueryString.t);	
		player.seekTo(jumpTo);
		//event.target.startVideo();
	}*/
}
var done = false;function onPlayerStateChange(event){if (event.data == YT.PlayerState.PLAYING && !done && 1 > 2){setTimeout(stopVideo, 6000);done = true;}}
function stopVideo(){player.stopVideo();}</script>';

		$this->scripts['ytplayer'] = $string;
    }
	public function setDOCTypeXHTML10(){
		$this->DOCType = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
		return $this;
	}
	public function setDOCTypeHTMLSimple(){
		$this->DOCType = '<!DOCTYPE html>';
		return $this;
	}
	
	public function setLanguage($string = 'en'){
		$this->language = '<html lang="'.$string.'">';		
		return $this;
	}
	public function setCanonicalLink($url = NULL){
		if($url === NULL || !is_string($url))
			return $this;
		
		$this->headerLinks['canonical'] = '<link rel="canonical" href="'.$url.'">';
		$this->meta->setOGURL($url);
		$this->meta->setTwitterURL($url);
	}
	public function selectMeta(){
		return $this->meta;
	}
	public function setFavicon($link = ''){
		$this->favicon = '<link rel="icon" href="'.$link.'">';
		return $this;
	}
	
	public function setTitle($string = 'The clock is tick\'in …'){
		$this->title = '<title>'.$string.'</title>';
		return $this;
	}
	public function setDescription($string = NULL){
		$this->description = '<meta name="Description" content="'.strip_tags($string).'" />';
		return $this;
	}
	public function addKeyword($keyword = NULL){
		if(strlen($keyword) > 0)
			$this->keywords[] = $keyword;
		
		return $this;
	}
	public function addStylesheet($link){
		$this->stylesheets[] = '<link rel="stylesheet" href="'.$link.'" />';
		return $this;
	}
	public function addScript($link){
		$this->scripts[] = '<script src="'.$link.'"></script>';
		return $this;
	}
	public function addOwnStyle($style){
		$this->styles[] = $style;
		return $this;
	}
	public function addOwnScriptString($string){
		$this->scripts[] = '<script>'.$string.'</script>';
		return $this;
	}
	public function addIEComment($string){
		$this->IEComments[] = $string;
		return $this;
	}
	public function loadBootstrapDependencies(){
		$this->addStylesheet('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css');
		$this->addStylesheet('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css');
		$this->addScript('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js');
		$this->addOwnStyle('#main {overflow: scroll;}');
		
		return $this;
	}
	public function broadcast(){
		$this->addScript('/ressources/core/js/login.js');
			
		echo $this->DOCType;
		echo $this->language;
		echo '<head>';
		
		$this->selectMeta()->broadcast();
		
		if(!empty($this->favicon))
			echo $this->favicon;
		if(!empty($this->title))
			echo $this->title;
		if(!empty($this->description))
			echo $this->description;
		if(!empty($this->keywords) && count($this->keywords) > 0){
			echo '<meta name="keywords" content="';	
			for($i = count($this->keywords) - 1; $i >= 0; $i--){
				echo $this->keywords[$i];	
				if($i > 0)
					echo ',';
			}
			echo '" />';
		}
		$this->broadcastStylesheets();
		$this->broadcastScripts();
		$this->broadcastIEScripts();
		$this->broadcastOwnStyles();
		$this->broadcastHeadLinks();
		
		echo '</head>';
	}
	private function broadcastHeadLinks(){
		if(!empty($this->headerLinks))	
			foreach($this->headerLinks as $val)
				echo $val;
	}
	private function broadcastIEScripts(){
		if(!empty($this->IEComments))
			foreach($this->IEComments as $val)
				echo $val;
	}
	private function broadcastScripts(){
		if(!empty($this->scripts))
			foreach($this->scripts as $val)
				echo $val;	
	}
	private function broadcastStylesheets(){
		if(!empty($this->stylesheets)){
			foreach($this->stylesheets as $val)
				echo $val;	
		}
	}
	private function broadcastOwnStyles(){
		if(!empty($this->styles)){
			echo '<style>';	
			foreach($this->styles as $val)
				echo $val;
			echo '</style>';	
		}
	}
}
class htmlMeta{
	private $httpEquiv = null;
	private $charset = null;
	private $viewport = null;
	private $description = null;
	private $author = null;
	private $googleMeta = null;
	
	private $openGraphMeta = NULL;
	private $twitterMeta = NULL;
	
	private $robots = '<meta name="robots" content="follow,index" />';
	
	public function setHTTPEquivXUA(){
		$this->httpEquiv = '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
		return $this;
	}
	public function setCharsetUTF8(){
		$this->charset = '<meta charset="utf-8">';
		return $this;
	}
	public function setViewportFullWidth(){
		$this->viewport = '<meta name="viewport" content="width=device-width, initial-scale=1" />';
		return $this;
	}
	public function setDescription($string = "EMPTY description"){
		$this->description = '<meta name="description" content="'.strip_tags($string).'" />';
		return $this;
	}
	public function setAuthor($string){
		$this->author = '<meta name="author" content="'.$string.'" />';
		return $this;
	}
	public function setGooglePlusKeys($key){
  		$this->googleMeta = '<meta name="google-signin-client_id" content="'.$key.'" />';

		return $this;
	}
	public function setGoogleWebmasterKey($key){
  		$this->googleMeta = '<meta name="google-site-verification" content="'.$key.'" />';

		return $this;
	}
	public function setContentNofollow(){
		$this->robots = '<meta name="robots" content="noindex,follow" />';
		
		return $this;
	}

	public function setOGTitle($title = NULL){
		if(strlen($title) > 0)	
			$this->openGraphMeta['title'] = '<meta property="og:title" content="'.$title.'"/>';
	
		return $this;
	}

	public function setTwitterTitle($title = NULL){
		if(strlen($title) > 0)	
			$this->twitterMeta['title'] = '<meta name="twitter:title" content="'.$title.'"/>';
	
		return $this;
	}
	public function setTwitterDescription($text = NULL){
		if(strlen($text) > 0)	
			$this->twitterMeta['description'] = '<meta name="twitter:description" content="'.strip_tags($text).'"/>';
	
		return $this;
	}
	public function setTwitterURL($string = NULL){
		if(strlen($string) > 0)	
			$this->twitterMeta['url'] = '<meta name="twitter:url" content="'.$string.'"/>';
	
		return $this;
	}
	public function setOGAuthor($title = NULL){
		if(strlen($title) > 0)	
			$this->openGraphMeta['author'] = '<meta property="og:site_name" content="'.$title.'"/>';
	
		return $this;
	}

	public function setTwitterAuthor($title = NULL){
		if(strlen($title) > 0)	
			$this->twitterMeta['author'] = '<meta property="article:author" content="'.$title.'"/>';
	
		return $this;
	}

	public function setOGTypeArticle(){
		$this->openGraphMeta['type'] = '<meta property="og:type" content="article"/>';
		
		return $this;
	}

	public function setTwitterTypeLargeImage(){
		$this->twitterMeta['type'] = '<meta name="twitter:card" content="summary_large_image"/>';
		
		return $this;
	}

	public function setOGImageURL($url = NULL){
		if(strlen($url) > 0)
			$this->openGraphMeta['image'] = '<meta property="og:image" content="'.$url.'" />';
	
		return $this;
	}

	public function setTwitterImageURL($url = NULL){
		if(strlen($url) > 0)
			$this->twitterMeta['image'] = '<meta name="twitter:image" content="'.$url.'" />';
	
		return $this;
	}

	public function setOGImageWidth($integer = 0){
		if((int)$integer > 0)	
			$this->openGraphMeta['imageWidth'] = '<meta property="og:image:width" content="'.(int)$integer.'" />';
		
		return $this;
	}
	
	public function setOGImageHeight($integer = 0){
		if((int)$integer > 0)	
			$this->openGraphMeta['imageHeight'] = '<meta property="og:image:height" content="'.(int)$integer.'" />';	
		
		return $this;
	}

	public function setOGDescription($string = NULL){
		if(strlen($string) > 0)
			$this->openGraphMeta['description'] = '<meta property="og:description" content="'.strip_tags($string).'" />';
	
		return $this;
	}

	public function setOGURL($string = NULL){
		if(strlen($string) > 0)
			$this->openGraphMeta['url'] = '<meta property="og:url" content="'.$string.'" />';
	
		return $this;
	}
	public function setClickaduVerificationKey(string $string = NULL):htmlMeta{
        if(strlen($string) > 0)
            $this->openGraphMeta['clickadu'] = '<meta name="clickadu" content="'.$string.'" />';

        return $this;
    }
    public function setPropellerVerificationKey(string $string = NULL):htmlMeta{
        if(strlen($string) > 0)
            $this->openGraphMeta['propeller'] = '<meta name="propeller" content="'.$string.'" />';

        return $this;
    }
	public function broadcast(){
		if(!is_null($this->httpEquiv))
			echo $this->httpEquiv;
		if(!is_null($this->robots))
			echo $this->robots;
		if(!is_null($this->charset))
			echo $this->charset;
		if(!is_null($this->viewport))
			echo $this->viewport;
		if(!is_null($this->description))
			echo $this->description;
		if(!is_null($this->author))
			echo $this->author;
		if(!is_null($this->googleMeta))
			echo $this->googleMeta;
		
		if(count($this->twitterMeta) > 0){
			foreach($this->twitterMeta as $val)
				echo $val;
		}
		if(count($this->openGraphMeta) > 0){
			foreach($this->openGraphMeta as $val)
				echo $val;
		}
		
		return null;
	}
}
class output{
	private static $urlMap = null;	
	public static function force404(){
		#	TODO redo 404 amd other html-output-funcs
		html::send404();
	}
	public static function force303($link){
		html::send303($link);
	}
	public static function sendHeader(){
		html::head()->broadcast();
	}
	public static function loadBackendTemplate(){
		require_once 'themes/'.backendLayoutDIR.'/config.php';
	}
	public static function getMap($urlKey = false){
		if(self::$urlMap === null)
			self::$urlMap = new pathMap($urlKey);
		return self::$urlMap;
	}
	public static function loadSite( urlObj $urlObj = NULL ){
		$j = 0;
		$lastFile = null;
		if($urlObj === NULL && count(core::getURLObj()->getPathArray()) == 1 && empty(core::getURLObj()->getPathArray()[0])){
			if(($f = output::getMap()->getFile()) !== NULL)
				require_once 'themes/'.frontendLayoutDIR.'/'.$f;
			else
				output::force404();
		} else if($urlObj === NULL || ($urlObj->isVirtual() && count($urlObj->getPathArray()) > 0) ){
			$checkPath = output::getMap();
			if($urlObj !== NULL && $urlObj->isVirtual())
	           core::setURLObj( $urlObj );
			
			foreach(core::getURLObj()->getPathArray() as $val){
				$checkPath = $checkPath->getChild($val);
				
				if($checkPath === FALSE)
					output::force404();
				else if($checkPath->isChildDynamicAll())
					break;
			}
			require_once 'themes/'.frontendLayoutDIR.'/'.$checkPath->getFile();
		} else {
		    output::force404();
		}
	}
	public static function loadFrontendTemplate(){
		require_once 'themes/'.frontendLayoutDIR.'/config.php';
	}
	public static function loadAJAX(){
		if(useAJAX)	
			require_once 'themes/'.frontendLayoutDIR.'/'.frontendLayoutAJAXFile;
		else
			return FALSE;
		return TRUE;
	}
	public static function loadCronjob(){
		$string = SERVER_ROOT.'/'.VH_ROOT.'/intern/'.CORE_VERSION.'/crons/_autoload.*.php';	
		foreach(glob($string) as $file){
		    require_once $file;
		}
	}
}
class B {
	/*
	 * searches the HTML-DOM-Tree for element by passed ID-String
	 *
	 * @param string $id id-string of element for look-up
	 *
	 * @return htmlTag the Element as object htmlTag or null, when not found
	 */
    public static function ID($id){
        $res = html::getElementByID($id);
		if(is_a($res,'htmlTag')){
			#echo $id."----";	
			return $res;
		} else if(is_a($res, 'htmlTagList')){
			return $res;
		} else {
        	$res = new htmlTag('div',$id);	
        	html::body()->addElement($res);	
        	return $res;
		}
    }
	public static function CLS($class){
		return ;
	}
}

?>