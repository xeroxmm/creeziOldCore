<?php
	/**
	 * Database class -> Full DB-Usability
	 *
	 * @package AdSocials
	 * @since 1.0.0
	 */
class dbObj{
	private $type = enumDBQueryType::SELECT;
	
	private $databases = array();
	private $selectField = array();
	private $conditionField = array();
	private $orderByField = array();
	
	private $updateValueField = array();
	private $insertValueField = array();
	private $onDuplicateField = array();
	
	private $updateDuplicateValueField = array();
	
	private $limit = NULL;
	
	private $presetString = NULL;
	
	private $errors = array();
	
	private $joinLefts = array();
	private $joinRights = array();
	
	private $groupByString = array();
	private $group = array();
	
	public function getErrors(){
		return $this->errors;
	}
    public function setSQL(string $sql ){

    }
	public function presetShowTableStatus($tableName, $databaseName){
		$this->type = enumDBQueryType::PRESET;
		$this->presetString = 'SHOW TABLE STATUS 
			FROM 
				`'.$databaseName.'` 
			LIKE '.db::harmAndString($tableName).';';
	}
	
	public function presetGetNextAutoIncrementValueOfTable($name){
		$this->type = enumDBQueryType::PRESET;
		$this->presetString = 'SELECT  `auto_increment` AS nextID 
		FROM INFORMATION_SCHEMA.TABLES
		WHERE table_name = '.db::harmAndString($name).'
		LIMIT 1';
	}
	
	public function setTypeSELECT(){
		$this->type = enumDBQueryType::SELECT;
	}
	public function setTypeINSERT(){
		$this->type = enumDBQueryType::INSERT;
	}
	public function setTypeDELETE(){
		$this->type = enumDBQueryType::DELETE;
	}
	public function setTypeUPDATE(){
		$this->type = enumDBQueryType::UPDATE;
	}
	
	public function setLimit($int = NULL, $offset = 0){
		$i = (int)$int;
		
		if($int < 1)
			$this->limit = NULL;
		else
			$this->limit = $offset.','.$int;
	}
	
	public function setOnDuplicateFieldValueNULL($updatedField){
		if(!is_string($updatedField))
			return;	

		$this->updateDuplicateValueField[$updatedField] = array('value' => 'NULL');
	}
	
	public function setOnDuplicateFieldValueNOW($updatedField){
		if(!is_string($updatedField))
			return;	

		$this->updateDuplicateValueField[$updatedField] = array('value' => 'NOW()');
	}
	
	public function setOnDuplicateFieldValueDATE_ADD_TO_NOW($updatedField, $updatedValueInteger, $dateUnity = 'SECOND'){
		if(!is_string($updatedField))
			return;	

		$this->updateDuplicateValueField[$updatedField] = array('value' => 'DATE_ADD(NOW(), INTERVAL '.(int)$updatedValueInteger.' '.$dateUnity.')');
	}
	
	public function setOnDuplicateFieldValueString($updatedField, $updatedValueString){
		if(!is_string($updatedField))
			return;	

		if($updatedValueString === NULL)
			$updatedValueString = 'NULL';
		else
			$updatedValueString = db::harmAndString($updatedValueString);

		$this->updateDuplicateValueField[$updatedField] = array('value' => $updatedValueString);
	}
	
	public function setOnDuplicateFieldValueInteger($updatedField, $updatedValueInteger){
		if(!is_string($updatedField))
			return;	

		if($updatedValueInteger === NULL)
			$updatedValueInteger = 'NULL';
		else
			$updatedValueInteger = (int)($updatedValueInteger);

		$this->updateDuplicateValueField[$updatedField] = array('value' => $updatedValueInteger);
	}
	
	public function setOnDuplicateFieldValueAddIntegerToColumn($updatedField, $updatedValueInteger, $updateColumn){
		if(!is_string($updatedField))
			return;	

		if($updatedValueInteger === NULL)
			$updatedValueInteger = 'NULL';
		else
			$updatedValueInteger = (int)($updatedValueInteger);

		$updateColumn = str_replace('`','',$updateColumn);

		$this->updateDuplicateValueField[$updatedField] = array('value' => '`'.$updateColumn.'` + '.$updatedValueInteger);
	}
	
	public function setOnDuplicateFieldAnotherColumn($updatedField_1, $updatedField_2, $databaseName_2){
		if(!is_string($updatedField_1) || !is_string($updatedField_2))
			return;	

		if(!is_string($databaseName_2))
			return;	

		$this->updateDuplicateValueField[$updatedField_1] = array('value' => $databaseName_2.'`'.$updatedField_2.'`');
	}
	
	public function setUpdatedFieldValueNULL($updatedField, $databaseName){
		if(!is_string($updatedField))
			return;	
			
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;	

		$this->updateValueField[$updatedField] = array('db' => $databaseName, 'value' => 'NULL');
	}
	
	public function setInsertFieldValueNULL($insertField, $databaseName){
		if(!is_string($insertField))
			return;	
			
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;	

		if(!isset($this->insertValueField[$insertField]))
			$this->insertValueField[$insertField] = array();

		$this->insertValueField[$insertField][] = array('db' => $databaseName, 'value' => 'NULL');
	}
	
	public function setUpdatedFieldValueNOW($updatedField, $databaseName){
		if(!is_string($updatedField))
			return;	
			
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;	

		$this->updateValueField[$updatedField] = array('db' => $databaseName, 'value' => 'NOW()');
	}
	
	public function setInsertFieldValueNOW($insertField, $databaseName){
		if(!is_string($insertField))
			return;	
			
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;	

		if(!isset($this->insertValueField[$insertField]))
			$this->insertValueField[$insertField] = array();

		$this->insertValueField[$insertField][] = array('db' => $databaseName, 'value' => 'NOW()');
	}
	
	public function setUpdatedFieldValueDATE_ADD_TO_NOW($updatedField, $updatedValueInteger, $databaseName, $dateUnity = 'SECOND'){
		if(!is_string($updatedField))
			return;	
			
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;	

		if($updatedValueInteger === NULL || $updatedValueInteger == 0)
			$this->updateValueField[$updatedField] = array('db' => $databaseName, 'value' => 'NOW()');
		else
			$this->updateValueField[$updatedField] = array('db' => $databaseName, 'value' => 'DATE_ADD(NOW(), INTERVAL '.(int)$updatedValueInteger.' '.$dateUnity.')');
	}
	
	public function setInsertFieldValueDATE_ADD_TO_NOW($insertField, $insertValueInteger, $databaseName, $dateUnity = 'SECOND'){
		if(!is_string($insertField))
			return;	
			
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;	
		
		if(!isset($this->insertValueField[$insertField]))
			$this->insertValueField[$insertField] = array();
		
		if($insertValueInteger === NULL || $insertValueInteger == 0)
			$this->insertValueField[$insertField][] = array('db' => $databaseName, 'value' => 'NOW()');
		else
			$this->insertValueField[$insertField][] = array('db' => $databaseName, 'value' => 'DATE_ADD(NOW(), INTERVAL '.(int)$insertValueInteger.' '.$dateUnity.')');
	}
	
	public function setUpdatedFieldValueString($updatedField, $updatedValueString, $databaseName){
		if(!is_string($updatedField))
			return;	
			
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;	

		if($updatedValueString === NULL)
			$updatedValueString = 'NULL';
		else
			$updatedValueString = db::harmAndString($updatedValueString);

		$this->updateValueField[$updatedField] = array('db' => $databaseName, 'value' => $updatedValueString);
	}
	
	public function setInsertFieldValueString($insertField, $insertValueString, $databaseName){
		if(!is_string($insertField))
			return;	
			
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;	
		if($insertValueString === NULL)
			$insertValueString = 'NULL';
		else
			$insertValueString = db::harmAndString($insertValueString);
		
		if(!isset($this->insertValueField[$insertField]))
			$this->insertValueField[$insertField] = array();
		
		$this->insertValueField[$insertField][] = array('db' => $databaseName, 'value' => $insertValueString);
	}
	
	public function setInsertFieldValueCounterAutoIncrement($insertField, $databaseName){
		if(!is_string($insertField))
			return;	
			
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;	

		if(!isset($this->insertValueField[$insertField]))
			$this->insertValueField[$insertField] = array();
		
		$this->insertValueField[$insertField][] = array('db' => $databaseName, 'value' => '(SELECT AUTO_INCREMENT
FROM information_schema.tables
WHERE table_name = \''.$databaseName.'\'
AND table_schema = DATABASE( ) )', 'counterAI' => TRUE);
	}
	public function setInsertFieldValueCounterAutoIncrementBase36($insertField, $databaseName){
		if(!is_string($insertField))
			return;	
			
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;	

		if(!isset($this->insertValueField[$insertField]))
			$this->insertValueField[$insertField] = array();
		
		$this->insertValueField[$insertField][] = array('db' => $databaseName, 'value' => '(SELECT LOWER(CONV(AUTO_INCREMENT,10,36))
FROM information_schema.tables
WHERE table_name = \''.$databaseName.'\'
AND table_schema = DATABASE( ) )', 'counterAI' => TRUE);
	}
	
