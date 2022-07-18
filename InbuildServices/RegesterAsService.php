<?php

namespace Hkm_services;

use Hkm_code\Vezirion\ServicesSystem;
use Hkm_Config\Hkm_Bin\Services;
use Hkm_services\Cookie\CookieJar;
use Hkm_services\Auth\AuthProvider;

class RegesterAsService extends Services
{
	public $helpers = [
		"InbuildServices/Auth/Auth.php",
		"InbuildServices/WebsiteManager/HKMWebsitemanager.php",
		"InbuildServices/ShortenUrl/ShortenUrl.php",
		"InbuildServices/Category/CategoryHelper.php",
        "InbuildServices/Tag/TagHelper.php",
        "InbuildServices/Admin/Admin.php",
        "InbuildServices/Blog/blog.php",

	];
	public $name = ['SESSION','AUTHBASIC'];

    public static function SESSION($config = null, bool $getShared = true):\Hkm_services\Session\Store
	{

		if ($getShared)
		{
			return static::GET_SHARED_INSTANCE('SESSION', $config);
		}

		$config = $config ?? hkm_config('App');


		$driverName = $config::$sessionDriver;

		$driver     = new $driverName(new CookieJar(),$config::$sessionExpiration);


		$session = new \Hkm_services\Session\Store($config::$sessionCookieName,$driver,'LOGGED_IN_KEY');

		if (session_status() === PHP_SESSION_NONE)
		{
			$session->start();
		}

		return $session;
    }
    
	public static function AUTHBASIC($config = null, bool $getShared = true): \Hkm_services\Auth\BasicAuth
	{

		if ($getShared)
		{
			return static::GET_SHARED_INSTANCE('AUTHBASIC', $config);
		}

		$config = $config ?? hkm_config('App');

		$auth = new \Hkm_services\Auth\BasicAuth(new AuthProvider($config),ServicesSystem::SESSION(),ServicesSystem::REQUEST());
		return $auth;

    }
    // {addon Service}

    // do not edit or modify the line above
}
