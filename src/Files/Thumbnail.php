<?php

namespace Hkm_code\Files;

/**
 * Thumbnail : PHP Thumb Library <http://Thumbnail.gxdlabs.com>
 * Copyright (c) 2009, Ian Selby/Gen X Design
 *
 * Author(s): Ian Selby <ian@gen-x-design.com>
 *
 * Licensed under the MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author Ian Selby <ian@gen-x-design.com>
 * @copyright Copyright (c) 2009 Gen X Design
 * @link http://Thumbnail.gxdlabs.com
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */

abstract class Thumbnail
{
    /**
     * The name of the file we're manipulating
     * This must include the path to the file (absolute paths recommended)
     *
     * @var string
     */
    protected static $fileName;

    /**
     * What the file format is (mime-type)
     *
     * @var string
     */
    protected static $format;

    /**
     * Whether or not the image is hosted remotely
     *
     * @var bool
     */
    protected static $remoteImage;

    /**
     * An array of attached plugins to execute in order.
     * @var array
     */
    protected static $plugins;

    /**
     * @param $fileName
     * @param array $options
     * @param array $plugins
     */
    protected static $thiss;

    public  function __construct($fileName, array $options = array(), array $plugins = array())
    {
        self::$thiss = $this;
        self::$fileName    = $fileName;
        self::$remoteImage = false;

        if(!self::VALIDATE_REQUESTED_RESOURSE($fileName)) {
            throw new \InvalidArgumentException("Image file not found: {$fileName}");
        }

        self::$thiss::SET_OPTIONS($options);

        self::$plugins = $plugins;
    }

    abstract public static function SET_OPTIONS(array $options = array());

    /**
     * Check the provided filename/url. If it is a url, validate that it is properly
     * formatted. If it is a file, check to make sure that it actually exists on
     * the filesystem.
     *
     * @param $filename
     * @return bool
     */
    protected static function VALIDATE_REQUESTED_RESOURSE($filename)
    {
        if(false !== filter_var($filename, FILTER_VALIDATE_URL)) {
            self::$remoteImage = true;
            return true;
        }

        if(file_exists($filename)) {
            return true;
        }

        return false;
    }

    /**
     * Returns the filename.
     * @return string
     */
    public static function GET_FILENAME()
    {
        return self::$fileName;
    }

    /**
     * Sets the filename.
     * @param $fileName
     * @return Thumbnail
     */
    public static function SET_FILENAME($fileName)
    {
        self::$fileName = $fileName;

        return self::$thiss;
    }

    /**
     * Returns the format.
     * @return string
     */
    public static function GET_FORMAT()
    {
        return self::$format;
    }

    /**
     * Sets the format.
     * @param $format
     * @return Thumbnail
     */
    public static function SET_FORMAT($format)
    {
        self::$format = $format;

        return self::$thiss;
    }

    /**
     * Returns whether the image exists remotely, i.e. it was loaded via a URL.
     * @return bool
     */
    public static function GET_IS_REMOTE_IMAGE()
    {
        return self::$remoteImage;
    }
}
