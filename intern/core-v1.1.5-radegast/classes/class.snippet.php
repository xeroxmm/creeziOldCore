<?php
require_once 'class.snippet.LEGO.php';
class snippet {
    private static $topPicturesLatestDate = NULL;	
    
    public static function getRelatedItems($relatedItems){
    	if(is_array($relatedItems)){    			
   			$snippet = new htmlTag('div','single-sidebar-vert','single-sidebar-vert');	
			foreach($relatedItems as $relatedContentElement)
				snippetLEGO::getRelatedContentBox($snippet, $relatedContentElement);
		} else {
			$snippet = new htmlTag(NULL);
		}
		return $snippet;
    }
    public static function getNewContent(int $limit = 80){
        $sql = self::getDBQueryObjectContentALL();
        $sql->setOrderByField('ID',DBTableNameContentAll, FALSE);
        $sql = self::addNonPrivateAndSaveMask( $sql );
        $sql = self::addDBQueryUserInfo($sql);
        $sql->setLimit($limit);

        $res = db::query($sql);

        if(count($res) > 0){
            $snippet = self::buildStartSnippetBasedOnInfedScroll(FALSE);

            self::setTopContentLatestElement(0);

            return self::buildHTMLDOMTree($res,$snippet);
        } else
            return new htmlTag(NULL);
    }
    public static function loginButtonGoogle(){
    	/*$snippet = new htmlTag('div','my-signin2','loginPanel');
		$snippet->setAttr('data-onsuccess','onSignIn');
		
		html::head()->addOwnScriptString('var lgURL = \''.LINK_Login_Script.'\';');
		html::head()->addScript('/ressources/core/js/googlePlus.js');
		html::footer()->addScript('https://apis.google.com/js/platform.js?onload=renderButton',true,true);
		
		return $snippet;*/
		
		$client = new Google_Client();
		
		$client->setApplicationName('TheFolder');
		$client->setClientId(googlePlusKey);
		$client->setClientSecret(googlePlusSecret);
		$client->setDeveloperKey(googlePlusServerKey);
		$client->setRedirectUri(HTTP_HOST.'/'.LINK_Login_Script.'?loginType=ggl');
		#$client->setAccessType("offline");
		$client->setScopes(['profile','email','openid']);
		
		$plus = new Google_Service_Plus($client);
		
		$loginUrl = $client->createAuthUrl();
		
		$snippet = new htmlTag('div','coreGoogleLogin',['txtCentered']);
		$snippet->_A($loginUrl,null,null,'Login with Google+');
		
		return $snippet;
    }
	public static function getPaginationLinkStart( $isJS = FALSE){
		$linkS = core::getURLObj()->getRequestedURL().'?page='.pagination::getNextPage().pagination::getSortTypeQueryString('&').'&filter='.snippet::getTopPicturesLatestDate();
		
		$snippet = new htmlTag('div');

        if(pagination::getElementCount() < 20)
            return $snippet;
        
		if($isJS)
			$linkS .= '&js';
		
		if(!$isJS)		
			$snippet->_A($linkS, 'linkLoadRecent', 'nextPagionationLink', 'Next items...')->in()->setAttr('rel','noindex, follow');
		else
			$snippet->_A($linkS, 'linkLoadRecent', 'nextPagionationLink', 'Next items...')->in()->setAttr('rel','noindex, follow')->setAttr('style','display: none');
		
		if(!$isJS)
			$snippet->setContent('<script>var link = document.getElementById("linkLoadRecent");link.setAttribute(\'href\', "'.$linkS.'&js");link.style.display = \'none\';</script>');
		
		return $snippet;
	}
	
