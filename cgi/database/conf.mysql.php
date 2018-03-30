<?php
	/**
	 * MySQL settings of Adsocials, like:
	 * 
	 * - Local Storage Variables
	 * - Static core variables
	 *  
	 * @package Adsocials
	 */
	 
	define('DB_NAME_2', 'c2311-urkraft');
	define('DB_NAME', 'core2311-radegast');
	define('DB_USER', 'root');
	define('DB_PASSWORD', 'gjgjAdk4324__fdsfsd11');
	define('DB_HOST', 'localhost');
	define('DB_PORT', 3306);
	define('DB_CHARSET', 'utf8mb4');
	define('DB_COLLATE', '');
	
	define('DBTableNameContentImages','content_images');
	define('DBTableNameContentTag','content_libraryTags');
	define('DBTableNameContentMatchTags', 'content_matchTags');
	define('DBTableNameContentMatchTagsID', 'content_matchTagsID');
	define('DBTableNameMatchImagesTags','match_images_tags');
	define('DBTableNameImageMetaInfo','content_images_meta');
	define('DBTableNameContentUser','userBase');
	
	define('DBTableNameContentVideos', 'content_videos');
	define('DBTableNameContentVideosComments', 'content_videos_comments');
	define('DBTableNameContentVideosIDList', 'content_videos_IDList');
	define('DBTableNameContentVideosTagsOnly', 'content_videos_tag');
	define('DBTableNameContentVideosTagsMatch', 'match_videos_tags');
	define('DBTableNameContentVideosPlaylist' , 'content_videos_playlist');
	define('DBTableNameContentAll' , 'content_libraryList');
	define('DBTableNameContentAllID' , 'content_libraryListID');
	
	define('DBTableNameUser', 'security_userBaseInfo');
	define('DBTableNameUserCookies', 'security_userCookies');
	define('DBTableNameUserNetworkSteam', 'security_userNetworkSteam');
	define('DBTableNameUserAPI' , 'security_userNetworkApi');
	
	define('DBTableNameSrcListID', 'content_srcListID');
	define('DBTableNameSrcImages', 'content_srcImages');
	define('DBTableNameSrcVideos', 'content_srcVideos');
	define('DBTableNameSrcListIDText', 'content_srcListIDText');
	define('DBTableNameSrcUserList', 'content_srcUserList');
	define('DBTableNameSrcThumbnails', 'content_srcThumbnails');
	define('DBTableNameSrcImagesThumbnails', 'content_matchTagsImages');
	define('DBTableNameSrcText', 'content_srcText');
	define('DBTableNameSrcOrigin', 'content_srcOrigin');
	
	define('DBTableNameSearchContentImages', 'search_contentImages');
	define('DBTableNameSearchContentVideos', 'search_contentVideos');
	define('DBTableNameSearchContentAll', 'search_contentAll');
	define('DBTableNameSearchContentCollections', 'search_contentCollections');
	
    define('DBTableNameSearchContentFrontpageAll', 'search_contentAll_new');
    define('DBTableNameSearchContentFrontpageImages', 'search_contentImages_new');
	define('DBTableNameSearchContentFrontpageVideos', 'search_contentVideos_new');
    define('DBTableNameSearchContentFrontpageCollections', 'search_contentCollections_new');
    
	define('DBTableNameMatchElements', 'content_matchElements');
	define('DBTableNameAITagged', 'ai_tags');
	
	define('DBTableNameErrorAPICrawlImage', 'error_apiImageCrawl');
	define('DBTableNameMetaImageFingerprints', 'info_images_fingerprint');
	define('DBTableNameMetaImageStats', 'info_images_meta');
	
	define('contentTypeVideoYoutube', 1);
	
	define('contentTypeImageHosted', 2);
?>