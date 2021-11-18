<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\API;

use Hkm_code\Format\FormatterInterface;
use Hkm_code\HTTP\IncomingRequest;
use Hkm_code\HTTP\Response;
use Hkm_code\Vezirion\Services;

/**
 * Response trait.
 *
 * Provides common, more readable, methods to provide
 * consistent HTTP responses under a variety of common
 * situations when working as an API.
 *
 * @property IncomingRequest $request
 * @property Response        $response
 */
trait ResponseTrait
{
	/**
	 * Allows child classes to override the
	 * status code that is used in their API.
	 *
	 * @var array
	 */
	protected static $codes = [
		'created'                   => 201,
		'deleted'                   => 200,
		'updated'                   => 200,
		'no_content'                => 204,
		'invalid_request'           => 400,
		'unsupported_response_type' => 400,
		'invalid_scope'             => 400,
		'temporarily_unavailable'   => 400,
		'invalid_grant'             => 400,
		'invalid_credentials'       => 400,
		'invalid_refresh'           => 400,
		'no_data'                   => 400,
		'invalid_data'              => 400,
		'access_denied'             => 401,
		'unauthorized'              => 401,
		'invalid_client'            => 401,
		'forbidden'                 => 403,
		'resource_not_found'        => 404,
		'not_acceptable'            => 406,
		'resource_exists'           => 409,
		'conflict'                  => 409,
		'resource_gone'             => 410,
		'payload_too_large'         => 413,
		'unsupported_media_type'    => 415,
		'too_many_requests'         => 429,
		'server_error'              => 500,
		'unsupported_grant_type'    => 501,
		'not_implemented'           => 501,
	];

	/**
	 * How to format the response data.
	 * Either 'json' or 'xml'. If blank will be
	 * determine through content negotiation.
	 *
	 * @var string
	 */
	protected static $format = 'json';

	/**
	 * Current Formatter instance. This is usually set by ResponseTrait::format
	 *
	 * @var FormatterInterface
	 */
	protected static $formatter;

	//--------------------------------------------------------------------

	/**
	 * Provides a single, simple method to return an API response, formatted
	 * to match the requested format, with proper content-type and status code.
	 *
	 * @param array|string|null $data
	 * @param integer           $status
	 * @param string            $message
	 *
	 * @return mixed
	 */
	public static function RESPOND($data = null, int $status = null, string $message = '')
	{
		// If data is null and status code not provided, exit and bail
		if ($data === null && $status === null)
		{
			$status = 404;

			// Create the output var here in case of self::$response([]);
			$output = null;
		}
		// If data is null but status provided, keep the output empty.
		elseif ($data === null && is_numeric($status))
		{
			$output = null;
		}
		else
		{
			$status = empty($status) ? 200 : $status;
			$output = self::FORMAT($data);
		}

		if (! is_null($output))
		{
			if (self::$format === 'json')
			{
				return self::$response::SET_JSON($output)::SET_STATUS_CODE($status, $message);
			}

			if (self::$format === 'xml')
			{
				return self::$response::SET_XML($output)::SET_STATUS_CODE($status, $message);
			}
		}

		return self::$response::SET_BODY($output)::SET_STATUS_CODE($status, $message);
	}

	//--------------------------------------------------------------------

	/**
	 * Used for generic failures that no custom methods exist for.
	 *
	 * @param string|array $messages
	 * @param integer      $status        HTTP status code
	 * @param string|null  $code          Custom, API-specific, error code
	 * @param string       $customMessage
	 *
	 * @return mixed
	 */
	public static function FAIL($messages, int $status = 400, string $code = null, string $customMessage = '')
	{
		if (! is_array($messages))
		{
			$messages = ['error' => $messages];
		}

		$response = [
			'status'   => $status,
			'error'    => $code ?? $status,
			'messages' => $messages,
		];

		return self::RESPOND($response, $status, $customMessage);
	}

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	// Response Helpers
	//--------------------------------------------------------------------

	/**
	 * Used after successfully creating a new resource.
	 *
	 * @param mixed  $data    Data.
	 * @param string $message Message.
	 *
	 * @return mixed
	 */
	public static function RESPOND_CREATED($data = null, string $message = '')
	{
		return self::RESPOND($data, self::$codes['created'], $message);
	}

	//--------------------------------------------------------------------

	/**
	 * Used after a resource has been successfully deleted.
	 *
	 * @param mixed  $data    Data.
	 * @param string $message Message.
	 *
	 * @return mixed
	 */
	public static function RESPOND_DELETED($data = null, string $message = '')
	{
		return self::RESPOND($data, self::$codes['deleted'], $message);
	}

	/**
	 * Used after a resource has been successfully updated.
	 *
	 * @param mixed  $data    Data.
	 * @param string $message Message.
	 *
	 * @return mixed
	 */
	public static function RESPOND_UPDATED($data = null, string $message = '')
	{
		return self::RESPOND($data, self::$codes['updated'], $message);
	}

	//--------------------------------------------------------------------

	/**
	 * Used after a command has been successfully executed but there is no
	 * meaningful reply to send back to the client.
	 *
	 * @param string $message Message.
	 *
	 * @return mixed
	 */
	public static function RESPOND_NO_CONTENT(string $message = 'No Content')
	{
		return self::RESPOND(null, self::$codes['no_content'], $message);
	}

	//--------------------------------------------------------------------

	/**
	 * Used when the client is either didn't send authorization information,
	 * or had bad authorization credentials. User is encouraged to try again
	 * with the proper information.
	 *
	 * @param string $description
	 * @param string $code
	 * @param string $message
	 *
	 * @return mixed
	 */
	public static function FAIL_UNAUTHORIZED(string $description = 'Unauthorized', string $code = null, string $message = '')
	{
		return self::FAIL($description, self::$codes['unauthorized'], $code, $message);
	}

