<?php
	security::loadUserLevel(userLevelEnum::NONE);
	
	html::head();
	//Browse Content
		B::ID('browse-wrapper')->setContent('
		   	<ul class="slide-nav">
	            <li>
	                <a href="/stream">My Stream</a>
	            </li>
	            <li>
	                <a href="/popular">Discover</a>
	            </li>
	            <li>
	                <a href="/popular">Random</a>
	            </li>
	            <li>
	                <a href="/channels">Channels <span class="pull-right"><i class="fa fa-chevron-down"></i></span></a>
	            </li>
	            <li role="separator" class="divider"></li>			            	
	            <li>
	                <a href="/recent">Favorites</a>
	            </li>	
	            <li>
	                <a href="/recent">Archive</a>
	            </li>		                                              
	            <li><small>Follows</small><span class="pull-right"><i class="fa fa-ellipsis-h"></i></span></li>   
	            <li>
					<form role="search">
				        <div class="form-group">
				          <input type="text" class="form-control" placeholder="Search">
				        </div>
		      		</form>
	            </li>  
	           	<li>
	           		<ul class="follow-list">
	           			<li><a href="/trending"><img width="25px" src="http://i.imgur.com/Ad1NC7H.jpg"> Cats</a></li>
	            	</ul>
	            </li>     
	        </ul>
		');
	B::ID('content-nav-wrapper')->setContent(				
			'<div class="content-header"><h1>My Stream</h1></div>
			<div id="content-nav">
			<ul class="nav nav-tabs content-tabs">
							  <li role="presentation" class="selected"><a href="#">All</a></li>
							  <li role="presentation"><a href="/images">Images</a></li>
							  <li role="presentation"><a href="#">Videos</a></li>
							  <li role="presentation"><a href="#">Links</a></li>
							  <li role="presentation"><a href="#">Posts</a></li>
							</ul>
							</div>
		');
	B::ID('content')->addElement(snippet::getTopPictures(20));	
	html::send200();
	
	exit;
?>