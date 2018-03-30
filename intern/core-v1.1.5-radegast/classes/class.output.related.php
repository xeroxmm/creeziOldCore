<?php
class contentRelated {
	private static $objects = [];
	public static function getRelatedObjectById($contentID, $type){
		if(!isset(self::$objects[$contentID.$type]))	{
			$valid = FALSE;	
			switch($type){
				case 'image':
				case 'video':
				case 'collection':
					break;
				default;
					return NULL;
					break;
			}
			if($valid)
				self::loadRelatedObjectByIDAndType($contentID,$type);
		} else
			return self::$objects[$contentID.$type];
	}
	
	private static function loadRelatedObjectByIDAndUpdate($contentID,$type){
		
	}
}
class contentRelatedObject {
	private $hasItems = FALSE;
	private $list = [];
	private $template = NULL;
	
	private $scope = NULL;
		
	function __construct(){
		$this->scope = new contentRelatedScope();
	}
	
	### Scopes ###
	public function getScopeObject(){
		return $this->scope;
	}
	
	### Information on article
	//public function 
}
class contentRelatedScope {
	private $status = FALSE;
	private $types = [];
	function __construct(){
		
	}
	public function addImage(){
		$this->types['images'] = TRUE;
	}
	public function addVideo(){
		$this->types['videos'] = TRUE;
	}
	public function addArticle(){
		$this->types['articles'] = TRUE;
	}
	public function addCollection(){
		$this->types['collections'] = TRUE;
	}
	public function getTypes(){
		if(count($this->types) > 0)
			return $this->types;
		
		return FALSE;
	}
}
?>