<?php
class picture {
	private $type = 0;
	private $path = '';
	
	private $img = NULL;
	private $imgNew = NULL;
	private $status = FALSE;
	
	private $mimeNew = NULL;
	private $mimeSrc = NULL;
	private $mimeCreateFrom = NULL;
	
	private $widthSrc = 0;
	private $heightSrc = 0;
	
	private $widthNew = 0;
	private $heightNew = 0;
	
	private $error = 'NONE';
	
	private $paletteSubImageCounter = 12;
	private $paletteColors = [];
	private $paletteColorsMain = [];
	private $paletteGranularity = 25;
	private $paletteColorFilterFactHex = 0x13;
	private $paletteColorCount = 25;
	
	private $paletteSubCluster = []; 
	private $paletteSubClusterCounter = 26; 
	
	
	
	function __construct(string $path){
		$this->path = $path;
	}
	function __destruct(){
		@imagedestroy($this->imgNew);
		$this->imgNew = NULL;
	}
	public function getPath(){
		return $this->path;
	}
	public function getResX(){
		return $this->widthSrc;
	}
	public function getResY(){
		return $this->heightSrc;
	}
	public function getSize(){
		return (int)@filesize($this->path);
	}
	public function buildThumbnail(int $x, int $y){
		$this->widthNew = $x;
		$this->heightNew = $y;
		
		$this->loadPictureMeta();
		$this->resizePicture();
		
	}
	public function getFingerPrintSum(){
		$sum = 0;	
		for($i = 0; $i < $this->widthNew; $i++){
			for($j = 0; $j < $this->heightNew; $j++){
				$rgb = imagecolorat($this->imgNew, $i, $j);
				$r = ($rgb >> 16) & 0xFF;
				$g = ($rgb >> 8) & 0xFF;
				$b = $rgb & 0xFF;
				
				$sum += ($r+$g+$b);
			}
		}
		return $sum;
	}
	private function resizePicture(){
		if(!file_exists($this->path)){
			$this->error = 'no file';
			return;
		}

		if(!$this->status){
			$this->error = 'no valid pic';
			return FALSE;
		}
		$method = $this->mimeCreateFrom;

		if(is_callable($method))
		    $imgSrc  = $method($this->path);

		@imagedestroy($this->imgNew);
		$this->imgNew = NULL;
		$this->imgNew = imagecreatetruecolor ( $this->widthNew, $this->heightNew );
		
		if(!imagecopyresampled($this->imgNew, $imgSrc, 0, 0, 0, 0, $this->widthNew, $this->heightNew, $this->widthSrc, $this->heightSrc)){
			$this->error = 'resampling failed';
			return;	
		}
	}
	public function printPicture(){
		header('Content-Type: image/png');

		imagepng($this->imgNew);
		imagedestroy($this->imgNew);

		exit();
	}
	public function getErrorString(){
		return $this->error;
	}
	public function getFingerPrintExactDEC(){
		$fPrint = array_fill(0,$this->widthNew,[]);	
		for($i = 0; $i < $this->widthNew; $i++){
			for($j = 0; $j < $this->heightNew; $j++){
				$rgb = imagecolorat($this->imgNew, $i, $j);
				$r = ($rgb >> 16) & 0xFF;
				$g = ($rgb >> 8) & 0xFF;
				$b = $rgb & 0xFF;
				
				$fPrint[$i][$j] = [$r,$g,$b];
			}
		}
		return $fPrint;
	}
	public function getFingerPrintExactHEX(){
		$fPrint = array_fill(0,$this->widthNew,[]);	
		for($i = 0; $i < $this->widthNew; $i++){
			for($j = 0; $j < $this->heightNew; $j++){
				$rgb = imagecolorat($this->imgNew, $i, $j);
				$r = ($rgb >> 16) & 0xFF;
				$g = ($rgb >> 8) & 0xFF;
				$b = $rgb & 0xFF;
				
				$fPrint[$i][$j] = [sprintf("%02s",base_convert($r, 10, 16)),sprintf("%02s",base_convert($g, 10, 16)),sprintf("%02s",base_convert($b, 10, 16))];
			}
		}
		$fPrintString = '';
		foreach($fPrint as $val){
			foreach($val as $pixel){
				$fPrintString .= implode('',$pixel);
			}
		}
		return $fPrintString;
	}
	public function getFingerPrintColours(){
		$this->paletteGranularity;
		$mapGranularity = 3;
		
		$widthCap = (int)(($this->widthNew + $mapGranularity + 1) / $mapGranularity);
		$heightCap = (int)(($this->heightNew  + $mapGranularity + 1) / $mapGranularity);
		
		$fPrint = [];	$corner = [];	
		for($i = 0; $i < $this->widthNew; $i++){
			$wK = (int)($i/$widthCap);	
			for($j = 0; $j < $this->heightNew; $j++){
				$rgb = imagecolorat($this->imgNew, $i, $j);
				$r = ((int)((($rgb >> 16) & 0xFF) / $this->paletteGranularity)) * $this->paletteGranularity;
				$g = ((int)((($rgb >> 8) & 0xFF) / $this->paletteGranularity)) * $this->paletteGranularity;;
				$b = ((int)(($rgb & 0xFF) / $this->paletteGranularity)) * $this->paletteGranularity;;
				
				$key = sprintf("%02s",base_convert($r, 10, 16)).sprintf("%02s",base_convert($g, 10, 16)).sprintf("%02s",base_convert($b, 10, 16));
				
				$hK = (int)($j/$heightCap);
				
				if(!isset($corner[$wK])){
					$corner[$wK] = [];
				}
				if(!isset($corner[$wK][$hK])){
					$corner[$wK][$hK] = [];
				}
				
				if(!isset($corner[$wK][$hK][$key]))
					$corner[$wK][$hK][$key] = 0;
				
				$corner[$wK][$hK][$key]++;
			}
		}

		for($i = 0; $i < count($corner); $i++){
			for($j = 0; $j < count($corner[$i]); $j++){
				arsort($corner[$i][$j]);
			}
		}
		
		$square = $mapGranularity*$mapGranularity;
		for($z = 0; $z < $square; $z++){
			for($i = 0; $i < count($corner); $i++){
				for($j = 0; $j < count($corner[$i]); $j++){
					if(count($fPrint) >= $square)
						break 3;	
					foreach($corner[$i][$j] as $key => $num){
						if(!isset($fPrint[$key])){
							$fPrint[$key] = $num;
							break;
						}
					}
				}
			}
		}
		
		$s = '';
		
		foreach($fPrint as $key => $val){
			$s .= $key; 	
		}
		
		return $s;
	}
	private function loadPictureMeta(){
		$imgsize = getimagesize($this->path);
	    $this->mimeSrc = $imgsize['mime'];
	 	$this->widthSrc = $imgsize[0];
		$this->heightSrc= $imgsize[1];

	 	$this->status = TRUE;
	 
	    switch($this->mimeSrc){
	        case 'image/gif':
	            $this->mimeCreateFrom = "imagecreatefromgif";
	            
	            break;
	 
	        case 'image/png':
	            $this->mimeCreateFrom = "imagecreatefrompng";
	            
	            break;
	 
	        case 'image/jpeg':
	            $this->mimeCreateFrom = "imagecreatefromjpeg";
	            
	            break;

	        default:
	            $this->status = FALSE;
	            break;
	    }
		return $this->status;
	}
	
