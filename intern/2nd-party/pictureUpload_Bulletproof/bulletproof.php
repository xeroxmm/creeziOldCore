<?php
/**
 * BulletProof
 *
 * A single class PHP-library for secure image uploading.
 *
 * PHP support 5.3+
 *
 * @package     BulletProof
 * @version     2.0.0
 * @author      Samayo  /@sama_io
 * @link        https://github.com/samayo/bulletproof
 * @license     MIT
 */
namespace BulletProof;

class Image implements \ArrayAccess
{
    /**
     * @var string The new image name, to be provided or will be generated.
     */
    protected $name;

    /**
     * @var int The image width in pixels
     */
    protected $width;

    /**
     * @var int The image height in pixels
     */
    protected $height;

    /**
     * @var string The image mime type (extension)
     */
    protected $mime;

    /**
     * @var string The full image path (dir + image + mime)
     */
    protected $fullPath;

    /**
     * @var string The folder or image storage location
     */
    protected $location;

    /**
     * @var array A json format of all information about an image
     */
    protected $serialize = array();

    /**
     * @var array The min and max image size allowed for upload (in bytes)
     */
    protected $size = array(100, uploadImageMaxSizeByte);

    /**
     * @var array The max height and width image allowed
     */
    protected $dimensions = array(10000, 10000);

    /**
     * @var array The mime types allowed for upload
     */
    protected $mimeTypes = array("jpeg", "png", "gif", "jpg");

    /**
     * @var array list of known image types
     */
    protected $imageMimes = array(
        1 => "gif", "jpeg", "png", "swf", "psd",
        "bmp", "tiff", "tiff", "jpc", "jp2", "jpx",
        "jb2", "swc", "iff", "wbmp", "xbm", "ico", "webp"
    );

    /**
     * @var array storage for the $_FILES global array
     */
    private $_files = array();

    /**
     * @var string storage for any errors
     */
    private $error = "";

    /**
     * @param array $_files represents the $_FILES array passed as dependancy
     */
    public function __construct(array $_files = [])
    {
        $this->_files = $_files;
    }

    /**
     * Gets the real image mime type
     *
     * @param $tmp_name string The upload tmp directory
     *
     * @return bool|string
     */
    protected function getImageMime($tmp_name)
    {
        if (isset($this->imageMimes[exif_imagetype($tmp_name)])) {
            return str_replace('jpeg','jpg',$this->imageMimes [exif_imagetype($tmp_name)]);
        }
        return false;
    }

    /**
     * array offset \ArrayAccess
     * unused
     */
    public function offsetSet($offset, $value){}
    public function offsetExists($offset){}
    public function offsetUnset($offset){}

    /**
     * Gets array value \ArrayAccess
     *
     * @param mixed $offset
     *
     * @return bool|mixed
     */
    public function offsetGet($offset)
    {
        if ($offset == "error") {
            return $this->error;
        }

        if (isset($this->_files[$offset]) && file_exists($this->_files[$offset]["tmp_name"])) {
            $this->_files = $this->_files[$offset];
            return true;
        }
        
        return false;
    }

    /**
     * Renames image
     *
     * @param null $isNameGiven if null, image will be auto-generated
     *
     * @return $this
     */
    public function setName($isNameProvided = null)
    {
        if ($isNameProvided) {
            $this->name = filter_var($isNameProvided, FILTER_SANITIZE_STRING);
        }
        
        return $this;
    }

    /**
     * Define a mime type for uploading
     *
     * @param array $fileTypes
     *
     * @return $this
     */
    public function setMime(array $fileTypes)
    {
        $this->mimeTypes = $fileTypes;
        return $this;
    }

    /**
     * Define a min and max image size for uploading
     *
     * @param $min int minimum value in bytes
     * @param $max int maximum value in bytes
     *
     * @return $this
     */
    public function setSize($min, $max)
    {
        $this->size = array($min, $max);
        return $this;
    }

