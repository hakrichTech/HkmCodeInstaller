<?php

namespace Hkm_code\Files;

/**
 * PhpThumb : PHP Thumb Library <http://phpthumb.gxdlabs.com>
 * Copyright (c) 2009, Ian Selby/Gen X Design
 *
 * Author(s): Ian Selby <ian@gen-x-design.com>
 *
 * Licensed under the MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author Ian Selby <ian@gen-x-design.com>
 * @copyright Copyright (c) 2009 Gen X Design
 * @link http://phpthumb.gxdlabs.com
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */

class GD extends Thumbnail
{
    /**
     * The prior image (before manipulation)
     *
     * 
     */
    protected static $oldImage;

    /**
     * The working image (used during manipulation)
     *
     * @var 
     */
    protected static $workingImage;

    /**
     * The current dimensions of the image
     *
     * @var array
     */
    protected static $currentDimensions;

    /**
     * The new, calculated dimensions of the image
     *
     * @var array
     */
    protected static $newDimensions;

    /**
     * The options for this class
     *
     * This array contains various options that determine the behavior in
     * various functions throughout the class.  Functions note which specific
     * option key / values are used in their documentation
     *
     * @var array
     */
    protected static $options = [];

    /**
     * The maximum width an image can be after resizing (in pixels)
     *
     * @var int
     */
    protected static $maxWidth;

    /**
     * The maximum height an image can be after resizing (in pixels)
     *
     * @var int
     */
    protected static $maxHeight;

    /**
     * The percentage to resize the image by
     *
     * @var int
     */
    protected static $percent;

    /**
     * @param string $fileName
     * @param array $options
     * @param array $plugins
     */
    public  function __construct($fileName, $options = array(), array $plugins = array())
    {
        parent::__construct($fileName, $options, $plugins);
        self::DETERMINE_FORMAT();
        self::VERIFY_FORMAT_COMPATIBILITY();

        switch (self::$format) {
            case 'GIF':
                self::$oldImage = imagecreatefromgif(self::$fileName);
                break;
            case 'JPG':
                self::$oldImage = imagecreatefromjpeg(self::$fileName);
                break;
            case 'PNG':
                self::$oldImage = imagecreatefrompng(self::$fileName);
                break;
            case 'STRING':
                self::$oldImage = imagecreatefromstring(self::$fileName);
                break;
        }

        self::$currentDimensions = array (
            'width'  => imagesx(self::$oldImage),
            'height' => imagesy(self::$oldImage)
        );
        
    }

    public  function __destruct()
    {
        if (is_resource(self::$oldImage)) {
            imagedestroy(self::$oldImage);
        }

        if (is_resource(self::$workingImage)) {
            imagedestroy(self::$workingImage);
        }
    }

    /**
     * Pad an image to desired dimensions. Moves the image into the center and fills the rest with $color.
     * @param $width
     * @param $height
     * @param array $color
     * @return GD
     */
    public static function PAD($width, $height, $color = array(255, 255, 255))
    {
        // no resize - woohoo!
        if ($width == self::$currentDimensions['width'] && $height == self::$currentDimensions['height']) {
            return self::$thiss;
        }

        // create the working image
        if (function_exists('imagecreatetruecolor')) {
            self::$workingImage = imagecreatetruecolor($width, $height);
        } else {
            self::$workingImage = imagecreate($width, $height);
        }

        // create the fill color
        $fillColor = imagecolorallocate(
            self::$workingImage,
            $color[0],
            $color[1],
            $color[2]
        );

        // fill our working image with the fill color
        imagefill(
            self::$workingImage,
            0,
            0,
            $fillColor
        );

        // copy the image into the center of our working image
        imagecopyresampled(
            self::$workingImage,
            self::$oldImage,
            intval(($width-self::$currentDimensions['width']) / 2),
            intval(($height-self::$currentDimensions['height']) / 2),
            0,
            0,
            self::$currentDimensions['width'],
            self::$currentDimensions['height'],
            self::$currentDimensions['width'],
            self::$currentDimensions['height']
        );

        // update all the variables and resources to be correct
        self::$oldImage                    = self::$workingImage;
        self::$currentDimensions['width']  = $width;
        self::$currentDimensions['height'] = $height;

        return self::$thiss;
    }

