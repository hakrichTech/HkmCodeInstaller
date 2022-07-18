<?php


namespace Hkm_code\Exceptions;

use RuntimeException;

class SystemException extends RuntimeException implements ExceptionInterface
{
	use DebugTraceableTrait;

	public static function FOR_ENABLED_ZLIB_OUTPUT_COMPRESSION()
	{
		return new static(hkm_lang('Core.enabledZlibOutputCompression'));
	}

	public static function FOR_INVALID_FILE(string $path)
	{
		return new static(hkm_lang('Core.invalidFile', [$path]));
	}

	public static function FOR_COPY_ERROR(string $path)
	{
		return new static(hkm_lang('Core.copyError', [$path]));
	}

	public static function FOR_MISSING_EXTENSION(string $extension)
	{
		if (strpos($extension, 'intl') !== false)
		{
			// @codeCoverageIgnoreStart
			$message = sprintf(
				'The framework needs the following extension(s) installed and loaded: %s.',
				$extension
			);
			// @codeCoverageIgnoreEnd
		}
		else
		{
			$message = hkm_lang('Core.missingExtension', [$extension]);
		}

		return new static($message);
	}
	public static function FOR_METHOD_NOT_DEFINED(string $path)
	{
		return new static(hkm_lang('Core.method_not_found', [$path]));
	}

	public static function FOR_NO_HANDLERS(string $class)
	{
		return new static(hkm_lang('Core.noHandlers', [$class]));
	}

	public static function FOR_FABRICATOR_CREATE_FAILED(string $table, string $reason)
	{
		return new static(hkm_lang('Fabricator.createFailed', [$table, $reason]));
	}
}
