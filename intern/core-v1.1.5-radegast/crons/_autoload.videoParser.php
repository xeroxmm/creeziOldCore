<?php
    // add videoLoad to cronJob Hook
    cronjobHook::registerHook('scrapeVideo', 'scrapeVideoClass::start');
	echo 'registered';
	
	class scrapeVideoClass{
		private static $videoID = 0;
		private static $contentID = 0;
		private static $user = 0;
		private static $res = NULL;
		private static $resContent = NULL;
		
		public static function start(){
			if(isset($_POST[0]) && isset($_POST[1]) && isset($_POST[2]) && (int)$_POST[2] > 0 && (int)$_POST[0] > 0 && (int)$_POST[1] > 0){
				self::$videoID = (int)$_POST[0];
				self::$contentID = (int)$_POST[1];
				self::$user = (int)$_POST[2];
				
				self::startScraping();
			}
		}
		private static function startScraping(){
			self::$res = dbQueries::get()->videoInformationByVideoID(self::$videoID);
			self::$resContent = dbQueries::get()->contentInformationByContentID(self::$contentID);
			
			/*self::$res = [0=>(object)[]];
			self::$res[0]->hoster = 'youtube';
			self::$res[0]->userID = 1;
			self::$res[0]->specificHosterID = 'iGmVQlATHkk';
			*/
			if(
				isset(self::$res[0]->hoster) && self::$res[0]->userID == self::$user &&
				isset(self::$resContent[0]->userID) && self::$resContent[0]->userID == self::$user
			){
				self::startScrapingHTML();
			} else {
				echo "scraping not available...";
				print_r(self::$res);
				print_r(self::$resContent);
			}
		}
		private static function startScrapingHTML(){
			switch(self::$res[0]->hoster){
				case 'youtube':
					$yt = new crawlYoutubeVideo(self::$res[0]->specificHosterID);
					$yt->setUserID(self::$res[0]->userID);
					//$yt->buildDBEntries(self::$videoID);
					
				// Fill Video Src List Entries
					dbQueries::change()->videoSrcInformationByObject(self::$videoID,$yt);	
					
				// Fill Dummy Content Post Entry
					if(empty(self::$resContent[0]->title))
						dbQueries::change()->contentInformationComplete(self::$resContent[0]->contentID, $yt->officialName, $yt->officialTags, '', 0, 0);
				
				// Fill the Match Elements Table
				// Add item to matchElementsTable
					if(!dbQueries::add()->elementToMatchItems(self::$resContent[0]->contentID, [(object)['type' => 'vs', 'contentID' => self::$resContent[0]->contentID,'videoID' => self::$videoID, 'ID' => self::$resContent[0]->contentID]]))
						echo "error building matching table!\n";

				// Add to searchable Database
				// Add this video to video search table
						dbQueries::add()->elementToSearchTableVideos(self::$resContent[0]->ID);
						dbQueries::add()->libraryElementToSearch_contentVideosFrontpage(self::$resContent[0]->ID);
						
				// scrape the official image
					if(empty(self::$resContent[0]->thumbnailLink)){
						$picS = new crawlImage($yt->officialThumbimage);
						$_FILES['file'] = $picS->getLastImageAsTempFile();
						$picS->deleteLastImage();
						
						$meta = @getimagesize($_FILES['file']['tmp_name']);
						
						if(!isset($meta[0])){
							api::getTemplateObject()->addError('no meta available -> url: '.urldecode(urldecode($data->url)));		
							dbQueries::add()->errorAPIImageCrawl(
								security::getUserObject()->getDatabaseIDCloaked(), 
								101, 
								'no image object -> '.urldecode(urldecode($data->url)), 
								$data->url 
							);
							continue;
						}
						$picUp = new uploadImage( TRUE );
						
						$yt = false;
						if(isset($picS))
							$yt = $picS->getYoutubeObj();
						
						if($picUp->process($yt)){
							dbQueries::change()->contentInformationThumbUrl(self::$resContent[0]->contentID, $picUp->getThumbStoreURL());
						}
					}
					break;
			}
		}
	}
?>