    /**
     * Resizes an image to be no larger than $maxWidth or $maxHeight
     *
     * If either param is set to zero, then that dimension will not be considered as a part of the resize.
     * Additionally, if self::$options['resizeUp'] is set to true (false by default), then this function will
     * also scale the image up to the maximum dimensions provided.
     *
     * @param  int          $maxWidth  The maximum width of the image in pixels
     * @param  int          $maxHeight The maximum height of the image in pixels
     * @return GD
     */
    public static function RESIZE($maxWidth = 0, $maxHeight = 0)
    {
        // make sure our arguments are valid
        if (!is_numeric($maxWidth)) {
            throw new \InvalidArgumentException('$maxWidth must be numeric');
        }

        if (!is_numeric($maxHeight)) {
            throw new \InvalidArgumentException('$maxHeight must be numeric');
        }

        // make sure we're not exceeding our image size if we're not supposed to
        if (self::$options['resizeUp'] === false) {
            self::$maxHeight = (intval($maxHeight) > self::$currentDimensions['height']) ? self::$currentDimensions['height'] : $maxHeight;
            self::$maxWidth  = (intval($maxWidth) > self::$currentDimensions['width']) ? self::$currentDimensions['width'] : $maxWidth;
        } else {
            self::$maxHeight = intval($maxHeight);
            self::$maxWidth  = intval($maxWidth);
        }

        // get the new dimensions...
        self::CALC_IMAGE_SIZE(self::$currentDimensions['width'], self::$currentDimensions['height']);

        // create the working image
        if (function_exists('imagecreatetruecolor')) {
            self::$workingImage = imagecreatetruecolor(self::$newDimensions['newWidth'], self::$newDimensions['newHeight']);
        } else {
            self::$workingImage = imagecreate(self::$newDimensions['newWidth'], self::$newDimensions['newHeight']);
        }

        self::PRESERVE_ALPHA();

        // and create the newly sized image
        imagecopyresampled(
            self::$workingImage,
            self::$oldImage,
            0,
            0,
            0,
            0,
            self::$newDimensions['newWidth'],
            self::$newDimensions['newHeight'],
            self::$currentDimensions['width'],
            self::$currentDimensions['height']
        );

        // update all the variables and resources to be correct
        self::$oldImage                    = self::$workingImage;
        self::$currentDimensions['width']  = self::$newDimensions['newWidth'];
        self::$currentDimensions['height'] = self::$newDimensions['newHeight'];

        return self::$thiss;
    }

    /**
     * Adaptively Resizes the Image
     *
     * This function attempts to get the image to as close to the provided dimensions as possible, and then crops the
     * remaining overflow (from the center) to get the image to be the size specified
     *
     * @param  int          $maxWidth
     * @param  int          $maxHeight
     * @return GD
     */
    public static function ADAPTIVE_RESIZE($width, $height)
    {
        // make sure our arguments are valid
        if ((!is_numeric($width) || $width  == 0) && (!is_numeric($height) || $height == 0)) {
            throw new \InvalidArgumentException('$width and $height must be numeric and greater than zero');
        }

        if (!is_numeric($width) || $width  == 0) {
            $width = ($height * self::$currentDimensions['width']) / self::$currentDimensions['height'];
        }

        if (!is_numeric($height) || $height  == 0) {
            $height = ($width * self::$currentDimensions['height']) / self::$currentDimensions['width'];
        }

        // make sure we're not exceeding our image size if we're not supposed to
        if (self::$options['resizeUp'] === false) {
            self::$maxHeight = (intval($height) > self::$currentDimensions['height']) ? self::$currentDimensions['height'] : $height;
            self::$maxWidth  = (intval($width) > self::$currentDimensions['width']) ? self::$currentDimensions['width'] : $width;
        } else {
            self::$maxHeight = intval($height);
            self::$maxWidth  = intval($width);
        }

        self::CALC_IMAGE_SIZE_STRICT(self::$currentDimensions['width'], self::$currentDimensions['height']);

        // resize the image to be close to our desired dimensions
        self::RESIZE(self::$newDimensions['newWidth'], self::$newDimensions['newHeight']);

        // reset the max dimensions...
        if (self::$options['resizeUp'] === false) {
            self::$maxHeight = (intval($height) > self::$currentDimensions['height']) ? self::$currentDimensions['height'] : $height;
            self::$maxWidth  = (intval($width) > self::$currentDimensions['width']) ? self::$currentDimensions['width'] : $width;
        } else {
            self::$maxHeight = intval($height);
            self::$maxWidth  = intval($width);
        }

        // create the working image
        if (function_exists('imagecreatetruecolor')) {
            self::$workingImage = imagecreatetruecolor(self::$maxWidth, self::$maxHeight);
        } else {
            self::$workingImage = imagecreate(self::$maxWidth, self::$maxHeight);
        }

        self::PRESERVE_ALPHA();

        $cropWidth  = self::$maxWidth;
        $cropHeight = self::$maxHeight;
        $cropX      = 0;
        $cropY      = 0;

        // now, figure out how to crop the rest of the image...
        if (self::$currentDimensions['width'] > self::$maxWidth) {
            $cropX = intval((self::$currentDimensions['width'] - self::$maxWidth) / 2);
        } elseif (self::$currentDimensions['height'] > self::$maxHeight) {
            $cropY = intval((self::$currentDimensions['height'] - self::$maxHeight) / 2);
        }

        imagecopyresampled(
            self::$workingImage,
            self::$oldImage,
            0,
            0,
            $cropX,
            $cropY,
            $cropWidth,
            $cropHeight,
            $cropWidth,
            $cropHeight
        );

        // update all the variables and resources to be correct
        self::$oldImage                    = self::$workingImage;
        self::$currentDimensions['width']  = self::$maxWidth;
        self::$currentDimensions['height'] = self::$maxHeight;

        return self::$thiss;
    }

