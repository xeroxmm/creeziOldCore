<?php
	/**
	 * dbEngine Enumeration
	 *
	 * @package AdSocials
	 * @since 1.0.0
	 */

class dbEnum extends enum {
	const __default = self::NONE;
	
	const NONE = 0;
	const MYSQL = 1;
	const MONGO = 2;
}

class mysqlTypeEnum extends enum {
	const __default = self::INT;
	
	const INT = 'INT';
	const BIGINT = 'BIGINT';
	const VARCHAR = 'VARCHAR';
}

class enumDBQueryType extends enum {
	const __default = self::SELECT;
	
	const SELECT = 0;
	const INSERT = 1;
	const UPDATE = 2;
	const DELETE = 3;
	
	const PRESET = 4;
}
?>