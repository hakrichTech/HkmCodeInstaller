<?php


namespace Hkm_code\Filters;

use Hkm_code\HTTP\Request;
use Hkm_code\HTTP\Response;
use Hkm_code\Vezirion\Services;
use Hkm_code\Honeypot\Exceptions\HoneypotException;

class Honeypot {
	
	public static function before(Request $request, $arguments = null)
	{
		$honeypot = Services::HONEYPOT(hkm_config('Honeypot'));
		if ($honeypot->hasContent($request))
		{
			throw HoneypotException::isBot();
		}
	}

	
	public static function after(Request $request, Response $response, $arguments = null)
	{
		$honeypot = Services::HONEYPOT(hkm_config('Honeypot'));
		$honeypot->attachHoneypot($response);
	}
}