	public function setUpdatedFieldValueInteger($updatedField, $updatedValueInteger, $databaseName){
		if(!is_string($updatedField))
			return;	
			
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;	
		if($updatedValueInteger === NULL)
			$updatedValueInteger = 'NULL';
		else
			$updatedValueInteger = (int)$updatedValueInteger;
		
		$this->updateValueField[$updatedField] = array('db' => $databaseName, 'value' => $updatedValueInteger);
	}
	public function setUpdatedFieldValueIntegerPlus($updatedField, $updatedValueInteger, $databaseName){
		if(!is_string($updatedField))
			return;	
			
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;	
		if($updatedValueInteger === NULL)
			$updatedValueInteger = 'NULL';
		else
			$updatedValueInteger = (int)$updatedValueInteger;
        
		$this->updateValueField[$updatedField] = array('db' => $databaseName, 'value' => '`'.$updatedField.'` + '.$updatedValueInteger);
	}
	public function setInsertFieldValueFloat($insertField, $insertValueInteger, $databaseName){
		if(!is_string($insertField))
			return;	
			
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;	
		if($insertValueInteger === NULL)
			$insertValueInteger = 'NULL';
		else
			$insertValueInteger = (float)$insertValueInteger;
		
		if(!isset($this->insertValueField[$insertField]))
			$this->insertValueField[$insertField] = array();
		
		$this->insertValueField[$insertField][] = array('db' => $databaseName, 'value' => $insertValueInteger);
	}
	public function setInsertFieldValueInteger($insertField, $insertValueInteger, $databaseName){
		if(!is_string($insertField))
			return;	
			
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;	
		if($insertValueInteger === NULL)
			$insertValueInteger = 'NULL';
		else
			$insertValueInteger = (int)$insertValueInteger;
		
		if(!isset($this->insertValueField[$insertField]))
			$this->insertValueField[$insertField] = array();
		
		$this->insertValueField[$insertField][] = array('db' => $databaseName, 'value' => $insertValueInteger);
	}
	
	public function setUpdatedFieldValueAnotherColumn($updatedField_1, $databaseName_1, $updatedField_2, $databaseName_2){
		if(!is_string($updatedField_1) || !is_string($updatedField_2))
			return;	
			
		if(!is_string($databaseName_1) || !isset($this->databases[$databaseName_1]))
			return;
		
		if(!is_string($databaseName_2) || !isset($this->databases[$databaseName_2]))
			return;	

		$this->updateValueField[$updatedField_1] = array('db' => $databaseName_1, 'value' => 't'.$this->databases[$databaseName_2].'`'.$updatedField_2.'`');
	}
	
	public function setDatabase($databaseName){
		if(!is_string($databaseName))
			return;
			
		#if(is_int($fieldNumber) || $fieldNumber < 0)
		#	$fieldNumber = 0;
		
		$this->databases[$databaseName] = (int)(count($this->databases) + 1);
	}
	
	public function setSELECTCount($fieldName, $databaseName, $alias = NULL){
		if(!is_string($fieldName))
			return;	
			
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;	
		
		if(!is_string($alias))
			$alias = NULL;
		
		$this->selectField[$databaseName][$fieldName] = array('db' => $databaseName, 'alias' => $alias, 'isCount' => TRUE);
	}
	
	public function setSELECTField($fieldName, $databaseName, $alias = NULL){
		if(!is_string($fieldName))
			return;	
			
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;	
		
		if(!is_string($alias))
			$alias = NULL;
		
		$this->selectField[$databaseName][$fieldName] = array('db' => $databaseName, 'alias' => $alias);
	}
	
	public function setConditionDateTimeLower($conditionField, $conditionValueDate, $databaseName, $andOrString = NULL, $group = NULL){
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;
		
		$this->conditionField[$conditionField][] = array('db' => $databaseName, 'value' => 'FROM_UNIXTIME('.(int)($conditionValueDate).')', 'aOr' => $this->getAndOrString($andOrString), 'type' => '<', 'group' => $group);
		
		$this->setGroupOfCondition($group);
	}
	public function setConditionDateTimeHigher($conditionField, $conditionValueDate, $databaseName, $andOrString = NULL, $group = NULL){
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;
		
		$this->conditionField[$conditionField][] = array('db' => $databaseName, 'value' => 'FROM_UNIXTIME('.(int)($conditionValueDate).')', 'aOr' => $this->getAndOrString($andOrString), 'type' => '>', 'group' => $group);
		
		$this->setGroupOfCondition($group);
	}
	public function setConditionStringEqual($conditionField, $conditionValueString, $databaseName, $andOrString = NULL, $group = NULL){
		if(!is_string($conditionField))
			return;
		
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;

		$this->conditionField[$conditionField][] = array('db' => $databaseName, 'value' => db::harmAndString($conditionValueString), 'aOr' => $this->getAndOrString($andOrString), 'type' => '=', 'group' => $group);
		
		$this->setGroupOfCondition($group);
	}
    public function setConditionIntegerLowerEqual(string $conditionField, $conditionValueInteger, string $databaseName, $andOrString = NULL, $group = NULL){
        if(!is_string($conditionField))
            return;

        if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
            return;

        $this->conditionField[$conditionField][] = array('db' => $databaseName, 'value' => (int)($conditionValueInteger), 'aOr' => $this->getAndOrString($andOrString), 'type' => '<=', 'group' => $group);

        $this->setGroupOfCondition($group);
    }
	public function setConditionIntegerLargerEqual(string $conditionField, $conditionValueInteger, string $databaseName, $andOrString = NULL, $group = NULL){
        if(!is_string($conditionField))
            return;

        if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
            return;

        $this->conditionField[$conditionField][] = array('db' => $databaseName, 'value' => (int)($conditionValueInteger), 'aOr' => $this->getAndOrString($andOrString), 'type' => '>=', 'group' => $group);

        $this->setGroupOfCondition($group);
    }
	public function setConditionIntegerEqual($conditionField, $conditionValueInteger, $databaseName, $andOrString = NULL, $group = NULL){
		if(!is_string($conditionField))
			return;
		
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;

		$this->conditionField[$conditionField][] = array('db' => $databaseName, 'value' => (int)($conditionValueInteger), 'aOr' => $this->getAndOrString($andOrString), 'type' => '=', 'group' => $group);
		
		$this->setGroupOfCondition($group);
	}
	
	public function setConditionFloatEqual($conditionField, $conditionValueFloat, $databaseName, $andOrString = NULL, $group = NULL){
		if(!is_string($conditionField))
			return;
		
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;

		$this->conditionField[$conditionField][] = array('db' => $databaseName, 'value' => (float)($conditionValueFloat), 'aOr' => $this->getAndOrString($andOrString), 'type' => '=', 'group' => $group);
		
		$this->setGroupOfCondition($group);
	}
	
	public function setConditionArrayIN($conditionField, $conditionValueArray, $databaseName, $andOrString = NULL, $group = NULL){
		if(!is_string($conditionField) || !is_array($conditionValueArray))
			return;
		
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;
		
		$conditionValueString = '';
		foreach($conditionValueArray as $val){
			$conditionValueString .= db::harmAndString($val).',';
		}
		$conditionValueString = '('.substr($conditionValueString, 0, -1).') ';
		
		$this->conditionField[$conditionField][] = array('db' => $databaseName, 'value' => $conditionValueString, 'aOr' => $this->getAndOrString($andOrString), 'type' => 'IN', 'group' => $group);
		
		$this->setGroupOfCondition($group);
	}
	
	private function setGroupOfCondition($group){
		if($group !== NULL){
			if(!isset($this->group[$group])){
				$this->group[$group] = array();
				$this->group[$group]['start'] = 1;
			} 
			$this->group[$group][] = 1;
		}
	}
	
