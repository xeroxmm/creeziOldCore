<?php
class cronjobHook {
	private static $requestParams = [];	
	private static $isValidCall = FALSE;
	private static $registeredHooks = [];
	
	public static function analyzeRequest(){
		$t = core::getURLObj()->getPathArray();
		if(count($t) > 1){	
			if(isset(self::$registeredHooks[ $t[1] ])){
				call_user_func(self::$registeredHooks[ $t[1] ]);
			}
		}
	}
	public static function registerHook($nameHook, $object){
		self::$registeredHooks[$nameHook] = $object;
	}
	private static function isRequestInFunctionList($requestName){
		switch($requestName){
			case 'relatedTags':
				if(empty(self::$requestParams) || !isset(self::$requestParams['ID']))
					return FALSE;
				break;
			default:
				return FALSE;
		}
		self::$isValidCall = TRUE;
		return self::$isValidCall;
	}
	private static function doRequest(){
		if(self::$isValidCall){
			
		}
	}
}
?>