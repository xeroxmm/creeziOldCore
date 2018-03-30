<?php
	/**
	 * Security Enumeration
	 *
	 * @package AdSocials
	 * @since 1.0.0
	 */

class userLevelEnum extends enum {
	const __default = self::NONE;
	
	const NONE = 0;
	const NORMAL = 1;
	const HELPER = 2;
	const ADMIN = 5;
	const SUPERADMIN = 10;
	const GOD = 100;
}

class authMethodEnum extends enum {
	const __default = self::NONE;
	
	const NONE = 0;
	const COOKIE = 1;
	const API = 2;
	const DB = 2;
	const SESSION = 4;
}

class apiTemplateEnum extends enum {
	const __default = self::NONE;
	
	const NONE = 0;
	const LOGIN = 1;
	const HTML = 2;
	const TIME = 3;
	const UPLOAD = 4;
	const statusUSER = 5;
}
?>