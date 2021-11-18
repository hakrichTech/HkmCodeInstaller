<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Session\Exceptions;

use Hkm_code\Exceptions\SystemException;

class SessionException extends SystemException
{
	public static function forMissingDatabaseTable()
	{
		return new static(hkm_lang('Session.missingDatabaseTable'));
	}

	public static function forInvalidSavePath(string $path = null)
	{
		return new static(hkm_lang('Session.invalidSavePath', [$path]));
	}

	public static function forWriteProtectedSavePath(string $path = null)
	{
		return new static(hkm_lang('Session.writeProtectedSavePath', [$path]));
	}

	public static function forEmptySavepath()
	{
		return new static(hkm_lang('Session.emptySavePath'));
	}

	public static function forInvalidSavePathFormat(string $path)
	{
		return new static(hkm_lang('Session.invalidSavePathFormat', [$path]));
	}

	/**
	 * @deprecated
	 *
	 * @codeCoverageIgnore
	 */
	public static function forInvalidSameSiteSetting(string $samesite)
	{
		return new static(hkm_lang('Session.invalidSameSiteSetting', [$samesite]));
	}
}
