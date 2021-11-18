<?php

namespace Hkm_code\Exceptions;

use RuntimeException;

/**
 * Class DownloadException
 */
class DownloadException extends RuntimeException implements ExceptionInterface
{
	use DebugTraceableTrait;

	public static function FOR_CONNOT_SET_FILE_PATH(string $path)
	{
		return new static(hkm_lang('HTTP.cannotSetFilepath', [$path]));
	}

	public static function FOR_CANNOT_SET_BINARY()
	{
		return new static(hkm_lang('HTTP.cannotSetBinary'));
	}

	public static function FOR_NOT_FOUND_DOWNLOAD_SOURCE()
	{
		return new static(hkm_lang('HTTP.notFoundDownloadSource'));
	}

	public static function FOR_CANNOT_SET_CACHE()
	{
		return new static(hkm_lang('HTTP.cannotSetCache'));
	}

	public static function FOR_CANNOT_SET_STATUS_CODE(int $code, string $reason)
	{
		return new static(hkm_lang('HTTP.cannotSetStatusCode', [$code, $reason]));
	}
}
