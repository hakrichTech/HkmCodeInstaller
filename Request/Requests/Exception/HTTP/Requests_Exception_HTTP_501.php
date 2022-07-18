<?php
namespace Hkm_Request\Requests\Exception\HTTP;

use Hkm_Request\Requests\Exception\Requests_Exception_HTTP;

/**
 * Exception for 501 Not Implemented responses
 *
 * @package Requests
 */

/**
 * Exception for 501 Not Implemented responses
 *
 * @package Requests
 */
class Requests_Exception_HTTP_501 extends Requests_Exception_HTTP {
	/**
	 * HTTP status code
	 *
	 * @var integer
	 */
	protected $code = 501;

	/**
	 * Reason phrase
	 *
	 * @var string
	 */
	protected $reason = 'Not Implemented';
}
