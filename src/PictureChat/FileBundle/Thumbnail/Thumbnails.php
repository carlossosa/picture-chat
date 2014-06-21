<?php
namespace PictureChat\FileBundle\Thumbnail;
use PictureChat\FileBundle\Thumbnail\Exception\ErrorToLoad;
use PictureChat\FileBundle\Thumbnail\Exception\FileNotFound;
use PictureChat\FileBundle\Thumbnail\Exception\FormatNotSupported;
use PictureChat\FileBundle\Thumbnail\Exception\NotCallableMethod;

/**
 * Thumbnails:
 * Create thumbnails for a given image.
 *
 * @author Carlos Sosa
 * @version 1.2 
 */
class Thumbnails {      
    //Position of selection in Original Image
    /**
     * Strech image to the new size.
     */
    const IMAGE_STRETCH = 1;
    /**
     * Preserve aspect ratio
     */
    const IMAGE_CENTER = 2;
    const IMAGE_POS_TOP = 4;
    const IMAGE_POS_BOTTOM = 8;
    const IMAGE_POS_LEFT = 16;
    const IMAGE_POS_RIGHT = 32;
    /**
     * Preserve aspect ratio and adjust into the boudary of new size
     */
    const IMAGE_TOUCH_OUTSIDE = 64;        
    
    //Format
    /**
     * Using embed Exif functions.
     */
    const IMAGE_FORMAT_AUTO_EXIF = 1024;
    /**
     * Using getimagesize function
     */
    const IMAGE_FORMAT_AUTO_GETIMAGESIZE  = 256;
    /**
     * Detect using the filename extension.
     */
    const IMAGE_FORMAT_AUTO_EXTENSION = 512;
    /**
     * Experimental!!!
     */
    const IMAGE_FORMAT_AUTO_HEADER = 2048;
    /**
     * Experimental!!!
     */
    const IMAGE_FORMAT_AUTO_STRING = 4096;
    //types
    const IMAGE_FORMAT_JPEG = 2;
    const IMAGE_FORMAT_PNG = 3;
    const IMAGE_FORMAT_GIF = 1;   
    
    //save options
    const SAVE_Q_HIGH = 128;
    const SAVE_Q_MED = 256;
    const SAVE_Q_LOW = 384; 

    //prints options
    const STRING_BASE64ENC = 512;
    
    //resize opt
    protected $resize_function = 'imagecopyresampled';

    //Support Formats
    protected $formats = array (
      self::IMAGE_FORMAT_GIF => 'gif',  
      self::IMAGE_FORMAT_JPEG => 'jpeg',  
      self::IMAGE_FORMAT_PNG => 'png',  
    );
    
    //Vars
    protected $img_type;
    protected $img_w;
    protected $img_h;
    protected $image;
    protected $img_path;
    
    //Thumb
    protected $thumb;
    protected $thumb_options;
    
    /**
     * Create instance of Thumbnails
     * 
     * @param type $pathToImg Path to image file or string of image (for this case you need pass IMAGE_FORMAT_AUTO_STRING in $format parameter and PHP 5.4).
     * @param type $format Format of image or method to to autodetect
     * @throws FormatNotSupported
     */
    public function __construct( $pathToImg, $format = self::IMAGE_FORMAT_AUTO_GETIMAGESIZE) {
        if ( ($format !== self::IMAGE_FORMAT_AUTO_STRING) && ( !file_exists($pathToImg) || !is_readable($pathToImg)) )
            throw new FileNotFound($pathToImg);
        //detect type
        if ( in_array($format, array_keys($this->formats)) )
            $this->img_type = $format;
        else {
            $format = self::detectType($pathToImg, $format);
            
            if (in_array($format, array_keys($this->formats)))
                $this->img_type = $format;
            else 
                throw new FormatNotSupported();            
        } 
                
        //image load
        if ( $format == self::IMAGE_FORMAT_AUTO_STRING)
            $this->imageLoadString($pathToImg);
        else
            $this->imageLoad($pathToImg);
    }  
    
