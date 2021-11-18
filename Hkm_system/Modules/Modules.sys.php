<?php


namespace Hkm_code\Modules;

class Modules
{
	/**
	 * Auto-Discover
	 *
	 * @var boolean
	 */
	public static $enabled = true;

	/**
	 * Auto-Discovery Within Composer Packages
	 *
	 * @var boolean
	 */
	public static $discoverInComposer = true;

	/**
	 * Auto-Discover Rules Handler
	 *
	 * @var array
	 */
	protected static $modulesLength_psr_4 = [];

	/**
	 * Should the application auto-discover the requested resource.
	 *
	 * @param string $alias
	 *
	 * @return boolean
	 */
	public static function SHOULD_DISCOVER(string $module): bool
	{
		if (! self::$enabled)
		{
			return false;
		}

		return in_array($module, self::$modulesLength_psr_4, true);
	}
}
