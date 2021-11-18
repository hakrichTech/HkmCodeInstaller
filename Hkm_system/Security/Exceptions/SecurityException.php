<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Security\Exceptions;

use Hkm_code\Exceptions\SystemException;

class SecurityException extends SystemException
{
	public static function forDisallowedAction()
	{
		return new static(hkm_lang('Security.disallowedAction'), 403);
	}

	/**
	 * @deprecated Use `CookieException::forInvalidSameSite()` instead.
	 *
	 * @codeCoverageIgnore
	 */
	public static function forInvalidSameSite(string $samesite)
	{
		return new static(hkm_lang('Security.invalidSameSite', [$samesite]));
	}
}
