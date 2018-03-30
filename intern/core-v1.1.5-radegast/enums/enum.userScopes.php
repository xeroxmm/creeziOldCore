<?php
class enumUserScopes  extends enum {
	const __default = self::NONE;
	
	const NONE = 0;
	const IDENT = 1;
	const PROFILE = 2;
	const MAIL = 3;
	
	const TEXT = 4;
	const VIDEO = 5;
	const AUDIO = 6;
	const IMAGE = 7;
	const LINK = 8;
	const STREAM = 9;
	
	const FRIEND = 10;
	const CATEGORIE = 11;
}
?>