	public function setGROUPBYandHAVING_COUNT_DISTINCT($conditionField_1, $databaseName_1, $conditionField_2, $databaseName_2, $countObj){
		if(!is_string($conditionField_1) || !is_string($conditionField_2))
			return;
		
		if(!is_string($databaseName_1) || !isset($this->databases[$databaseName_1]))
			return;
		
		if(!is_string($databaseName_2) || !isset($this->databases[$databaseName_2]))
			return;
		
		if(!is_array($countObj))
			return;
		
		$this->groupByString = array('type' => 'COUNTDISTINCT', 'field' => $conditionField_1, 'db' => $databaseName_1, 'value' => 't'.$this->databases[$databaseName_2].'.`'.$conditionField_2.'`', 'count' => count($countObj));
	
	}
	public function setGROUPBYandHAVING_COUNT($conditionField_1, $databaseName_1, $conditionField_2, $databaseName_2, $countObj){
		if(!is_string($conditionField_1) || !is_string($conditionField_2))
			return;
		
		if(!is_string($databaseName_1) || !isset($this->databases[$databaseName_1]))
			return;
		
		if(!is_string($databaseName_2) || !isset($this->databases[$databaseName_2]))
			return;
		
		if(!is_array($countObj))
			return;
		
		$this->groupByString = array('type' => 'COUNT', 'field' => $conditionField_1, 'db' => $databaseName_1, 'value' => 't'.$this->databases[$databaseName_2].'.`'.$conditionField_2.'`', 'count' => count($countObj));
	
	}
    public function setGROUPBY($conditionField_1, $databaseName_1){
        if(!is_string($conditionField_1))
            return;
        if(!is_string($databaseName_1))
            return;
        
        $this->groupByString = array('type' => 'GROUP BY', 'field' => $conditionField_1, 'db' => $databaseName_1, 'value' => 't'.$this->databases[$databaseName_1].'.`'.$conditionField_1.'`');
    
    }
	public function setConditionIsNULL ($conditionField, $databaseName, $andOrString = NULL, $group = NULL){
		if(!is_string($conditionField))
			return;
		
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;

		$this->conditionField[$conditionField][] = array('db' => $databaseName, 'value' => 'NULL', 'aOr' => $this->getAndOrString($andOrString), 'type' => 'IS', 'group' => $group);
	
		$this->setGroupOfCondition($group);
	}
	public function setConditionNotNULL($conditionField, $databaseName, $andOrString = NULL, $group = NULL){
		if(!is_string($conditionField))
			return;
		
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;

		$this->conditionField[$conditionField][] = array('db' => $databaseName, 'value' => 'NOT NULL', 'aOr' => $this->getAndOrString($andOrString), 'type' => 'IS', 'group' => $group);
	
		$this->setGroupOfCondition($group);
	}
	public function setConditionBooleanEqual($conditionField, $conditionValueBool, $databaseName, $andOrString = NULL, $group = NULL){
		if(!is_string($conditionField))
			return;
		
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;

		$bool = (is_bool($conditionValueBool)) ? (int)$conditionValueBool : FALSE;
		
		$this->conditionField[$conditionField][] = array('db' => $databaseName, 'value' => $bool, 'aOr' => $this->getAndOrString($andOrString), 'type' => '=', 'group' => $group);
		
		$this->setGroupOfCondition($group);
	}
	
	public function setConditionAnotherColumnEqual($conditionField_1, $databaseName_1, $conditionField_2, $databaseName_2, $andOrString = NULL, $group = NULL){
		if(!is_string($conditionField_1) || !is_string($conditionField_2))
			return;
		
		if(!is_string($databaseName_1) || !isset($this->databases[$databaseName_1]))
			return;
		
		if(!is_string($databaseName_2) || !isset($this->databases[$databaseName_2]))
			return;
		
		$this->conditionField[$conditionField_1][] = array('db' => $databaseName_1, 'value' => 't'.$this->databases[$databaseName_2].'.`'.$conditionField_2.'`', 'aOr' => $this->getAndOrString($andOrString), 'type' => '=', 'group' => $group);
		
		$this->setGroupOfCondition($group);
	}
	
	public function setOrderByField($orderByFieldName, $databaseName, $orderASC = true){
		if(!is_string($orderByFieldName))
			return;
		
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;

		$bool = (is_bool($orderASC)) ? $orderASC : TRUE;

		if($bool)
			$bool = 'ASC';
		else
			$bool = 'DESC';

		$this->orderByField[$orderByFieldName] = array('db' => $databaseName, 'field' => $bool, 'dir' => $bool);
	}
	
	private function getAndOrString($andOrField){
		$andOrField = strtoupper($andOrField);
		
		if($andOrField == 'AND' || $andOrField == 'OR')
			return $andOrField;
		else
			return '';
	}
	
	public function setDBonLeftJoinEqualToColumn($conditionField_1, $databaseName_1, $conditionField_2, $databaseName_2){
		if(!isset($this->joinLefts[$databaseName_1]))	
			$this->joinLefts[$databaseName_1] = array();
		
		$this->joinLefts[$databaseName_1] = array(
												'field' => $conditionField_1,
												'on' => 't'.$this->databases[$databaseName_2].'.`'.$conditionField_2.'`'
											);
	}
	
	public function getQueryString(){
		switch ($this->type){
			case enumDBQueryType::PRESET :
				if(!empty($this->presetString))
					return $this->presetString;
				else
					return FALSE;
				break;	
			case enumDBQueryType::SELECT :
				return $this->getQueryStringTypeSELECT();
				break;
			case enumDBQueryType::UPDATE :
				return $this->getQueryStringTypeUPDATE();
				break;
			case enumDBQueryType::INSERT :
				return $this->getQueryStringTypeINSERT();
				break;
			case enumDBQueryType::DELETE :
				return $this->getQueryStringTypeDELETE();
				break;
			default:
				$this->errors[] = "wrong querz type";
				return FALSE;
		}
	}
	
	private function getQueryStringTypeDELETE(){
		if(empty($this->databases) || empty($this->conditionField))
			return FALSE;
		
		$sql = 'DELETE FROM ';
		
		$first_value = reset($this->databases); // First Element's Value
		$first_key = key($this->databases);
		
		$sql .= '`'.$first_key.'` WHERE ';
		
		foreach($this->conditionField as $key => $valX){
			foreach($valX as $val)	
				$sql .= $val['aOr'].' `'.$key.'` '.$val['type'].' '.$val['value'].' ';
		}

		return $sql.';';
	}
	
	private function getQueryStringTypeINSERT(){
		if(empty($this->databases) || empty($this->insertValueField)){
			$this->errors[] = "database / value fields not set";	
			return FALSE;
		}
		
		$sql = 'INSERT INTO ';
		
		$first_value = reset($this->databases); // First Element's Value
		$first_key = key($this->databases);
		
		$sql .= '`'.$first_key.'` (';
		$values = '';
		
		// check data integrity
		
		$c = NULL;
		foreach($this->insertValueField as $val){
			if($c === NULL)
				$c = count($val);
			if($c != count($val)){
				print_r($this->insertValueField);	
				$this->errors[] = "count does not match ".count($val)." -> ".$val[0];	
				
				return FALSE;
			}
			$c = count($val);
		}
		
		$valBox = array();
		
		for($i = 0; $i < $c; $i++){
			$values = '';	
			foreach($this->insertValueField as $key => $val){
				if($val[$i]['db'] == $first_key){
					if($i == 0)		
						$sql .= '`'.$key.'`, ';
					
					$values .= $val[$i]['value'].', ';
				}
			}
			if(strlen($values) < 2){
				$this->errors[] = "values < 2 ?";	
				return FALSE;
			}

			$valBox[] = substr($values, 0, -2);
		}
		
		if(empty($valBox)){
			$this->errors[] = "valBox empty";	
			return FALSE;
		}
		
		$sql = substr($sql, 0, -2).') VALUES ';
		
		foreach($valBox as $val){
			$sql .= '('.$val.'), ';
		}
		
		$sql = substr($sql, 0, -2);
		
		if(empty($this->updateDuplicateValueField))
			return $sql.';';
		
		$sql .= ' ON DUPLICATE KEY UPDATE ';
		
		foreach($this->updateDuplicateValueField as $key => $val){	
			$sql .= '`'.$key.'` = '.$val['value'].', ';
		}
		
		return substr($sql, 0, -2).';';
	}
	
