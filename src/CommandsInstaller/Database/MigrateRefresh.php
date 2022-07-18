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

/**
 * Does a rollback followed by a latest to refresh the current state
 * of the database.
 */
class MigrateRefresh extends BaseCommand
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
	protected $name = 'migrate:refresh';

	/**
	 * the Command's short description
	 *
	 * @var string
	 */
	protected $description = 'Does a rollback followed by a latest to refresh the current state of the database.';

	/**
	 * the Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'migrate:refresh [options]';

	/**
	 * the Command's Options
	 *
	 * @var array
	 */
	protected $options = [
		'-n'    => 'Set migration namespace',
		'-g'    => 'Set database group',
		'--all' => 'Set latest for all namespace, will ignore (-n) option',
		'-f'    => 'Force command - this option allows you to bypass the confirmation question when running this command in a production environment',
	];

	/**
	 * Does a rollback followed by a latest to refresh the current state
	 * of the database.
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public function run(array $params)
	{
		$params['b'] = 0;

		if (ENVIRONMENT === 'production')
		{
			// @codeCoverageIgnoreStart
			$force = array_key_exists('f', $params) || CLI::getOption('f');

			if (! $force && CLI::prompt(hkm_lang('Migrations.refreshConfirm'), ['y', 'n']) === 'n')
			{
				return;
			}

			$params['f'] = null;
			// @codeCoverageIgnoreEnd
		}

		$this->call('migrate:rollback', $params);
		$this->call('migrate', $params);
	}
}
