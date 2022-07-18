<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@hakrichteam.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Database;

use GuzzleHttp\Promise\Is;
use InvalidArgumentException;
use Hkm_code\Vezirion\BaseVezirion;
use Hkm_WebsiteHostingServer\HkmServer;

/**
 * Class Config
 */
class Vezirion extends BaseVezirion{
	/**
	 * Cache for instance of any connections that
	 * have been requested as a "shared" instance.
	 *
	 * @var array
	 */
	static protected $instances = [];

	/**
	 * The main instance used to manage all of
	 * our open database connections.
	 *
	 * @var Database|null
	 */
	static protected $factory;

	//--------------------------------------------------------------------
	/**
	 * Creates the default
	 *
	 * @param string|array $group     The name of the connection group to use,
	 *                                or an array of configuration settings.
	 * @param boolean      $getShared Whether to return a shared instance of the connection.
	 *
	 * @return BaseConnection
	 */
	public static function CONNECT($group = null, bool $getShared = false)
	{
		
		// If a DB connection is passed in, just pass it back
		if ($group instanceof BaseConnection)
		{
			return $group;
		}

		

		if (is_array($group))
		{
			$config = $group;
			$group  = 'custom-' . md5(json_encode($config));
		}

		$config = $config ?? hkm_config('Database');
		
		if (empty($group))
		{
			$group = ENVIRONMENT === 'testing' ? 'tests' : $config::$defaultGroup;
		}

		if (is_string($group) && ! isset($config::$$group) && strpos($group, 'custom-') !== 0 && !(strpos($group,"db.config.") !== false))
		{
			throw new InvalidArgumentException($group . ' is not a valid database connection group.');
		}


		if ($getShared && isset(static::$instances[$group]))
		{
			return static::$instances[$group];
		}


		static::ENSURE_FACTORY();

		if (isset($config::$$group))
		{
			
			if ($config::$$group == "system") {
				$config0 = require(__DIR__."/../../.sysConfig");
			}elseif($group == 'network'){
				global $currentAppDatabase;
				if(!empty($currentAppDatabase)){
					$config0 = $currentAppDatabase;
				}else{
					print "this group database not exist";
					exit;
				}
			}else{
				global $engine;
				if (defined('SYSTEM') && SYSTEM==true) {
				   $config0 = require(__DIR__."/../../.sysConfig");	
				}else{
				   $config0 = $config::$$group;	
				}
			}
		}

		if (is_array($config0) && is_string($group)) {
			$connection = static::$factory::LOAD($config0, $group);

		     $connection::INITIALIZE();

		}

		
		if ($config::$$group != "system" && $config::$$group != "network") {
		    static::$instances[$group] = $connection;
		}



		
		return $connection;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an array of all db connections currently made.
	 *
	 * @return array
	 */
	public static function getConnections(): array
	{
		return static::$instances;
	}

	/**
	 * Loads and returns an instance of the Forge for the specified
	 * database group, and loads the group if it hasn't been loaded yet.
	 *
	 * @param ConnectionInterface|string|array|null $group
	 *
	 * @return Forge
	 */
	public static function FORGE($group = null)
	{
		$db = static::CONNECT($group);

		return static::$factory::LOAD_FORGE($db);
	}

	//--------------------------------------------------------------------
	/**
	 * Returns a new instance of the Database Utilities class.
	 *
	 * @param string|array|null $group
	 *
	 * @return BaseUtils
	 */
	public static function utils($group = null)
	{
		$db = static::connect($group);

		return static::$factory::LOAD_UTILS($db);
	}

	//--------------------------------------------------------------------
	/**
	 * Returns a new instance of the Database Seeder.
	 *
	 * @param string|null $group
	 *
	 * @return Seeder
	 */
	public static function seeder(string $group = null)
	{
		$config = hkm_config('Database');

		return new Seeder($config, static::CONNECT($group));
	}

	//--------------------------------------------------------------------

	/**
	 * Ensures the database Connection Manager/Factory is loaded and ready to use.
	 */
	protected static function ENSURE_FACTORY()
	{
		if (static::$factory instanceof Database)
		{
			return;
		}

		static::$factory = new Database();
	}

	//--------------------------------------------------------------------
}
