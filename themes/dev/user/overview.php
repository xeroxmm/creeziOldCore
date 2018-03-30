<?php
	security::loadUserLevel(userLevelEnum::NONE);
	html::head();
	B::ID('content')->setContent(				
			'All Users Unite!'
		);
	html::send200();
	
	exit;
?>