    /**
     * Load image from file.
     * 
     * @param type $path
     * @return type
     * @throws ImageSmart_Exception_FileNotFound
     * @throws ImageSmart_Exception_ErrorImagenLoad
     */
    protected function imageLoad ( $path) {
        //function of load
        $loadFunc = "imagecreatefrom{$this->formats[$this->img_type]}";
        
        //check file
        if ( !file_exists($path) || !is_readable($path))
            throw new FileNotFound($path);
        
        //loadImg
        $this->image = $loadFunc($path);
        
        if ( !is_resource($this->image))
            throw new ErrorToLoad($path);
        
        $this->img_path = $path;
        $this->imageLoadDimensions();
        
        return ;
    }
    
    /**
     * Load image from var.
     * 
     * @param string $str
     * @return type
     * @throws ImageSmart_Exception_ErrorImagenLoad
     */
    protected function imageLoadString ( $str) {
        $this->image = imagecreatefromstring($str);
        
        if ( !is_resource($this->image))
            throw new ImageSmart_Exception_ErrorImagenLoad('image into a string');
        
        $this->img_path = NULL;
        $this->imageLoadDimensions();
        
        return ;
    }    
    
    /**
     * Load dimensions from loaded image.
     */
    protected function imageLoadDimensions () {
        $this->img_w = imagesx($this->image);
        $this->img_h = imagesy($this->image);
    }

    /**
     * 
     * @param type $path
     * @param type $method
     * @return int
     */
    public static function detectType ( $path, $method){
        if (is_callable($method)) {
                $format = call_user_func($method, $path);
        } else
            switch ($method) {
                case self::IMAGE_FORMAT_AUTO_EXIF:
                    $format = self::_detectTypeByExif($path);
                    break;
                case self::IMAGE_FORMAT_AUTO_EXTENSION:
                    $format = self::_detectTypeByExtension($path);
                    break;
                case self::IMAGE_FORMAT_AUTO_STRING:
                    $format = self::_detectTypeByGetImagenSizeString($path);
                    break;
                case self::IMAGE_FORMAT_AUTO_GETIMAGESIZE:
                    $format = self::_detectTypeByGetImagenSize($path);
                    break;
                case self::IMAGE_FORMAT_AUTO_HEADER:
                    $format = self::_detectTypeByHeader($path);
                    break;
                default:
                    break;
            }
        return $format;
    }
    
    /**
     * @see detectType()
     * {@link detectType()}
     */
    protected static function _detectTypeByExif ( $path) {
        //Verify Exif
        if ( !function_exists( 'exif_imagetype') )
            throw new NotCallableMethod('exif_imagetype');
        
        return exif_imagetype( $path);
    }
    
    /**
     * @see detectType()
     * {@link detectType()}
     */
    protected static function _detectTypeByGetImagenSizeString ( $path) {
        if ( !function_exists('getimagesizefromstring'))
            throw new NotCallableMethod('getimagesizefromstring');

        $data = getimagesizefromstring($path);
        return $data[2];
    }
    
    /**
     * @see detectType()
     * {@link detectType()}
     */
    protected static function _detectTypeByGetImagenSize ( $path) {
        $data = getimagesize($path);
        return $data[2];
    }
    
    /**
     * @see detectType()
     * {@link detectType()}
     */
    protected static function _detectTypeByExtension ( $path) {       
        switch ('.'.strtolower( pathinfo( $path, PATHINFO_EXTENSION))) {
            case image_type_to_extension(self::IMAGE_FORMAT_JPEG):
            case '.jpg': return self::IMAGE_FORMAT_JPEG;
                break;
            case image_type_to_extension(self::IMAGE_FORMAT_GIF): return self::IMAGE_FORMAT_GIF;
                break;
            case image_type_to_extension(self::IMAGE_FORMAT_PNG): return self::IMAGE_FORMAT_PNG;
                break;
            default: return -1;
                break;
        }
    }
    