	private function getQueryStringTypeUPDATE(){
		if(empty($this->databases) || empty($this->conditionField) || empty($this->updateValueField))
			return FALSE;
			
		$sql = 'UPDATE ';
		
		foreach($this->databases as $key => $val){
			$sql .= '`'.$key.'` AS t'.$val.', ';
		}
		
		$sql = substr($sql, 0, -2);
		
		$sql .= ' SET ';
		
		foreach($this->updateValueField as $key => $val){
			$sql .= 't'.$this->databases[$val['db']].'.`'.$key.'` = '.$val['value'].', ';
		}
		
		$sql = substr($sql, 0, -2);
		
		$sql .= ' WHERE ';
		
		foreach($this->conditionField as $key => $valX){
			foreach($valX as $val)	
				$sql .= $val['aOr'].' t'.$this->databases[$val['db']].'.`'.$key.'` '.$val['type'].' '.$val['value'].' ';
		}
		
		$sql = substr($sql, 0, -1).';';
		
		return $sql;
	}
	private $isSelectAll = FALSE;
	public function setSelectFieldALL(){
		$this->isSelectAll = TRUE;
	}
	private function getQueryStringTypeSELECT(){
		if(empty($this->databases))
			return FALSE;
			
		$sql = 'SELECT ';

		if($this->isSelectAll){
			$sql .= '*, ';
		} else {
			foreach($this->selectField as $dbName => $valField){
				foreach($valField as $fieldName => $val){
					if(isset($val['isCount']))
						$sql.= 'COUNT(';	
					$sql .= 't'.$this->databases[$val['db']].'.`'.$fieldName.'`';
					if(isset($val['isCount']))
						$sql.= ')';
					
					if($val['alias'] === NULL)
						$sql .= ', ';
					else
						$sql .= ' AS '.$val['alias'].', ';
				}
			}
		}
		$sql = substr($sql,0, -2).' ';
		$sql .= 'FROM ';
		
		foreach($this->databases as $key => $val){
			if(!isset($this->joinLefts[$key]))	
				$sql .= '`'.$key.'` AS t'.$val.', ';
		}
		
		$sql = substr($sql, 0, -2);

		if(!empty($this->joinLefts)){
			foreach($this->joinLefts as $key => $val){
				//print_r($val);	
				$sql .= ' LEFT JOIN `'.$key.'` AS t'.$this->databases[$key].'';
				$sql .= ' ON t'.$this->databases[$key].'.`'.$val['field'].'` = '.$val['on'];
			}
		}

		if(!empty($this->conditionField)){
			$forRepeat = $this->group;			
			$sql .= ' WHERE ';
			$u = 0;
			foreach($this->conditionField as $key => $valX){
				foreach($valX as $val){
					$ss = '';
					$es = '';
					if( isset($val['group']) ){
						if($this->group[ $val['group'] ]['start'] == 1){
							$ss .= '('; // echo "ss..";
							$this->group[ $val['group'] ]['start'] = 0; 
							end($this->group[ $val['group'] ]);
							$Fkey = key($this->group[ $val['group'] ]);
							unset($this->group[ $val['group'] ][$Fkey]);
							
							if(count($this->group[ $val['group'] ]) < 2)
								$es .= ') ';
						} else if(count($this->group[ $val['group'] ]) > 1){
							end($this->group[ $val['group'] ]);
							$Fkey = key($this->group[ $val['group'] ]);
							
							if(count($this->group[ $val['group'] ]) == 2)
								$es .= ') ';
							
							unset($this->group[ $val['group'] ][$Fkey]);
						}
					}
					if($u > 0)
						$sql.= $val['aOr'];
					$u++;
					$sql .= ' '.$ss.'t'.$this->databases[$val['db']].'.`'.$key.'` '.$val['type'].' '.$val['value'].$es.' ';
				}
			}	
			$sql = substr($sql, 0, -1);
			$this->group = $forRepeat;	
		}
		
		if(!empty($this->groupByString)){
			switch($this->groupByString['type']){
				case 'COUNTDISTINCT':
					$sql .= ' GROUP BY t'.$this->databases[$this->groupByString['db']].'.`'.$this->groupByString['field'].'` HAVING COUNT(DISTINCT '.$this->groupByString['value'].') = '.$this->groupByString['count'];
					break;
				case 'COUNT':
					$sql .= ' GROUP BY t'.$this->databases[$this->groupByString['db']].'.`'.$this->groupByString['field'].'` HAVING COUNT('.$this->groupByString['value'].') = '.$this->groupByString['count'];
					break;
                case 'GROUP BY':
                    $sql .= ' GROUP BY t'.$this->databases[$this->groupByString['db']].'.`'.$this->groupByString['field'].'`';
                    break;
				default:
					break;
			}
		}
		
		if(!empty($this->orderByField)){
			$sql .= ' ORDER BY ';
			
			foreach($this->orderByField as $key => $val){
				$sql .= 't'.$this->databases[$val['db']].'.`'.$key.'` '.$val['dir'].', ';
			}
			
			$sql = substr($sql, 0, -2);
		}

		if($this->limit === NULL)
			return $sql.=';';
		
		return $sql.= ' LIMIT '.$this->limit.';';
	}
}
	 
class dbObj2{
	private $type = enumDBQueryType::SELECT;
	
	private $databases = array();
	private $selectField = array();
	private $conditionField = array();
	private $orderByField = array();
	
	private $updateValueField = array();
	private $insertValueField = array();
	private $onDuplicateField = array();
	
	private $updateDuplicateValueField = array();
	
	private $limit = NULL;
	
	private $presetString = NULL;
	
	private $errors = array();
	
	private $joinLefts = array();
	private $joinRights = array();
	
	private $groupByString = array();
	private $group = array();
	
	public function getErrors(){
		return $this->errors;
	}
	
	public function presetShowTableStatus($tableName, $databaseName){
		$this->type = enumDBQueryType::PRESET;
		$this->presetString = 'SHOW TABLE STATUS 
			FROM 
				`'.$databaseName.'` 
			LIKE '.db2::harmAndString($tableName).';';
	}
	
	public function presetGetNextAutoIncrementValueOfTable($name){
		$this->type = enumDBQueryType::PRESET;
		$this->presetString = 'SELECT  `auto_increment` AS nextID 
		FROM INFORMATION_SCHEMA.TABLES
		WHERE table_name = '.db2::harmAndString($name).'
		LIMIT 1';
	}
	
	public function setTypeSELECT(){
		$this->type = enumDBQueryType::SELECT;
	}
	public function setTypeINSERT(){
		$this->type = enumDBQueryType::INSERT;
	}
	public function setTypeDELETE(){
		$this->type = enumDBQueryType::DELETE;
	}
	public function setTypeUPDATE(){
		$this->type = enumDBQueryType::UPDATE;
	}
	
	public function setLimit($int = NULL, $offset = 0){
		$i = (int)$int;
		
		if($int < 1)
			$this->limit = NULL;
		else
			$this->limit = $offset.','.$int;
	}
	
	public function setOnDuplicateFieldValueNULL($updatedField){
		if(!is_string($updatedField))
			return;	

		$this->updateDuplicateValueField[$updatedField] = array('value' => 'NULL');
	}
	
	public function setOnDuplicateFieldValueNOW($updatedField){
		if(!is_string($updatedField))
			return;	

		$this->updateDuplicateValueField[$updatedField] = array('value' => 'NOW()');
	}
	
	public function setOnDuplicateFieldValueDATE_ADD_TO_NOW($updatedField, $updatedValueInteger, $dateUnity = 'SECOND'){
		if(!is_string($updatedField))
			return;	

		$this->updateDuplicateValueField[$updatedField] = array('value' => 'DATE_ADD(NOW(), INTERVAL '.(int)$updatedValueInteger.' '.$dateUnity.')');
	}
	
	public function setOnDuplicateFieldValueString($updatedField, $updatedValueString){
		if(!is_string($updatedField))
			return;	

		if($updatedValueString === NULL)
			$updatedValueString = 'NULL';
		else
			$updatedValueString = db::harmAndString($updatedValueString);

		$this->updateDuplicateValueField[$updatedField] = array('value' => $updatedValueString);
	}
	
	public function setOnDuplicateFieldValueInteger($updatedField, $updatedValueInteger){
		if(!is_string($updatedField))
			return;	

		if($updatedValueInteger === NULL)
			$updatedValueInteger = 'NULL';
		else
			$updatedValueInteger = (int)($updatedValueInteger);

		$this->updateDuplicateValueField[$updatedField] = array('value' => $updatedValueInteger);
	}
	
	public function setOnDuplicateFieldValueAddIntegerToColumn($updatedField, $updatedValueInteger, $updateColumn){
		if(!is_string($updatedField))
			return;	

		if($updatedValueInteger === NULL)
			$updatedValueInteger = 'NULL';
		else
			$updatedValueInteger = (int)($updatedValueInteger);

		$updateColumn = str_replace('`','',$updateColumn);

		$this->updateDuplicateValueField[$updatedField] = array('value' => '`'.$updateColumn.'` + '.$updatedValueInteger);
	}
	