    /**
     * Adaptively Resizes the Image and Crops Using a Percentage
     *
     * This function attempts to get the image to as close to the provided dimensions as possible, and then crops the
     * remaining overflow using a provided percentage to get the image to be the size specified.
     *
     * The percentage mean different things depending on the orientation of the original image.
     *
     * For Landscape images:
     * ---------------------
     *
     * A percentage of 1 would crop the image all the way to the left, which would be the same as
     * using adaptiveResizeQuadrant() with $quadrant = 'L'
     *
     * A percentage of 50 would crop the image to the center which would be the same as using
     * adaptiveResizeQuadrant() with $quadrant = 'C', or even the original adaptiveResize()
     *
     * A percentage of 100 would crop the image to the image all the way to the right, etc, etc.
     * Note that you can use any percentage between 1 and 100.
     *
     * For Portrait images:
     * --------------------
     *
     * This works the same as for Landscape images except that a percentage of 1 means top and 100 means bottom
     *
     * @param  int          $maxWidth
     * @param  int          $maxHeight
     * @param  int          $percent
     * @return GD
     */
    public static function ADAPTIVE_RESIZE_PERCENT($width, $height, $percent = 50)
    {
        // make sure our arguments are valid
        if (!is_numeric($width) || $width  == 0) {
            throw new \InvalidArgumentException('$width must be numeric and greater than zero');
        }

        if (!is_numeric($height) || $height == 0) {
            throw new \InvalidArgumentException('$height must be numeric and greater than zero');
        }

        // make sure we're not exceeding our image size if we're not supposed to
        if (self::$options['resizeUp'] === false) {
            self::$maxHeight = (intval($height) > self::$currentDimensions['height']) ? self::$currentDimensions['height'] : $height;
            self::$maxWidth  = (intval($width) > self::$currentDimensions['width']) ? self::$currentDimensions['width'] : $width;
        } else {
            self::$maxHeight = intval($height);
            self::$maxWidth  = intval($width);
        }

        self::CALC_IMAGE_SIZE_STRICT(self::$currentDimensions['width'], self::$currentDimensions['height']);

        // resize the image to be close to our desired dimensions
        self::RESIZE(self::$newDimensions['newWidth'], self::$newDimensions['newHeight']);

        // reset the max dimensions...
        if (self::$options['resizeUp'] === false) {
            self::$maxHeight = (intval($height) > self::$currentDimensions['height']) ? self::$currentDimensions['height'] : $height;
            self::$maxWidth  = (intval($width) > self::$currentDimensions['width']) ? self::$currentDimensions['width'] : $width;
        } else {
            self::$maxHeight = intval($height);
            self::$maxWidth  = intval($width);
        }

        // create the working image
        if (function_exists('imagecreatetruecolor')) {
            self::$workingImage = imagecreatetruecolor(self::$maxWidth, self::$maxHeight);
        } else {
            self::$workingImage = imagecreate(self::$maxWidth, self::$maxHeight);
        }

        self::PRESERVE_ALPHA();

        $cropWidth  = self::$maxWidth;
        $cropHeight = self::$maxHeight;
        $cropX      = 0;
        $cropY      = 0;

        // Crop the rest of the image using the quadrant

        if ($percent > 100) {
            $percent = 100;
        } elseif ($percent < 1) {
            $percent = 1;
        }

        if (self::$currentDimensions['width'] > self::$maxWidth) {
            // Image is landscape
            $maxCropX = self::$currentDimensions['width'] - self::$maxWidth;
            $cropX    = intval(($percent / 100) * $maxCropX);

        } elseif (self::$currentDimensions['height'] > self::$maxHeight) {
            // Image is portrait
            $maxCropY = self::$currentDimensions['height'] - self::$maxHeight;
            $cropY    = intval(($percent / 100) * $maxCropY);
        }

        imagecopyresampled(
            self::$workingImage,
            self::$oldImage,
            0,
            0,
            $cropX,
            $cropY,
            $cropWidth,
            $cropHeight,
            $cropWidth,
            $cropHeight
        );

        // update all the variables and resources to be correct
        self::$oldImage                    = self::$workingImage;
        self::$currentDimensions['width']  = self::$maxWidth;
        self::$currentDimensions['height'] = self::$maxHeight;

        return self::$thiss;
    }

