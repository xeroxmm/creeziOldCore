<?php
	security::loadUserLevel(userLevelEnum::NONE);
	
	pagination::setAsPaginationAnker();
	
	html::head();
	
	$urlObj = core::getURLObj()->getPathArray();
	$tagName = $urlObj[count($urlObj)-1];
	$tagArray = security::getTagArrayFromRequestString($urlObj);
	$isTrack = false;
	
	$nameSearch = $urlObj[count($urlObj)-2];
	if($nameSearch == 'track')
		$isTrack = true;
	
	html::head()->setTitle(implode($tagArray,' #').' | '.$nameSearch.' search - creezi.com');
	
	B::ID('wrap')
		->in()
		->_DIV('sidebar-main',['sidebar-main','fixed'])
		->_DIV('content-main','content-main')
			->in()
				->_DIV('content-main-in','content-main-in')
				->in()
					->_DIV(NULL,'content-main-head')
						->in()
							->_DIV(NULL,'content-main-head-in')->in()->setContent('<h1 class="font-white globalheadline font-size-160">#'.implode($tagArray,' #').'</h1><span class="header-subline">'.snippet::getAmountOfFilesByAct($tagArray, $isTrack).' Files Found</span>')->outer()
						->outer()
					->_DIV('content-main-nav','content-main-nav')
					->outer();

	//Browse Content
		B::ID('sidebar-main')->setContent('
		   	<ul class="slide-nav">
	            <li>
	                <a href="/popular">Discover</a>
	            </li>
	            <li>
	                <a href="/popular">Random</a>
	            </li>
	            <li role="separator" class="divider"></li>
	            <li>
	                <a href="/t/animals">Animals</a>
	            </li>
	            <li>
	                <a href="/t/education">Education</a>
	            </li>		            
	            <li>
	                <a href="/t/entertainment">Entertainment</a>
	            </li>	  
	            <li>
	                <a href="/t/food">Food</a>
	            </li>
	            <li>
	                <a href="/t/funny">Funny</a>
	            </li>		                      
	            <li>
	                <a href="/t/gaming">Gaming</a>
	            </li>
	            <li>
	                <a href="/t/health">Health</a>
	            </li>		                        
	            <li>
	                <a href="/t/sports">Sports</a>
	            </li>
	            <li>
	                <a href="/t/travel">Travel</a>
	            </li>			            	            	            	            	            
	        </ul>
		');
	B::ID('content-main-nav')->addElement( snippet::getAdvancedFilterDOMElements() );	
		
	if(!$isTrack){
		B::ID('content-main-in')
			->addElement( snippet::getTopContentAct(20 , $tagArray) );
	} else{
		B::ID('content-main-in')
			->addElement( snippet::getTopContentTrack(20 , $tagArray) );
	}
	B::ID('img-grid-box')->addElement(snippet::getPaginationLinkStart());

	html::send200();
	
	exit;
?>