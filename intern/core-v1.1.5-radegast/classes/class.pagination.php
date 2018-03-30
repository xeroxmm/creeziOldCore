<?php 
class pagination {
	private static $isSetAsNoFollow = FALSE;
	private static $collectionObject = NULL;
    private static $elementCount = 0;
    
	private static function setNofollow(){
		if(!self::$isSetAsNoFollow)	{
			self::$isSetAsNoFollow = TRUE;
			html::head()->selectMeta()->setContentNofollow();
		}
	}
    public static function setElementCount( int $i ){
        self::$elementCount = $i;
    }
    public static function getElementCount(){
        return self::$elementCount;
    }	
	public static function isPagination(){
		if(isset($_GET['js']) && isset($_GET['page']))
			return TRUE;
		
		return FALSE;
	}
	public static function getPage(){
		if(isset($_GET['page']))
			return (int)$_GET['page'];
		else
			return 1;
	}
	public static function getNextPage(){
		if(isset($_GET['page']))
			return ((int)$_GET['page'] + 1);
		else
			return 2;
	}
	public static function getSortType(){
		if(!isset($_GET['sort']))
			return NULL;
		else 
			return $_GET['sort'];
	}
	public static function getSortTypeQueryString($amp = ''){
		if(!isset($_GET['sort']))
			return NULL;
		else 
			return $amp.'sort='.$_GET['sort'];
	}
	public static function getTimeStamp(){
		if(!isset($_GET['filter']))
			return 0;
		else return ((int)$_GET['filter']);
	}
	public static function getCollectionObject(){
		return self::$collectionObject;
	}
	public static function setAsPaginationAnker(){
		self::checkForNoIndex();
		if( pagination::isPagination() ){
			$tags = NULL;
			
			if( core::getURLObj()->isTagPage() ){
				$urlObj = core::getURLObj()->getPathArray();
				$tagName = $urlObj[count($urlObj)-1];
				
				$tags = security::getTagArrayFromRequestString($urlObj);
			}
			if(core::getURLObj()->isCollectionPage()){
				// echo snippet::getContentPaginated( 20,pagination::getTimeStamp(), pagination::getPage(), TRUE )->broadcastAsString();
				if(self::$collectionObject === NULL){
					self::$collectionObject = (object)['contentID' => 0, 'limit' => 24, 'timeStamp' => pagination::getTimeStamp(), 'page' => pagination::getPage(), 'infScroll' => TRUE];
				}
				return;
			} else 
				echo snippet::getTopContent( 20,$tags, pagination::getTimeStamp(), pagination::getPage(), TRUE )->broadcastAsString();
			
			if(snippet::getTopPicturesRowCount() < 1)
				exit();
			
			echo self::getPaginationNextLink();
			
			exit();
		} else if(isset($_GET['page'])){
			if(core::getURLObj()->isCollectionPage()){
				// echo snippet::getContentPaginated( 20,pagination::getTimeStamp(), pagination::getPage(), TRUE )->broadcastAsString();
				if(self::$collectionObject === NULL){
					self::$collectionObject = (object)['contentID' => 0, 'limit' => 24, 'timeStamp' => pagination::getTimeStamp(), 'page' => pagination::getPage(), 'infScroll' => FALSE];
				}
				// print_r(self::$collectionObject);
			}
		}
	}
	public static function getPaginationNextLink(){
		$linkS = core::getURLObj()->getRequestedURL().'?page='.pagination::getNextPage().pagination::getSortTypeQueryString('&').'&filter='.pagination::getTimeStamp().'&js';
		return '<div><a href="'.$linkS.'" class="nextPagionationLink" style="display:none" rel="noindex, follow">Load next items</a></div>';
	}
	private static function checkForNoIndex(){
		if( isset($_GET['page']) ){
			self::setNofollow();
		}
	}
}
?>