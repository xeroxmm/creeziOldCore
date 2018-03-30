<?php
	security::loadUserLevel(userLevelEnum::NONE);
	html::head();
	$urlObj = core::getURLObj()->getPathArray();
	$userName = $urlObj[1];
	
	$userAct = user::getUserFromDBByUserURL($userName);
	pagination::setAsPaginationAnker();
	
	B::ID('wrap')
		->_DIV('content-main','content-main')
			->in()
					->_DIV(NULL,'content-main-head')
						->in()
							->_DIV(NULL,'content-main-head-in')->in()->setContent('<img class="avatar-profile inline-block" src="'.$userAct->getAvatarURL().'"><h1 class="font-white globalheadline font-size-160 inline-block">'.$userAct->getNickname().'</h1>');
						
	B::ID('content-main')->_DIV('content-main-nav','content-main-nav');
	
	B::ID('content-main-nav')->addElement( snippet::getAdvancedFilterDOMElementsUserPage() );

	B::ID('content-main')
		->addElement( snippet::getTopContent(20 , NULL, pagination::getTimeStamp(), pagination::getPage()) );
	B::ID('img-grid-box')->addElement( snippet::getPaginationLinkStart() );
	
	html::send200();
	
	exit;
?>