    /**
     * Experimental!
     * 
     * @see detectType()
     * {@link detectType()}
     */
    protected static function _detectTypeByHeader ( $path) {
        //todo
    }

    /**
     * Free resources.
     */
    public function __destruct() {
        imagedestroy($this->image);
        if ( is_resource($this->thumb) )
            imagedestroy ($this->thumb);
    }
    
    /**
     * Configure settings to use whenever you make thumbnails.
     * 
     * Example:
     * <pre><code>
     * <?php
     *   $obj = new Thumbnails("/var/www/image.jpg");
     *   $obj->setThumbnailDefaultOptions(Thumbnails::IMAGE_CENTER | Thumbnails::IMAGE_POS_TOP | Thumbnails::IMAGE_POS_RIGHT)
     *       ->doThumbnail(100,20)
     *       ->save('/var/www/image_min.png')
     *       ->doThumbnail(200,40)
     *       ->save('/var/www/image_med.png')
     *       ->doThumbnail(400,80)
     *       ->save('/var/www/image_big.png');
     * ?>
     * </code></pre>
     * 
     * @param type $options     Position of selection in Original Image
     * @return \Thumbnails
     */
    public function setThumbnailDefaultOptions( $options) {
        $this->thumb_options = $options;        
        return $this;
    }
    
    /**
     * setMethodToResize
     * 
     * @param type $method Function name to use when for resize the image.
     * @return \Thumbnails
     * @throws NotCallableMethod
     */
    public function setMethodToResize( $method) {
        if (!is_callable($method))
            throw new NotCallableMethod($method);
            
        $this->resize_function = $method;        
        return $this;
    }

