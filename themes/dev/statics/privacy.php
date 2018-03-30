<?php	
	security::loadUserLevel(userLevelEnum::NONE);
	html::head();
	B::ID('wrap')
			->inner()
			->_DIV('content-main','content-main')
				->in()
					->_DIV('content-main-in','content-main-in')
					->in()
						->_DIV(NULL,'content-main-head')
							->in()
								->_DIV(NULL,'content-main-head-in')->in()->setContent('<h1 class="font-white globalheadline font-size-160">Privacy Policy</h1>')->outer()
							->outer();
	html::send200();
	
	exit;
?>