	public static function loginButtonSteam(){
		$link = '#';	
		$openid = new LightOpenID(HTTP_HOST.'/'.LINK_Login_Script);	
		
		if(!$openid->mode){
			$openid->identity = 'http://steamcommunity.com/openid/?l=english';    // This is forcing english because it has a weird habit of selecting a random language otherwise
            $openid->returnUrl = HTTP_HOST.'/'.LINK_Login_Script.'?loginType=stm';
            $link = $openid->authUrl();
		}
			
		$snippet = new htmlTag('div','steamSignIn',['loginPanel','clickable']);
		
		$snippet->_DIV()->in()->setStyle('width', '155px')->setStyle('margin', '0 auto')->setStyle('cursor', 'pointer')
			->_A($link)->in()
			->_IMG('/ressources/core/pictures/loginSteamSmall.png');
		
		return $snippet;
	}
	public static function loginButtonFacebook(){
		/*$snippet = new htmlTag('div','coreFacebookLogin');	
		
		$snip = new htmlTag('fb:login-button','fb-button-core',['loginPanel','fb-login-button']);		
		$snip->setAttr('data-max-rows','1');
		$snip->setAttr('data-size','large');
		$snip->setAttr('data-show-faces','false');
		$snip->setAttr('data-auto-logout-link','false');
		
		$snip->setAttr('scope','public_profile,email,user_about_me');
		$snip->setAttr('onlogin','checkLoginState();');
		
		html::head()->addScript('/ressources/core/js/facebook.js');
				
		html::body()->setContentAtBegin(
			'<div id="fb-root"></div>
			<script>(function(d, s, id) {
			  var js, fjs = d.getElementsByTagName(s)[0];
			  if (d.getElementById(id)) return;
			  js = d.createElement(s); js.id = id;
			  js.src = "//connect.facebook.net/de_DE/sdk.js#xfbml=1&version=v2.5&appId=1522352841425143";
			  fjs.parentNode.insertBefore(js, fjs);
			}(document, \'script\', \'facebook-jssdk\'));</script>'
		);
		
		$snippet->addElement($snip);
		$snippet->addElement(new htmlTag('div','status'));
		*/
		$fb = new Facebook\Facebook([
				'app_id' => facebookAppId,
				'app_secret' => facebookAppSecret,
				'default_graph_version' => facebookDefaultGraphVersion
			]);

		$helper = $fb->getRedirectLoginHelper();
		$permissions = ['email', 'user_likes', 'public_profile', 'user_about_me']; // optional
		$loginUrl = $helper->getLoginUrl(HTTP_HOST.'/'.LINK_Login_Script.'?loginType=fbk', $permissions);
		
		$snippet = new htmlTag('div','coreFacebookLogin',['txtCentered']);
		$snippet->_A($loginUrl,null,null,'Login with Facebook');
		
		return $snippet;
	}	
    public static function miniLogin(){
        $snippet = new htmlTag('div','mini-login','mini');
        
		$snippet->_p(null,'textCentered','Login with Email');
        
        $snippet
        	->_DIV(null,['inputEmailField','space_5'])->in()
			->_BtInputText(null,null,htmlTag::_BtIconS('envelope'),'E-Mail');
        $snippet
        	->_DIV(null,['inputPasswordField','space_5'])->in()
			->_BtInputPassword(null,null,htmlTag::_BtIconS('lock'),'Password');
        
		$snippet->_p(null,'textCentered','or:');
    	
		$snippet
        	->_DIV(null,['inputRegisterField','space_5'])->in()
			->_BtButton(null, null, 'Register','send')->in()->setStyle('width', '100%');
				
		return $snippet;
    }
	public static function miniInteractionMenu(){
		$snippet = new htmlTag('ul','login-interaction-menu-list');
	}
	public static function getTopBarUserInterface(){
		$snippet = new htmlTagList();
		$snippet->setClass(['nav','navbar-nav']);
		/*
		$snippet->_LILink('#toggle-menu-notes', '<i class="fa fa-bell"></i>', NULL , ['pull-left', ' navbar-btn-grp'] , NULL , ['btn' , 'btn-default' , 'navbar-btn']);
		$snippet->_LILink('#toggle-menu-messages', '<i class="fa fa-envelope-o"></i>', NULL , ['pull-left' , 'navbar-btn-grp'] , NULL , ['btn' , 'btn-default' , 'navbar-btn']);
		$snippet->_LILink('#toggle-menu-friends', '<i class="fa fa-user"></i>', NULL , ['pull-left'] , NULL , ['btn' , 'btn-default' , 'navbar-btn']); 
		
		$snippet->_LIText('
				<form class="navbar-form navbar-left" role="search">
			        <div class="form-group">
			          <input type="text" class="form-control" placeholder="Search">
			        </div>
		      	</form>', NULL, ['navbar-form' , 'navbar-left']);
		
		#$snippet->_LILink();
		#$snippet->_LILink( NULL , ['pull-right' , , 'fixed-button' ,'fixed-right-2'] , );
		*/
		$snippet2 = new htmlTag('div',NULL, ['fixed-button-container']);
		$snippet2->_DIV(NULL , ['fixed-button' ,'a-btn'])->in()->_A('#toggle-sidebar', 'menu-toggle' , ['btn' , 'btn-default' , 'navbar-btn', 'isDark'], '<i class="fa fa-bars"></i>')
			->outer()->_DIV(NULL , ['fixed-button' ,'a-btn'])->in()->_A('#toggle-menu-profile', NULL , ['btn' , 'btn-default' , 'navbar-btn', 'avatar-top'], '<img id="main-user-avatar" src="'.user::getAvatarURL().'">');
		$snippet->_LIText($snippet2->broadcastAsString());	
			
		 /*
		 
		 <ul class="nav navbar-nav">
				<li class="pull-left navbar-btn-grp"><a id="menu-toggle" href="#menu-toggle" class="btn btn-default navbar-btn"><i class="fa fa-bars"></i></a></li>
		        <li class="pull-left navbar-btn-grp"><a href="#" class="btn btn-default navbar-btn"><i class="fa fa-envelope-o"></i></a></li>
		        <li class="pull-left"><a href="#"                class="btn btn-default navbar-btn"><i class="fa fa-user"></i></a></li>
		        <form class="navbar-form navbar-left" role="search">
			        <div class="form-group">
			          <input type="text" class="form-control" placeholder="Search">
			        </div>
		      	</form>
		        <li class="pull-right"><a href="#" class="btn btn-default navbar-btn avatar-top"><img src="https://s3.amazonaws.com/uifaces/faces/twitter/brad_frost/48.jpg"></a></li>
		        <li class="pull-right navbar-btn-grp"><a href="#" class="btn btn-default navbar-btn"><i class="fa fa-bell"></i></a></li>
	      	</ul> 
		 
		 */
		 
		 return $snippet;
	}
	public static function getTopContentTrack($limit, $acts = NULL, $timeStamp = NULL, $page = 1, $isInfScroll = FALSE){
		$sql = self::getDBQueryObjectContentVidAndMusic();
		$sql = self::addDBQueryTracks($sql,$acts);
		$sql = self::addDBQueryLimitAndOffset($sql, $limit, $page);
		
		$res = db::query($sql);
		
		self::$topPicturesRowCount = count($res);
		if(count($res) > 0){
			$snippet = self::buildStartSnippetBasedOnInfedScroll($isInfScroll);
		
			self::setTopContentLatestElement(0);
			
			return self::buildHTMLDOMTree($res,$snippet);
		} else 
			return new htmlTag(NULL);
	}
	public static function getTopContentAct($limit, $acts = NULL, $timeStamp = NULL, $page = 1, $isInfScroll = FALSE){
		$sql = self::getDBQueryObjectContentVidAndMusic();
		$sql = self::addDBQueryActs($sql,$acts);
		$sql = self::addDBQueryLimitAndOffset($sql, $limit, $page);
		
		$res = db::query($sql);
		//echo $sql->getQueryString();
		self::$topPicturesRowCount = count($res);
		if(count($res) > 0){
			$snippet = self::buildStartSnippetBasedOnInfedScroll($isInfScroll);
		
			self::setTopContentLatestElement(0);
			
			return self::buildHTMLDOMTree($res,$snippet);
		} else 
			return new htmlTag(NULL);
	}
    private static $useSpecial = FALSE;
    public static function useSpecialContentDB(){
        self::$useSpecial = TRUE;
    }
    public static function getAllInfo(string $sql){
        return db::queryRaw( $sql );
    }
	public static function getTopContent($limit, $tags = NULL, $timeStamp = NULL, $page = 1, $isInfScroll = FALSE){
		$ur = core::getURLObj()->getPathArray();

		if(isset($ur[1]) && $ur[0] == 't')
			$ur = $ur[1];
		else if(isset($ur[2]) && $ur[0] == 'u')
			$ur = $ur[2];
		else if(isset($ur[0]) && count($ur) == 1)
			$ur = $ur[0];
		else
			$ur = NULL;
		switch($ur){
			case 'images':
				$sql = self::getDBQueryObjectContentImages();
				break;
			case 'videos':
				$sql = self::getDBQueryObjectContentVideos();
				break;
			case 'collections':
				$sql = self::getDBQueryObjectContentCollections();
				break;
			default:
				$sql = self::getDBQueryObjectContentALL( self::$useSpecial );
				break;
		}
		
		$sql = self::addNonPrivateAndSaveMask( $sql );
		$sql = self::addDBQueryTags($sql,$tags);
		$sql = self::addDBQueryLimitAndOffset($sql, $limit, $page);
		
		if(user::isActiveUserPage())
			$sql = self::addDBQueryByUSERID($sql, user::getActiveUserDBID());
		
		$sql = self::addDBQueryUserInfo($sql);
		
		$res = db::query($sql);
		if($limit == -1){
		    return $res;
        }
			// echo "<!--".$sql->getQueryString()." -->";
			//echo "<!--";print_r($res);echo "-->";
		self::$topPicturesRowCount = count($res);
        pagination::setElementCount( count($res) );
        
		if(count($res) > 0){
			$snippet = self::buildStartSnippetBasedOnInfedScroll($isInfScroll);
		
			self::setTopContentLatestElement(0);
			
			return self::buildHTMLDOMTree($res,$snippet);
		} else 
			return new htmlTag(NULL);

		die();
	}
	private static function addDBQueryByUSERID(dbObj $sql, $int){
		$sql->setConditionIntegerEqual('userID', $int, DBTableNameContentAll, 'AND');
		
		return $sql;
	}
	private static function addDBQueryUserInfo(dbObj $sql){
		$databaseName = DBTableNameUser;
			
		$sql->setDatabase($databaseName);
		
		$sql->setDBonLeftJoinEqualToColumn('ID', $databaseName, 'userID', DBTableNameContentAll);
		
		$sql->setSELECTField('userURL', $databaseName);
		$sql->setSELECTField('nick', $databaseName);
		$sql->setSELECTField('avatarHTML', $databaseName);

		return $sql;
	}
	private static function addDBQueryTracks(dbObj $sql,$acts){
		if(!is_a($sql,'dbObj'))
			return NULL;
		if($acts === NULL || empty($acts) || !is_array($acts))
			return $sql;
			
		//$databaseTagIMG = 'match_images_tags';
		$databaseTagVID = 'content_videos_playlist';
		//$databaseIMG 	= 'content_images';
		$databaseVID 	= 'content_videos';
		$databaseName 	= DBTableNameContentAll;
		
		//$sql->setDatabase($databaseTagIMG);	
		$sql->setDatabase($databaseTagVID);
		
		//$sql->setConditionAnotherColumnEqual('imageID', $databaseName, 'imageID', $databaseTagIMG, 'AND' , 1);
		$sql->setConditionAnotherColumnEqual('videoID', $databaseName, 'videoID', $databaseTagVID, 'AND');
		
		//$sql->setConditionArrayIN('tagLink', $tags, $databaseTagIMG, 'AND' , 2);
		$sql->setConditionArrayIN('secondPart', $acts, $databaseTagVID, 'AND');
		
		$sql->setGROUPBYandHAVING_COUNT_DISTINCT('ID', DBTableNameContentAll, 'ID', DBTableNameContentAll, $acts);
		
		//$sql->setDBonLeftJoinEqualToColumn('imageID', 	$databaseTagIMG, 	'imageID', 	$databaseName);
		$sql->setDBonLeftJoinEqualToColumn('videoID', 	$databaseTagVID, 	'videoID', 	$databaseName);

		return $sql;
	}
	
