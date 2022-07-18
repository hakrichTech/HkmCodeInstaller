<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@Hkm_code.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\CommandsInstaller\Housekeeping;

use Hkm_code\CLI\BaseCommand;
use Hkm_code\CLI\CLI;

/**
 * ClearDebugbar Command
 */
class ClearDebugbar extends BaseCommand
{
	/**
	 * The group the command is lumped under
	 * when listing commands.
	 *
	 * @var string
	 */
	protected $group = 'Housekeeping';

	/**
	 * The Command's name
	 *
	 * @var string
	 */
	protected $name = 'debugbar:clear';

	/**
	 * The Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'debugbar:clear';

	/**
	 * The Command's short description.
	 *
	 * @var string
	 */
	protected $description = 'Clears all debugbar JSON files.';

	/**
	 * Actually runs the command.
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public function run(array $params)
	{
		hkm_helper('filesystem');

		if (! hkm_delete_files(WRITEPATH . 'debugbar'))
		{
			// @codeCoverageIgnoreStart
			CLI::error('Error deleting the debugbar JSON files.');
			CLI::newLine();
			return;
			// @codeCoverageIgnoreEnd
		}

		CLI::write('Debugbar cleared.', 'green');
		CLI::newLine();
	}
}