    /**
     * Adaptively Resizes the Image and Crops Using a Quadrant
     *
     * This function attempts to get the image to as close to the provided dimensions as possible, and then crops the
     * remaining overflow using the quadrant to get the image to be the size specified.
     *
     * The quadrants available are Top, Bottom, Center, Left, and Right:
     *
     *
     * +---+---+---+
     * |   | T |   |
     * +---+---+---+
     * | L | C | R |
     * +---+---+---+
     * |   | B |   |
     * +---+---+---+
     *
     * Note that if your image is Landscape and you choose either of the Top or Bottom quadrants (which won't
     * make sence since only the Left and Right would be available, then the Center quadrant will be used
     * to crop. This would have exactly the same result as using adaptiveResize().
     * The same goes if your image is portrait and you choose either the Left or Right quadrants.
     *
     * @param  int          $maxWidth
     * @param  int          $maxHeight
     * @param  string       $quadrant  T, B, C, L, R
     * @return GD
     */
    public static function ADAPTIVE_RESIZE_QUADRANT($width, $height, $quadrant = 'C')
    {
        // make sure our arguments are valid
        if (!is_numeric($width) || $width  == 0) {
            throw new \InvalidArgumentException('$width must be numeric and greater than zero');
        }

        if (!is_numeric($height) || $height == 0) {
            throw new \InvalidArgumentException('$height must be numeric and greater than zero');
        }

        // make sure we're not exceeding our image size if we're not supposed to
        if (self::$options['resizeUp'] === false) {
            self::$maxHeight = (intval($height) > self::$currentDimensions['height']) ? self::$currentDimensions['height'] : $height;
            self::$maxWidth  = (intval($width) > self::$currentDimensions['width']) ? self::$currentDimensions['width'] : $width;
        } else {
            self::$maxHeight = intval($height);
            self::$maxWidth  = intval($width);
        }

        self::CALC_IMAGE_SIZE_STRICT(self::$currentDimensions['width'], self::$currentDimensions['height']);

        // resize the image to be close to our desired dimensions
        self::RESIZE(self::$newDimensions['newWidth'], self::$newDimensions['newHeight']);

        // reset the max dimensions...
        if (self::$options['resizeUp'] === false) {
            self::$maxHeight = (intval($height) > self::$currentDimensions['height']) ? self::$currentDimensions['height'] : $height;
            self::$maxWidth  = (intval($width) > self::$currentDimensions['width']) ? self::$currentDimensions['width'] : $width;
        } else {
            self::$maxHeight = intval($height);
            self::$maxWidth  = intval($width);
        }

        // create the working image
        if (function_exists('imagecreatetruecolor')) {
            self::$workingImage = imagecreatetruecolor(self::$maxWidth, self::$maxHeight);
        } else {
            self::$workingImage = imagecreate(self::$maxWidth, self::$maxHeight);
        }

        self::PRESERVE_ALPHA();

        $cropWidth  = self::$maxWidth;
        $cropHeight = self::$maxHeight;
        $cropX      = 0;
        $cropY      = 0;

        // Crop the rest of the image using the quadrant

        if (self::$currentDimensions['width'] > self::$maxWidth) {
            // Image is landscape
            switch ($quadrant) {
                case 'L':
                    $cropX = 0;
                    break;
                case 'R':
                    $cropX = intval((self::$currentDimensions['width'] - self::$maxWidth));
                    break;
                case 'C':
                default:
                    $cropX = intval((self::$currentDimensions['width'] - self::$maxWidth) / 2);
                    break;
            }
        } elseif (self::$currentDimensions['height'] > self::$maxHeight) {
            // Image is portrait
            switch ($quadrant) {
                case 'T':
                    $cropY = 0;
                    break;
                case 'B':
                    $cropY = intval((self::$currentDimensions['height'] - self::$maxHeight));
                    break;
                case 'C':
                default:
                    $cropY = intval((self::$currentDimensions['height'] - self::$maxHeight) / 2);
                    break;
            }
        }

        imagecopyresampled(
            self::$workingImage,
            self::$oldImage,
            0,
            0,
            $cropX,
            $cropY,
            $cropWidth,
            $cropHeight,
            $cropWidth,
            $cropHeight
        );

        // update all the variables and resources to be correct
        self::$oldImage                    = self::$workingImage;
        self::$currentDimensions['width']  = self::$maxWidth;
        self::$currentDimensions['height'] = self::$maxHeight;

        return self::$thiss;
    }

    /**
     * Resizes an image by a given percent uniformly,
     * Percentage should be whole number representation (i.e. 1-100)
     *
     * @param int $percent
     * @return GD
     * @throws \InvalidArgumentException
     */
    public static function RESIZE_PERCENT($percent = 0)
    {
        if (!is_numeric($percent)) {
            throw new \InvalidArgumentException ('$percent must be numeric');
        }

        self::$percent = intval($percent);

        self::CALC_IMAGE_SIZE_PERCENT(self::$currentDimensions['width'], self::$currentDimensions['height']);

        if (function_exists('imagecreatetruecolor')) {
            self::$workingImage = imagecreatetruecolor(self::$newDimensions['newWidth'], self::$newDimensions['newHeight']);
        } else {
            self::$workingImage = imagecreate(self::$newDimensions['newWidth'], self::$newDimensions['newHeight']);
        }

        self::PRESERVE_ALPHA();

        imagecopyresampled(
            self::$workingImage,
            self::$oldImage,
            0,
            0,
            0,
            0,
            self::$newDimensions['newWidth'],
            self::$newDimensions['newHeight'],
            self::$currentDimensions['width'],
            self::$currentDimensions['height']
        );

        self::$oldImage                    = self::$workingImage;
        self::$currentDimensions['width']  = self::$newDimensions['newWidth'];
        self::$currentDimensions['height'] = self::$newDimensions['newHeight'];

        return self::$thiss;
    }

