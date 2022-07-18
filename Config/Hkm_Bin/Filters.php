<?php

namespace Hkm_Config\Hkm_Bin;


use App\Filters\Login;
use App\Filters\WhiteScreen;
use App\Filters\UserAgent;
use Hkm_code\Vezirion\BaseVezirion;
use Hkm_code\Filters\CSRF;
use Hkm_code\Filters\DebugToolbar;
use Hkm_code\Filters\Honeypot;
use Hkm_Vote\AwardFilter;

class Filters extends BaseVezirion
{
	/**
	 * Configures aliases for Filter classes to
	 * make reading things nicer and simpler.
	 *
	 * @var array
	 */
	public static $aliases = [
		'csrf'     => CSRF::class,
		'toolbar'  => DebugToolbar::class,
		'honeypot' => Honeypot::class,
		'login' => Login::class,
		'upgrading' => WhiteScreen::class,
		'logout' => '',
		'userAgent' => UserAgent::class	
	];

	/**
	 * List of filter aliases that are always
	 * applied before and after every request.
	 *
	 * @var array
	 */
	public static $globals = [
		'before' => [
			// 'honeypot',
			// 'csrf',
			'userAgent'
		],
		'after'  => [
			'toolbar',
			'userAgent'
			// 'honeypot',
		],
	];

	/**
	 * List of filter aliases that works on a
	 * particular HTTP method (GET, POST, etc.).
	 *
	 * Example:
	 * 'post' => ['csrf', 'throttle']
	 *
	 * @var array
	 */
	public static $methods = [];

	/**
	 * List of filter aliases that should run on any
	 * before or after URI patterns.
	 *
	 * Example:
	 * 'isLoggedIn' => ['before' => ['account/*', 'profiles/*']]
	 *
	 * @var array
	 */
	public static $filters = [
       
	];
}
