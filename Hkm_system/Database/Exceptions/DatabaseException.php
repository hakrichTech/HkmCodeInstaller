<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@Hkm_code.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Database\Exceptions;

use Error;

class DatabaseException extends Error implements ExceptionInterface
{
	/**
	 * Exit status code
	 *
	 * @var integer
	 */
	protected $code = 8;
}