    /**
     * Make a thumbnail for Loaded Image
     * 
     * @param type $thumb_w     Thumbnail Width
     * @param type $thumb_h     Thumbnail Height
     * @param type $options     Position of selection in Original Image
     */
    public function doThumbnail (   $thumb_w, $thumb_h='auto',
                                    $options = self::IMAGE_CENTER,
                                    $bg_color = null)  {  
        //Options
        if ( $this->thumb_options !== NULL )
            $options = $this->thumb_options;
       
        //Img sizes
        $img_w = $this->width();
        $img_h = $this->height();
        //Calc image ratios
        $img_r = $this->ratio();           
        if ($thumb_h == 'auto' || $thumb_h == NULL) { $thumb_h = $thumb_w / $img_r; } //thumbnail height proportional
        $thumb_r = $thumb_w / $thumb_h;        
       
        if (( $options & self::IMAGE_CENTER ) && ( $options & self::IMAGE_TOUCH_OUTSIDE )) {
            $O_h = $img_h;
            $O_w = $img_w;
            $O_x = $O_y = 0;
            $T_w = $thumb_w;
            $T_h = $thumb_h;
            $T_x = $T_y = 0;
            if ($img_r < $thumb_r) { //mov horizontal
                $T_w = $thumb_h * $img_r;
                $T_x = 0;
               
                if (( $options & self::IMAGE_POS_RIGHT)) {
                    $T_x = $thumb_w - $T_w;
                } else if (!( $options & self::IMAGE_POS_LEFT )) //center
                    $T_x = ($thumb_w - $T_w)/2;
            } else { //mov vertical
                $T_h = $thumb_w / $img_r;
                $T_y = 0;
               
                if (( $options & self::IMAGE_POS_BOTTOM)) {
                    $T_y = $thumb_h - $T_h;
                } else if (!( $options & self::IMAGE_POS_TOP ))
                    $T_y = ($thumb_h - $T_h)/2;
            }
        } else {
            $T_w = $thumb_w;
            $T_h = $thumb_h;
            $T_x = $T_y = 0;
            //Calc sizes
            $O_h = ( $options % 2 != 0 ) ? $img_w : $img_w / $thumb_r;
            $O_w = $img_w;
            //Correct sizes
            if ($img_r > $thumb_r) {
                $O_h_diff = $O_h - $img_h;
                $O_h = $img_h;
                $O_w = $O_w - ($O_h_diff * $thumb_r);
            }
            //X,Y Pos in Image
            //By default is aligned to left and top.
            $O_x = $O_y = 0;
            if ($options % 2 == 0) { //If not stretch then calc Pos
                if ($O_w < $img_w) { //x
                    if (( $options & self::IMAGE_POS_RIGHT)) {
                        $O_x = ($img_w - $O_w);
                    } else if (!( $options & self::IMAGE_POS_LEFT )) //center
                        $O_x = ($img_w - $O_w) / 2;
                }//x
 
                if ($O_h < $img_h) { //y
                    if (( $options & self::IMAGE_POS_BOTTOM)) {
                        $O_y = ($img_h - $O_h);
                    } else if (!( $options & self::IMAGE_POS_TOP ))
                        $O_y = ($img_h - $O_h) / 2;
                }//y
            }//center
        }
       
        //Create blank image
        if ( $this->thumb)            
            imagedestroy ($this->thumb);
        $this->thumb = imagecreatetruecolor($thumb_w, $thumb_h);
       
        /**
         * Thanks to WaKeMaTTa! http://www.phpclasses.org/discuss/package/7899/thread/2/
         */
                if ($bg_color == NULL) {
                        //$bg_color = array('r' => 255, 'g' => 255, 'b' => 255);                       
                        //$transparent = imagecolorallocate($this->thumb, $bg_color['r'], $bg_color['g'], $bg_color['b']);
                        imagefill($this->thumb, 0, 0, self::getColor( 'FEFEFE', $this->thumb)); 
                        imagecolortransparent($this->thumb,
                                                self::getColor(  'FEFEFE', // Color
                                                                 $this->thumb //Image for color allocate
                                                )); 
                }
               
        /* Deprecated: Remove in next future: if ( is_array($bg_color)) {            
            imagefill($this->thumb, 0, 0, imagecolorallocate($this->thumb, $bg_color['r'], $bg_color['g'], $bg_color['b']));
        } */
        
        if ( null !== $bg_color )
        {
            // Set background color
            imagefill( $this->thumb, 
                       //X,Y Values where start fill operation 
                       0, //X: 
                       0, //Y: 
                       self::getColor(  $bg_color, // Color
                                        $this->thumb //Image for color allocate
                                        )
                    );
        }
                
        //Copy and resize the Big image into Thumbnail
        //imagecopyresampled( $this->thumb, $this->image, 0, 0, $O_x, $O_y, $thumb_w, $thumb_h, $O_w, $O_h);        
        call_user_func($this->resize_function,$this->thumb, $this->image, $T_x, $T_y, $O_x, $O_y, $T_w, $T_h, $O_w, $O_h);        
       
        return $this;      
    }

    /**
     * createThumb
     * 
     * Example:
     * Generate a thumbnail from image, if aspect ratio of both 
     * images is not equal then select area from top right corner.
     * <pre><code>
     * <?php
     * Thumbnails::createThumb( '/path/to/big_img.png', '/path/to/thumb_big_img.gif', 60, 60, Thumbnails::IMAGE_CENTER | Thumbnails::IMAGE_POS_TOP | Thumbnails::IMAGE_POS_RIGHT, Thumbnails::IMAGE_FORMAT_GIF );
     * ?>
     * </code></pre>
     * 
     * Example:
     * Generate a thumbnails save and show.
     * <pre><code>
     * <?php
     *  require 'Thumbnails.php';
     *  $obj = Thumbnails::createThumb('img.jpg', 'th.jpg', 350,350, Thumbnails::IMAGE_CENTER | Thumbnails::IMAGE_POS_TOP , Thumbnails::IMAGE_FORMAT_PNG);
     *  header("Pragma: public");
     *  header('Content-disposition: filename=image_thumb.png');
     *  header("Content-type: image/png");
     *  header('Content-Transfer-Encoding: binary');
     *  ob_clean();
     *  flush();
     *  $obj->printThumbnailAsPng();
     * ?>
     * * </code></pre>
     * 
     * @param type $imgPath     Full path to Orignal Image
     * @param type $thumbPath   Full to store Thumbnail
     * @param type $thumb_w     Thumbnail Width
     * @param type $thumb_h     Thumbnail Height
     * @param type $options     Position of selection in Original Image
     * @param type $format      Format for Generated Thumbnail
     * @return Thumbnails Return object used to make the thumbnail.
     * @throws Exception
     */
    public static function createThumb (    $imgPath, $thumbPath, 
                                            $thumb_w, $thumb_h, 
                                            $options = self::IMAGE_CENTER, 
                                            $format = NULL,
                                            $bg_color = null)            
    {
        $obj = new self($imgPath);
        $obj->doThumbnail($thumb_w, $thumb_h, $options, $bg_color);
        if ( $thumbPath !== null)
                $obj->save($thumbPath,$format);
        
        return $obj;
    }        
    
