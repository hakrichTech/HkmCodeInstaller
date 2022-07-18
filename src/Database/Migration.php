<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@Hkm_code.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Database;


/**
 * Class Migration
 */
abstract class Migration
{
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

	//--------------------------------------------------------------------

	/**
	 * Returns the database group name this migration uses.
	 *
	 * @return string
	 */
	public static function GET_DB_GROUP(): ?string
	{
		return self::$DBGroup;
	}

	//--------------------------------------------------------------------

	/**
	 * Perform a migration step.
	 */
	abstract public static function UP();

	//--------------------------------------------------------------------

	/**
	 * Revert a migration step.
	 */
	abstract public static function DOWN();

	//--------------------------------------------------------------------
}
