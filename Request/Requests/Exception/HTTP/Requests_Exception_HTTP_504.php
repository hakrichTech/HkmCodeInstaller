<?php

namespace Hkm_Request\Requests\Exception\HTTP;

use Hkm_Request\Requests\Exception\Requests_Exception_HTTP;

/**
 * Exception for 504 Gateway Timeout responses
 *
 * @package Requests
 */

/**
 * Exception for 504 Gateway Timeout responses
 *
 * @package Requests
 */
class Requests_Exception_HTTP_504 extends Requests_Exception_HTTP {
	/**
	 * HTTP status code
	 *
	 * @var integer
	 */
	protected $code = 504;

	/**
	 * Reason phrase
	 *
	 * @var string
	 */
	protected $reason = 'Gateway Timeout';
}