	private static function addDBQueryActs($sql,$acts){
		if(!is_a($sql,'dbObj'))
			return NULL;
		if($acts === NULL || empty($acts) || !is_array($acts))
			return $sql;
			
		//$databaseTagIMG = 'match_images_tags';
		$databaseTagVID = 'content_videos_playlist';
		//$databaseIMG 	= 'content_images';
		$databaseVID 	= 'content_videos';
		$databaseName 	= DBTableNameContentAll;
		
		//$sql->setDatabase($databaseTagIMG);	
		$sql->setDatabase($databaseTagVID);
		
		//$sql->setConditionAnotherColumnEqual('imageID', $databaseName, 'imageID', $databaseTagIMG, 'AND' , 1);
		$sql->setConditionAnotherColumnEqual('videoID', $databaseName, 'videoID', $databaseTagVID, 'AND');
		
		//$sql->setConditionArrayIN('tagLink', $tags, $databaseTagIMG, 'AND' , 2);
		$sql->setConditionArrayIN('firstPart', $acts, $databaseTagVID, 'AND');
		
		$sql->setGROUPBYandHAVING_COUNT_DISTINCT('ID', DBTableNameContentAll, 'ID', DBTableNameContentAll, $acts);
		
		//$sql->setDBonLeftJoinEqualToColumn('imageID', 	$databaseTagIMG, 	'imageID', 	$databaseName);
		$sql->setDBonLeftJoinEqualToColumn('videoID', 	$databaseTagVID, 	'videoID', 	$databaseName);

		return $sql;
	}
	private static function addNonPrivateAndSaveMask($sql){
		$databaseName 	= DBTableNameContentAll;
		
		$sql->setConditionIntegerEqual('is_private', 0, $databaseName, 'AND');
		$sql->setConditionIntegerEqual('is_adult', 0, $databaseName, 'AND');
		
		return $sql;
	}
	private static function addDBQueryTags($sql,$tags){
		if(!is_a($sql,'dbObj'))
			return NULL;
		if($tags === NULL || empty($tags) || !is_array($tags))
			return $sql;
			
		$databaseCont 	= DBTableNameContentAll;
		$databaseName 	= DBTableNameContentMatchTags;
		
		$sql->setDatabase($databaseName);	
		
		$sql->setConditionStringEqual('tagLinkS', $tags[0], $databaseName, 'AND');
		
		$sql->setDBonLeftJoinEqualToColumn('contentID', $databaseName, 'contentID', $databaseCont);

		return $sql;
	}
	private static function addDBQueryLimitAndOffset($sql, $limit = 0, $page = 1){
		if(!is_a($sql,'dbObj'))
			return NULL;
		$offset = ($page - 1) * $limit;
		
		if($offset < 0)
			$offset = 0;

		if($limit < 0)
            $limit = 999999999999999999999999999999999999999999999999;

		$sql->setLimit((int) $limit, $offset);
		
		return $sql;
	}
	private static function buildHTMLDOMTree($res,$snippet){
		if(!isset($res[0]->type))
			return new htmlTag(NULL);
		
		foreach($res as $val){
			if($val->type == 'v' && !empty($val->contentID)){
				$element = new contentThumbnail($val);
				snippetLEGO::getOverviewVideoBox( $snippet, $element, $val );
			} else if($val->type == 'i' && !empty($val->contentID)){
				$element = new contentThumbnail($val);
				snippetLEGO::getOverviewImageBox( $snippet, $element, $val );
			} else if($val->type == 'c'){
				$element = new contentThumbnail($val);
				snippetLEGO::getOverviewCollectionBox( $snippet, $element, $val );
			} else {
				//print_r($val);	
				continue;
			}
		}

		return $snippet;
	}
	