	public function setOnDuplicateFieldAnotherColumn($updatedField_1, $updatedField_2, $databaseName_2){
		if(!is_string($updatedField_1) || !is_string($updatedField_2))
			return;	

		if(!is_string($databaseName_2))
			return;	

		$this->updateDuplicateValueField[$updatedField_1] = array('value' => $databaseName_2.'`'.$updatedField_2.'`');
	}
	
	public function setUpdatedFieldValueNULL($updatedField, $databaseName){
		if(!is_string($updatedField))
			return;	
			
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;	

		$this->updateValueField[$updatedField] = array('db' => $databaseName, 'value' => 'NULL');
	}
	
	public function setInsertFieldValueNULL($insertField, $databaseName){
		if(!is_string($insertField))
			return;	
			
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;	

		if(!isset($this->insertValueField[$insertField]))
			$this->insertValueField[$insertField] = array();

		$this->insertValueField[$insertField][] = array('db' => $databaseName, 'value' => 'NULL');
	}
	
	public function setUpdatedFieldValueNOW($updatedField, $databaseName){
		if(!is_string($updatedField))
			return;	
			
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;	

		$this->updateValueField[$updatedField] = array('db' => $databaseName, 'value' => 'NOW()');
	}
	
	public function setInsertFieldValueNOW($insertField, $databaseName){
		if(!is_string($insertField))
			return;	
			
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;	

		if(!isset($this->insertValueField[$insertField]))
			$this->insertValueField[$insertField] = array();

		$this->insertValueField[$insertField][] = array('db' => $databaseName, 'value' => 'NOW()');
	}
	
	public function setUpdatedFieldValueDATE_ADD_TO_NOW($updatedField, $updatedValueInteger, $databaseName, $dateUnity = 'SECOND'){
		if(!is_string($updatedField))
			return;	
			
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;	

		if($updatedValueInteger === NULL || $updatedValueInteger == 0)
			$this->updateValueField[$updatedField] = array('db' => $databaseName, 'value' => 'NOW()');
		else
			$this->updateValueField[$updatedField] = array('db' => $databaseName, 'value' => 'DATE_ADD(NOW(), INTERVAL '.(int)$updatedValueInteger.' '.$dateUnity.')');
	}
	
	public function setInsertFieldValueDATE_ADD_TO_NOW($insertField, $insertValueInteger, $databaseName, $dateUnity = 'SECOND'){
		if(!is_string($insertField))
			return;	
			
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;	
		
		if(!isset($this->insertValueField[$insertField]))
			$this->insertValueField[$insertField] = array();
		
		if($insertValueInteger === NULL || $insertValueInteger == 0)
			$this->insertValueField[$insertField][] = array('db' => $databaseName, 'value' => 'NOW()');
		else
			$this->insertValueField[$insertField][] = array('db' => $databaseName, 'value' => 'DATE_ADD(NOW(), INTERVAL '.(int)$insertValueInteger.' '.$dateUnity.')');
	}
	
	public function setUpdatedFieldValueString($updatedField, $updatedValueString, $databaseName){
		if(!is_string($updatedField))
			return;	
			
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;	

		if($updatedValueString === NULL)
			$updatedValueString = 'NULL';
		else
			$updatedValueString = db2::harmAndString($updatedValueString);

		$this->updateValueField[$updatedField] = array('db' => $databaseName, 'value' => $updatedValueString);
	}
	
	public function setInsertFieldValueString($insertField, $insertValueString, $databaseName){
		if(!is_string($insertField))
			return;	
			
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;	
		if($insertValueString === NULL)
			$insertValueString = 'NULL';
		else
			$insertValueString = db2::harmAndString($insertValueString);
		
		if(!isset($this->insertValueField[$insertField]))
			$this->insertValueField[$insertField] = array();
		
		$this->insertValueField[$insertField][] = array('db' => $databaseName, 'value' => $insertValueString);
	}
	
	public function setInsertFieldValueCounterAutoIncrement($insertField, $databaseName){
		if(!is_string($insertField))
			return;	
			
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;	

		if(!isset($this->insertValueField[$insertField]))
			$this->insertValueField[$insertField] = array();
		
		$this->insertValueField[$insertField][] = array('db' => $databaseName, 'value' => '(SELECT AUTO_INCREMENT
FROM information_schema.tables
WHERE table_name = \''.$databaseName.'\'
AND table_schema = DATABASE( ) )', 'counterAI' => TRUE);
	}
	public function setInsertFieldValueCounterAutoIncrementBase36($insertField, $databaseName){
		if(!is_string($insertField))
			return;	
			
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;	

		if(!isset($this->insertValueField[$insertField]))
			$this->insertValueField[$insertField] = array();
		
		$this->insertValueField[$insertField][] = array('db' => $databaseName, 'value' => '(SELECT LOWER(CONV(AUTO_INCREMENT,10,36))
FROM information_schema.tables
WHERE table_name = \''.$databaseName.'\'
AND table_schema = DATABASE( ) )', 'counterAI' => TRUE);
	}
	
	public function setUpdatedFieldValueInteger($updatedField, $updatedValueInteger, $databaseName){
		if(!is_string($updatedField))
			return;	
			
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;	
		if($updatedValueInteger === NULL)
			$updatedValueInteger = 'NULL';
		else
			$updatedValueInteger = (int)$updatedValueInteger;
		
		$this->updateValueField[$updatedField] = array('db' => $databaseName, 'value' => $updatedValueInteger);
	}
	public function setInsertFieldValueFloat($insertField, $insertValueInteger, $databaseName){
		if(!is_string($insertField))
			return;	
			
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;	
		if($insertValueInteger === NULL)
			$insertValueInteger = 'NULL';
		else
			$insertValueInteger = (float)$insertValueInteger;
		
		if(!isset($this->insertValueField[$insertField]))
			$this->insertValueField[$insertField] = array();
		
		$this->insertValueField[$insertField][] = array('db' => $databaseName, 'value' => $insertValueInteger);
	}
	public function setInsertFieldValueInteger($insertField, $insertValueInteger, $databaseName){
		if(!is_string($insertField))
			return;	
			
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;	
		if($insertValueInteger === NULL)
			$insertValueInteger = 'NULL';
		else
			$insertValueInteger = (int)$insertValueInteger;
		
		if(!isset($this->insertValueField[$insertField]))
			$this->insertValueField[$insertField] = array();
		
		$this->insertValueField[$insertField][] = array('db' => $databaseName, 'value' => $insertValueInteger);
	}
	
	public function setUpdatedFieldValueAnotherColumn($updatedField_1, $databaseName_1, $updatedField_2, $databaseName_2){
		if(!is_string($updatedField_1) || !is_string($updatedField_2))
			return;	
			
		if(!is_string($databaseName_1) || !isset($this->databases[$databaseName_1]))
			return;
		
		if(!is_string($databaseName_2) || !isset($this->databases[$databaseName_2]))
			return;	

		$this->updateValueField[$updatedField_1] = array('db' => $databaseName_1, 'value' => 't'.$this->databases[$databaseName_2].'`'.$updatedField_2.'`');
	}
	
	public function setDatabase($databaseName){
		if(!is_string($databaseName))
			return;
			
		#if(is_int($fieldNumber) || $fieldNumber < 0)
		#	$fieldNumber = 0;
		
		$this->databases[$databaseName] = (int)(count($this->databases) + 1);
	}
	
	public function setSELECTCount($fieldName, $databaseName, $alias = NULL){
		if(!is_string($fieldName))
			return;	
			
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;	
		
		if(!is_string($alias))
			$alias = NULL;
		
		$this->selectField[$databaseName][$fieldName] = array('db' => $databaseName, 'alias' => $alias, 'isCount' => TRUE);
	}
	
	public function setSELECTField($fieldName, $databaseName, $alias = NULL){
		if(!is_string($fieldName))
			return;	
			
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;	
		
		if(!is_string($alias))
			$alias = NULL;
		
		$this->selectField[$databaseName][$fieldName] = array('db' => $databaseName, 'alias' => $alias);
	}
	
	public function setConditionDateTimeLower($conditionField, $conditionValueDate, $databaseName, $andOrString = NULL, $group = NULL){
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;
		
		$this->conditionField[$conditionField][] = array('db' => $databaseName, 'value' => 'FROM_UNIXTIME('.(int)($conditionValueDate).')', 'aOr' => $this->getAndOrString($andOrString), 'type' => '<', 'group' => $group);
		
		$this->setGroupOfCondition($group);
	}
	public function setConditionDateTimeHigher($conditionField, $conditionValueDate, $databaseName, $andOrString = NULL, $group = NULL){
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;
		
		$this->conditionField[$conditionField][] = array('db' => $databaseName, 'value' => 'FROM_UNIXTIME('.(int)($conditionValueDate).')', 'aOr' => $this->getAndOrString($andOrString), 'type' => '>', 'group' => $group);
		
		$this->setGroupOfCondition($group);
	}
	public function setConditionStringEqual($conditionField, $conditionValueString, $databaseName, $andOrString = NULL, $group = NULL){
		if(!is_string($conditionField))
			return;
		
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;

		$this->conditionField[$conditionField][] = array('db' => $databaseName, 'value' => db::harmAndString($conditionValueString), 'aOr' => $this->getAndOrString($andOrString), 'type' => '=', 'group' => $group);
		
		$this->setGroupOfCondition($group);
	}
	
