<?php
	security::loadUserLevel(userLevelEnum::NONE);
	
	html::head();
	B::ID('content')->setContent('
<div class="img-grid">
    <div class="img-item">
      <div class="pin"> 
        <div class="video-thumb">
  					<img src="http://placehold.it/320x180" class="img-responsive">
  					<span class="video-time">3:54</span>	
  			</div>
			<div class="img-meta">
				<h3 class="item-title">Video Titel 1</h3>
				<span><small>113,154 views | 6 months ago </small></span>
				<ul class="list-inline tags-list">
	  				<li><span class="label label-default"><a href="tag-page">Tag1</a></span></li>
	  				<li><span class="label label-default"><a href="tag-page">Tag2</a></span></li>
	  				<li><span class="label label-default"><a href="tag-page">Tag3</a></span></li>
	  				<li><span class="label label-default"><a href="tag-page">Tag4</a></span></li>
	  			</ul>	
	  			<hr>
				<div class="media img-media">
  					<div class="media-left">
    					<a href="user-profile">
      					<img class="media-object" src="http://placehold.it/30x30" alt="...">
    					</a>
  					</div>
  					<div class="media-body">
						<strong>Username</strong><br>
						<span>in </span><a href="category-page">Category</a><span> to </span><a href="single-folder">Folder Name</a>
  					</div>
  				</div>		
			</div>
      </div>
    </div>                      
</div>
		
	');
	html::send200();
	
	exit;
?>