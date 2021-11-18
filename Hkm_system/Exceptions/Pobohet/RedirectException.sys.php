<?php


namespace Hkm_code\Exceptions\Pobohet;

use Exception;

/**
 * RedirectException
 */
class RedirectException extends Exception
{
	/**
	 * Status code for redirects
	 *
	 * @var integer
	 */
	protected $code = 302;
}
