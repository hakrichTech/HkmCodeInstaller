<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@hakrichteam.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Files\Exceptions;

use Hkm_code\Exceptions\DebugTraceableTrait;
use Hkm_code\Exceptions\ExceptionInterface;
use RuntimeException;

class FileException extends RuntimeException implements ExceptionInterface
{
	use DebugTraceableTrait;

	public static function FOR_UNABLE_TO_MOVE(string $from = null, string $to = null, string $error = null)
	{
		return new static(hkm_lang('Files.cannotMove', [$from, $to, $error]));
	}
}