	public function setConditionIntegerEqual($conditionField, $conditionValueInteger, $databaseName, $andOrString = NULL, $group = NULL){
		if(!is_string($conditionField))
			return;
		
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;

		$this->conditionField[$conditionField][] = array('db' => $databaseName, 'value' => (int)($conditionValueInteger), 'aOr' => $this->getAndOrString($andOrString), 'type' => '=', 'group' => $group);
		
		$this->setGroupOfCondition($group);
	}
	
	public function setConditionFloatEqual($conditionField, $conditionValueFloat, $databaseName, $andOrString = NULL, $group = NULL){
		if(!is_string($conditionField))
			return;
		
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;

		$this->conditionField[$conditionField][] = array('db' => $databaseName, 'value' => (float)($conditionValueFloat), 'aOr' => $this->getAndOrString($andOrString), 'type' => '=', 'group' => $group);
		
		$this->setGroupOfCondition($group);
	}
	
	public function setConditionArrayIN($conditionField, $conditionValueArray, $databaseName, $andOrString = NULL, $group = NULL){
		if(!is_string($conditionField) || !is_array($conditionValueArray))
			return;
		
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;
		
		$conditionValueString = '';
		foreach($conditionValueArray as $val){
			$conditionValueString .= db::harmAndString($val).',';
		}
		$conditionValueString = '('.substr($conditionValueString, 0, -1).') ';
		
		$this->conditionField[$conditionField][] = array('db' => $databaseName, 'value' => $conditionValueString, 'aOr' => $this->getAndOrString($andOrString), 'type' => 'IN', 'group' => $group);
		
		$this->setGroupOfCondition($group);
	}
	
	private function setGroupOfCondition($group){
		if($group !== NULL){
			if(!isset($this->group[$group])){
				$this->group[$group] = array();
				$this->group[$group]['start'] = 1;
			} 
			$this->group[$group][] = 1;
		}
	}
	
	public function setGROUPBYandHAVING_COUNT_DISTINCT($conditionField_1, $databaseName_1, $conditionField_2, $databaseName_2, $countObj){
		if(!is_string($conditionField_1) || !is_string($conditionField_2))
			return;
		
		if(!is_string($databaseName_1) || !isset($this->databases[$databaseName_1]))
			return;
		
		if(!is_string($databaseName_2) || !isset($this->databases[$databaseName_2]))
			return;
		
		if(!is_array($countObj))
			return;
		
		$this->groupByString = array('type' => 'COUNTDISTINCT', 'field' => $conditionField_1, 'db' => $databaseName_1, 'value' => 't'.$this->databases[$databaseName_2].'.`'.$conditionField_2.'`', 'count' => count($countObj));
	
	}
	public function setGROUPBYandHAVING_COUNT($conditionField_1, $databaseName_1, $conditionField_2, $databaseName_2, $countObj){
		if(!is_string($conditionField_1) || !is_string($conditionField_2))
			return;
		
		if(!is_string($databaseName_1) || !isset($this->databases[$databaseName_1]))
			return;
		
		if(!is_string($databaseName_2) || !isset($this->databases[$databaseName_2]))
			return;
		
		if(!is_array($countObj))
			return;
		
		$this->groupByString = array('type' => 'COUNT', 'field' => $conditionField_1, 'db' => $databaseName_1, 'value' => 't'.$this->databases[$databaseName_2].'.`'.$conditionField_2.'`', 'count' => count($countObj));
	
	}
	public function setConditionNotNULL($conditionField, $databaseName, $andOrString = NULL, $group = NULL){
		if(!is_string($conditionField))
			return;
		
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;

		$this->conditionField[$conditionField][] = array('db' => $databaseName, 'value' => 'NOT NULL', 'aOr' => $this->getAndOrString($andOrString), 'type' => 'IS', 'group' => $group);
	
		$this->setGroupOfCondition($group);
	}
	public function setConditionBooleanEqual($conditionField, $conditionValueBool, $databaseName, $andOrString = NULL, $group = NULL){
		if(!is_string($conditionField))
			return;
		
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;

		$bool = (is_bool($conditionValueBool)) ? (int)$conditionValueBool : FALSE;
		
		$this->conditionField[$conditionField][] = array('db' => $databaseName, 'value' => $bool, 'aOr' => $this->getAndOrString($andOrString), 'type' => '=', 'group' => $group);
		
		$this->setGroupOfCondition($group);
	}
	
	public function setConditionAnotherColumnEqual($conditionField_1, $databaseName_1, $conditionField_2, $databaseName_2, $andOrString = NULL, $group = NULL){
		if(!is_string($conditionField_1) || !is_string($conditionField_2))
			return;
		
		if(!is_string($databaseName_1) || !isset($this->databases[$databaseName_1]))
			return;
		
		if(!is_string($databaseName_2) || !isset($this->databases[$databaseName_2]))
			return;
		
		$this->conditionField[$conditionField_1][] = array('db' => $databaseName_1, 'value' => 't'.$this->databases[$databaseName_2].'.`'.$conditionField_2.'`', 'aOr' => $this->getAndOrString($andOrString), 'type' => '=', 'group' => $group);
		
		$this->setGroupOfCondition($group);
	}
	
	public function setOrderByField($orderByFieldName, $databaseName, $orderASC = true){
		if(!is_string($orderByFieldName))
			return;
		
		if(!is_string($databaseName) || !isset($this->databases[$databaseName]))
			return;

		$bool = (is_bool($orderASC)) ? $orderASC : TRUE;

		if($bool)
			$bool = 'ASC';
		else
			$bool = 'DESC';

		$this->orderByField[$orderByFieldName] = array('db' => $databaseName, 'field' => $bool, 'dir' => $bool);
	}
	
	private function getAndOrString($andOrField){
		$andOrField = strtoupper($andOrField);
		
		if($andOrField == 'AND' || $andOrField == 'OR')
			return $andOrField;
		else
			return '';
	}
	
	public function setDBonLeftJoinEqualToColumn($conditionField_1, $databaseName_1, $conditionField_2, $databaseName_2){
		if(!isset($this->joinLefts[$databaseName_1]))	
			$this->joinLefts[$databaseName_1] = array();
		
		$this->joinLefts[$databaseName_1] = array(
												'field' => $conditionField_1,
												'on' => 't'.$this->databases[$databaseName_2].'.`'.$conditionField_2.'`'
											);
	}
	
	public function getQueryString(){
		switch ($this->type){
			case enumDBQueryType::PRESET :
				if(!empty($this->presetString))
					return $this->presetString;
				else
					return FALSE;
				break;	
			case enumDBQueryType::SELECT :
				return $this->getQueryStringTypeSELECT();
				break;
			case enumDBQueryType::UPDATE :
				return $this->getQueryStringTypeUPDATE();
				break;
			case enumDBQueryType::INSERT :
				return $this->getQueryStringTypeINSERT();
				break;
			case enumDBQueryType::DELETE :
				return $this->getQueryStringTypeDELETE();
				break;
			default:
				$this->errors[] = "wrong querz type";
				return FALSE;
		}
	}
	
	private function getQueryStringTypeDELETE(){
		if(empty($this->databases) || empty($this->conditionField))
			return FALSE;
		
		$sql = 'DELETE FROM ';
		
		$first_value = reset($this->databases); // First Element's Value
		$first_key = key($this->databases);
		
		$sql .= '`'.$first_key.'` WHERE ';
		
		foreach($this->conditionField as $key => $valX){
			foreach($valX as $val)	
				$sql .= $val['aOr'].' `'.$key.'` '.$val['type'].' '.$val['value'].' ';
		}

		return $sql.';';
	}
	
