<?php
namespace Hkm_Request\Requests\Exception\HTTP;

use Hkm_Request\Requests\Exception\Requests_Exception_HTTP;

/**
 * Exception for 409 Conflict responses
 *
 * @package Requests
 */

/**
 * Exception for 409 Conflict responses
 *
 * @package Requests
 */
class Requests_Exception_HTTP_409 extends Requests_Exception_HTTP {
	/**
	 * HTTP status code
	 *
	 * @var integer
	 */
	protected $code = 409;

	/**
	 * Reason phrase
	 *
	 * @var string
	 */
	protected $reason = 'Conflict';
}