    /**
     * Crops an image from the center with provided dimensions
     *
     * If no height is given, the width will be used as a height, thus creating a square crop
     *
     * @param  int          $cropWidth
     * @param  int          $cropHeight
     * @return GD
     */
    public static function CROP_FROM_CENTER($cropWidth, $cropHeight = null)
    {
        if (!is_numeric($cropWidth)) {
            throw new \InvalidArgumentException('$cropWidth must be numeric');
        }

        if ($cropHeight !== null && !is_numeric($cropHeight)) {
            throw new \InvalidArgumentException('$cropHeight must be numeric');
        }

        if ($cropHeight === null) {
            $cropHeight = $cropWidth;
        }

        $cropWidth  = (self::$currentDimensions['width'] < $cropWidth) ? self::$currentDimensions['width'] : $cropWidth;
        $cropHeight = (self::$currentDimensions['height'] < $cropHeight) ? self::$currentDimensions['height'] : $cropHeight;

        $cropX = intval((self::$currentDimensions['width'] - $cropWidth) / 2);
        $cropY = intval((self::$currentDimensions['height'] - $cropHeight) / 2);

        self::CROP($cropX, $cropY, $cropWidth, $cropHeight);

        return self::$thiss;
    }

    /**
     * Vanilla Cropping - Crops from x,y with specified width and height
     *
     * @param  int          $startX
     * @param  int          $startY
     * @param  int          $cropWidth
     * @param  int          $cropHeight
     * @return GD
     */
    public static function CROP($startX, $startY, $cropWidth, $cropHeight)
    {
        // validate input
        if (!is_numeric($startX)) {
            throw new \InvalidArgumentException('$startX must be numeric');
        }

        if (!is_numeric($startY)) {
            throw new \InvalidArgumentException('$startY must be numeric');
        }

        if (!is_numeric($cropWidth)) {
            throw new \InvalidArgumentException('$cropWidth must be numeric');
        }

        if (!is_numeric($cropHeight)) {
            throw new \InvalidArgumentException('$cropHeight must be numeric');
        }

        // do some calculations
        $cropWidth  = (self::$currentDimensions['width'] < $cropWidth) ? self::$currentDimensions['width'] : $cropWidth;
        $cropHeight = (self::$currentDimensions['height'] < $cropHeight) ? self::$currentDimensions['height'] : $cropHeight;

        // ensure everything's in bounds
        if (($startX + $cropWidth) > self::$currentDimensions['width']) {
            $startX = (self::$currentDimensions['width'] - $cropWidth);
        }

        if (($startY + $cropHeight) > self::$currentDimensions['height']) {
            $startY = (self::$currentDimensions['height'] - $cropHeight);
        }

        if ($startX < 0) {
            $startX = 0;
        }

        if ($startY < 0) {
            $startY = 0;
        }

        // create the working image
        if (function_exists('imagecreatetruecolor')) {
            self::$workingImage = imagecreatetruecolor($cropWidth, $cropHeight);
        } else {
            self::$workingImage = imagecreate($cropWidth, $cropHeight);
        }

        self::PRESERVE_ALPHA();

        imagecopyresampled(
            self::$workingImage,
            self::$oldImage,
            0,
            0,
            $startX,
            $startY,
            $cropWidth,
            $cropHeight,
            $cropWidth,
            $cropHeight
        );

        self::$oldImage                    = self::$workingImage;
        self::$currentDimensions['width']  = $cropWidth;
        self::$currentDimensions['height'] = $cropHeight;

        return self::$thiss;
    }

    /**
     * Rotates image either 90 degrees clockwise or counter-clockwise
     *
     * @param string $direction
     * @return GD
     */
    public static function ROTATE_IMAGE($direction = 'CW')
    {
        if ($direction == 'CW') {
            self::ROTATE_IMAGE_N_DEGREES(90);
        } else {
            self::ROTATE_IMAGE_N_DEGREES(-90);
        }

        return self::$thiss;
    }

    /**
     * Rotates image specified number of degrees
     *
     * @param  int          $degrees
     * @return GD
     */
    public static function ROTATE_IMAGE_N_DEGREES($degrees)
    {
        if (!is_numeric($degrees)) {
            throw new \InvalidArgumentException('$degrees must be numeric');
        }

        if (!function_exists('imagerotate')) {
            throw new \RuntimeException('Your version of GD does not support image rotation');
        }

        self::$workingImage = imagerotate(self::$oldImage, $degrees, 0);

        $newWidth                          = self::$currentDimensions['height'];
        $newHeight                         = self::$currentDimensions['width'];
        self::$oldImage                    = self::$workingImage;
        self::$currentDimensions['width']  = $newWidth;
        self::$currentDimensions['height'] = $newHeight;

        return self::$thiss;
    }

    /**
     * Applies a filter to the image
     *
     * @param  int          $filter
     * @return GD
     */
    public static function IMAGE_FILTER($filter, $arg1 = false, $arg2 = false, $arg3 = false, $arg4 = false)
    {
        if (!is_numeric($filter)) {
            throw new \InvalidArgumentException('$filter must be numeric');
        }

        if (!function_exists('imagefilter')) {
            throw new \RuntimeException('Your version of GD does not support image filters');
        }

        $result = false;
        if ($arg1 === false) {
            $result = imagefilter(self::$oldImage, $filter);
        } elseif ($arg2 === false) {
            $result = imagefilter(self::$oldImage, $filter, $arg1);
        } elseif ($arg3 === false) {
            $result = imagefilter(self::$oldImage, $filter, $arg1, $arg2);
        } elseif ($arg4 === false) {
            $result = imagefilter(self::$oldImage, $filter, $arg1, $arg2, $arg3);
        } else {
            $result = imagefilter(self::$oldImage, $filter, $arg1, $arg2, $arg3, $arg4);
        }

        if (!$result) {
            throw new \RuntimeException('GD imagefilter failed');
        }

        self::$workingImage = self::$oldImage;

        return self::$thiss;
    }

