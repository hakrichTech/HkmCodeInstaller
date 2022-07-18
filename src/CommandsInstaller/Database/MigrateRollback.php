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
 * Runs all of the migrations in reverse order, until they have
 * all been unapplied.
 */
class MigrateRollback extends BaseCommand
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
	protected $name = 'migrate:rollback';

	/**
	 * the Command's short description
	 *
	 * @var string
	 */
	protected $description = 'Runs the "down" method for all migrations in the last batch.';

	/**
	 * the Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'migrate:rollback [options]';

	/**
	 * the Command's Options
	 *
	 * @var array
	 */
	protected $options = [
		'-b' => 'Specify a batch to roll back to; e.g. "3" to return to batch #3 or "-2" to roll back twice',
		'-g' => 'Set database group',
		'-f' => 'Force command - this option allows you to bypass the confirmation question when running this command in a production environment',
	];

	/**
	 * Runs all of the migrations in reverse order, until they have
	 * all been unapplied.
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public function run(array $params)
	{
		if (ENVIRONMENT === 'production')
		{
			// @codeCoverageIgnoreStart
			$force = array_key_exists('f', $params) || CLI::getOption('f');

			if (! $force && CLI::prompt(hkm_lang('Migrations.rollBackConfirm'), ['y', 'n']) === 'n')
			{
				return;
			}
			// @codeCoverageIgnoreEnd
		}

		$runner = ServicesSystem::MIGRATIONS();
		$group  = $params['g'] ?? CLI::getOption('g');

		if (is_string($group))
		{
			$runner::SET_GROUP($group);
		}

		try
		{
			$batch = $params['b'] ?? CLI::getOption('b') ?? $runner::GET_LAST_BATCH() - 1;
			CLI::write(hkm_lang('Migrations.rollingBack') . ' ' . $batch, 'yellow');

			if (! $runner::REGRESS($batch))
			{
				CLI::error(hkm_lang('Migrations.generalFault'), 'light_gray', 'red'); // @codeCoverageIgnore
			}

			$messages = $runner::GET_CLI_MESSAGES();

			foreach ($messages as $message)
			{
				CLI::write($message);
			}

			CLI::write('Done rolling back migrations.', 'green');
		}
		// @codeCoverageIgnoreStart
		catch (Throwable $e)
		{
			$this->showError($e);
		}
		// @codeCoverageIgnoreEnd
	}
}
