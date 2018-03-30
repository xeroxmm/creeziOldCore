<?php
	security::loadUserLevel(userLevelEnum::NONE);
	html::head();
	B::ID('page-content-wrapper-full')
	->in()
	->_DIV('content-page-full')
		->in()
			->_DIV(NULL, 'content-page-full-wrapper');
				
	B::ID('content-page-full-wrapper')->setContent(				
			'<div id="bar-nav-top">
				<div class="flex-100">
					<div class="is-blue">
						<div class="is-white" style="position:relative">
							<div id="searchbar">
							<ul class="nav nav-tabs">
							  <li role="presentation" class="selected"><a href="#">All</a></li>
							  <li role="presentation"><a href="/images">Images</a></li>
							  <li role="presentation"><a href="#">Videos</a></li>
							  <li role="presentation"><a href="#">Links</a></li>
							  <li role="presentation"><a href="#">Posts</a></li>
							  <li role="presentation"><a href="#">Audio</a></li>
							  <li role="presentation"><a href="#">Croups</a></li>
							</ul>
					      	</div>
						</div>
					</div>
				</div>
			</div>'
		);
	html::send200();
	
	exit;
?>