	private static $topContentLatestElement = 0;
	private static function setTopContentLatestElement($i){
		self::$topContentLatestElement = $i;
	}
	private static function buildStartSnippetBasedOnInfedScroll($isInfScroll){
		if(!$isInfScroll)
			$snippet = new htmlTag('div', 'img-grid-box', ['grid-main','infinite-scroll']);
		else
			$snippet = new htmlTag('div', NULL, ['grid-main']);
		
		return $snippet;
	}
	private static function getDBQueryObjectContentVidAndMusic(){
		$databaseName 	= DBTableNameContentAll;
		// $databaseMSC	= DBTableNameContentImages;
		$databaseVid	= DBTableNameContentVideos;
		$databaseUser	= DBTableNameContentUser;
		
		$sql = new dbObj();
		$sql->setTypeSELECT();
		$sql->setDatabase($databaseName);
		// $sql->setDatabase($databaseMSC);
		$sql->setDatabase($databaseVid);
		$sql->setDatabase($databaseUser);
		
		$sql->setSELECTField('dateCreated', $databaseName);
		$sql->setSELECTField('shortTitle', $databaseName);
		// $sql->setSELECTField('title', $databaseMSC, 'imgTitle');
		$sql->setSELECTField('title', $databaseVid, 'vidTitle');
		
		// $sql->setSELECTField('imageID', $databaseMSC);
		$sql->setSELECTField('videoID', $databaseVid);
		$sql->setSELECTField('imageID', $databaseVid, 'vidImageID');
		
		// $sql->setSELECTField('link', $databaseMSC, 'imgLink');
		$sql->setSELECTField('link', $databaseVid, 'videoLink');
		/*$sql->setSELECTField('linkStored', $databaseIMG);
		$sql->setSELECTField('mime', $databaseMSC);
		$sql->setSELECTField('score', $databaseMSC);
		$sql->setSELECTField('linkFilename', $databaseMSC);
		$sql->setSELECTField('isMOZupdated', $databaseMSC);*/
		
		$sql->setSELECTField('duration', $databaseVid);
		
		$sql->setSELECTField('avatarHTML', $databaseUser);
		$sql->setSELECTField('nick', $databaseUser);
		$sql->setSELECTField('userURL', $databaseUser);
		$sql->setSELECTField('userID', $databaseName);
		
		// $sql->setDBonLeftJoinEqualToColumn('imageID', 	$databaseMSC, 	'imageID', 	$databaseName);
		$sql->setDBonLeftJoinEqualToColumn('videoID', 	$databaseVid, 	'videoID', 	$databaseName);
		$sql->setDBonLeftJoinEqualToColumn('ID', 		$databaseUser, 	'userID', 	$databaseName);
		
		$sql->setConditionIntegerEqual('private', 0, $databaseName);
		$sql->setConditionBooleanEqual('isNew', FALSE, $databaseName, 'AND');

		$sql->setOrderByField('dateCreated', $databaseName, FALSE);

		return $sql;
	}
	private static function getDBQueryObjectContentCollections(){		
		$databaseName = DBTableNameSearchContentCollections;
		$databaseName2 = DBTableNameContentAll;
		
		$sql = new dbObj();
		$sql->setTypeSELECT();
		//$sql->setDatabase( $databaseName );
		$sql->setDatabase( $databaseName2 );
		
		//$sql->setSELECTField('libraryListID', $databaseName);
		$sql->setSELECTField('ID', $databaseName2);
		$sql->setSELECTField('userID', $databaseName2);
		$sql->setSELECTField('type', $databaseName2);
		$sql->setSELECTField('title', $databaseName2);
		$sql->setSELECTField('link', $databaseName2);
		$sql->setSELECTField('shortTitle', $databaseName2);
		$sql->setSELECTField('dateCreated', $databaseName2);
		$sql->setSELECTField('is_private', $databaseName2);
		$sql->setSELECTField('is_adult', $databaseName2);
		$sql->setSELECTField('is_colUpload', $databaseName2);
		$sql->setSELECTField('contentID', $databaseName2);
		$sql->setSELECTField('thumbnailLink', $databaseName2);
		$sql->setSELECTField('mediaIn', $databaseName2);
		//$sql->setDBonLeftJoinEqualToColumn('ID', 	$databaseName2, 	'libraryListID', 	$databaseName);
		
		$sql->setConditionStringEqual('type', 'c', $databaseName2);
		$sql->setOrderByField('dateCreated', $databaseName2, FALSE);
		
		return $sql;
	}
	private static function getDBQueryObjectContentVideos(){
		$databaseName = DBTableNameSearchContentVideos;
		$databaseName2 = DBTableNameContentAll;
		
		$sql = new dbObj();
		$sql->setTypeSELECT();
		//$sql->setDatabase( $databaseName );
		$sql->setDatabase( $databaseName2 );
		
		//$sql->setSELECTField('libraryListID', $databaseName);
		$sql->setSELECTField('ID', $databaseName2);
		$sql->setSELECTField('userID', $databaseName2);
		$sql->setSELECTField('type', $databaseName2);
		$sql->setSELECTField('title', $databaseName2);
		$sql->setSELECTField('link', $databaseName2);
		$sql->setSELECTField('shortTitle', $databaseName2);
		$sql->setSELECTField('dateCreated', $databaseName2);
		$sql->setSELECTField('is_private', $databaseName2);
		$sql->setSELECTField('is_adult', $databaseName2);
		$sql->setSELECTField('is_colUpload', $databaseName2);
		$sql->setSELECTField('contentID', $databaseName2);
		$sql->setSELECTField('thumbnailLink', $databaseName2);
		//$sql->setDBonLeftJoinEqualToColumn('ID', 	$databaseName2, 	'libraryListID', 	$databaseName);
		
		$sql->setConditionStringEqual('type', 'v', $databaseName2);
		$sql->setOrderByField('dateCreated', $databaseName2, FALSE);
		
		return $sql;
	}
	private static function getDBQueryObjectContentImages(){	
		$databaseName = DBTableNameSearchContentImages;
		$databaseName2 = DBTableNameContentAll;
		
		$sql = new dbObj();
		$sql->setTypeSELECT();
		//$sql->setDatabase( $databaseName );
		$sql->setDatabase( $databaseName2 );
		
		//$sql->setSELECTField('libraryListID', $databaseName);
		$sql->setSELECTField('ID', $databaseName2);
		$sql->setSELECTField('userID', $databaseName2);
		$sql->setSELECTField('type', $databaseName2);
		$sql->setSELECTField('title', $databaseName2);
		$sql->setSELECTField('link', $databaseName2);
		$sql->setSELECTField('shortTitle', $databaseName2);
		$sql->setSELECTField('dateCreated', $databaseName2);
		$sql->setSELECTField('is_private', $databaseName2);
		$sql->setSELECTField('is_adult', $databaseName2);
		$sql->setSELECTField('is_colUpload', $databaseName2);
		$sql->setSELECTField('contentID', $databaseName2);
		$sql->setSELECTField('thumbnailLink', $databaseName2);
		//$sql->setDBonLeftJoinEqualToColumn('ID', 	$databaseName2, 	'libraryListID', 	$databaseName);
		
		$sql->setConditionStringEqual('type', 'i', $databaseName2);
		$sql->setOrderByField('dateCreated', $databaseName2, FALSE);
		
		return $sql;
	}
	public static function getDBQueryObjectContentALL( bool $isSpecial = false ){
        //$databaseName = DBTableNameSearchContentAll;
        $databaseName2 = DBTableNameContentAll;
        
        
		$sql = new dbObj();
		$sql->setTypeSELECT();
		// 
		$sql->setDatabase( $databaseName2 );
		if( $isSpecial ){
            $databaseName = DBTableNameSearchContentFrontpageAll;
			$sql->setDatabase( $databaseName );
			$sql->setDBonLeftJoinEqualToColumn('ID', 	$databaseName2, 	'libraryListID', 	$databaseName);
        }
		//$sql->setSELECTField('libraryListID', $databaseName);
		$sql->setSELECTField('ID', $databaseName2);
		$sql->setSELECTField('userID', $databaseName2);
		$sql->setSELECTField('type', $databaseName2);
		$sql->setSELECTField('title', $databaseName2);
		$sql->setSELECTField('link', $databaseName2);
		$sql->setSELECTField('shortTitle', $databaseName2);
		$sql->setSELECTField('dateCreated', $databaseName2);
		$sql->setSELECTField('is_private', $databaseName2);
		$sql->setSELECTField('is_adult', $databaseName2);
		$sql->setSELECTField('is_colUpload', $databaseName2);
		$sql->setSELECTField('contentID', $databaseName2);
		$sql->setSELECTField('thumbnailLink', $databaseName2);
		$sql->setSELECTField('mediaIn', $databaseName2);
		//
		
		$sql->setOrderByField('ID', $databaseName2, TRUE);
		
		return $sql;
	}
	public static function getTopPictures($limit , $tags = NULL, $timeStamp = NULL, $page = 1, $isInfScroll = FALSE){
		if(is_array($tags) && !empty($tags)){
			$tagsM = TRUE;
		} else if(is_string($tags) && strlen($tags) > 1){
			$tagsM = TRUE;
			$tags = array($tags);
		} else
			$tagsM = FALSE;
		
		$databaseName = 'content_images';	
		$databaseName2 = 'userBase';
		$databaseTag = 'match_images_tags';
			
		$sql = new dbObj();
		$sql->setTypeSELECT();
		$sql->setDatabase($databaseName);
		$sql->setDatabase($databaseName2);
		
		if($tagsM)
			$sql->setDatabase($databaseTag);
		
		$sql->setSELECTField('title', $databaseName);
		$sql->setSELECTField('link', $databaseName);
		$sql->setSELECTField('dimensionX', $databaseName);
		$sql->setSELECTField('dimensionY', $databaseName);
		$sql->setSELECTField('userID', $databaseName);
		$sql->setSELECTField('views', $databaseName);
		$sql->setSELECTField('imageID', $databaseName);
		$sql->setSELECTField('dateCreated', $databaseName);
		$sql->setSELECTField('votesUp', $databaseName);
		$sql->setSELECTField('votesDown', $databaseName);
		$sql->setSELECTField('score', $databaseName);
		$sql->setSELECTField('private', $databaseName);
		$sql->setSELECTField('linkStored', $databaseName);
		$sql->setSELECTField('linkFilename', $databaseName);
		$sql->setSELECTField('scoreReported', $databaseName);
		$sql->setSELECTField('isAdult', $databaseName);
		$sql->setSELECTField('comments', $databaseName);
		$sql->setSELECTField('hash', $databaseName);
		$sql->setSELECTField('mime', $databaseName);
		
		$sql->setSELECTField('avatarHTML', $databaseName2);
		$sql->setSELECTField('nick', $databaseName2);
		$sql->setSELECTField('userURL', $databaseName2);
		
		$sql->setSELECTField('isMOZupdated', $databaseName);
		
		$sql->setConditionAnotherColumnEqual('userID', $databaseName, 'ID', $databaseName2);
		$sql->setConditionBooleanEqual('isNew', FALSE, $databaseName, 'AND');
		$sql->setConditionIntegerEqual('private', 0, $databaseName, 'AND');
		
		if($timeStamp !== NULL && (int)$timeStamp > 1){
			$sql->setConditionDateTimeLower('dateCreated', ((int)$timeStamp)+1, $databaseName, 'AND');
		}
		
		$sql->setOrderByField('dateCreated', $databaseName, FALSE);
		
		$offset = ($page - 1) * $limit;
		
		if($offset < 0)
			$offset = 0;
		
		$sql->setLimit((int) $limit, $offset);

		if($tagsM){
			$sql->setConditionAnotherColumnEqual('imageID', $databaseName, 'imageID', $databaseTag, 'AND');	
			$sql->setConditionArrayIN('tagLink', $tags, $databaseTag, 'AND');
			$sql->setGROUPBYandHAVING_COUNT_DISTINCT('imageID', $databaseName, 'tagLink', $databaseTag, $tags);
			//echo $sql->getQueryString();
		}

		$res = db::query($sql);
		//echo $sql->getQueryString();
		self::$topPicturesRowCount = count($res);
		if(!isset($res[0]->userID))
			return new htmlTag(NULL);
		
		if(!$isInfScroll)
			$snippet = new htmlTag('div', 'img-grid-box', ['grid-main','infinite-scroll']);
		else
			$snippet = new htmlTag('div', NULL, ['grid-main']);
		
			
		
		self::$topPicturesLatestDate = 0;
		foreach($res as $val){
			$image = new contentThumbnail($val);	
			$date = $image->getTimeStamp();
			if($date > self::$topPicturesLatestDate)
				self::$topPicturesLatestDate = $date;
			
			$tagList = new htmlTag('ul', NULL, ['list-inline','tags-list']);
			$tL = $tagList->aList();
			
			$tags = array('TestT1', 'T2', 'TestT3', 'TIV');
			foreach($tags as $valT){
				$tL->_LIText('<span class="label label-default"><a href="tag-page">'.$valT.'</a></span>');
			}

			$snippet->_DIV(NULL, 'grid-main-item')
				->in()->_DIV(NULL, 'grid-main-item-in')
				->in()
				->_A('/'.LINK_imagePageSingle.'/'.$image->getLinkOfPictureSite(),NULL, 'global-link-white')
						->in()->_DIV(NULL, 'img-responsive')
							->in()->setAttr('style','background-image: url('.$image->getLinkHot().')')
							->outer()
							#->_DIV(NULL, ['img-type-of-crop'])
								#->in()
								#->setContent('<i class="fa fa-file-image-o"></i>')
							#->outer()
							->_DIV(NULL, ['img-info-field'])
									->in()
									->_A('/'.LINK_imagePageSingle.'/'.$image->getLinkOfPictureSite(),NULL, 'global-link-white')
										->in()
											->_DIV(NULL, 'grid-img-info-title')
											->in()
												->setContent('<div><h2 class="globalheadline font-size-90 ">'.$image->getTitle().'</h2></div>')
												->outer()
											->_A('/'.LINK_userPageSingle.'/'.$val->userURL)->in()
											->_P(NULL, 'img-info-user-name-string', $val->nick)
											->outer()
											->_P(NULL, 'img-info-time-posted', $image->getTimeStampHumanReadable());
		}
		
		return $snippet;
	}
	private static $topPicturesRowCount;
	public static function getTopPicturesRowCount(){
		return self::$topPicturesRowCount;
	}
	
