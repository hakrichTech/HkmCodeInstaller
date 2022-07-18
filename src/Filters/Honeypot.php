<?php


namespace Hkm_code\Filters;

use Hkm_code\HTTP\Request;
use Hkm_code\HTTP\Response;
use Hkm_code\Vezirion\ServicesSystem;
use Hkm_code\Honeypot\Exceptions\HoneypotException;

class Honeypot {
	
	public static function before(Request $request, $arguments = null)
	{
		$honeypot = ServicesSystem::HONEYPOT(hkm_config('Honeypot'));
		if ($honeypot->hasContent($request))
		{
			throw HoneypotException::isBot();
		}
	}

	
	public static function after(Request $request, Response $response, $arguments = null)
	{
		$honeypot = ServicesSystem::HONEYPOT(hkm_config('Honeypot'));
		$honeypot->attachHoneypot($response);
	}
}