	private function getQueryStringTypeINSERT(){
		if(empty($this->databases) || empty($this->insertValueField)){
			$this->errors[] = "database / value fields not set";	
			return FALSE;
		}
		
		$sql = 'INSERT INTO ';
		
		$first_value = reset($this->databases); // First Element's Value
		$first_key = key($this->databases);
		
		$sql .= '`'.$first_key.'` (';
		$values = '';
		
		// check data integrity
		
		$c = NULL;
		foreach($this->insertValueField as $val){
			if($c === NULL)
				$c = count($val);
			if($c != count($val)){
				print_r($this->insertValueField);	
				$this->errors[] = "count does not match ".count($val)." -> ".$val[0];	
				
				return FALSE;
			}
			$c = count($val);
		}
		
		$valBox = array();
		
		for($i = 0; $i < $c; $i++){
			$values = '';	
			foreach($this->insertValueField as $key => $val){
				if($val[$i]['db'] == $first_key){
					if($i == 0)		
						$sql .= '`'.$key.'`, ';
					
					$values .= $val[$i]['value'].', ';
				}
			}
			if(strlen($values) < 2){
				$this->errors[] = "values < 2 ?";	
				return FALSE;
			}

			$valBox[] = substr($values, 0, -2);
		}
		
		if(empty($valBox)){
			$this->errors[] = "valBox empty";	
			return FALSE;
		}
		
		$sql = substr($sql, 0, -2).') VALUES ';
		
		foreach($valBox as $val){
			$sql .= '('.$val.'), ';
		}
		
		$sql = substr($sql, 0, -2);
		
		if(empty($this->updateDuplicateValueField))
			return $sql.';';
		
		$sql .= ' ON DUPLICATE KEY UPDATE ';
		
		foreach($this->updateDuplicateValueField as $key => $val){	
			$sql .= '`'.$key.'` = '.$val['value'].', ';
		}
		
		return substr($sql, 0, -2).';';
	}
	
	private function getQueryStringTypeUPDATE(){
		if(empty($this->databases) || empty($this->conditionField) || empty($this->updateValueField))
			return FALSE;
			
		$sql = 'UPDATE ';
		
		foreach($this->databases as $key => $val){
			$sql .= '`'.$key.'` AS t'.$val.', ';
		}
		
		$sql = substr($sql, 0, -2);
		
		$sql .= ' SET ';
		
		foreach($this->updateValueField as $key => $val){
			$sql .= 't'.$this->databases[$val['db']].'.`'.$key.'` = '.$val['value'].', ';
		}
		
		$sql = substr($sql, 0, -2);
		
		$sql .= ' WHERE ';
		
		foreach($this->conditionField as $key => $valX){
			foreach($valX as $val)	
				$sql .= $val['aOr'].' t'.$this->databases[$val['db']].'.`'.$key.'` '.$val['type'].' '.$val['value'].' ';
		}
		
		$sql = substr($sql, 0, -1).';';
		
		return $sql;
	}
	
	private function getQueryStringTypeSELECT(){
		if(empty($this->databases))
			return FALSE;
			
		$sql = 'SELECT ';
		

		foreach($this->selectField as $dbName => $valField){
			foreach($valField as $fieldName => $val){
				if(isset($val['isCount']))
					$sql.= 'COUNT(';	
				$sql .= 't'.$this->databases[$val['db']].'.`'.$fieldName.'`';
				if(isset($val['isCount']))
					$sql.= ')';
				
				if($val['alias'] === NULL)
					$sql .= ', ';
				else
					$sql .= ' AS '.$val['alias'].', ';
			}
		} 
		$sql = substr($sql,0, -2).' ';
		$sql .= 'FROM ';
		
		foreach($this->databases as $key => $val){
			if(!isset($this->joinLefts[$key]))	
				$sql .= '`'.$key.'` AS t'.$val.', ';
		}
		
		$sql = substr($sql, 0, -2);

		if(!empty($this->joinLefts)){
			foreach($this->joinLefts as $key => $val){
				//print_r($val);	
				$sql .= ' LEFT JOIN `'.$key.'` AS t'.$this->databases[$key].'';
				$sql .= ' ON t'.$this->databases[$key].'.`'.$val['field'].'` = '.$val['on'];
			}
		}

		if(!empty($this->conditionField)){
			$forRepeat = $this->group;			
			$sql .= ' WHERE ';
			$u = 0;
			foreach($this->conditionField as $key => $valX){
				foreach($valX as $val){
					$ss = '';
					$es = '';
					if( isset($val['group']) ){
						if($this->group[ $val['group'] ]['start'] == 1){
							$ss .= '('; // echo "ss..";
							$this->group[ $val['group'] ]['start'] = 0; 
							end($this->group[ $val['group'] ]);
							$Fkey = key($this->group[ $val['group'] ]);
							unset($this->group[ $val['group'] ][$Fkey]);
							
							if(count($this->group[ $val['group'] ]) < 2)
								$es .= ') ';
						} else if(count($this->group[ $val['group'] ]) > 1){
							end($this->group[ $val['group'] ]);
							$Fkey = key($this->group[ $val['group'] ]);
							
							if(count($this->group[ $val['group'] ]) == 2)
								$es .= ') ';
							
							unset($this->group[ $val['group'] ][$Fkey]);
						}
					}
					if($u > 0)
						$sql.= $val['aOr'];
					$u++;
					$sql .= ' '.$ss.'t'.$this->databases[$val['db']].'.`'.$key.'` '.$val['type'].' '.$val['value'].$es.' ';
				}
			}	
			$sql = substr($sql, 0, -1);
			$this->group = $forRepeat;	
		}
		
		if(!empty($this->groupByString)){
			switch($this->groupByString['type']){
				case 'COUNTDISTINCT':
					$sql .= 'GROUP BY t'.$this->databases[$this->groupByString['db']].'.`'.$this->groupByString['field'].'` HAVING COUNT(DISTINCT '.$this->groupByString['value'].') = '.$this->groupByString['count'];
					break;
				case 'COUNT':
					$sql .= 'GROUP BY t'.$this->databases[$this->groupByString['db']].'.`'.$this->groupByString['field'].'` HAVING COUNT('.$this->groupByString['value'].') = '.$this->groupByString['count'];
					break;
				default:
					break;
			}
		}
		
		if(!empty($this->orderByField)){
			$sql .= ' ORDER BY ';
			
			foreach($this->orderByField as $key => $val){
				$sql .= 't'.$this->databases[$val['db']].'.`'.$key.'` '.$val['dir'].', ';
			}
			
			$sql = substr($sql, 0, -2);
		}

		if($this->limit === NULL)
			return $sql.=';';
		
		return $sql.= ' LIMIT '.$this->limit.';';
	}
}
	
	 	 
class dbClassMySQL {
	private $isInit = false;
	private $engine = null;
	
	private $databaseName = NULL;
	
	private $mql = null;
	
	public function __construct($type = 'main'){
		switch($type){
			case 'main':
				$this->initMySQL(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);
				$this->databaseName = DB_NAME;
				break;
			case 'main_2':
				$this->initMySQL(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME_2, DB_PORT);
				$this->databaseName = DB_NAME;
				break;
			default:
				$isInit = false;
				debug::addError('Could not find DB-Connection ('.$type.')');
				break;
		}
	}
	
	public function getStatus(){
		return $this->isInit;
	}
	
	public function getDatabaseName(){
		return $this->databaseName;
	}
	
	public function query($sql){
		if(!$this->isInit){
			debug::addWarning('DB-Connection has not initialized');
			return false;
		}
			
		
		$res = $this->mql->query($sql);

		if (!is_bool($res)) {
			$a = array();	
			while($obj = $res->fetch_object()){
        		$a[] = $obj;
    		}
			mysqli_free_result($res);
			return($a);
		} else 
			return($res);
	}
	
	public function close(){
		$this->mql->close();
		$this->isInit = FALSE;
	}
	
	public function getLastID(){
		return($this->mql->insert_id);
	}
	
	public function getLastError(){
		return($this->mql->error);
	}
	
	public function getHarmonizedString($string){
		return($this->mql->real_escape_string($string));
	}
	
	#
	#
	#	---------------------------------------------------------
	#
	#
	
	private function initMySQL($host, $user, $pass, $name, $port){
		$this->mql = new mysqli($host, $user, $pass, $name, $port);

		if($this->mql->connect_errno){ 
			debug::addWarning('Connection failed: '.$host.','.$user.','.$pass.','.$name.','.$port.'.');	 
			return;
		}
		
		$this->isInit = TRUE;
		$this->engine = dbEnum::MYSQL;
		$this->query('SET NAMES \'utf8\'');
		$this->query("SET CHARACTER SET 'utf8'");
		#mysqli_set_charset(self::$mql, 'utf8');

		return;
	}
}

class db {
	private static $dbObj = null;
	private static $lastID = FALSE;	
	private static $isInit = FALSE;
	
	private static $mql;
	private static $engine = FALSE;

	public static function get_engine(){
		return(self::$engine);
	}
	

