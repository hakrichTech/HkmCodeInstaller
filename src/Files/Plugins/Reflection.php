<?php

namespace Hkm_code\Files\Plugins;

use Hkm_code\Files\GD;
use Hkm_code\Files\Thumbnail;
use Hkm_code\Files\PluginInterface;

/**
 * GD Reflection Lib Plugin Definition File
 *
 * This file contains the plugin definition for the GD Reflection Lib for PHP Thumb
 *
 * PHP Version 5.3 with GD 2.0+
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
 * @version 3.0
 * @package PhpThumb
 * @filesource
 */

/**
 * GD Reflection Lib Plugin
 *
 * This plugin allows you to create those fun Apple(tm)-style reflections in your images
 *
 * @package PhpThumb
 * @subpackage Plugins
 */
class Reflection implements PluginInterface
{
    protected static $currentDimensions;
    protected static $workingImage;
    protected static $newImage;
    protected static $options;

    protected static $percent;
    protected static $reflection;
    protected static $white;
    protected static $border;
    protected static $borderColor;
    protected static $reflectionHeight;

    public function __construct($percent, $reflection, $white, $border, $borderColor)
    {
        self::$percent     = $percent;
        self::$reflection  = $reflection;
        self::$white       = $white;
        self::$border      = $border;
        self::$borderColor = $borderColor;
    }

    /**
     * @param GD $phpthumb
     * @return GD
     */
    public static function EXECUTE($phpthumb)
    {
        self::$currentDimensions = $phpthumb::GET_CURRENT_DIMENSIONS();
        self::$workingImage      = $phpthumb::GET_WORKING_IMAGE();
        self::$newImage          = $phpthumb::GET_OLD_IMAGE();
        self::$options           = $phpthumb::GET_OPTIONS();

        $width                  = self::$currentDimensions['width'];
        $height                 = self::$currentDimensions['height'];
        self::$reflectionHeight = intval($height * (self::$reflection / 100));
        $newHeight              = $height + self::$reflectionHeight;
        $reflectedPart          = $height * (self::$percent / 100);

        self::$workingImage = imagecreatetruecolor($width, $newHeight);

        imagealphablending(self::$workingImage, true);

        $colorToPaint = imagecolorallocatealpha(
            self::$workingImage,
            255,
            255,
            255,
            0
        );

        imagefilledrectangle(
            self::$workingImage,
            0,
            0,
            $width,
            $newHeight,
            $colorToPaint
        );

        imagecopyresampled(
            self::$workingImage,
            self::$newImage,
            0,
            0,
            0,
            $reflectedPart,
            $width,
            self::$reflectionHeight,
            $width,
            ($height - $reflectedPart)
        );

        self::IMAGE_FLIP_VERTICAL();

        imagecopy(
            self::$workingImage,
            self::$newImage,
            0,
            0,
            0,
            0,
            $width,
            $height
        );

        imagealphablending(self::$workingImage, true);

        for ($i = 0; $i < self::$reflectionHeight; $i++) {
            $colorToPaint = imagecolorallocatealpha(
                self::$workingImage,
                255,
                255,
                255,
                ($i / self::$reflectionHeight * -1 + 1) * self::$white
            );

            imagefilledrectangle(
                self::$workingImage,
                0,
                $height + $i,
                $width,
                $height + $i,
                $colorToPaint
            );
        }

        if (self::$border == true) {
            $rgb          = self::HEX2RGB(self::$borderColor, false);
            $colorToPaint = imagecolorallocate(self::$workingImage, $rgb[0], $rgb[1], $rgb[2]);

            //top line
            imageline(
                self::$workingImage,
                0,
                0,
                $width,
                0,
                $colorToPaint
            );

            //bottom line
            imageline(
                self::$workingImage,
                0,
                $height,
                $width,
                $height,
                $colorToPaint
            );

            //left line
            imageline(
                self::$workingImage,
                0,
                0,
                0,
                $height,
                $colorToPaint
            );

            //right line
            imageline(
                self::$workingImage,
                $width - 1,
                0,
                $width - 1,
                $height,
                $colorToPaint
            );
        }

        if ($phpthumb::GET_FORMAT() == 'PNG') {
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

        $phpthumb::SET_OLD_IMAGE(self::$workingImage);
        self::$currentDimensions['width']  = $width;
        self::$currentDimensions['height'] = $newHeight;
        $phpthumb::SET_CURRENT_DIMENSIONS(self::$currentDimensions);

        return $phpthumb;
    }

    /**
     * Flips the image vertically
     *
     */
    protected static function IMAGE_FLIP_VERTICAL ()
    {
        $x_i = imagesx(self::$workingImage);
        $y_i = imagesy(self::$workingImage);

        for ($x = 0; $x < $x_i; $x++) {
            for ($y = 0; $y < $y_i; $y++) {
                imagecopy(
                    self::$workingImage,
                    self::$workingImage,
                    $x,
                    $y_i - $y - 1,
                    $x,
                    $y,
                    1,
                    1
                );
            }
        }
    }

    /**
     * Converts a hex color to rgb tuples
     *
     * @return mixed
     * @param  string $hex
     * @param  bool   $asString
     */
    protected static function HEX2RGB ($hex, $asString = false)
    {
        // strip off any leading #
        if (0 === strpos($hex, '#')) {
           $hex = substr($hex, 1);
        } elseif (0 === strpos($hex, '&H')) {
           $hex = substr($hex, 2);
        }

        // break into hex 3-tuple
        $cutpoint = ceil(strlen($hex) / 2)-1;
        $rgb      = explode(':', wordwrap($hex, $cutpoint, ':', $cutpoint), 3);

        // convert each tuple to decimal
        $rgb[0] = (isset($rgb[0]) ? hexdec($rgb[0]) : 0);
        $rgb[1] = (isset($rgb[1]) ? hexdec($rgb[1]) : 0);
        $rgb[2] = (isset($rgb[2]) ? hexdec($rgb[2]) : 0);

        return ($asString ? "{$rgb[0]} {$rgb[1]} {$rgb[2]}" : $rgb);
    }
}
