<?php
namespace Hkm_Request\Requests\Exception\HTTP;

use Hkm_Request\Requests\Exception\Requests_Exception_HTTP;

/**
 * Exception for 401 Unauthorized responses
 *
 * @package Requests
 */

/**
 * Exception for 401 Unauthorized responses
 *
 * @package Requests
 */
class Requests_Exception_HTTP_401 extends Requests_Exception_HTTP {
	/**
	 * HTTP status code
	 *
	 * @var integer
	 */
	protected $code = 401;

	/**
	 * Reason phrase
	 *
	 * @var string
	 */
	protected $reason = 'Unauthorized';
}
