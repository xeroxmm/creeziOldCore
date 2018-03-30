<?php
	security::loadUserLevel(userLevelEnum::NONE);
	
	html::head();
	html::send200();
	
	exit;
?>