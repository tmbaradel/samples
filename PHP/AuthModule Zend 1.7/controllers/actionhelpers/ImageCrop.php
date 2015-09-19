<?php
/**
 * Greenroom
 *
 * @category Greenroom
 * @package Admin
 * @subpackage Controller
 * @author Tom Baradel 
 */

/**
 * Image crop helper
 *
 * @category Greenroom
 * @package Admin
 * @subpackage Controller
 * @author Tom Baradel
 * @version $Id$
 */
class Auth_Controller_Action_Helper_ImageCrop extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Path to image file
     * @var string
     */
    protected $_filename;
    
    /**
     * Product image model
     * @var Users_Model_ProfileImage
     */
    protected $_profileImage;
    
    /**
     * Applicatin configuration
     * 
     * @var array
     */
    protected $_config;
    
    public function __construct() {
        $this->_config = Zend_Registry::get('config');
    }

    /**
     * Save the uploaded image,
     * if current image exists then
     * overwrite
     */
    public function execute()
    {
        //create large image
        $this->_crop(Users_Model_ProfileImage::LARGE_MAX_WIDTH, 
                     Users_Model_ProfileImage::LARGE_MAX_HEIGHT,
                     $this->getProfileImage()->getImageUploadPathBySize('large')
                );

        $this->_crop(Users_Model_ProfileImage::THUMB_MAX_WIDTH, 
                     Users_Model_ProfileImage::THUMB_MAX_HEIGHT,
                     $this->getProfileImage()->getImageUploadPathBySize('thumb')
            );
        
        return $this;

    }
    
    /**
     * Find the width and height param to 
     * fill the given target size.
     * 
     * @param float $width
     * @param float $height
     * @param float $targetWidth
     * @param float $targetHeight
     * @return array with width and height 
     */
    protected function _setFillSize($width,$height,$targetWidth,$targetHeight) {
        if ($height > $width) {
            $aspectRatio = $height/$width;
            $fillWidth = $targetWidth;
            $fillHeight = round($targetHeight*$aspectRatio);
        } else {
            $aspectRatio = $width/$height;
            $fillWidth = round($targetWidth*$aspectRatio);
            $fillHeight = $targetHeight;
        }
        
        return array('width'=>$fillWidth, 'height'=>$fillHeight);
    }
    
/**
     * Filter the file to a JPEG
     *
     * @param string $value
     * @return string
     */
    public function _crop($maxWidth, $maxHeight, $destination)
    {
        $source = $this->getProfileImage()->getImageUploadPathBySize('original');
        
        $imagick = new Imagick($source);
        $imagick->setImageFormat("png");
        $imageWidth  = $imagick->getImageWidth();
        $imageHeight = $imagick->getImageHeight();
        
        //$imagick->setGravity(imagick::GRAVITY_CENTER);
        $resize = $this->_setFillSize($imageWidth, $imageHeight, $maxWidth, $maxHeight);
        foreach ($imagick as $image) {
            $image->resizeImage($resize['width'], $resize['height'],Imagick::FILTER_LANCZOS,1);
            $image->cropImage($maxWidth, $maxHeight, 0, 0);
        }
        
        $imagick->writeImages($destination, true);            
    }
    
    /**
     * Get story image model
     *  
     * @return Users_Model_ProfileImage
     */
    public function getProfileImage()
    {
        if (!$this->_profileImage) {
            throw new Zend_Controller_Action_Exception(
                "No profile image set",
                500
            );
        }
        return $this->_profileImage;
    }
    
    /**
     * Set product image DB Table row
     * 
     * @param $row Users_Model_ProfileImage
     * @return Admin_Controller_Action_Helper_ImageCrop
     */
    public function setProfileImage(Users_Model_ProfileImage $row)
    {
        $this->_profileImage = $row;        
        return $this;
    }
}