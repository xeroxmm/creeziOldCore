<?php

class contentTag {
	private $ID;
	private $label;
	private $link;
	private $score;
	private $usedNumber;
	
	public function getAllTagsAsString(){
		$tags = dbQueries::get()->allTagLinkS();
		$string = ',';	
		if(isset($tags[0])){
			foreach($tags as $val){
				$string .= $val->tagLinkS.',';
			}
		}
		return trim($string, ',');
	}
}

?>