	public static function getTopPicturesLatestDate(){
		if(self::$topPicturesLatestDate === NULL)
			return 0;
		else return self::$topPicturesLatestDate;
	}
	public static function getAmountOfFilesByAct($acts){	
		if($acts == -1){
			return 0;
		} else if(is_array($acts) ){
			$tagsM = TRUE;
		} else if(strlen($acts) > 1){
			$tagsM = TRUE;
			$acts = array($acts);
		} else
			$tagsM = FALSE;
		
		$databaseName = 'content_videos';	
		$databaseTag = 'content_videos_playlist';
			
		$sql = new dbObj();
		$sql->setTypeSELECT();
		$sql->setDatabase($databaseName);
		
		if($tagsM)
			$sql->setDatabase($databaseTag);
		
		$sql->setSELECTCount('title', $databaseName, 'ct');
		
		$sql->setConditionBooleanEqual('isNew', FALSE, $databaseName);
		$sql->setConditionIntegerEqual('private', 0, $databaseName, 'AND');

		if($tagsM){
			$sql->setConditionAnotherColumnEqual('videoID', $databaseName, 'videoID', $databaseTag, 'AND');	
			$sql->setConditionArrayIN('firstPart', $acts, $databaseTag, 'AND');
			$sql->setGROUPBYandHAVING_COUNT_DISTINCT('imageID', $databaseName, 'firstPart', $databaseTag, $acts);
		}

		$res = db::query($sql);
		//echo $sql->getQueryString();
		
		if(isset($res[0]->ct))
			return count($res);
		else
			return 'no countable ';
	}
	private static $tagArray;
	private static $lastTag = NULL;
	private static $lastTagCountString = '';
	public static function getAmountOfFilesByTag($tags, $genStr = NULL, $types = []){
		$s = 'no media';	
		if(is_array($tags))
			$tags = $tags[0];
		
		if(self::$lastTag == $tags)
			return self::$lastTagCountString;
		self::$lastTag = $tags;
		
		$databaseName = DBTableNameContentMatchTags;
		$databaseName2= DBTableNameContentAll;
		
		$sql = new dbObj();
		$sql->setTypeSELECT();
		$sql->setDatabase($databaseName);
		$sql->setDatabase($databaseName2);
		
		$sql->setSELECTCount('ID', $databaseName, 'c');
		
		$sql->setConditionStringEqual('tagLinkS', $tags, $databaseName);
		$sql->setConditionIntegerEqual('is_private', 0, $databaseName2, 'AND');
		$sql->setConditionIntegerEqual('is_adult', 0, $databaseName2, 'AND');
		$sql->setDBonLeftJoinEqualToColumn('contentID', $databaseName2, 'contentID', $databaseName);
		
		$res = db::query($sql);
		//print_r($res);
		
		if(isset($res[0]->c) && $res[0]->c > 0){
			$c = $res[0]->c;	
			if($c < 2)
				$s = '1 result';
			else {
				if($c < 50)
					$s = $c.' results';
				else {
					$k = -1*(int)(strlen($c)*0.5); $l = substr(-1*($k*10),1);
					$s = substr($c,0,$k).$l.'+ results';
				}
			}
		}
		self::$lastTagCountString = $s;
		return $s;
		
		$tagC = implode('',$tags);	
		if(isset(self::$tagArray[$tagC])){
			if($genStr !== NULL){
				if(self::$tagArray[$tagC] > 1)
					return self::$tagArray[$tagC].' '.$genStr.'s';
				else
					return self::$tagArray[$tagC].' '.$genStr;
			}	
			return self::$tagArray[$tagC];
		}
		
		if($tags == -1){
			return 0;
		} else if(is_array($tags) ){
			$tagsM = TRUE;
		} else if(strlen($tags) > 1){
			$tagsM = TRUE;
			$tags = array($tags);
		} else
			$tagsM = FALSE;
		
		$s = 0;
		
		if(is_array($types)){
			foreach($types as $val){
				switch($val){
					case 'i':
						$databaseName = 'content_images';	
						$databaseTag = 'match_images_tags';
						$valCol = 'imageID';
						break;
					case 'c':
						$databaseName = 'content_collections';	
						$databaseTag = 'match_collection_tags';
						$valCol = 'collectionID';
						break;
					case 'v':
						$databaseName = 'content_videos';	
						$databaseTag = 'match_videos_tags';
						$valCol = 'videoID';
						break;
					default:
						continue;
						break;
				}
				
					
				$sql = new dbObj();
				$sql->setTypeSELECT();
				$sql->setDatabase($databaseName);
				
				if($tagsM)
					$sql->setDatabase($databaseTag);
				
				$sql->setSELECTCount('ID', $databaseName, 'ct');
				
				if($val != 'c')
					$sql->setConditionBooleanEqual('isNew', FALSE, $databaseName);
		
				if($tagsM){
					$sql->setConditionAnotherColumnEqual($valCol, $databaseName, $valCol, $databaseTag, 'AND');	
					$sql->setConditionArrayIN('tagLink', $tags, $databaseTag, 'AND');
					$sql->setGROUPBYandHAVING_COUNT_DISTINCT($valCol, $databaseName, 'tagLink', $databaseTag, $tags);
				}
		
				$res = db::query($sql);
				//echo $sql->getQueryString()."\n";
				
				if(isset($res[0]->ct))
					$s += count($res);
			}
		}
		self::$tagArray[$tagC] = $s;
		
		if($genStr !== NULL){
			if($s > 1)
				$s .= ' '.$genStr.'s';
			else
				$s .= ' '.$genStr;
		}
			
		if($s < 1)
			$s = 'no countable ';
		
		return $s;
	}

