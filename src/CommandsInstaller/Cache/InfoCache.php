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
use Hkm_code\I18n\Time;

/**
 * Shows information on the cache.
 */
class InfoCache extends BaseCommand
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
	protected $name = 'cache:info';

	/**
	 * the Command's short description
	 *
	 * @var string
	 */
	protected $description = 'Shows file cache information in the current system.';

	/**
	 * the Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'cache:info';

	/**
	 * Clears the cache
	 *
	 * @param array $params
	 */
	public function run(array $params)
	{
		$config = hkm_config('Cache');
		hkm_helper('number');

		if ($config::$handler !== 'file')
		{
			CLI::error('This command only supports the file cache handler.');

			return;
		}

		$cache  = CacheFactory::GET_HANDLER($config);
		$caches = $cache::GET_CACHE_INFO();
		$tbody  = [];

		foreach ($caches as $key => $field)
		{
			$tbody[] = [
				$key,
				hkm_clean_path($field['server_path']),
			    number_to_size($field['size']),
				Time::CREATE_FROM_TIMESTAMP($field['date']),
			];
		}

		$thead = [
			CLI::color('Name', 'green'),
			CLI::color('Server Path', 'green'),
			CLI::color('Size', 'green'),
			CLI::color('Date', 'green'),
		];

		CLI::table($tbody, $thead);
	}
}
