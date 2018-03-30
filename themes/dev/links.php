<?php
	security::loadUserLevel(userLevelEnum::NONE);
	
	html::head();
	B::ID('content')->setContent('
<ul class="media-list">
  <li class="media">
    <div class="media-left">
      <a href="#">
        <img class="media-object" src="http://placehold.it/70x40" width="70px" alt="...">
      </a>
    </div>
    <div class="media-body">
            <h4 class="media-heading"><a href="#">Link heading </a><small><a href="#">(imgur.com)</a></small></h4>
       <span class="item-username"><small><a href="user-profile">Username</a></small></span>
       <span><small>113,154 views | in <a href="category-page">Wallpaper</a> | 5 hours ago </small></span>
    </div>
  </li>
  <li class="media">
    <div class="media-left">
      <a href="#">
        <img class="media-object" src="http://placehold.it/70x40" width="70px" alt="...">
      </a>
    </div>
    <div class="media-body">
      <h4 class="media-heading"><a href="#">Link heading </a><small><a href="#">(cnn.com)</a></small></h4>
       <span class="item-username"><small><a href="user-profile">Username</a></small></span>
       <span><small>113,154 views | posted in <a href="category-page">Wallpaper</a> | 5 hours ago </small></span>
    </div>
  </li>  
</ul>
	');
	html::send200();
	
	exit;
?>