	public static function getStringEmptyDescriptionSingle($title, $tags, $user, $content, $isInCollection = false){
		$tag = '';    

        $string = $content.' by '.$user;
        
        if(is_array($tags) && !empty($tags)){   
            $string .= ' - Tags: ';
            $string .= self::getStringTags($tags);
        }
        
        if( $isInCollection !== false && isset($isInCollection->title) )
          $string .= ' - in \''.$isInCollection->title.'\'';
            
        return $string ;//= $content.' by '.$user.' \''.$title.'\' '.$tag.' was posted by '.$user.' on '.brandingName;
	}
	public static function getStringTags($tags){
		if(!is_array($tags) && empty($tags))
			return '';

		$y = count($tags)-1; $z = $y - 1;
		$tag = '';
		for($i = 0; $i <= $y; $i++){
			if(is_array($tags[$i]))	
				$tag .= $tags[$i][0];
			else
				$tag .= $tags[$i]; 
			if($i != $z || $y == 0)
				$tag.=', ';
			else 
				$tag.=' and ';
		}
		$tag = substr($tag, 0, -2);
		
		return $tag;
	}
	public static function getAdvancedFilterDOMElements(){
		$u = core::getURLObj()->getPathArray();
		$isHome = '';
		if(isset($u[0]) && $u[0] == 't')
			$isHome = delimiterTagPage.LINK_tagPageSingle;
		
		$line = ''; $isActive = ['images'=>'','videos'=>'','gifs'=>'','articles'=>'','links'=>'','collections'=>'','all'=>' isActive'];
		if(count($u) > 0){
			if(count($u) > 1){	
				for($i = 1; $i < count($u); $i++){
					if(!in_array($u[$i], ['images','videos','gifs','articles','links','collections','act','track'])){	
						$line .= '/'.$u[$i];
					} else {
						$isActive[$u[$i]] = ' isActive';
						$isActive['all'] = '';
					}
				}
			} else {
				if(!in_array($u[0], ['images','videos','gifs','articles','links','collections','act','track'])){	
					//$line .= '/'.$u[0];
				} else {
					$isActive[$u[0]] = ' isActive';
					$isActive['all'] = '';
				}
			}
		}
		$element = new htmlTag('ul',NULL,'content-main-nav-ul');
		$element->in()
			->_LI(NULL,'inline-block')->in()->_A(HTTP_HOST.$isHome.$line,NULL,'content-main-nav-a'.$isActive['all'], 'All')->outer()->outer()
			->_LI(NULL,'inline-block')->in()->_A(HTTP_HOST.$isHome.delimiterTagPage.'images'.$line,NULL,'content-main-nav-a'.$isActive['images']	, 'Images')->outer()->outer()
			->_LI(NULL,'inline-block')->in()->_A(HTTP_HOST.$isHome.delimiterTagPage.'videos'.$line,NULL,'content-main-nav-a'.$isActive['videos']	, 'Videos')->outer()->outer()/*
			->_LI(NULL,'inline-block')->in()->_A(HTTP_HOST.delimiterTagPage.LINK_tagPageSingle.delimiterTagPage.'gifs'.$line,NULL,'content-main-nav-a'.$isActive['gifs']		, 'Gifs')->outer()->outer()
			->_LI(NULL,'inline-block')->in()->_A(HTTP_HOST.delimiterTagPage.LINK_tagPageSingle.delimiterTagPage.'articles'.$line,NULL,'content-main-nav-a'.$isActive['articles']	, 'Articles')->outer()->outer()
			->_LI(NULL,'inline-block')->in()->_A(HTTP_HOST.delimiterTagPage.LINK_tagPageSingle.delimiterTagPage.'links'.$line,NULL,'content-main-nav-a'.$isActive['links']		, 'Links')->outer()->outer()
			*/->_LI(NULL,'inline-block')->in()->_A(HTTP_HOST.$isHome.delimiterTagPage.'collections'.$line,NULL,'content-main-nav-a'.$isActive['collections'], 'Collections')->outer()->outer();
			/*'<ul class="content-main-nav-ul">
				<li class="inline-block"><a class="content-main-nav-a" href="/">All</a></li>
				<li class="inline-block"><a class="content-main-nav-a"  href="#">Images</a></li>
				<li class="inline-block"><a class="content-main-nav-a"  href="#">Videos</a></li>
				<li class="inline-block"><a class="content-main-nav-a"  href="#">Articles</a></li>
				<li class="inline-block"><a class="content-main-nav-a"  href="#">Links</a></li>
				<li class="inline-block"><a class="content-main-nav-a"  href="#">Collections</a></li>
				<!--
				<li class="inline-block"><a class="content-main-nav-a"  href="/">latest <i class="fa fa-caret-down" aria-hidden="true"></i></a></li>
				<li class="inline-block"><a class="content-main-nav-a"  href="/#">tools <i class="fa fa-caret-down" aria-hidden="true"></i></a></li>			
				-->
			</ul>';*/
		return $element;
	}
	public static function getAdvancedFilterDOMElementsUserPage(){
		$u = core::getURLObj()->getPathArray();
		$isHome = '';
		if(!isset($u[0]) || $u[0] != 'u')
			return new htmlTag(NULL);
		
		$line = ''; $isActive = ['images'=>'','videos'=>'','gifs'=>'','articles'=>'','links'=>'','collections'=>''];
		if(count($u) > 0){
			for($i = 1; $i < count($u); $i++){
				if(!in_array($u[$i], ['images','videos','gifs','articles','links','collections','act','track'])){
					$line .= '/'.$u[$i];
				} else {
					$isActive[$u[$i]] = ' isActive';
				}
			}
		}
		$element = new htmlTag('ul',NULL,'content-main-nav-ul');
		$element->in()
			->_LI(NULL,'inline-block')->in()->_A(HTTP_HOST.delimiterTagPage.'u'.$line,NULL,'content-main-nav-a', 'All')->outer()->outer()
			->_LI(NULL,'inline-block')->in()->_A(HTTP_HOST.delimiterTagPage.'u'.$line.delimiterTagPage.'images',NULL,'content-main-nav-a'.$isActive['images']	, 'Images')->outer()->outer()
			->_LI(NULL,'inline-block')->in()->_A(HTTP_HOST.delimiterTagPage.'u'.$line.delimiterTagPage.'videos',NULL,'content-main-nav-a'.$isActive['videos']	, 'Videos')->outer()->outer();
		return $element;
	}
	private static function getWindowDummy($inx = 1, $isToLoad = TRUE){
		$classes = ['window-container','size-60p'];
		if($isToLoad)
			$classes[] = 'toLoad';
		
		if($inx == 1){
			$classes[] = 'active';
			$classes[] = 'posCenter';
		} else {
			$classes[] = 'posCenter-'.($inx*5);
		}
		$window = new htmlTag('div','wct-'.$inx,$classes);
		$window->_DIV(NULL,['stretchbox','stretch-n'])
			->_DIV(NULL,['stretchbox','stretch-ne'])
			->_DIV(NULL,['stretchbox','stretch-e'])
			->_DIV(NULL,['stretchbox','stretch-se'])
			->_DIV(NULL,['stretchbox','stretch-s'])
			->_DIV(NULL,['stretchbox','stretch-sw'])
			->_DIV(NULL,['stretchbox','stretch-w'])
			->_DIV(NULL,['stretchbox','stretch-nw'])
			
			->_DIV(NULL,['window-viewport'])->in()
				->_DIV(NULL,['window-bar-top'])->in()
					->_DIV(NULL,['icon','isleft'])->in()
						->_I(NULL,'wvp-icon-'.$inx,['fa','fa-exclamation'])->outer()
					->_DIV(NULL,['title','isleft'])->in()
						->_P('wvp-text-'.$inx,NULL, '< a new window >')->outer()
					->_DIV(NULL,['buttons','isright'])->in()
						->_DIV(NULL,['minimize'])->in()
							->_I(NULL,NULL,['fa','fa-minus'])->outer()
						->_DIV(NULL,['maximize'])->in()
							->_I(NULL,NULL,['fa','fa-plus'])->outer()
						->_DIV(NULL,['close'])->in()
							->_I(NULL,NULL,['fa','fa-times'])->outer()->outer()->outer()
				->_DIV(NULL,['window-bar-nav'])->in()
					->_DIV(NULL,['buttons'])->in()
						->_DIV(NULL,['history-back'])->in()
							->_I(NULL,NULL,['fa','fa-arrow-left'])->outer()
						->_DIV(NULL,['history-forward'])->in()
							->_I(NULL,NULL,['fa','fa-arrow-right','not-possible'])->outer()->outer()
					->_DIV(NULL,['map-container','a-container'])->in()
						->_DIV('wbn-input-'.$inx,['input','isUrlZone'])
						->_DIV(NULL,['refresh','a-box','right-box'])->in()
							->_I(NULL,NULL,['fa','fa-repeat'])->outer()->outer()
					->_DIV(NULL,['search-container','a-container'])->in()
						->_DIV(NULL,['input'])->in()
							->_DIV(NULL,['inputBox'])->in()
								->_Input(NULL,'search-box','text','search','sBox-w1')->outer()->outer()
						->_DIV(NULL,['search','a-box','left-box'])->in()
							->_I(NULL,NULL,['fa','fa-search'])->outer()->outer()->outer()
				->_DIV('wwb-ct-'.$inx,['window-board'])->in()
					->_DIV('wwb-left-'.$inx,['column-left'])
					->_DIV('wwb-right-'.$inx,['column-right','niceScrollY'])
						->in()->_DIV(NULL, ['loading-animation','hidden'])
							->in()->_DIV(NULL, 'fullyCentered')
								->in()->_I(NULL,NULL,['fa','fa-circle-o-notch', 'fa-spin'])->outer()->outer()->outer()->outer()
				->_DIV('wct-wbb-'.$inx,['window-bar-bottom'])->in()
					->_DIV(NULL,['info-elements'])->in()
						->_P(NULL, NULL, 'loading elements&hellip;')->outer()
					->_DIV(NULL,['info-selected'])
					->_DIV(NULL ,['info-view-container'])->in()
						->_DIV(NULL,['view-list-detail'])->in()
							->_I(NULL, NULL, ['fa','fa-server'])->outer()
						->_DIV(NULL,['view-list-only'])->in()
							->_I(NULL, NULL, ['fa','fa-reorder'])->outer()
						->_DIV(NULL,['view-list-pictures','active'])->in()
							->_I(NULL, NULL, ['fa','fa-image'])->outer();
		return $window->reset();
	}