    /**
     * Creates a location for upload storage
     *
     * @param $dir string the folder name to create
     * @param int $permission chmod permission
     *
     * @return $this
     */
    public function setLocation($dir = "userUploads/temp", $permission = 0666)
    {
        if (!file_exists($dir) && !is_dir($dir) && !$this->location) {
            $createFolder = @mkdir("" . $dir, (int) $permission, true);
            if (!$createFolder) {
                $this->error = "Folder " . $dir . " could not be created";

                return;
            }
        }

        $this->location = $dir;
        return $this;
    }

    /**
     * Sets acceptable max image height and width
     *
     * @param $maxWidth int max width value
     * @param $maxHeight int max height value
     *
     * @return $this
     */
    public function setDimension($maxWidth, $maxHeight)
    {
        $this->dimensions = array($maxWidth, $maxHeight);
        return $this;
    }

    /**
     * Returns the image name
     *
     * @return string
     */
    public function getName(){
        if (!$this->name) {
           return  uniqid(true) . "_" . str_shuffle(implode(range("e", "q")));
        }

        return $this->name;
    }

    /**
     * Returns the full path of the image ex "location/image.mime"
     *
     * @return string
     */
    public function getFullPath(){
        return $this->fullPath;
    }

	public function getTargetedFullPath(){
		$this->fullPath = $this->location . "/" . $this->name . "." . $this->mime;
		return $this->fullPath;
	}

    /**
     * Returns the image size in bytes
     *
     * @return int
     */
    public function getSize()
    {
        return (int) $this->_files["size"];
    }

    /**
     * Returns the image height in pixels
     *
     * @return int
     */
    public function getHeight()
    {
        if ($this->height != null) {
            return $this->height;
        }

        list(, $height) = getImageSize($this->_files["tmp_name"]); 
        return $height;
    }

    /**
     * Returns the image width
     *
     * @return int
     */
    public function getWidth()
    {
        if ($this->width != null) {
            return $this->width;
        }

        list($width) = getImageSize($this->_files["tmp_name"]); 
        return $width;
    }

    /**
     * Returns the storage / folder name
     *
     * @return string
     */
    public function getLocation()
    {
        if(!$this->location){
            $this->setLocation(); 
        }

        return $this->location; 
    }

    /**
     * Returns a JSON format of the image width, height, name, mime ...
     *
     * @return string
     */
    public function getJson()
    {
        return json_encode($this->serialize);
    }

    /**
     * Returns the image mime type
     *
     * @return string
     */
    public function getMime()
    {
        return $this->mime;
    }

	public function getFiles(){
		//if(isset($this->_files['file']))
		
		return $this->_files;
	}

    /**
     * Returns error string or false if no errors occurred
     *
     * @return string|bool
     */
    public function getError(){
        return $this->error != "" ? $this->error : false;
    }

    /**
     * Checks for the common upload errors
     *
     * @param $e int error constant
     */
    protected function uploadErrors($e)
    {
        $errors = array(
            UPLOAD_ERR_OK           => "",
            UPLOAD_ERR_INI_SIZE     => "Image is larger than the specified amount set by the server",
            UPLOAD_ERR_FORM_SIZE    => "Image is larger than the specified amount specified by browser",
            UPLOAD_ERR_PARTIAL      => "Image could not be fully uploaded. Please try again later",
            UPLOAD_ERR_NO_FILE      => "Image is not found",
            UPLOAD_ERR_NO_TMP_DIR   => "Can't write to disk, due to server configuration ( No tmp dir found )",
            UPLOAD_ERR_CANT_WRITE   => "Failed to write file to disk. Please check you file permissions",
            UPLOAD_ERR_EXTENSION    => "A PHP extension has halted this file upload process"
        );
        return $errors[$e];
    }

