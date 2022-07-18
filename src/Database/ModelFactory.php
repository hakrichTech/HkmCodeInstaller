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

use Hkm_code\Vezirion\Factories;

/**
 * Returns new or shared Model instances
 *
 * @deprecated Use Hkm_code\Factories::models()
 *
 * @codeCoverageIgnore
 */
class ModelFactory
{
	/**
	 * Creates new Model instances or returns a shared instance
	 *
	 * @param string              $name       Model name, namespace optional
	 * @param boolean             $getShared  Use shared instance
	 * @param ConnectionInterface $connection
	 *
	 * @return mixed|null
	 */
	public static function get(string $name, bool $getShared = true, ConnectionInterface $connection = null)
	{
		return Factories::models($name, ['getShared' => $getShared], $connection);
	}

	/**
	 * Helper method for injecting mock instances while testing.
	 *
	 * @param string $name
	 * @param object $instance
	 */
	public static function injectMock(string $name, $instance)
	{
		Factories::injectMock('models', $name, $instance);
	}

	/**
	 * Resets the static arrays
	 */
	public static function reset()
	{
		Factories::reset('models');
	}
}
