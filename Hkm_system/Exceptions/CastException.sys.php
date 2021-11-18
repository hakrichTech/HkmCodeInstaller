<?php


namespace Hkm_code\Exceptions;

class CastException extends CriticalError
{
	use DebugTraceableTrait;

	/**
	 * Error code
	 *
	 * @var integer
	 */
	protected $code = 3;

	public static function FOR_INVALID_JSON_FORMAT_EXCEPTION(int $error)
	{
		switch ($error)
		{
			case JSON_ERROR_DEPTH:
				return new static(hkm_lang('Cast.jsonErrorDepth'));
			case JSON_ERROR_STATE_MISMATCH:
				return new static(hkm_lang('Cast.jsonErrorStateMismatch'));
			case JSON_ERROR_CTRL_CHAR:
				return new static(hkm_lang('Cast.jsonErrorCtrlChar'));
			case JSON_ERROR_SYNTAX:
				return new static(hkm_lang('Cast.jsonErrorSyntax'));
			case JSON_ERROR_UTF8:
				return new static(hkm_lang('Cast.jsonErrorUtf8'));
			default:
				return new static(hkm_lang('Cast.jsonErrorUnknown'));
		}
	}
}