    /**
     * Shows an image
     *
     * This function will show the current image by first sending the appropriate header
     * for the format, and then outputting the image data. If headers have already been sent,
     * a runtime exception will be thrown
     *
     * @param  bool         $rawData Whether or not the raw image stream should be output
     * @return GD
     */
    public static function SHOW($rawData = false)
    {
        //Execute any plugins
        if (self::$plugins) {
            foreach (self::$plugins as $plugin) {
                /* @var $plugin PluginInterface */
                $plugin::EXECUTE(self::$thiss);
            }
        }

        if (headers_sent() && php_sapi_name() != 'cli') {
            throw new \RuntimeException('Cannot show image, headers have already been sent');
        }

        // When the interlace option equals true or false call imageinterlace else leave it to default
        if (self::$options['interlace'] === true) {
            imageinterlace(self::$oldImage, 1);
        } elseif (self::$options['interlace'] === false) {
            imageinterlace(self::$oldImage, 0);
        }

        switch (self::$format) {
            case 'GIF':
                if ($rawData === false) {
                    header('Content-type: image/gif');
                }
                imagegif(self::$oldImage);
                break;
            case 'JPG':
                if ($rawData === false) {
                    header('Content-type: image/jpeg');
                }
                imagejpeg(self::$oldImage, null, self::$options['jpegQuality']);
                break;
            case 'PNG':
            case 'STRING':
                if ($rawData === false) {
                    header('Content-type: image/png');
                }
                imagepng(self::$oldImage);
                break;
        }

        return self::$thiss;
    }

    /**
     * Returns the Working Image as a String
     *
     * This function is useful for getting the raw image data as a string for storage in
     * a database, or other similar things.
     *
     * @return string
     */
    public static function GET_IMAGE_AS_STRING()
    {
        $data = null;
        ob_start();
        self::SHOW(true);
        $data = ob_get_contents();
        ob_end_clean();

        return $data;
    }

    /**
     * Saves an image
     *
     * This function will make sure the target directory is writeable, and then save the image.
     *
     * If the target directory is not writeable, the function will try to correct the permissions (if allowed, this
     * is set as an option (self::$options['correctPermissions']).  If the target cannot be made writeable, then a
     * \RuntimeException is thrown.
     *
     * @param  string       $fileName The full path and filename of the image to save
     * @param  string       $format   The format to save the image in (optional, must be one of [GIF,JPG,PNG]
     * @return GD
     */
    public static function SAVE($fileName, $format = null)
    {
        $validFormats = array('GIF', 'JPG', 'PNG');
        $format = ($format !== null) ? strtoupper($format) : self::$format;

        if (!in_array($format, $validFormats)) {
            throw new \InvalidArgumentException("Invalid format type specified in save function: {$format}");
        }

        // make sure the directory is writeable
        if (!is_writeable(dirname($fileName))) {
            // try to correct the permissions
            if (self::$options['correctPermissions'] === true) {
                @chmod(dirname($fileName), 0777);

                // throw an exception if not writeable
                if (!is_writeable(dirname($fileName))) {
                    throw new \RuntimeException("File is not writeable, and could not correct permissions: {$fileName}");
                }
            } else { // throw an exception if not writeable
                throw new \RuntimeException("File not writeable: {$fileName}");
            }
        }

        // When the interlace option equals true or false call imageinterlace else leave it to default
        if (self::$options['interlace'] === true) {
            imageinterlace(self::$oldImage, 1);
        } elseif (self::$options['interlace'] === false) {
            imageinterlace(self::$oldImage, 0);
        }

        switch ($format) {
            case 'GIF':
                imagegif(self::$oldImage, $fileName);
                break;
            case 'JPG':
                imagejpeg(self::$oldImage, $fileName, self::$options['jpegQuality']);
                break;
            case 'PNG':
                imagepng(self::$oldImage, $fileName);
                break;
        }

        return self::$thiss;
    }

    #################################
    # ----- GETTERS / SETTERS ----- #
    #################################

    /**
     * Sets options for all operations.
     * @param array $options
     * @return GD
     */
    public static function SET_OPTIONS(array $options = array())
    {
        // we've yet to init the default options, so create them here
        if (sizeof(self::$options) == 0) {
            $defaultOptions = array(
                'resizeUp'              => false,
                'jpegQuality'           => 100,
                'correctPermissions'    => false,
                'preserveAlpha'         => true,
                'alphaMaskColor'        => array (255, 255, 255),
                'preserveTransparency'  => true,
                'transparencyMaskColor' => array (0, 0, 0),
                'interlace'             => null
            );
        } else { // otherwise, let's use what we've got already
            $defaultOptions = self::$options;
        }

        self::$options = array_merge($defaultOptions, $options);

        return self::$thiss;
    }

    /**
     * Returns $currentDimensions.
     *
     * @see GD::$currentDimensions
     */
    public static function GET_CURRENT_DIMENSIONS()
    {
        return self::$currentDimensions;
    }

    /**
     * @param $currentDimensions
     * @return GD
     */
    public static function SET_CURRENT_DIMENSIONS($currentDimensions)
    {
        self::$currentDimensions = $currentDimensions;

        return self::$thiss;
    }