	private function loadPaletteInformation(){
		$method = $this->mimeCreateFrom;

		if(is_callable($method))
		    $this->img = @$method($this->path);
		
		if(!$this->img){
			$this->status = FALSE;
			return $this->status;
		}
		
		$w = (int)($this->width  / $this->paletteSubImageCounter) + 1;
		$h = (int)($this->height / $this->paletteSubImageCounter) + 1;
		
		for($y = 0; $y < $this->height; $y += $this->paletteGranularity){
	    	$m = (int)($y/$h);
			if(!isset($this->paletteColors[$m]))
	        	$this->paletteColors[$m] = [];
		   
	    	for($x = 0; $x < $this->width; $x += $this->paletteGranularity) {
				$n = (int)($x/$w);
				
				if(!isset($this->paletteColors[$m][$n])) 
	         		$this->paletteColors[$m][$n] = [];
				
				$thisColor = imagecolorat($this->img, $x, $y); 
				$rgb = imagecolorsforindex($this->img, $thisColor); 
				
				$red = round(round(($rgb['red'] 	/ $this->paletteColorFilterFactHex)) 	* $this->paletteColorFilterFactHex); 
				$green = round(round(($rgb['green'] / $this->paletteColorFilterFactHex)) 	* $this->paletteColorFilterFactHex); 
				$blue = round(round(($rgb['blue'] 	/ $this->paletteColorFilterFactHex)) 	* $this->paletteColorFilterFactHex); 
				
				$thisRGB = sprintf('%02X%02X%02X', $red, $green, $blue);
				
				if(array_key_exists($thisRGB, $this->paletteColors[$m][$n]))
	            	$this->paletteColors[$m][$n][$thisRGB]++; 
	         	else
	            	$this->paletteColors[$m][$n][$thisRGB] = 1;
				
				if(array_key_exists($thisRGB, $this->paletteColorsMain))
	            	$this->paletteColorsMain[$thisRGB]++; 
	         	else
	            	$this->paletteColorsMain[$thisRGB] = 1;
			 	
			}
		}

		foreach($this->paletteColors as $key => $val){
		   	foreach($val as $key2 => $val2){
		   		arsort($this->paletteColors[$key][$key2]);
				$this->paletteColors[$key][$key2] = array_slice(array_keys($this->paletteColors[$key][$key2]), 0, 3);
		   	}
	    }
		
		arsort($this->paletteColorsMain);
		$this->paletteColorsMain = array_slice(array_keys($this->paletteColorsMain), 0, $this->paletteColorCount);
		
		return TRUE;
	}

