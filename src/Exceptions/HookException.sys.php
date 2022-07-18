<?php



namespace Hkm_code\Exceptions;

use RuntimeException;

/**
 * Class DownloadException
 */
class HookException extends RuntimeException implements ExceptionInterface
{
	use DebugTraceableTrait;


}