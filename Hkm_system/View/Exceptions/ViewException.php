<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@Hkm_code.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\View\Exceptions;

use Hkm_code\Exceptions\SystemException;

class ViewException extends SystemException
{
	public static function FOR_INVALID_CELL_METHOD(string $class, string $method)
	{
		return new static(hkm_lang('View.invalidCellMethod', ['class' => $class, 'method' => $method]));
	}

	public static function FOR_MISSING_CELL_PARAMETERS(string $class, string $method)
	{
		return new static(hkm_lang('View.missingCellParameters', ['class' => $class, 'method' => $method]));
	}

	public static function FOR_INVALID_CELL_PARAMETER(string $key)
	{
		return new static(hkm_lang('View.invalidCellParameter', [$key]));
	}

	public static function FOR_NO_CELL_CLASS()
	{
		return new static(hkm_lang('View.noCellClass'));
	}

	public static function FOR_INVALID_CELL_CLASS(string $class = null)
	{
		return new static(hkm_lang('View.invalidCellClass', [$class]));
	}

	public static function FOR_TAG_SYNTAX_ERROR(string $output)
	{
		return new static(hkm_lang('View.tagSyntaxError', [$output]));
	}
}
