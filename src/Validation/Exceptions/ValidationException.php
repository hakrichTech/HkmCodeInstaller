<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@Hkm_code.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Validation\Exceptions;

use Hkm_code\Exceptions\SystemException;

class ValidationException extends SystemException
{
	public static function FOR_RULE_NOT_FOUND(string $rule = null)
	{
		return new static(hkm_lang('Validation.ruleNotFound', [$rule]));
	}

	public static function FOR_GROUP_NOT_FOUND(string $group = null)
	{
		return new static(hkm_lang('Validation.groupNotFound', [$group]));
	}

	public static function FOR_GROUP_NOT_ARRAY(string $group = null)
	{
		return new static(hkm_lang('Validation.groupNotArray', [$group]));
	}

	public static function FOR_INVALID_TEMPLATE(string $template = null)
	{
		return new static(hkm_lang('Validation.invalidTemplate', [$template]));
	}

	public static function FOR_NO_RULE_SETS()
	{
		return new static(hkm_lang('Validation.noRuleSets'));
	}
}
