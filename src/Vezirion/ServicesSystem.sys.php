<?php
namespace Hkm_code\Vezirion;

use Hkm_code\Cache\CacheFactory;
use Hkm_code\CLI\Commands;
use Hkm_code\CLI\CommandsInstaller;
use Hkm_code\Database\ConnectionInterface;
use Hkm_code\Database\MigrationRunner;
use Hkm_code\Debug\Timer;
use Hkm_code\Filters\Filters as FiltersFilters;
use Hkm_code\Format\Format;
use Hkm_code\HTTP\CLIRequest;
use Hkm_code\HTTP\IncomingRequest;
use Hkm_code\HTTP\Negotiate;
use Hkm_code\HTTP\RedirectResponse;
use Hkm_code\HTTP\Request;
use Hkm_code\HTTP\Response;
use Hkm_code\HTTP\URI;
use Hkm_code\HTTP\UserAgent;
use Hkm_code\Language\Language;
use Hkm_code\Log\Logger;
use Hkm_code\Pobohet\Pobohet;
use Hkm_code\Pobohet\PobohetCollection;
use Hkm_code\Setup;
use Hkm_code\Validation\Validation;
use Hkm_code\Vezirion\vezirionData\vezirionDataHelper;
use Hkm_code\View\View;

class ServicesSystem extends ServicesBase
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

    public static function LANGUAGE(string $locale = null, bool $getShared = true){

        $local = HKMLANG;
		if ($getShared)
		{
			return static::GET_SHARED_INSTANCE('LANGUAGE', $local);
		}

		return new Language($locale);
	}
    public static function SETUP(bool $getShared = true)
	{
		
		if ($getShared)
		{
			return static::GET_SHARED_INSTANCE('SETUP');
		}

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

	/**
	 * @return IncomingRequest|Request
	 */
	public static function REQUEST( $config = null, bool $getShared = true)
	{
		$config = $config ?? hkm_config('App');
		
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

	public static function URI(bool $getShared = true):URI
	{
		if ($getShared)
		{
			return static::GET_SHARED_INSTANCE('URI');
		}
		$c = hkm_config('App');
		return new URI($c::$baseURL);
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
		$response::SET_PROTOCOL_VERSION(self::REQUEST()::GET_PROTOCOL_VERSION());

		return $response;
	}
    /**
	 * The cache class provides a simple way to store and retrieve
	 * complex data for later.
	 *
	 * @param  Object|null $config
	 * @param boolean    $getShared
	 *
	 * @return CacheInterface
	 */
	public static function CACHE( $config = null, bool $getShared = true)
	{
		$config = $config ?? hkm_config('Cache');
		
		if ($getShared)
		{
			return static::GET_SHARED_INSTANCE('CACHE', $config);
		}


		return CacheFactory::GET_HANDLER($config);
	}

	public static function FILTERS( $config = null, bool $getShared = true)
	{
		
		$config = hkm_config('Filters');

		return new FiltersFilters($config, self::REQUEST(), self::RESPONSE());
	}
    public static function CLI_REQUEST( $config, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::GET_SHARED_INSTANCE('CLI_REQUEST', $config);
		}

		return new CLIRequest($config);
	}
    public static function RESPONSE( $config = null, bool $getShared = true):Response
	{
		if ($getShared)
		{
			return static::GET_SHARED_INSTANCE('RESPONSE', $config);
		}

		$config = $config ?? hkm_config('App');

		return new Response($config);
	}
    public static function COMMANDS(bool $getShared=true):Commands
	{
		if ($getShared)
		{
			return static::GET_SHARED_INSTANCE('COMMANDS');
		}

		return new Commands(self::LOGGER());
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

	public static function COMMANDS_INSTALLER(bool $getShared = true)
	{
		if ($getShared)
		{
			return static::GET_SHARED_INSTANCE('COMMANDS_INSTALLER');
		}
		
		return new CommandsInstaller(self::LOGGER());
	}
	public static function VALIDATION( $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::GET_SHARED_INSTANCE('VALIDATION', $config);
		}

		$config = $config ?? hkm_config('Validation');

		return new Validation($config, self::RENDERER());
	}
    public static function LOGGER(bool $getShared = true)
	{

		if ($getShared)
		{
			return static::GET_SHARED_INSTANCE('LOGGER');
		}

		return new Logger(hkm_config('Logger'));
	}
    public static function DOM_XML_IN_ARRAY(bool $getShared = true)
	{
		if ($getShared)
		{
			return static::GET_SHARED_INSTANCE('DOM_XML_IN_ARRAY');
		}
		return new vezirionDataHelper();
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
		$routes  = $routes ?? self::ROUTES();
		$request = $request ?? self::REQUEST();

		if ($getShared)
		{
			return static::GET_SHARED_INSTANCE('ROUTER', $routes, $request);
		}

		return new Pobohet($routes, $request);
	}
    public static function TIMER(bool $getShared = true)
	{
		if ($getShared)
		{
			return static::GET_SHARED_INSTANCE('TIMER');
		}

		return new Timer();
	}


	public static function RENDERER(string $viewPath = null,  $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::GET_SHARED_INSTANCE('RENDERER', $viewPath, $config);
		}
		global $engine;
		$Locator = self::LOCATOR();
		$files = $Locator::SEARCH('\Config\Hkm_Bin\Paths');
		$files2 = $Locator::SEARCH('\SystemConfig\Paths');

		if(!empty($files)){
			$clas = $Locator::GET_CLASS_NAME($files[0]);
			$viewPath = $viewPath ?: $clas::$viewDirectory;
		}else if(!empty($files2) && $engine == "."){
			$viewPath = $viewPath ?: \SystemConfig\Paths::$viewDirectory;
		}

		$config   = $config ?? hkm_config('View'); 
 
		return new View($config, $viewPath, null, HKM_DEBUG, self::LOGGER());
	}
    
}