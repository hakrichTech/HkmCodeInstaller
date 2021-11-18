<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Pager\Exceptions;

use Hkm_code\Exceptions\FrameworkException;

class PagerException extends FrameworkException
{
	public static function forInvalidTemplate(string $template = null)
	{
		return new static(hkm_lang('Pager.invalidTemplate', [$template]));
	}

	public static function forInvalidPaginationGroup(string $group = null)
	{
		return new static(hkm_lang('Pager.invalidPaginationGroup', [$group]));
	}
}
