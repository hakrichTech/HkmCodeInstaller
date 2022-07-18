<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@Hkm_code.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\CommandsInstaller\Utilities;

use Hkm_code\CLI\BaseCommand;
use Hkm_code\CLI\CLI;
use Hkm_code\Vezirion\ServicesSystem;

/**
 * Lists all of the user-defined routes. This will include any Routes files
 * that can be discovered, but will NOT include any routes that are not defined
 * in a routes file, but are instead discovered through auto-routing.
 */
class Routes extends BaseCommand
{
	/**
	 * The group the command is lumped under
	 * when listing commands.
	 *
	 * @var string
	 */
	protected $group = 'Hkm_code';

	/**
	 * The Command's name
	 *
	 * @var string
	 */
	protected $name = 'routes';

	/**
	 * the Command's short description
	 *
	 * @var string
	 */
	protected $description = 'Displays all of user-defined routes. Does NOT display auto-detected routes.';

	/**
	 * the Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'routes';

	/**
	 * the Command's Arguments
	 *
	 * @var array
	 */
	protected $arguments = [];

	/**
	 * the Command's Options
	 *
	 * @var array
	 */
	protected $options = [];

	//--------------------------------------------------------------------

	/**
	 * Displays the help for the spark cli script itself.
	 *
	 * @param array $params
	 */
	public function run(array $params)
	{
		$collection = ServicesSystem::ROUTES(true);
		$methods    = [
			'get',
			'head',
			'post',
			'patch',
			'put',
			'delete',
			'options',
			'trace',
			'connect',
			'cli',
		];

		$tbody = [];
		foreach ($methods as $method)
		{
			$routes = $collection::GET_ROUTES($method);

			foreach ($routes as $route => $handler)
			{
				// filter for strings, as callbacks aren't displayable
				if (is_string($handler))
				{
					$tbody[] = [
						strtoupper($method),
						$route,
						$handler,
					];
				}
			}
		}

		$thead = [
			'Method',
			'Route',
			'Handler',
		];

		CLI::table($tbody, $thead);
	}
}