    /**
     * Use for allocate color in image
     * 
     * @param mixed Array RGB, Hex or Name of color
     * @param GdImage $image
     * @return int
     */
    public static function getColor( $str, $image) {
        $func = 'imagecolorallocate';
        $color = array(255,255,255);
        
        //colors
        $colors = array(
            'black' => '000',       'blue' => '00F',        'green' => '0F0',
            'gray' => 'CCC',        'red' => 'F00',         'white' => 'FFF',
            'darkblue' => '053368', 'skyblue' => '00CBFF',  'yellow' => 'FF0',
            'violet' => '7B00FF',   'pink' => 'F0F',
        );
        
        if (array_key_exists($str, $colors))
        {
            return self::getColor($colors[$str], $image);
        }
        elseif (is_array($str)) {
            if ( count($str) == 3)
            {
                $color = $str;
            }
        } else {
            $_match = array();
            if ( preg_match('#^([a-fA-F0-9]{1})([a-fA-F0-9]{1})([a-fA-F0-9]{1})$#', trim($str,' #'), $_match) ) {
                    $_match[1] .= $_match[1];$_match[2] .= $_match[2];$_match[3] .= $_match[3];
                    $color = array( base_convert($_match[1],16,10), base_convert($_match[2],16,10), base_convert($_match[3],16,10));
            } elseif ( preg_match('#^([a-fA-F0-9]{2})([a-fA-F0-9]{2})([a-fA-F0-9]{2})$#', trim($str,' #'), $_match) ) {
                    $color = array( base_convert($_match[1],16,10), base_convert($_match[2],16,10), base_convert($_match[3],16,10));
            }            
        }
        array_unshift( $color, $image);
        return call_user_func_array($func, $color);
    }
    
    /**
     * Get image resource id.
     * @return Resource Id
     */
    public function getImageResource ()
    {
        return $this->image;
    }
    
    /**
     * Get image resource id.
     * @return Resource Id
     */
    public function getThumbnailResource ()
    {
        if (is_resource($this->thumb))
            return $this->thumb;
        else   
            return false;
    }
    
    /**
     * Get width size of image.
     * @return integer
     */
    public function width() {
        return $this->img_w;
    }
    
    /**
     * Get height size of image
     * @return integer
     */
    public function height() {
        return $this->img_h;
    }
    
    /**
     * Ratio of image
     * @return integer
     */
    public function ratio ()
    {
        return $this->width()/$this->height();
    }

    /**
     * @see width()
     */
    public function getWidth() { return $this->width(); }
    /**
     * @see height()
     */
    public function getHeight() { return $this->height(); }
    /**
     * @see ratio()
     */
    public function getRatio() { return $this->ratio(); }

