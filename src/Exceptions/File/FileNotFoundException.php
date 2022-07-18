<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@Hkm_code.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Exceptions\File;

use Hkm_code\Exceptions\DebugTraceableTrait;
use Hkm_code\Exceptions\ExceptionInterface;
use RuntimeException;

class FileNotFoundException extends RuntimeException implements ExceptionInterface
{
	use DebugTraceableTrait;

	public static function FOR_FILE_NOT_FOUND(string $path)
	{
		return new static(hkm_lang('Files.fileNotFound', [$path]));
	}
}