    /**
     * @return int
     */
    public static function GET_MAX_HEIGHT()
    {
        return self::$maxHeight;
    }

    /**
     * @param $maxHeight
     * @return GD
     */
    public static function SET_MAX_HEIGHT($maxHeight)
    {
        self::$maxHeight = $maxHeight;

        return self::$thiss;
    }

    /**
     * @return int
     */
    public static function GET_MAX_WIDTH()
    {
        return self::$maxWidth;
    }

    /**
     * @param $maxWidth
     * @return GD
     */
    public static function SET_MAX_WIDTH($maxWidth)
    {
        self::$maxWidth = $maxWidth;

        return self::$thiss;
    }

    /**
     * Returns $newDimensions.
     *
     * @see GD::$newDimensions
     */
    public static function GET_NEW_DIMENSIONS()
    {
        return self::$newDimensions;
    }

    /**
     * Sets $newDimensions.
     *
     * @param object $newDimensions
     * @see GD::$newDimensions
     */
    public static function SET_NEW_DIMENSIONS($newDimensions)
    {
        self::$newDimensions = $newDimensions;

        return self::$thiss;
    }

    /**
     * Returns $options.
     *
     * @see GD::$options
     */
    public static function GET_OPTIONS()
    {
        return self::$options;
    }

    /**
     * Returns $percent.
     *
     * @see GD::$percent
     */
    public static function GET_PERCENT()
    {
        return self::$percent;
    }

    /**
     * Sets $percent.
     *
     * @param object $percent
     * @see GD::$percent
     */
    public static function SET_PERCENT($percent)
    {
        self::$percent = $percent;

        return self::$thiss;
    }

    /**
     * Returns $oldImage.
     *
     * @see GD::$oldImage
     */
    public static function GET_OLD_IMAGE()
    {
        return self::$oldImage;
    }

    /**
     * Sets $oldImage.
     *
     * @param object $oldImage
     * @see GD::$oldImage
     */
    public static function SET_OLD_IMAGE($oldImage)
    {
        self::$oldImage = $oldImage;

        return self::$thiss;
    }

    /**
     * Returns $workingImage.
     *
     * @see GD::$workingImage
     */
    public static function GET_WORKING_IMAGE()
    {
        return self::$workingImage;
    }

    /**
     * Sets $workingImage.
     *
     * @param object $workingImage
     * @see GD::$workingImage
     */
    public static function SET_WORKING_IMAGE($workingImage)
    {
        self::$workingImage = $workingImage;

        return self::$thiss;
    }


    #################################
    # ----- UTILITY FUNCTIONS ----- #
    #################################

    /**
     * Calculates a new width and height for the image based on self::$maxWidth and the provided dimensions
     *
     * @return array
     * @param  int   $width
     * @param  int   $height
     */
    protected static function CALC_WIDTH($width, $height)
    {
        $newWidthPercentage = (100 * self::$maxWidth) / $width;
        $newHeight          = ($height * $newWidthPercentage) / 100;

        return array(
            'newWidth'  => intval(self::$maxWidth),
            'newHeight' => intval($newHeight)
        );
    }

    /**
     * Calculates a new width and height for the image based on self::$maxWidth and the provided dimensions
     *
     * @return array
     * @param  int   $width
     * @param  int   $height
     */
    protected static function CALC_HEIGHT($width, $height)
    {
        $newHeightPercentage = (100 * self::$maxHeight) / $height;
        $newWidth            = ($width * $newHeightPercentage) / 100;

        return array(
            'newWidth'  => ceil($newWidth),
            'newHeight' => ceil(self::$maxHeight)
        );
    }

    /**
     * Calculates a new width and height for the image based on self::$percent and the provided dimensions
     *
     * @return array
     * @param  int   $width
     * @param  int   $height
     */
    protected static function CALC_PERCENT($width, $height)
    {
        $newWidth  = ($width * self::$percent) / 100;
        $newHeight = ($height * self::$percent) / 100;

        return array(
            'newWidth'  => ceil($newWidth),
            'newHeight' => ceil($newHeight)
        );
    }

    /**
     * Calculates the new image dimensions
     *
     * These calculations are based on both the provided dimensions and self::$maxWidth and self::$maxHeight
     *
     * @param int $width
     * @param int $height
     */
    protected static function CALC_IMAGE_SIZE($width, $height)
    {
        $newSize = array(
            'newWidth'  => $width,
            'newHeight' => $height
        );

        if (self::$maxWidth > 0) {
            $newSize = self::CALC_WIDTH($width, $height);

            if (self::$maxHeight > 0 && $newSize['newHeight'] > self::$maxHeight) {
                $newSize = self::CALC_HEIGHT($newSize['newWidth'], $newSize['newHeight']);
            }
        }

        if (self::$maxHeight > 0) {
            $newSize = self::CALC_HEIGHT($width, $height);

            if (self::$maxWidth > 0 && $newSize['newWidth'] > self::$maxWidth) {
                $newSize = self::CALC_WIDTH($newSize['newWidth'], $newSize['newHeight']);
            }
        }

        self::$newDimensions = $newSize;
    }