	public function buildColorClusterMap(){
		foreach($this->paletteColors as $key => $val2){
			foreach($val2 as $key2 => $val){
				$hex = $val[0];	
				//foreach($val as $hex){
					$col = $this->getRGBArrayFromHEXString($hex);
					
					$rCl = (int)($col[0] / $this->paletteSubClusterCounter);
					$gCl = (int)($col[1] / $this->paletteSubClusterCounter);
					$bCl = (int)($col[2] / $this->paletteSubClusterCounter);
					
					$cluster = (int)($rCl.$gCl.$bCl);
					
					if(!isset($this->paletteSubCluster[$cluster]))
						$this->paletteSubCluster[$cluster] = array('counter' => 0, 'color' => $hex, 'cluster' => $cluster,'var' => round($this->getColorClusterVariance([$col[0], $col[1], $col[2]]), 2));
					
					$this->paletteSubCluster[$cluster]['counter']++;
				//}
			}
		}
		usort($this->paletteSubCluster, "usortByCounterDESC");
		$this->paletteSubCluster = array_slice($this->paletteSubCluster , 0, $this->paletteColorCount);
		//print_r($this->paletteSubCluster); exit;
	}

	public function getColorPaletteSub(){
		return $this->paletteSubCluster;
	}
	
	public function getColorPaletteMain(){
		$temp = [];	
		foreach($this->paletteColorsMain as $val)
			$temp[] = ['color' => $val, 'var' => 0];
		
		$this->paletteColorsMain = $temp;
		
		return $this->paletteColorsMain;
	}
	
	private function getColorClusterVariance($values){
		$var = 0.0;	
		$mean = 0.0;
		$sum = array_sum($values);
		$count = count($values);
		
		$mean = $sum / $count;
		$sumVar = 0.0;
		
		for ($i = 0; $i < $count; $i++)
			$sumVar += pow(($values[$i] - $mean), 2);
		
		$var = $sumVar / $count;
		
		return $var;
	}

	private function getRGBArrayFromHEXString($hex){
		$r = hexdec(substr($hex,0,2));
	    $g = hexdec(substr($hex,2,2));
	    $b = hexdec(substr($hex,4,2));
		
		return [$r,$g,$b];
	}

	public function printColorPaletteMain(){
		foreach($this->paletteColorsMain as $key => $value){
			echo '<div style="width: 20px; height: 20px; background-color: #'.$value.'">&nbsp;</div>';
		}
	}
	public function printColorPaletteSub(){
		foreach($this->paletteSubCluster as $key => $value){
			echo '<div style="width: 20px; height: 20px; background-color: #'.$value['color'].'">'.$value['var'].'</div>';
		}
	}
}

function usortByCounterASC($a, $b) {
	return $a["counter"] - $b["counter"];
}
function usortByCounterDESC($a, $b) {
	return $b["counter"] - $a["counter"];
}

?>