<?php
	security::loadUserLevel(userLevelEnum::NONE);
	
	html::head();
	//Content Nav
	B::ID('nav-second')->_DIV('nav-content',['left','']);
	
	 B::ID('nav-content')->setContent('
			<ul class="nav nav-tabs">
  				<li role="presentation" class="active"><a href="/">All</a></li>
  				<li role="presentation"><a href="images">Images</a></li>
  				<li role="presentation"><a href="videos">Videos</a></li>
  				<li role="presentation"><a href="audio">Audio</a></li>
  				<li role="presentation"><a href="links">Links</a></li>
  				<li role="presentation"><a href="articles">Articles</a></li>            		        			        					  			
			</ul>
        ');
    B::ID('nav-filter')->setContent('
    			<ul class="nav nav-pills tabs-content">
  				<li role="presentation" class="dropdown">
          			<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Most Viral <span class="caret"></span></a>
	          		<ul class="dropdown-menu">
	            		<li><a href="#">Most Viral</a></li>
            			<li><a href="#">Trending</a></li>
            			<li><a href="#">Newest</a></li>
            			<li><a href="#">User Submitted</a></li>
          			</ul>
        		</li>
  				<li role="presentation" class="dropdown">
          			<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Sort by <span class="caret"></span></a>
	          		<ul class="dropdown-menu">
	            		<li><a href="#">Highest score</a></li>
            			<li><a href="#">Most discussed</a></li>
            			<li><a href="#">Most tipped</a></li>
          			</ul>
        		</li>        		
  				<li role="presentation" class="dropdown">
          			<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Time <span class="caret"></span></a>
	          		<ul class="dropdown-menu">
	            		<li><a href="#">Last 24h</a></li>
            			<li><a href="#">Last Week</a></li>
            			<li><a href="#">Last Month</a></li>
            			<li><a href="#">Last Year</a></li>
          			</ul>
        		</li>
  				<li role="presentation" class="dropdown pull-right">
          			<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><img src="https://cdn0.iconfinder.com/data/icons/layout-and-location/24/Untitled-2-29-20.png"></a>
	          		<ul class="dropdown-menu">
	            		<li><a href="#"><img src="https://cdn2.iconfinder.com/data/icons/flat-ui-icons-24-px/24/menu-24-20.png"></a></li>
	            		<li><a href="#"><img src="https://cdn0.iconfinder.com/data/icons/layout-and-location/24/Untitled-2-31-20.png"></a></li>
          			</ul>
        		</li>         		       		        		        			        					  			
			</ul>        		       		        		        			        					  			
			</ul>
    ');   
    B::ID('container')->_DIV('main');
	// Content
	B::ID('main')->inner()->_DIV('content',['left','space']);
	B::ID('content')->setContent('Text');
	html::send200();
	
	exit;
?>