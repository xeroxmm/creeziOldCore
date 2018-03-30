<?php
class fileHandler{
	public static function createFolder($dir, $permission = 0764){
        $createFolder = false;
			
        if (!file_exists($dir) && !is_dir($dir))
            $createFolder = mkdir("" . $dir, $permission, true);
		else if(is_dir($dir))
			$createFolder = true;
		
		return $createFolder;
	}
	public static function createFile($dir, $fileName, $content = NULL){
		if(is_dir($dir)){
			if(FALSE !== ($createFile = fopen($dir.'/'.$fileName, 'w'))){
				if(is_string($content))
					fwrite($createFile, $content);
				fclose($createFile);
				
				return TRUE;
			}
		}
		return FALSE;
	}
	public static function isFolder( $dir ){
		return is_dir($dir);
	}
	public static function getSubFolderAsArray( $dir ){
		$folders = [];	
		
		if(!self::isFolder($dir))
			return $folders;

		$files = scandir($dir);
		
		foreach($files as $val){
			if($val != '.' && $val != '..' && self::isFolder($dir.'/'.$val))
				$folders[] = $dir.'/'.$val;
		}
		
		return $folders;
	}
	public static function getSubFilesAsArray($dir){
		$files = [];	
		
		if(!self::isFolder($dir))
			return $files;
		
		$filesS = scandir($dir);
		
		foreach($filesS as $val){
			if($val != '.' && $val != '..' && !self::isFolder($dir.'/'.$val))
				$files[] = $val;
		}
		
		return $files;
	}
	public static function isFileInFolder( $dir, $fileName ){
		return (is_dir($dir) && file_exists($dir.'/'.$fileName));
	}
}
?>