<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@hakrichteam.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\CommandsInstaller\Cache;

use Hkm_code\Cache\CacheFactory;
use Hkm_code\CLI\BaseCommand;
use Hkm_code\CLI\CLI;

/**
 * Clears current cache.
 */
class ClearCache extends BaseCommand
{
	/**
	 * Command grouping.
	 *
	 * @var string
	 */
	protected $group = 'Cache';

	/**
	 * The Command's name
	 *
	 * @var string
	 */
	protected $name = 'cache:clear';

	/**
	 * the Command's short description
	 *
	 * @var string
	 */
	protected $description = 'Clears the current system caches.';

	/**
	 * the Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'cache:clear [driver]';

	/**
	 * the Command's Arguments
	 *
	 * @var array
	 */
	protected $arguments = [
		'driver' => 'The cache driver to use',
	];

	/**
	 * Clears the cache
	 *
	 * @param array $params
	 */
	public function run(array $params)
	{
		$config  = hkm_config('Cache');
		$handler = $params[0] ?? $config::$handler;

		if (! array_key_exists($handler, $config::$validHandlers))
		{
			CLI::error($handler . ' is not a valid cache handler.');

			return;
		}

		$config::$handler = $handler;
		$cache           = CacheFactory::GET_HANDLER($config);

		if (! $cache::CLEAN())
		{
			// @codeCoverageIgnoreStart
			CLI::error('Error while clearing the cache.');

			return;
			// @codeCoverageIgnoreEnd
		}

		CLI::write(CLI::color('Cache cleared.', 'green'));
	}
}