	//--------------------------------------------------------------------

	/**
	 * Used when access is always denied to this resource and no amount
	 * of trying again will help.
	 *
	 * @param string $description
	 * @param string $code
	 * @param string $message
	 *
	 * @return mixed
	 */
	public static function FAIL_FORBIDDEN(string $description = 'Forbidden', string $code = null, string $message = '')
	{
		return self::FAIL($description, self::$codes['forbidden'], $code, $message);
	}

	//--------------------------------------------------------------------

	/**
	 * Used when a specified resource cannot be found.
	 *
	 * @param string $description
	 * @param string $code
	 * @param string $message
	 *
	 * @return mixed
	 */
	public static function FAIL_NOT_FOUND(string $description = 'Not Found', string $code = null, string $message = '')
	{
		return self::FAIL($description, self::$codes['resource_not_found'], $code, $message);
	}

	//--------------------------------------------------------------------

	/**
	 * Used when the data provided by the client cannot be validated.
	 *
	 * @param string $description
	 * @param string $code
	 * @param string $message
	 *
	 * @return mixed
	 *
	 * @deprecated Use FAIL_VALIDATION_ERRORs instead
	 */
	public static function FAIL_VALIDATION_ERROR(string $description = 'Bad Request', string $code = null, string $message = '')
	{
		return self::FAIL($description, self::$codes['invalid_data'], $code, $message);
	}

	/**
	 * Used when the data provided by the client cannot be validated on one or more fields.
	 *
	 * @param string|string[] $errors
	 * @param string|null 	  $code
	 * @param string		  $message
	 *
	 * @return mixed
	 */
	public static function FAIL_VALIDATION_ERRORS($errors, string $code = null, string $message = '')
	{
		return self::FAIL($errors, self::$codes['invalid_data'], $code, $message);
	}

	//--------------------------------------------------------------------

	/**
	 * Use when trying to create a new resource and it already exists.
	 *
	 * @param string $description
	 * @param string $code
	 * @param string $message
	 *
	 * @return mixed
	 */
	public static function FAIL_RESOURCE_EXISTS(string $description = 'Conflict', string $code = null, string $message = '')
	{
		return self::FAIL($description, self::$codes['resource_exists'], $code, $message);
	}

	//--------------------------------------------------------------------

	/**
	 * Use when a resource was previously deleted. This is different than
	 * Not Found, because here we know the data previously existed, but is now gone,
	 * where Not Found means we simply cannot find any information about it.
	 *
	 * @param string $description
	 * @param string $code
	 * @param string $message
	 *
	 * @return mixed
	 */
	public static function FAIL_RESOURCE_GONE(string $description = 'Gone', string $code = null, string $message = '')
	{
		return self::FAIL($description, self::$codes['resource_gone'], $code, $message);
	}

	//--------------------------------------------------------------------

	/**
	 * Used when the user has made too many requests for the resource recently.
	 *
	 * @param string $description
	 * @param string $code
	 * @param string $message
	 *
	 * @return mixed
	 */
	public static function FAIL_TOO_MANY_REQUESTS(string $description = 'Too Many Requests', string $code = null, string $message = '')
	{
		return self::FAIL($description, self::$codes['too_many_requests'], $code, $message);
	}

	//--------------------------------------------------------------------

	/**
	 * Used when there is a server error.
	 *
	 * @param string      $description The error message to show the user.
	 * @param string|null $code        A custom, API-specific, error code.
	 * @param string      $message     A custom "reason" message to return.
	 *
	 * @return Response The value of the Response's send() method.
	 */
	public static function FAIL_SERVER_ERROR(string $description = 'Internal Server Error', string $code = null, string $message = ''): Response
	{
		return self::FAIL($description, self::$codes['server_error'], $code, $message);
	}

	//--------------------------------------------------------------------
	// Utility Methods
	//--------------------------------------------------------------------

	/**
	 * Handles formatting a response. Currently makes some heavy assumptions
	 * and needs updating! :)
	 *
	 * @param string|array|null $data
	 *
	 * @return string|null
	 */
	protected static function FORMAT($data = null)
	{
		// If the data is a string, there's not much we can do to it...
		if (is_string($data))
		{
			// The content type should be text/... and not application/...
			$contentType = self::$response::GET_HEADER_LINE('Content-Type');
			$contentType = str_replace('application/json', 'text/html', $contentType);
			$contentType = str_replace('application/', 'text/', $contentType);
			self::$response::SET_CONTENT_TYPE($contentType);
			self::$format = 'html';

			return $data;
		}

		$format = Services::FORMAT();
		$f=self::$format;
		$mime   = "application/{$f}";

		// Determine correct response type through content negotiation if not explicitly declared
		if (empty(self::$format) || ! in_array(self::$format, ['json', 'xml'], true))
		{
			$mime = self::$request::NEGOTIATE('media', $format::GET_CONFIG()::$supportedResponseFormats, false);
		}

		self::$response::SET_CONTENT_TYPE($mime);

		// if we don't have a formatter, make one
		if (! isset(self::$formatter))
		{
			// if no formatter, use the default
			self::$formatter = $format::GET_FORMATTER($mime);
		}

		if ($mime !== 'application/json')
		{
			// Recursively convert objects into associative arrays
			// Conversion not required for JSONFormatter
			$data = json_decode(json_encode($data), true);
		}

		return self::$formatter::FORMAT($data);
	}

	/**
	 * Sets the format the response should be in.
	 *
	 * @param string $format
	 *
	 * @return $this
	 */
	public static function SET_RESPONSE_FORMAT(string $format = null)
	{
		self::$format = strtolower($format);

		return self::$thiss;
	}
}
