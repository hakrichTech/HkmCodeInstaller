<?php

namespace Hkm_code\Vezirion;

use Hkm_code\Setup;
use Hkm_code\HTTP\URI;
use Hkm_code\View\Cell;
use Hkm_code\View\View;
use Hkm_code\Log\Logger;
use Hkm_code\Debug\Timer;
use Hkm_code\View\Parser;
use TeamsMailerSystem\Mailer;
use Hkm_code\CLI\Commands;
use Hkm_code\HTTP\Request;
use Hkm_code\Debug\Toolbar;
use Hkm_code\HTTP\Response;
use Hkm_code\HTTP\Negotiate;
use Hkm_code\HTTP\UserAgent;
use Hkm_code\Filters\Filters;
use Hkm_code\HTTP\CLIRequest;
use Hkm_code\Pobohet\Pobohet;
use Hkm_code\Language\Language;
use Hkm_code\Cache\CacheFactory;
use Hkm_code\Cache\CacheInterface;
use Hkm_code\CLI\CommandsInstaller;
use Hkm_code\HTTP\IncomingRequest;
use Hkm_code\Validation\Validation;
use Hkm_code\Database\ConnectionInterface;
use Hkm_code\Database\MigrationRunner;
use Hkm_code\Format\Format;
use Hkm_code\HTTP\RedirectResponse;
use Hkm_code\View\RendererInterface;
use Hkm_code\Pobohet\PobohetCollection;
use Hkm_code\Session\Session;
use Hkm_code\Vezirion\vezirionData\vezirionDataHelper;



class Services extends ServicesBase
{
	
	
	public static function MIGRATIONS( $config = null, ConnectionInterface $db = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::GET_SHARED_INSTANCE('migrations', $config, $db);
		}

		$config = $config ?? hkm_config('Migrations');