    /**
     * Save generated thumbnail into file.
     * 
     * Example:
     * <pre><code>
     * <?php
     *   $obj = new Thumbnails("/var/www/image.jpg");
     *   $obj->doThumbnail(60, null, Thumbnails::IMAGE_CENTER | Thumbnails::IMAGE_TOUCH_OUTSIDE)
     *       ->saveAs("/var/www/image_thumb.jpg", Thumbnails::IMAGE_FORMAT_JPEG);
     * ?>
     * </code></pre>
     * 
     * @param string $path
     * @param int $format
     * @param int $options
     * @return \Thumbnails
     */
    public function saveAs ( $path, $format = NULL, $options = NULL)
    {               
        //set the format to use
        $format = ($format) ?:$this->img_type;      
        
        //prepare function and arguments
        $func = "image{$this->formats[$format]}";           
        $args = array( $this->thumb, $path);
        
        //Set quality
        $qlty = $this->qualityByFormat($format,$options);
        if ( $qlty) $args[] = $qlty;
        
        //call function
        call_user_func_array($func, $args);
        
        return $this;
    }    
    
    /**
     * Alias of saveAs
     * @see saveAs
     */
    public function save () {
        return call_user_func_array(array($this,'saveAs'), func_get_args());
    }

    /**
     * Print image to output
     * 
     * Example:
     * <pre><code>
     * <?php
     *   $obj = new Thumbnails("/var/www/image.jpg");
     *   $obj->doThumbnail(60, 60);
     *   
     *   header("Pragma: public"); 
     *   header('Content-disposition: filename=image_thumb.png'); 
     *   header("Content-type: image/png"); 
     *   header('Content-Transfer-Encoding: binary'); 
     *   ob_clean(); 
     *   flush(); 
     *   $obj->printThumbnail(Thumbnails::IMAGE_FORMAT_PNG);
     * ?>
     * </code></pre>
     * 
     * @param type $format
     * @param type $options
     * @return \Thumbnails
     */
    public function printImage( $format = NULL, $options = NULL)
    {
        $this->saveAs( NULL, $format, $options);
        
        return $this;
    }
    
    /**
     * Alias of printImage
     * @see printImage
     * @deprecated
     */
    public function printThumbnail () {
        return call_user_func_array(array($this,'printImage'), func_get_args());
    }
        
    /**
     * getAsString
     * Store thumbnails into string
     * 
     * Example:
     * <pre><code>
     * <?php
     *   $obj = new Thumbnails("/var/www/image.jpg");
     *   $obj->doThumbnail(60, 60);
     *   
     *   echo "My image thumbnail : ";
     *   echo '<img src="data:image/png;base64,'. $obj->getAsString(Thumbnails::IMAGE_FORMAT_PNG,Thumbnails::STRING_BASE64ENC) .'">';
     * ?>
     * </code></pre>
     * 
     * @param type $format
     * @param type $options
     * @return type
     */
    public function getAsString( $format = NULL, $options = NULL) {
        ob_start();
        $this->printImage($format, $options);
        $string = ob_get_clean();
        
        //return string
        if ( $options & self::STRING_BASE64ENC) //if want encoded with b64
            return base64_encode($string);
        else return $string; //raw string of image
    }
    
    /**
     * Alias of getAsString
     * @see getAsString
     * @deprecated
     */
    public function getThumbnailAsString () {
        return call_user_func_array(array($this,'getAsString'), func_get_args());
    }

    /**
     * @internal Determine quality of image by format.
     * @param type $format
     * @param type $options
     * @return boolean
     */
    protected function qualityByFormat( $format = NULL, $options = self::SAVE_Q_LOW)
    {
        $quality = 'H';
        if ( $options & self::SAVE_Q_HIGH) $quality = 'H';
        if ( $options & self::SAVE_Q_MED) $quality = 'M';
        if ( $options & self::SAVE_Q_LOW) $quality = 'L';
        
        $_quality[self::IMAGE_FORMAT_JPEG] = array (
            'H' => 100,
            'M' => 75,
            'L' => 35,
        );
        $_quality[self::IMAGE_FORMAT_PNG] = array (
            'L' => 9,
            'M' => 6,
            'H' => 2,
        );
        
        if (array_key_exists($format, $_quality) && $quality !== false ) {
            $r = $_quality[$format][$quality];
        } else {
            $r = false;
        }
        
        return $r;
    }
}









