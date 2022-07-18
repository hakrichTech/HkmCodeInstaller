<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@hakrichteam.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Exceptions;

/**
 * Model Exceptions.
 */

class ModelException extends SystemException
{
	public static function FOR_NO_PRIMARY_KEY(string $modelName)
	{
		return new static(hkm_lang('Database.noPrimaryKey', [$modelName]));
	}

	public static function FOR_NO_DATE_FORMAT(string $modelName)
	{
		return new static(hkm_lang('Database.noDateFormat', [$modelName]));
	}
}
