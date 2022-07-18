<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@Hkm_code.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\CommandsInstaller\Database;

use Hkm_code\CLI\BaseCommand;
use Hkm_code\CLI\CLI;
use Hkm_code\Vezirion\ServicesSystem;
use Throwable;

/**
 * Runs all new migrations.
 */
class Migrate extends BaseCommand
{
	/**
	 * The group the command is lumped under
	 * when listing commands.
	 *
	 * @var string
	 */
	protected $group = 'Database';

	/**
	 * The Command's name
	 *
	 * @var string
	 */
	protected $name = 'migrate';

	/**
	 * the Command's short description
	 *
	 * @var string
	 */
	protected $description = 'Locates and runs all new migrations against the database.';

	/**
	 * the Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'migrate [options]';

	/**
	 * the Command's Options
	 *
	 * @var array
	 */
	protected $options = [
		'-n'    => 'Set migration namespace',
		'-g'    => 'Set database group',
		'--all' => 'Set for all namespaces, will ignore (-n) option',
	];

	/**
	 * Ensures that all migrations have been run.
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public function run(array $params)
	{
		// $d = $this->AppName();
        // $app = $d[2];
		// define('APP_TO_RUN',$app);
		// define('APP_TO_RUN_PATH',$d[1]);

		$runner = ServicesSystem::MIGRATIONS();
		$runner::CLEAR_CLI_MESSAGES();

		CLI::write(hkm_lang('Migrations.latest'), 'yellow');

		$namespace = $params['n'] ?? CLI::getOption('n');
		$group     = $params['g'] ?? CLI::getOption('g');

		try
		{
			// Check for 'all' namespaces
			if (array_key_exists('all', $params) || CLI::getOption('all'))
			{
				$runner::SET_NAMESPACE(null);
			}
			// Check for a specified namespace
			elseif ($namespace)
			{
				$runner::SET_NAMESPACE($namespace);
			}
              
			if (! $runner::LATEST($group))
			{
				CLI::error(hkm_lang('Migrations.generalFault'), 'light_gray', 'red'); // @codeCoverageIgnore
			}

			$messages = $runner::GET_CLI_MESSAGES();

			foreach ($messages as $message)
			{
				CLI::write($message);
			}

			CLI::write('Done migrations.', 'green');
		}
		// @codeCoverageIgnoreStart
		catch (Throwable $e)
		{
			$this->showError($e);
		}
		// @codeCoverageIgnoreEnd
	}
}
