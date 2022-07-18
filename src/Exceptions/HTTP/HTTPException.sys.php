<?php

/**
 * This file is part of the Hkm_code 4 System.
 *
 * (c) Hkm_code Foundation <admin@hakrichteam.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
namespace Hkm_code\Exceptions\HTTP;

use Hkm_code\Exceptions\SystemException;

/**
 * Things that can go wrong with HTTP
 */
class HTTPException extends SystemException
{
	/**
	 * For CurlRequest
	 *
	 * @return HTTPException
	 *
	 * @codeCoverageIgnore
	 */
	public static function FOR_MISSING_CURL()
	{
		return new static(hkm_lang('HTTP.missingCurl'));
	}

	/**
	 * For CurlRequest
	 *
	 * @param string $cert
	 *
	 * @return HTTPException
	 */
	public static function FOR_SSL_CERT_NOT_FOUND(string $cert)
	{
		return new static(hkm_lang('HTTP.sslCertNotFound', [$cert]));
	}

	/**
	 * For CurlRequest
	 *
	 * @param string $key
	 *
	 * @return HTTPException
	 */
	public static function FOR_INVALID_SSL_KEY(string $key)
	{
		return new static(hkm_lang('HTTP.invalidSSLKey', [$key]));
	}

	/**
	 * For CurlRequest
	 *
	 * @param string $errorNum
	 * @param string $error
	 *
	 * @return HTTPException
	 *
	 * @codeCoverageIgnore
	 */
	public static function FOR_CURL_ERROR(string $errorNum, string $error)
	{
		return new static(hkm_lang('HTTP.curlError', [$errorNum, $error]));
	}

	/**
	 * For IncomingRequest
	 *
	 * @param string $type
	 *
	 * @return HTTPException
	 */
	public static function FOR_INVALID_NEGOTIATION_TYPE(string $type)
	{
		return new static(hkm_lang('HTTP.invalidNegotiationType', [$type]));
	}

	/**
	 * For Message
	 *
	 * @param string $protocols
	 *
	 * @return HTTPException
	 */
	public static function FOR_INVALID_HTTP_PROTOCOL(string $protocols)
	{
		return new static(hkm_lang('HTTP.invalidHTTPProtocol', [$protocols]));
	}

	/**
	 * For Negotiate
	 *
	 * @return HTTPException
	 */
	public static function FOR_EMPTY_SUPPORTED_NEGOTIATIONS()
	{
		return new static(hkm_lang('HTTP.emptySupportedNegotiations'));
	}

	/**
	 * For RedirectResponse
	 *
	 * @param string $route
	 *
	 * @return HTTPException
	 */
	public static function FOR_INVALID_REDIRECT_ROUTE(string $route)
	{
		return new static(hkm_lang('HTTP.invalidRoute', [$route]));
	}

	/**
	 * For Response
	 *
	 * @return HTTPException
	 */
	public static function FOR_MISSING_RESPONSE_STATUS()
	{
		return new static(hkm_lang('HTTP.missingResponseStatus'));
	}

	/**
	 * For Response
	 *
	 * @param integer $code
	 *
	 * @return HTTPException
	 */
	public static function FOR_INVALID_STATUS_CODE(int $code)
	{
		return new static(hkm_lang('HTTP.invalidStatusCode', [$code]));
	}

	/**
	 * For Response
	 *
	 * @param integer $code
	 *
	 * @return HTTPException
	 */
	public static function FOR_UNKNOWN_STATUS_CODE(int $code)
	{
		return new static(hkm_lang('HTTP.unknownStatusCode', [$code]));
	}

	/**
	 * For URI
	 *
	 * @param string $uri
	 *
	 * @return HTTPException
	 */
	public static function FOR_UNABLE_TO_PARSE_URI(string $uri)
	{
		return new static(hkm_lang('HTTP.cannotParseURI', [$uri]));
	}

	/**
	 * For URI
	 *
	 * @param integer $segment
	 *
	 * @return HTTPException
	 */
	public static function FOR_URI_SEGMENT_OUT_OF_RANGE(int $segment)
	{
		return new static(hkm_lang('HTTP.segmentOutOfRange', [$segment]));
	}

	/**
	 * For URI
	 *
	 * @param integer $port
	 *
	 * @return HTTPException
	 */
	public static function FOR_INVALID_PORT(int $port)
	{
		return new static(hkm_lang('HTTP.invalidPort', [$port]));
	}

	/**
	 * For URI
	 *
	 * @return HTTPException
	 */
	public static function FOR_MAL_FORMED_QUERY_STRING()
	{
		return new static(hkm_lang('HTTP.malformedQueryString'));
	}

	/**
	 * For Uploaded file move
	 *
	 * @return HTTPException
	 */
	public static function FOR_ALREADY_MOVED()
	{
		return new static(hkm_lang('HTTP.alreadyMoved'));
	}

	/**
	 * For Uploaded file move
	 *
	 * @param string|null $path
	 *
	 * @return HTTPException
	 */
	public static function FOR_INVALID_FILE(string $path = null)
	{
		return new static(hkm_lang('HTTP.invalidFile'));
	}

	/**
	 * For Uploaded file move
	 *
	 * @param string $source
	 * @param string $target
	 * @param string $error
	 *
	 * @return HTTPException
	 */
	public static function FOR_MOVE_FAILED(string $source, string $target, string $error)
	{
		return new static(hkm_lang('HTTP.moveFailed', [$source, $target, $error]));
	}

	/**
	 * For Invalid SameSite attribute setting
	 *
	 * @param string $samesite
	 *
	 * @return HTTPException
	 *
	 * @deprecated Use `CookieException::forInvalidSameSite()` instead.
	 *
	 * @codeCoverageIgnore
	 */
	public static function FOR_INVALID_SAME_SITE_SETTING(string $samesite)
	{
		return new static(hkm_lang('Security.invalidSameSiteSetting', [$samesite]));
	}
	
}
