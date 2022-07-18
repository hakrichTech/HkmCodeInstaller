<?php
namespace Hkm_code\Exceptions\Log;

use Hkm_code\Exceptions\SystemException;

class LogException extends SystemException
{
	public static function FOR_INVALID_LOG_LEVEL(string $level)
	{
		return new static(hkm_lang('Log.invalidLogLevel', [$level]));
	}

	public static function FOR_INVALID_MESSAGE_TYPE(string $messageType)
	{
		return new static(hkm_lang('Log.invalidMessageType', [$messageType]));
	}
}
