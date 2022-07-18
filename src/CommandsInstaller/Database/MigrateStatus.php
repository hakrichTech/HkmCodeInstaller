<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@hakrichteam.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\CommandsInstaller\Database;

use Hkm_code\CLI\CLI;
use Hkm_code\CLI\BaseCommand;
use Hkm_code\Vezirion\AutoloadVezirion;
use Hkm_code\Vezirion\ServicesSystem;

/**
 * Displays a list of all migrations and whether they've been run or not.
 */
class MigrateStatus extends BaseCommand
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
	protected $name = 'migrate:status';

	/**
	 * the Command's short description
	 *
	 * @var string
	 */
	protected $description = 'Displays a list of all migrations and whether they\'ve been run or not.';

	/**
	 * the Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'migrate:status [options]';

	/**
	 * the Command's Options
	 *
	 * @var array<string, string>
	 */
	protected $options = [
		'-g' => 'Set database group',
		'-n' => 'Set database group',
	];

	/**
	 * Namespaces to ignore when looking for migrations.
	 *
	 * @var string[]
	 */
	protected $ignoredNamespaces = [
		'Hkm_code',
		'Kint',
		'Laminas\ZendFrameworkBridge',
		'Laminas\Escaper',
		'Psr\Log',
	];

	/**
	 * Displays a list of all migrations and whether they've been run or not.
	 *
	 * @param array<string, mixed> $params
	 *
	 * @return void
	 */
	public function run(array $params)
	{
		$runner = ServicesSystem::MIGRATIONS(null,null,false);
		$group  = $params['g'] ?? CLI::getOption('g');
		$namespace  = $params['n'] ?? CLI::getOption('n');

		// Get all namespaces
		$namespaces = array_keys(array_merge(AutoloadVezirion::$classmap['system'],AutoloadVezirion::$class));
		$namespaces[] = '____plugins____migration_files';


		// Collection of migration status
		$status = [];
		if ($namespace) {
			$runner::SET_NAMESPACE($namespace);
		}else $runner::SET_NAMESPACE(null);
		$history = $runner::GET_HISTORY($group?(string) $group:'default');

		foreach ($namespaces as $namespace)
		{
			if (ENVIRONMENT !== 'testing')
			{
				// Make Tests\\Support discoverable for testing
				$this->ignoredNamespaces[] = 'Tests\Support'; // @codeCoverageIgnore
			}

			if (in_array($namespace, $this->ignoredNamespaces, true))
			{
				continue;
			}
     
			if (APP_NAMESPACE !== 'App' && $namespace === 'App' && SYSTEM)
			{
				continue; // @codeCoverageIgnore
			}
            $migrations = [];
				$migrations = $runner::FIND_NAMESPACE_MIGRATIONS($namespace);

			if (empty($migrations))
			{
				continue;
			}

			ksort($migrations);

			foreach ($migrations as $uid => $migration)
			{
				$migrations[$uid]->name = mb_substr($migration->name, mb_strpos($migration->name, $uid . '_'));
				$date  = '---';
				$group = $group??'---';
				$batch = '---';

				foreach ($history as $row)
				{
					// @codeCoverageIgnoreStart
					if ($runner::GET_OBJECT_UID($row) !== $migration->uid)
					{
						continue;
					}

					$date  = date('Y-m-d H:i:s', $row->time);
					$group = $row->groupe;
					$batch = $row->batch;
					// @codeCoverageIgnoreEnd
				}

				$status[] = [
					$migration->namespace,
					$migration->version,
					$migration->name,
					$group,
					$date,
					$batch,
				];
			}
		}

		if (! $status)
		{
			// @codeCoverageIgnoreStart
			CLI::error(hkm_lang('Migrations.noneFound'), 'light_gray', 'red');
			CLI::newLine();

			return;
			// @codeCoverageIgnoreEnd
		}

		$headers = [
			CLI::color(hkm_lang('Migrations.namespace'), 'yellow'),
			CLI::color(hkm_lang('Migrations.version'), 'yellow'),
			CLI::color(hkm_lang('Migrations.filename'), 'yellow'),
			CLI::color(hkm_lang('Migrations.group'), 'yellow'),
			CLI::color(str_replace(': ', '', hkm_lang('Migrations.on')), 'yellow'),
			CLI::color(hkm_lang('Migrations.batch'), 'yellow'),
		];

		CLI::table($status, $headers);
	}
}
