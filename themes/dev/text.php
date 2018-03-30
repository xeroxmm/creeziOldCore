<?php
	security::loadUserLevel(userLevelEnum::NONE);
	
	html::head();
	B::ID('content')->setContent('
	<div class="row">
		<div class="col-md-6">
			<div class="text-item thumbnail">
			<a href="single-text">
			<img src="http://placehold.it/550x250" class="img-responsive">
			</a>
			<div class="text-item-meta">
			<a href="single-text">
			<h3>Article Headline</h3>
			<span>Article excerpt</span>
			</a>
			<div class="item-username"><small><a href="user-profile">Username</a></small></div>
  			<span><small>113,154 views | in <a href="category-page">Category</a> | 6 months ago </small></span>
  			<ul class="list-inline tags-list">
  			<li><span class="label label-default"><a href="tag-page">Tag1</a></span></li>
  			<li><span class="label label-default"><a href="tag-page">Tag2</a></span></li>
  			<li><span class="label label-default"><a href="tag-page">Tag3</a></span></li>
  			<li><span class="label label-default"><a href="tag-page">Tag4</a></span></li>
  			</ul>
			</div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="text-item thumbnail">
			<a href="single-text">
			<img src="http://placehold.it/550x250" class="img-responsive">
			</a>
			<div class="text-item-meta">
			<a href="single-text">
			<h3>Article Headline</h3>
			<span>Article excerpt</span>
			</a>
			<div class="item-username"><small><a href="user-profile">Username</a></small></div>
  			<span><small>113,154 views | in <a href="category-page">Category</a> | 6 months ago </small></span>
  			<ul class="list-inline tags-list">
  			<li><span class="label label-default"><a href="tag-page">Tag1</a></span></li>
  			<li><span class="label label-default"><a href="tag-page">Tag2</a></span></li>
  			<li><span class="label label-default"><a href="tag-page">Tag3</a></span></li>
  			<li><span class="label label-default"><a href="tag-page">Tag4</a></span></li>
  			</ul>
			</div>
			</div>
		</div>										
		<div class="col-md-6">
			<div class="text-item thumbnail">
			<a href="single-text">
			<img src="http://placehold.it/350x150" class="img-responsive">
			</a>
			<div class="text-item-meta">
			<a href="single-text">
			<h3>Article Headline</h3>
			<span>Article excerpt</span>
			</a>
			<div class="item-username"><small><a href="user-profile">Username</a></small></div>
  			<span><small>113,154 views | in <a href="category-page">Category</a> | 6 months ago </small></span>
  			<ul class="list-inline tags-list">
  			<li><span class="label label-default"><a href="tag-page">Tag1</a></span></li>
  			<li><span class="label label-default"><a href="tag-page">Tag2</a></span></li>
  			<li><span class="label label-default"><a href="tag-page">Tag3</a></span></li>
  			<li><span class="label label-default"><a href="tag-page">Tag4</a></span></li>
  			</ul>
			</div>
			</div>
		</div>		
		<div class="col-md-6">
			<div class="text-item thumbnail">
			<a href="single-text">
			<img src="http://placehold.it/350x150" class="img-responsive">
			</a>
			<div class="text-item-meta">
			<a href="single-text">
			<h3>Article Headline</h3>
			<span>Article excerpt</span>
			</a>
			<div class="item-username"><small><a href="user-profile">Username</a></small></div>
  			<span><small>113,154 views | in <a href="category-page">Category</a> | 6 months ago </small></span>
  			<ul class="list-inline tags-list">
  			<li><span class="label label-default"><a href="tag-page">Tag1</a></span></li>
  			<li><span class="label label-default"><a href="tag-page">Tag2</a></span></li>
  			<li><span class="label label-default"><a href="tag-page">Tag3</a></span></li>
  			<li><span class="label label-default"><a href="tag-page">Tag4</a></span></li>
  			</ul>
			</div>
			</div>
		</div>						
	</div>
	');
	html::send200();
	
	exit;
?>