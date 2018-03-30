<?php
class cookie {
	private static $lastCookieDBObject = NULL;
	
	public static function check($string){
		if(isset($_COOKIE[$string]))
			return true;
		else
			return false;
	}
	public static function set($string,$value,$time = cookieStandardTime){
		setcookie($string, $value, time()+ $time);
	}
	public static function delete($string){
		setcookie($string, "", time() - 3600);
	}
	public static function deleteAll(){
		self::delete('ID');
		self::delete('key');
		self::delete('score');
	}
	public static function deleteDBEntry(){
		if(self::isUserCookieValid()){
			#$sql = 'DELETE FROM `security_CookieIdent` WHERE `ID` = '.(int)(self::$lastCookieDBObject->ID).' AND `cookieTrashWhoore` = '.(int)(self::$lastCookieDBObject->cookieTrashWhoore).';';
			$databaseName = DBTableNameUserCookies;
			$sql = new dbObj();
			$sql->setTypeDELETE();
			
			$sql->setDatabase();
			
			$sql->setConditionIntegerEqual('ID', self::$lastCookieDBObject->ID, $databaseName);
			$sql->setConditionIntegerEqual('cookieTrashWhoore', self::$lastCookieDBObject->cookieTrashWhoore, $databaseName, 'AND');
			
			db::query($sql);
		}
	}
	public static function get($string){
		if(isset($_COOKIE[$string]))
			return $_COOKIE[$string];
		else
			return false;
	}
	public static function setUserCookie(userObj $userObject){
		# Generate Cookie- and Db-Values	
			$userID = $userObject->getDatabaseID();

			$keyHash = security::getRandomString(40);
			$cookieID = abs(crc32($userID.'F01'.dechex(time()).'A'.crc32($keyHash)));
			
			$saltRandom = security::getRandomString(20);
			$cookieTrashWhoore = (int)rand(0, 9999999999);
			
			$keyInCookie = md5($keyHash.$saltRandom.$cookieTrashWhoore);
		
			$date = new DateTime("now");
			$date->format('Y-m-d H:i:s');
					
			$dateCookieSet = $date->format('Y-m-d H:i:s');
			date_add($date, date_interval_create_from_date_string(cookieStandardTime.' seconds'));
			$dateCookieExpire = $date->format('Y-m-d H:i:s');
		 	
			$status = FALSE;
		
		# Security-Check ->
			if($userID > 0){
				$databaseName = DBTableNameUserCookies;
				$sql = new dbObj();
				$sql->setTypeINSERT();
				
				$sql->setDatabase($databaseName);
				
				$sql->setInsertFieldValueNULL('ID',$databaseName);
				$sql->setInsertFieldValueInteger('userID', $userID,$databaseName);
				$sql->setInsertFieldValueString('keyHash', $keyHash, $databaseName);
				$sql->setInsertFieldValueString('keyInCookie', $keyInCookie, $databaseName);
				$sql->setInsertFieldValueString('saltRandom', $saltRandom, $databaseName);
				$sql->setInsertFieldValueString('dateCookieSet', $dateCookieSet, $databaseName);
				$sql->setInsertFieldValueString('dateCookieExpired', $dateCookieExpire, $databaseName);
				$sql->setInsertFieldValueInteger('cookieID', $cookieID, $databaseName);
				$sql->setInsertFieldValueInteger('cookieTrashWhoore', $cookieTrashWhoore, $databaseName);
			
				$sql->setOnDuplicateFieldValueString('keyHash', $keyHash);
				$sql->setOnDuplicateFieldValueString('keyInCookie', $keyInCookie);
				$sql->setOnDuplicateFieldValueString('saltRandom', $saltRandom);
				$sql->setOnDuplicateFieldValueString('dateCookieSet', $dateCookieSet);
				$sql->setOnDuplicateFieldValueString('dateCookieExpired', $dateCookieExpire);
				$sql->setOnDuplicateFieldValueInteger('cookieID', $cookieID);
				$sql->setOnDuplicateFieldValueInteger('cookieTrashWhoore', $cookieTrashWhoore);

				if(db::query($sql)){
					# Set Cookie Values
					
					self::set('ID', $cookieID);
					self::set('key', $keyInCookie);
					self::set('score', $cookieTrashWhoore);
					
					$status = TRUE;
				} // else {echo "<--".$sql->getQueryString()."-->";die();}
			}
			if(!$status){
				self::deleteAll();
				
				return FALSE;
			}
			
		return TRUE;
	}
	public static function isUserCookieValid(){	
		if(self::check('ID') === FALSE || self::check('key') === FALSE || self::check('score') === FALSE)
			return FALSE;

		/*$sql = 'SELECT 
					`ID`, `userID`, `keyHash`, `keyInCookie`, `saltRandom`, `dateCookieSet`, `dateCookieExpired`, `cookieID` ,`cookieTrashWhoore`
				FROM
					`security_CookieIdent`
				WHERE 
					`cookieID` = '.(int)db::harm(self::get('ID')).' AND `cookieTrashWhoore` = '.(int)db::harm(self::get('score')).' AND `keyInCookie` = '.db::harmAndString(self::get('key')).';';
		*/
		
		$databaseName = DBTableNameUserCookies;
		
		$sql = new dbObj();
		$sql->setTypeSELECT();
		$sql->setDatabase($databaseName);
		
		$sql->setSELECTField('ID', $databaseName);
		$sql->setSELECTField('userID', $databaseName);
		$sql->setSELECTField('keyHash', $databaseName);
		$sql->setSELECTField('keyInCookie', $databaseName);
		$sql->setSELECTField('saltRandom', $databaseName);
		$sql->setSELECTField('dateCookieSet', $databaseName);
		$sql->setSELECTField('dateCookieExpired', $databaseName);
		$sql->setSELECTField('cookieID', $databaseName);
		$sql->setSELECTField('cookieTrashWhoore', $databaseName);
		
		$sql->setConditionIntegerEqual('cookieID', self::get('ID'), $databaseName);
		$sql->setConditionIntegerEqual('cookieTrashWhoore', self::get('score'), $databaseName, 'AND');
		$sql->setConditionStringEqual('keyInCookie', self::get('key'), $databaseName, 'AND');
		$sql->setConditionDateTimeHigher('dateCookieExpired', time(), $databaseName, 'AND');
		
		$res = db::query($sql); // echo "<!--".$sql->getQueryString()."-->";

		if(count($res) == 1){
			self::$lastCookieDBObject = $res[0];	

			return TRUE;
		} else {

			self::deleteAll();
			
			self::$lastCookieDBObject = NULL;

			return FALSE;
		}
	}

	public static function getLastCookieDBObject(){
		return self::$lastCookieDBObject;
	}
}
?>