	public static function getWindowAccountInfo($inx = 1, userObj $user, $isPreloaded = FALSE){
		$title = 'Account Information';
		$diffUser = false;

		if($user->getDatabaseID() != user::getDBID())
			$diffUser = true;
		
		if($diffUser){
			$title .= ' of '.$user->getNickname();
		}		
		
		$window = snippet::getWindowDummy($inx, !$isPreloaded);
		
		$window->getElementByID('wct-'.$inx)->addClass('containerTypeConfig');
		$window->getElementByID('wvp-text-'.$inx)->setContentNew('Account Information');
		$window->getElementByID('wvp-icon-'.$inx)->setIcon('cogs');
		$window->getElementByID('wbn-input-'.$inx)->in()
			->_P(NULL, NULL, 'Settings')->in()->setAttr('data-url','settings')->outer()
			->_P(NULL, NULL)->in()->_I(NULL, NULL, ['fa','fa-angle-right'])->outer()
			->_P(NULL, NULL, 'Account')->in()->setAttr('data-url','account');
			
		if($diffUser){
			$window->getElementByID('wbn-input-'.$inx)->in()
				->_P(NULL, NULL)->in()->_I(NULL, NULL, ['fa','fa-angle-right'])->outer()
				->_P(NULL, NULL, $user->getNickname())->in()->setAttr('data-url','account');
		}		
		
		$window->getElementByID('wct-wbb-'.$inx)->remove();			
		$window->getElementByID('wwb-ct-'.$inx)->addClass('columnSingle');
		$window->getElementByID('wwb-right-'.$inx)
			->_H(3,NULL,'content-info-headline')->in()->setContentNew('Change your account data &amp; information')->outer()
			->_DIV(NULL,'content-container')->in()
				->_DIV()->in()
					->_H(5, NULL, 'headline')->in()->setContentNew('General Information')->outer()
					->_HR(NULL, 'hr')->outer()
				->_DIV(NULL, 'sub')->in()
					->_DIV(NULL,'field-row')->in()
						->_P(NULL,'info','Nickname')->_DIV(NULL, 'container-input')->in()->_Input(NULL, 'input-text','text',NULL,'userNickname')->in()->setAttr('value',$user->getNickname())->outer()->outer()->outer()
					->_DIV(NULL,'field-row')->in()
						->_P(NULL,'info','First Name')->_DIV(NULL, 'container-input')->in()->_Input(NULL, 'input-text','text',NULL,'userFirstname')->in()->setAttr('value','Martin')->outer()->outer()->outer()
					->_DIV(NULL,'field-row')->in()
						->_P(NULL,'info','Last Name')->_DIV(NULL, 'container-input')->in()->_Input(NULL, 'input-text','text',NULL,'userLastname')->in()->setAttr('value','Consades')->outer()->outer()->outer()
					->_DIV(NULL,'field-row')->in()
						->_P(NULL,'info','Localization')->_DIV(NULL, 'container-input')->in()->_Input(NULL, 'input-text','text',NULL,'userLocale')->in()->setAttr('value','de_DE')->outer()->outer()->outer()->outer()
			->_DIV()->in()
				->_H(5, NULL, 'headline')->in()->setContent('Avatar Settings')->outer()
				->_HR(NULL, 'hr')->outer()
			->_DIV(NULL, 'sub')->in()
				->_DIV(NULL, 'avatar-pic-box')->in()
					->_DIV(NULL, ['avatar-builder','avatar-full'])->in()->setStyle('background-image', 'url(\''.$user->getAvatarURL().'\')')->outer()
					->_DIV(NULL, ['avatar-builder','avatar-medium'])->in()->setStyle('background-image', 'url(\''.$user->getAvatarURL().'\')')->outer()
					->_DIV(NULL, ['avatar-builder', 'avatar-mini'])->in()->setStyle('background-image', 'url(\''.$user->getAvatarURL().'\')')->outer()->outer()
				->_DIV(NULL, 'buttons')->in()
					->_DIV(NULL,['media-box-upload'])
					->_DIV(NULL,['media-box-recent'])->outer()->outer()
			->_DIV()->in()
				->_H(5, NULL, 'headline')->in()->setContent('User Profile Settings')->outer()
				->_HR(NULL, 'hr')->outer()
			->_DIV(NULL, 'sub')->in()
				->_DIV(NULL, 'profile-pic-builder')->in()
					->_DIV(NULL,'field-row')->in()
						->_P(NULL,'info','Profile-URL')->_SPAN('https://creezi.com/u/')->in()->_Input(NULL, 'input-text','text',$user->getUserURL(),'userUrl')->outer()->outer()
				->_DIV(NULL, 'buttons');
		return $window;
	}

