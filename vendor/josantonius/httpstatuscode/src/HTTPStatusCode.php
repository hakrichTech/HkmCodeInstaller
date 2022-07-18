<?php declare(strict_types=1);
/**
 * PHP library to get the meaning from HTTP response status codes.
 * 
 * @category   JST
 * @package    HTTPStatusCode
 * @subpackage HTTPStatusCode
 * @author     Josantonius - info@josantonius.com
 * @copyright  Copyright (c) 2016 JST PHP Framework
 * @license    https://opensource.org/licenses/MIT - The MIT License (MIT)
 * @version    1.0.0
 * @link       https://github.com/Josantonius/PHP-HTTPStatusCode
 * @since      File available since 1.0.0 - Update: 2016-12-19
 */

namespace Josantonius\HTTPStatusCode;

# use Josantonius\HTTPStatusCode\Exception\HTTPStatusCodeException;

/**
 * HTTP response status codes handler.
 *
 * @since 1.0.0
 */
class HTTPStatusCode {

    /**
     * HTTP status code list.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public static $status;

    /**
     * Default language to display notices.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public static $lang;

    /**
     * Load Jsond file with HTTP response status code definitions.
     *
     * @since 1.0.0
     *
     * @param string $lang → language
     *
     * @return array → HTTP response status code
     */
    protected static function load(string $lang = 'en'): array {

        if ($lang != static::$lang) {

            static::$status = null;

            static::$lang = $lang;
        }

        if (is_null(static::$status)) {

            $filepath = __DIR__ . "/resources/http-status-code.jsond";

            $jsonFile = file_get_contents($filepath);

            $status = json_decode($jsonFile, true);

            static::$status = $status['data'][$lang];
        }

        return static::$status;
    }

    /**
     * Get meaning from an HTTP response code.
     * 
     * @since 1.0.0
     *
     * @param int    $code →  100  -  511  → HTTP status code definition
     * @param string $code → "1xx" - "5xx" → HTTP status code general type definition
     * @param string $lang                 → language for definition
     * @param string $size →    "short"    → short definition
     *                          "large"    → large definition
     *
     * @return string → HTTP status code definition
     */
    public static function get(int $code, string $lang = 'en', string $size = 'short'): string {

        static::load($lang);

        return static::$status[$code][$size] ?? "Undefined";
    }

    /**
     * Get all meanings in a array.
     *
     * @since 1.0.0
     *
     * @param string $lang → language
     *
     * @return array → all definitions of HTTP response codes
     */
    public static function getAll(string $lang = 'en'): array {

        return static::load($lang);
    }
}