    /**
     * Main upload method.
     * This is where all the monkey business happens
     *
     * @return $this|bool
     */
    public function upload()
    {
        /* modify variable names for convenience */
        $image = $this; 
        $files = $this->_files;

        /* initialize image properties */
        $image->name     = $image->getName();
        $image->width    = $image->getWidth();
        $image->height   = $image->getHeight(); 
        $image->location = $image->getLocation();

        /* get image sizes */
        list($minSize, $maxSize) = $image->size;

        /* check for common upload errors */
        if($image->error = $image->uploadErrors($files["error"])){
            return ;
        }

        /* check image for valid mime types and return mime */
        $image->mime = $image->getImageMime($files["tmp_name"]);

        /* validate image mime type */
        if (!in_array($image->mime, $image->mimeTypes)) {
            $ext = implode(", ", $image->mimeTypes);
            $image->error = "Invalid File! Only ($ext) image types are allowed";
            return ;
        }     

        /* check image size based on the settings */
        if ($files["size"] < $minSize || $files["size"] > $maxSize) {
            $min = intval($minSize / 1000) ?: 1; $max = intval($maxSize / 1000);
            
            $image->error = "Image size should be atleast more than min: $min and less than max: $max kb";
            return ;
        }

        /* check image dimension */
        list($allowedHeight, $allowedWidth) = $image->dimensions;

        if ($image->height > $allowedHeight || $image->width > $allowedWidth) {
            $image->error = "Image height/width should be less than ' $allowedHeight \ $allowedWidth ' pixels";
            return ;
        }

        if($image->height < pictureUploadHeightMin || $image->width < pictureUploadWidthMin){
            $image->error = "Invalid! Image height/width is too small or maybe corrupted"; 
            return ;
        }
 
        /* set and get folder name */
        #$image->fullPath = $image->location. "/" . $image->name . "." . $image->mime;
		$image->fullPath = $files["tmp_name"];
        
        /* gather image info for json storage */ 
        $image->serialize = array(
            "name"     => $image->name,
            "mime"     => $image->mime,
            "height"   => $image->height,
            "width"    => $image->width,
            "size"     => $files["size"],
            "location" => $image->location,
            "fullpath" => $image->fullPath
        );

		return $image;

        if ($image->error === "") {
            $moveUpload = $image->moveUploadedFile($files["tmp_name"], $image->fullPath);
            if (false !== $moveUpload) {
                return $image;
            }
        }
        
        $image->error = "Upload failed, Unknown error occured";
        return false;
    }

    /**
     * Final upload method to be called, isolated for testing purposes
     *
     * @param $tmp_name int the temporary location of the image file
     * @param $destination int upload destination
     *
     * @return bool
     */
    public function moveUploadedFile($tmp_name, $destination)
    {
        return move_uploaded_file($tmp_name, $destination);
    }
	public function copyUploadedFile($tmp_name, $destination){
		return copy($tmp_name, $destination);	
	}
	public function moveCrawledFile($tmp_name, $destination){
		return rename($tmp_name, $destination);
	}
	
	public function convertToWEBP(){
		if(!mimeWEBPenabled)
			return FALSE;
			
		$imgsize = getimagesize($this->getFullPath());
	    $mime = $imgsize['mime'];
	 
	    switch($mime){
	        case 'image/gif':
	            $image_create = "imagecreatefromgif";
	            $image = "imagegif";
	            break;
	 
	        case 'image/png':
	            $image_create = "imagecreatefrompng";
	            $image = "imagepng";
	            $quality = 7;
	            break;
	 
	        case 'image/jpeg':
	            $image_create = "imagecreatefromjpeg";
	            $image = "imagejpeg";
	            $quality = 80;
	            break;
	 
	 		case 'image/webp':
	            return TRUE;
	            break;
	 
	        default:
	            return false;
	            break;
	    }
		
		$im = $image_create($this->getFullPath());
		
		if(imagewebp($im, substr($this->getFullPath(),0,strlen($this->mime)).'webp'))
			if(imagedestroy($im) && unlink($this->getFullPath())){
				$this->fullPath = substr($this->getFullPath(),0,strlen($this->mime)).'webp';
				$this->mime = 'webp';
				
				return TRUE;
			}
		
		return FALSE;
	}
}
?>