		return new MigrationRunner($config, $db);
	}

	public static function SETUP()
	{
		
		
		return new Setup(hkm_config('App'));
	}
	public static function NEGOTIATOR(Request $request = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::GET_SHARED_INSTANCE('NEGOTIATOR', $request);
		}

		$request = $request ?? self::REQUEST();

		return new Negotiate($request);
	}
	
	public static function REQUEST(\Hkm_code\Vezirion\BaseVezirion $config = null, bool $getShared = true)
	{
		$config = $config ?? hkm_config('Cache');
		
		if ($getShared)
		{
			return static::GET_SHARED_INSTANCE('REQUEST', $config);
		}
		
		return new IncomingRequest(
			$config,
			self::URI(),
			'php://input',
			new UserAgent()
		);
	}

	public static function URI(bool $getShared = true)
	{
		if ($getShared)
		{
			return static::GET_SHARED_INSTANCE('URI');
		}
		return new URI();
	}
     /**
	 * The Redirect class provides nice way of working with redirects.
	 *
	 * @param App|null $config
	 * @param boolean  $getShared
	 *
	 * @return RedirectResponse
	 */
	public static function REDIRECT_RESPONSE($config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::GET_SHARED_INSTANCE('REDIRECT_RESPONSE', $config);
		}
		$config   = $config ?? hkm_config('App');
		$response = new RedirectResponse($config);
		$response::SET_PROTOCOL_VERSION(Services::REQUEST()::GET_PROTOCOL_VERSION());

		return $response;
	}

	public static function SESSION($config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::GET_SHARED_INSTANCE('SESSION', $config);
		}

		$config = $config ?? hkm_config('App');
		$logger = Services::LOGGER();

		$driverName = $config::$sessionDriver;
		$driver     = new $driverName($config, Services::REQUEST()::GET_IP_ADDRESS());
		$driver->setLogger($logger);

		$session = new Session($driver, $config);
		$session->setLogger($logger);

		if (session_status() === PHP_SESSION_NONE)
		{
			$session->start();
		}

		return $session;
	}

	/**
	 * The Format class is a convenient place to create Formatters.
	 *
	 * @param FormatConfig|null $config
	 * @param boolean           $getShared
	 *
	 * @return Format
	 */
	public static function FORMAT($config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::GET_SHARED_INSTANCE('FORMAT', $config);
		}

		$config = $config ?? hkm_config('Format');

		return new Format($config);
	}


	/**
	 * The cache class provides a simple way to store and retrieve
	 * complex data for later.
	 *
	 * @param \Hkm_code\Vezirion\BaseVezirion |null $config
	 * @param boolean    $getShared
	 *
	 * @return CacheInterface
	 */
	public static function CACHE(\Hkm_code\Vezirion\BaseVezirion $config = null, bool $getShared = true)
	{
		$config = $config ?? hkm_config('Cache');
		
		if ($getShared)
		{
			return static::GET_SHARED_INSTANCE('CACHE', $config);
		}


		return CacheFactory::GET_HANDLER($config);
	}

	public static function FILTERS(\Hkm_code\Vezirion\BaseVezirion $config = null, bool $getShared = true)
	{
		
		$config = $config ?? hkm_config('Filters');

		return new Filters($config, Services::REQUEST(), Services::RESPONSE());
	}

	public static function ROUTES(bool $getShared = true):PobohetCollection
	{
		if ($getShared)
		{
			return static::GET_SHARED_INSTANCE('ROUTES');
		}

		$routes = new PobohetCollection();
		$routes::INITIALIZE();

		return $routes;
	}

	public static function ROUTER(PobohetCollection $routes = null, Request $request = null, bool $getShared = true)
	{
		$routes  = $routes ?? Services::ROUTES();
		$request = $request ?? Services::REQUEST();

		if ($getShared)
		{
			return static::GET_SHARED_INSTANCE('ROUTER', $routes, $request);
		}

		return new Pobohet($routes, $request);
	}

	public static function DOM_XML_IN_ARRAY(bool $getShared = true)
	{
		if ($getShared)
		{
			return static::GET_SHARED_INSTANCE('DOM_XML_IN_ARRAY');
		}
		return new vezirionDataHelper();
	}

	public static function TEAMS_MAILER(bool $getShared = true)
	{
		if ($getShared)
		{
			return static::GET_SHARED_INSTANCE('TEAMS_MAILER');
		}

		// $config = $config ?? hkm_config('App');

		return new Mailer(true);
	}

	public static function TIMER(bool $getShared = true)
	{
		
		return new Timer();
	}
	public static function CLI_REQUEST(\Hkm_code\Vezirion\BaseVezirion $config, bool $getShared = true)
	{
		return new CLIRequest($config);
	}
	public static function RESPONSE( $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::GET_SHARED_INSTANCE('RESPONSE', $config);
		}

		$config = $config ?? hkm_config('App');

		return new Response($config);
	}
	public static function LANGUAGE(string $locale = null, bool $getShared = true){
		
		$locale = $locale ?: hkm_config('App')::$defaultLocale;

		return new Language($locale);
	}
	public static function LOGGER(bool $getShared = true)
	{

		return new Logger(hkm_config('Logger'));
	}
	public static function VALIDATION(\Hkm_code\Vezirion\BaseVezirion $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::GET_SHARED_INSTANCE('VALIDATION', $config);
		}

		$config = $config ?? hkm_config('Validation');

		return new Validation($config, self::RENDERER());
	}

	public static function COMMANDS()
	{
		return new Commands(self::LOGGER());
	}

	public static function COMMANDS_INSTALLER(bool $getShared = true)
	{
		
		return new CommandsInstaller(self::LOGGER());
	}

	public static function RENDERER(string $viewPath = null, \Hkm_code\Vezirion\BaseVezirion $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::GET_SHARED_INSTANCE('RENDERER', $viewPath, $config);
		}

		$viewPath = $viewPath ?: hkm_config('Paths')::$viewDirectory;
		$config   = $config ?? hkm_config('View');

		return new View($config, $viewPath, null, HKM_DEBUG, Services::LOGGER());
	}
	public static function TOOLBAR( $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::GET_SHARED_INSTANCE('TOOLBAR', $config);
		}

		$config = $config ?? hkm_config('Toolbar');

		return new Toolbar($config);
	}

}
