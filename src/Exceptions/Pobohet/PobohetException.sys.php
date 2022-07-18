<?php
namespace Hkm_code\Exceptions\Pobohet;

use Hkm_code\Exceptions\SystemException;

class PobohetException extends SystemException
{
    public static function FOR_INVALID_PARAMETER_TYPE()
	{
		return new static(hkm_lang('Pobohet.invalidParameterType'));
    }
    public static function FOR_MISSING_DEFAULT_ROUTE()
	{
		return new static(hkm_lang('Pobohet.missingDefaultRoute'));
    }
    public static function FOR_CONTROLLER_NOT_FOUND(string $controller, string $method)
	{
		return new static(hkm_lang('HTTP.controllerNotFound', [$controller, $method]));
    }
    public static function FOR_INVALID_ROUTE(string $route)
	{
		return new static(hkm_lang('HTTP.invalidRoute', [$route]));
	}
}


