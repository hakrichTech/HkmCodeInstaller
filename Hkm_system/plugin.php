<?php

/**
 * This file is part of the @HkmCode 1 framework.
 *
 * (c) HkmCode Foundation <admin@hkmcode.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code;

use Hkm_code\Database\SQLite3\Forge;
use Hkm_code\Database\BaseConnection;



/**
 * Class plugin
 */
abstract class plugin
{
	/**
	 * The name of the plugin.
	 *
	 * @var string
	 */
	protected static $plugin_name;

	/**
	 * The URL of the plugin
	 *
	 * @var string
	 */
	protected static $plugin_uri;

	/**
	 * Description
	 *
	 * @var string
	 */
	protected static $Description;


	/**
	 * Author.
	 *
	 * @var string
	 */
	protected static $Author;


	/**
	 * Author URI.
	 *
	 * @var string
	 */
	protected static $Author_uri;

	/**
	 * Version.
	 *
	 * @var string
	 */
	protected static $version;


	/**
	 * The name of the database group to use.
	 *
	 * @var string
	 */
	protected static $DBGroup;


	/**
	 * Database Connection instance
	 *
	 * @var BaseConnection
	 */
	protected static $db;

	/**
	 * Database Forge instance.
	 *
	 * @var Forge
	 */
	protected static $forge;
	//--------------------------------------------------------------------
	/**
	 * Constructor.
	 *
	 * @param Forge $forge
	 */
	public  function __construct(Forge $forge = null)
	{
		
		
		self::$forge = ! is_null($forge) ? $forge : hkm_config('Database')::FORGE(self::$DBGroup ?? hkm_config('Database')::$defaultGroup);

		self::$db = self::$forge->getConnection();
	}

	/**
	 * Returns the Author uri of this plugin.
	 *
	 * @return string
	 */
	public static function GET_AUTHOR_URI()
	{
		return self::$Author_uri;
	}


	/**
	 * Returns the Author of this plugin.
	 *
	 * @return string
	 */
	public static function GET_AUTHOR()
	{
		return self::$Author;
	}



	/**
	 * Returns the Description of this plugin.
	 *
	 * @return string
	 */
	public static function GET_DESCRIPTION()
	{
		return self::$Description;
	}



	/**
	 * Returns the version of this plugin.
	 *
	 * @return string
	 */
	public static function GET_VERSION()
	{
		return self::$version;
	}

	/**
	 * Returns the plugin name.
	 *
	 * @return string
	 */
	public static function GET_PLUGIN_NAME()
	{
		return self::$plugin_name;
	}

	/**
	 * Returns the plugin uri.
	 *
	 * @return string
	 */
	public static function GET_PLUGIN_URI()
	{
		return self::$plugin_uri;
	}
	//--------------------------------------------------------------------

	/**
	 * Returns the database group name this plugin uses.
	 *
	 * @return string
	 */
	public static function GET_DB_GROUP(): ?string
	{
		return self::$DBGroup;
	}

	//--------------------------------------------------------------------

	/**
	 * Perform a plugin step.
	 */
	abstract public static function INSTALL();
	

	//--------------------------------------------------------------------

	/**
	 * Revert a plugin step.
	 */
	abstract public static function UNINSTALL();

	//--------------------------------------------------------------------
}
