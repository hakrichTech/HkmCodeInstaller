<?php


namespace Hkm_code\Exceptions;

/**
 * Exception for automatic logging.
 */
class ConfigException extends CriticalError
{
	use DebugTraceableTrait;

	/**
	 * Error code
	 *
	 * @var integer
	 */
	protected $code = 3;

	public static function FOR_DISABLED_MIGRATIONS()
	{
		return new static(hkm_lang('Migrations.disabled'));
	}
}