    /**
     * Calculates new image dimensions, not allowing the width and height to be less than either the max width or height
     *
     * @param int $width
     * @param int $height
     */
    protected static function CALC_IMAGE_SIZE_STRICT($width, $height)
    {
        // first, we need to determine what the longest resize dimension is..
        if (self::$maxWidth >= self::$maxHeight) {
            // and determine the longest original dimension
            if ($width > $height) {
                $newDimensions = self::CALC_HEIGHT($width, $height);

                if ($newDimensions['newWidth'] < self::$maxWidth) {
                    $newDimensions = self::CALC_WIDTH($width, $height);
                }
            } elseif ($height >= $width) {
                $newDimensions = self::CALC_WIDTH($width, $height);

                if ($newDimensions['newHeight'] < self::$maxHeight) {
                    $newDimensions = self::CALC_HEIGHT($width, $height);
                }
            }
        } elseif (self::$maxHeight > self::$maxWidth) {
            if ($width >= $height) {
                $newDimensions = self::CALC_WIDTH($width, $height);

                if ($newDimensions['newHeight'] < self::$maxHeight) {
                    $newDimensions = self::CALC_HEIGHT($width, $height);
                }
            } elseif ($height > $width) {
                $newDimensions = self::CALC_HEIGHT($width, $height);

                if ($newDimensions['newWidth'] < self::$maxWidth) {
                    $newDimensions = self::CALC_WIDTH($width, $height);
                }
            }
        }

        self::$newDimensions = $newDimensions;
    }

    /**
     * Calculates new dimensions based on self::$percent and the provided dimensions
     *
     * @param int $width
     * @param int $height
     */
    protected static function CALC_IMAGE_SIZE_PERCENT($width, $height)
    {
        if (self::$percent > 0) {
            self::$newDimensions = self::CALC_PERCENT($width, $height);
        }
    }

    /**
     * Determines the file format by mime-type
     *
     * This function will throw exceptions for invalid images / mime-types
     *
     */
    protected static function DETERMINE_FORMAT()
    {
        $formatInfo = getimagesize(self::$fileName);

        // non-image files will return false
        if ($formatInfo === false) {
            $fileName = self::$fileName;
            if (self::$remoteImage) {
                throw new \Exception("Could not determine format of remote image: {$fileName}");
            } else {
                throw new \Exception("File is not a valid image: {$fileName}");
            }
        }

        $mimeType = isset($formatInfo['mime']) ? $formatInfo['mime'] : null;

        switch ($mimeType) {
            case 'image/gif':
                self::$format = 'GIF';
                break;
            case 'image/jpeg':
                self::$format = 'JPG';
                break;
            case 'image/png':
                self::$format = 'PNG';
                break;
            default:
                throw new \Exception("Image format not supported: {$mimeType}");
        }
    }

    /**
     * Makes sure the correct GD implementation exists for the file type
     *
     */
    protected static function VERIFY_FORMAT_COMPATIBILITY()
    {
        $isCompatible = true;
        $gdInfo       = gd_info();

        switch (self::$format) {
            case 'GIF':
                $isCompatible = $gdInfo['GIF Create Support'];
                break;
            case 'JPG':
                $isCompatible = (isset($gdInfo['JPG Support']) || isset($gdInfo['JPEG Support'])) ? true : false;
                break;
            case 'PNG':
                $isCompatible = $gdInfo[self::$format . ' Support'];
                break;
            default:
                $isCompatible = false;
        }

        if (!$isCompatible) {
            // one last check for "JPEG" instead
            $isCompatible = $gdInfo['JPEG Support'];
            $format = self::$format;
            if (!$isCompatible) {
                throw new \Exception("Your GD installation does not support {$format} image types");
            }
        }
    }

    /**
     * Preserves the alpha or transparency for PNG and GIF files
     *
     * Alpha / transparency will not be preserved if the appropriate options are set to false.
     * Also, the GIF transparency is pretty skunky (the results aren't awesome), but it works like a
     * champ... that's the nature of GIFs tho, so no huge surprise.
     *
     * This functionality was originally suggested by commenter Aimi (no links / site provided) - Thanks! :)
     *
     */
    protected static function PRESERVE_ALPHA()
    {
        if (self::$format == 'PNG' && self::$options['preserveAlpha'] === true) {
            imagealphablending(self::$workingImage, false);

            $colorTransparent = imagecolorallocatealpha(
                self::$workingImage,
                self::$options['alphaMaskColor'][0],
                self::$options['alphaMaskColor'][1],
                self::$options['alphaMaskColor'][2],
                0
            );

            imagefill(self::$workingImage, 0, 0, $colorTransparent);
            imagesavealpha(self::$workingImage, true);
        }
        // preserve transparency in GIFs... this is usually pretty rough tho
        if (self::$format == 'GIF' && self::$options['preserveTransparency'] === true) {
            $colorTransparent = imagecolorallocate(
                self::$workingImage,
                self::$options['transparencyMaskColor'][0],
                self::$options['transparencyMaskColor'][1],
                self::$options['transparencyMaskColor'][2]
            );

            imagecolortransparent(self::$workingImage, $colorTransparent);
            imagetruecolortopalette(self::$workingImage, true, 256);
        }
    }
}