	private static function getExplorerTreeViewNew($inx = 1, userObj $user){
		$html = new htmlTag('div',NULL,'windowFolderCt');
		
		$html->_DIV('fastAccess-'.$inx,'containerFolderEntry')->setAttr('data-linkHard','/usr/'.$user->getUserURL().'/settings/fastaccess')->in()->_P(NULL, 'iconFolder')->in()->_I(NULL,NULL,['fa','fa-star'])->outer()->_P(NULL,'textFolder','fast access')->outer()
			->_DIV(NULL, 'containerFolder')->in()
				->_DIV(NULL,'containerFolderEntry')->in()->setAttr('data-linkHard','/usr/'.$user->getUserURL().'/data/uploads/untagged')->_P(NULL, 'iconFolder')->in()->_I(NULL,NULL,['fa','fa-tags'])->outer()->_P(NULL,'textFolder','untagged content')->outer()
				->_DIV(NULL,'containerFolderEntry')->in()->setAttr('data-linkHard','/usr/'.$user->getUserURL().'/data/uploads/untitled')->_P(NULL, 'iconFolder')->in()->_I(NULL,NULL,['fa','fa-header'])->outer()->_P(NULL,'textFolder','untitled content')->outer()
				->_DIV(NULL,'containerFolderEntry')->in()->setAttr('data-linkHard','/usr/'.$user->getUserURL().'/data/uploads/private')->_P(NULL, 'iconFolder')->in()->_I(NULL,NULL,['fa','fa-eye-slash'])->outer()->_P(NULL,'textFolder','private content')->outer()
				->_DIV(NULL,'containerFolderEntry')->in()->setAttr('data-linkHard','/usr/'.$user->getUserURL().'/deleted')->_P(NULL, 'iconFolder')->in()->_I(NULL,NULL,['fa','fa-trash'])->outer()->_P(NULL,'textFolder','deleted content')->outer();
		
		return $html;
	}

	public static function getWindowExplorer($inx = 1, userObj $user){
		$title = 'Account Information';
		$diffUser = false;
			
		$window = snippet::getWindowDummy($inx);
		$window->getElementByID('wvp-text-'.$inx)->setContentNew('My untagged media');
		$window->getElementByID('wvp-icon-'.$inx)->setIcon('folder-open');
		$window->getElementByID('wbn-input-'.$inx)->in()->setAttr('data-linkHard','/usr/'.$user->getUserURL().'/data/uploads/untagged')
			->_P(NULL, NULL, 'Data')->in()->setAttr('data-url','settings')->outer()
			->_P(NULL, NULL)->in()->_I(NULL, NULL, ['fa','fa-angle-right'])->outer()
			->_P(NULL, NULL, 'My Uploads')->in()->setAttr('data-url','settings')->outer()
			->_P(NULL, NULL)->in()->_I(NULL, NULL, ['fa','fa-angle-right'])->outer()
			->_P(NULL, NULL, 'Untagged Media')->in()->setAttr('data-url','account');
		
		$window->getElementByID('wwb-left-'.$inx)->addElement( self::getExplorerTreeViewNew($inx, $user) );
			
		return $window;
	}
}


?>