	public static function queryRaw(string $sql){
        return self::$dbObj->query($sql);
    }
    /**
     * starts a simple database query -> only full string support ==> DB-Engine has to match string-Syntax
     *
     * @param[in] $s as String
     * @return DB-Object
     *
     */
	public static function query(dbObj $sql){
		if(!self::initMain())
			return NULL;

		$sqlString = $sql->getQueryString();

		if($sqlString === FALSE)
			return NULL;
		else
			return self::$dbObj->query($sqlString);
	}
	/**
	 * returns the increment counter of given database-table +1
	 * 
	 * @param[in] $s as String
	 * @return Integer 
	 * 
	 */
	public static function getIncrementCounterNextNumberOfTable($string){
		$sql = new dbObj();			
		$sql->presetShowTableStatus($string,self::$dbObj->getDatabaseName());
		
		$res = db::query($sql);
		
		if(!isset($res[0]->Auto_increment))
			return FALSE;
		else 
			return ((int)$res[0]->Auto_increment);
	}
	
	public static function close(){
		self::$dbObj->close();
		self::$isInit = false;
	}
	
	/**
	 * @return Integer, last affected Row of query
	 * 
	 */
	
	public static function getLastID(){
		return(self::$dbObj->getLastID());
	}
	
	/**
	 * @return String, last Error triggered by query
	 * 
	 */
	
	public static function getLastError(){
		return(self::$dbObj->getLastError());
	}
	/**
	 * harmonizes parameter string to encapsule characters like ' and "
	 * 
	 * @param[in] $s as String
	 * @return String, harmonized 
	 * 
	 */
	public static function harm($s){
		if(!self::initMain()){ 
			debug::addWarning('String could not be harmonized ('.$s.')');
			return $s;
		}
		
		return(self::$dbObj->getHarmonizedString($s));
	}
	/**
	 * harmonizes parameter string to encapsule characters like ' and ", finalizes String with '-char 
	 * 
	 * @param[in] $s as String
	 * @return String, harmonized 
	 * 
	 */
	public static function harmAndString($s){
		if(!self::initMain()){ 
			debug::addWarning('String could not be harmonized ('.$s.')');
			return 'NULL-STRING';
		}
		
		return("'".self::$dbObj->getHarmonizedString($s)."'");
	}
		
	/**
	 * returns Float value of string or the substitude, if string does not express a formal number
	 * 
	 * @param[in] $s as String/Number, Substitude
	 * @return Float, Standard = 0.0
	 * 
	 */
	public static function float($s,$sub = 0.0){
		if(is_float($s))	
			return(floatval($s));
		else
			return(floatval($sub));
	} 
	/**
	 * returns Integer value of string or the substitude, if string does not express a formal number
	 * 
	 * @param[in] $s as String/Number, Substitude
	 * @return Integer, Standard = 0
	 * 
	 */
	public static function int($s, $sub = 0){
		if(is_int($s))	
			return(intval($s));
		else
			return(intval($sub));
	} 
	
	########################
	
	private static function initMain(){
		self::$engine = dbEnum::MYSQL;	
		if(self::init()){
			self::$isInit = true;
			return true;
		} else {
			self::$engine = dbEnum::NONE;	
			debug::addWarning('Could not initialize DB-Connection (main)');
		}
		
		return false;
	}
	
	private static function init(){
		if(self::$isInit) {
			#debug::addHint('DB already Init (main)'); 
			return TRUE;
		}

		self::$dbObj = new dbClassMySQL();
		
		return self::$dbObj->getStatus();
	}

	/**
     * TODO implement harmPassword-Function
     * 
     * @param[in] $string as String to core-password-Function
     * 
     */
	public static function harmPassword(&$string){
		$string = '';
	}
    /**
     * TODO implement harmIP-Function
     * 
     * @param[in] $string as String to core-IP-Function
     * 
     */
	public static function harm_ip(&$string){
		$string = str_replace('%ip', "'".$_SERVER['REMOTE_ADDR']."'", $string);
	}
	private static function getErrorEstablish(){
		return 'Error Establishing DB';
	}
	
}

class db2 {
	private static $dbObj = null;
	private static $lastID = FALSE;	
	private static $isInit = FALSE;
	
	private static $mql;
	private static $engine = FALSE;

	public static function get_engine(){
		return(self::$engine);
	}
	
	/**
	 * starts a simple database query -> only full string support ==> DB-Engine has to match string-Syntax
	 * 
	 * @param[in] $s as String
	 * @return DB-Object 
	 * 
	 */
	
	public static function query(dbObj2 $sql){
		if(!self::initMain())
			return NULL;

		$sqlString = $sql->getQueryString();

		if($sqlString === FALSE)
			return NULL;
		else
			return self::$dbObj->query($sqlString);
	}
	/**
	 * returns the increment counter of given database-table +1
	 * 
	 * @param[in] $s as String
	 * @return Integer 
	 * 
	 */
	public static function getIncrementCounterNextNumberOfTable($string){
		$sql = new dbObj();			
		$sql->presetShowTableStatus($string,self::$dbObj->getDatabaseName());
		
		$res = db::query($sql);
		
		if(!isset($res[0]->Auto_increment))
			return FALSE;
		else 
			return ((int)$res[0]->Auto_increment);
	}
	
	public static function close(){
		self::$dbObj->close();
		self::$isInit = false;
	}
	
	/**
	 * @return Integer, last affected Row of query
	 * 
	 */
	
	public static function getLastID(){
		return(self::$dbObj->getLastID());
	}
	
	/**
	 * @return String, last Error triggered by query
	 * 
	 */
	
	public static function getLastError(){
		return(self::$dbObj->getLastError());
	}
	/**
	 * harmonizes parameter string to encapsule characters like ' and "
	 * 
	 * @param[in] $s as String
	 * @return String, harmonized 
	 * 
	 */
	public static function harm($s){
		if(!self::initMain()){ 
			debug::addWarning('String could not be harmonized ('.$s.')');
			return $s;
		}
		
		return(self::$dbObj->getHarmonizedString($s));
	}
	/**
	 * harmonizes parameter string to encapsule characters like ' and ", finalizes String with '-char 
	 * 
	 * @param[in] $s as String
	 * @return String, harmonized 
	 * 
	 */
	public static function harmAndString($s){
		if(!self::initMain()){ 
			debug::addWarning('String could not be harmonized ('.$s.')');
			return 'NULL-STRING';
		}
		
		return("'".self::$dbObj->getHarmonizedString($s)."'");
	}
		
	/**
	 * returns Float value of string or the substitude, if string does not express a formal number
	 * 
	 * @param[in] $s as String/Number, Substitude
	 * @return Float, Standard = 0.0
	 * 
	 */
	public static function float($s,$sub = 0.0){
		if(is_float($s))	
			return(floatval($s));
		else
			return(floatval($sub));
	} 
	/**
	 * returns Integer value of string or the substitude, if string does not express a formal number
	 * 
	 * @param[in] $s as String/Number, Substitude
	 * @return Integer, Standard = 0
	 * 
	 */
	public static function int($s, $sub = 0){
		if(is_int($s))	
			return(intval($s));
		else
			return(intval($sub));
	} 
	
	########################
	
	private static function initMain(){
		self::$engine = dbEnum::MYSQL;	
		if(self::init()){
			self::$isInit = true;
			return true;
		} else {
			self::$engine = dbEnum::NONE;	
			debug::addWarning('Could not initialize DB-Connection (main)');
		}
		
		return false;
	}
	
	private static function init(){
		if(self::$isInit) {
			#debug::addHint('DB already Init (main)'); 
			return TRUE;
		}

		self::$dbObj = new dbClassMySQL( 'main_2' );
		
		return self::$dbObj->getStatus();
	}

	/**
     * TODO implement harmPassword-Function
     * 
     * @param[in] $string as String to core-password-Function
     * 
     */
	public static function harmPassword(&$string){
		$string = '';
	}
    /**
     * TODO implement harmIP-Function
     * 
     * @param[in] $string as String to core-IP-Function
     * 
     */
	public static function harm_ip(&$string){
		$string = str_replace('%ip', "'".$_SERVER['REMOTE_ADDR']."'", $string);
	}
	private static function getErrorEstablish(){
		return 'Error Establishing DB';
	}
	
}


class theCoreTableLayout{
	private $name = '';
	private $rowLayout = array();	
	
	public function __construct($nameOfTable){
		$this->name = $nameOfTable;
	}
	public function setRowsFromArray($array){
		if(is_array($array) && isset($array[0]['name']) && isset($array[0]['type'])){
			$this->rowLayout = $array;
		}
	}
	public function getRowLayout(){
		return $this->rowLayout;
	}
	public function getName(){
		return $this->name;
	}
}
?>