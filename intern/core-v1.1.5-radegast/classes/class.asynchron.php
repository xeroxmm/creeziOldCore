<?php
/*
post_async('http:://localhost/batch/myjob.php', $params);

/*
 * Executes a PHP page asynchronously so the current page does not have to wait for it to     finish running.
 *  
 */
 /*
function post_async($url, array $params)
{
    foreach ($params as $key => &$val) {
      if (is_array($val)) $val = implode(',', $val);
        $post_params[] = $key.'='.urlencode($val);  
    }
    $post_string = implode('&', $post_params);

    $parts=parse_url($url);

    $fp = fsockopen($parts['host'],
        isset($parts['port'])?$parts['port']:80,
        $errno, $errstr, 30);

    $out = "POST ".$parts['path']." HTTP/1.1\r\n";
    $out.= "Host: ".$parts['host']."\r\n";
    $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
    $out.= "Content-Length: ".strlen($post_string)."\r\n";
    $out.= "Connection: Close\r\n\r\n";
    if (isset($post_string)) $out.= $post_string;

    fwrite($fp, $out);
    fclose($fp);
} */
class asynchronCall {
	private $status = FALSE;
	private $url = NULL;
	
	function __construct(){
		
	}
	public function scrapeVideoInformation($videoID, $contentID, $userID){
		$this->url = 'https://creezi.com/'.LINK_CRONJOB.'/scrapeVideo';
		
		$params = [$videoID, $contentID, $userID, md5($videoID.rand(0,9))];
		
		return $this->doCall($params);
	}
	public function doUpdateRelatedContentItems($contentID, $type){
		$this->url = 'https://localhost/'.LINK_CRONJOB.'/updatecontent';
		
		$params = [$contentID,md5($contentID.rand(0,9))];
		
		return $this->doCall($params);
	}
	
	public function simplePOSTCall($url, $getArray_keyName_value){
		if(!is_array($getArray_keyName_value))
			return FALSE;
		
		$this->url = $url;
		
		return $this->doCall($getArray_keyName_value);
	}
	
	private function doCall($params){
		$post_params = [];	
		foreach ($params as $key => $val) {
	    	if(is_array($val)) 
	    		$val = implode(',', $val);
	    	
	    	$post_params[] = $key.'='.urlencode($val);  
	    }
	    $post_string = implode('&', $post_params);
		$content = http_build_query($params);
		
	    $parts = parse_url($this->url);
		$port = isset($parts['port']) ? $parts['port']: 80;
	   	$port = (isset($parts['scheme']) && $parts['scheme'] == 'https') ? 443 : $port;

		if($port == 443)
			$pre = 'ssl://';
		else
			$pre = '';
		//print_r($parts); echo $port; die();
	    $fp = @fsockopen($pre.$parts['host'], $port, $errno, $errstr, 30);
	
		if(!$fp){
			//echo $errstr;	
			return FALSE;
		}
	
	    $out = "POST ".$parts['path']." HTTP/1.0\r\n";
	    $out.= "Host: ".$parts['host']."\r\n";
	    $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
	    $out.= "Content-Length: ".strlen($content)."\r\n";
	    $out.= "Connection: Close\r\n\r\n";
	    
	    if(isset($content)) 
	    	$out.= $content;
	
	    fwrite($fp, $out);
		/*
		// read response
		    while (!feof($fp)) {
		        echo fgets($fp, 128);
		    }
		*/
	    fclose($fp);
		
		return TRUE;
	}
}
 