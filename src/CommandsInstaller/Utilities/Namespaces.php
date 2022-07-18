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
use Hkm_code\Vezirion\AutoloadVezirion;

/**
 * Lists namespaces set in Autoload with their
 * full server path. Helps you to verify that you have
 * the namespaces setup correctly.
 */
class Namespaces extends BaseCommand
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
	protected $name = 'namespaces';

	/**
	 * the Command's short description
	 *
	 * @var string
	 */
	protected $description = 'Verifies your namespaces are setup correctly.';

	/**
	 * the Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'namespaces';

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

		$tbody = [];
		if (defined('SYSTEM') && SYSTEM == true) {
			foreach (AutoloadVezirion::$classmap['system'] as $ns => $path)
			{
				if (is_array($path)) $path = $path[0]; 
				$path = realpath($path) ?: $path;

				$tbody[] = [
					$ns,
					hkm_clean_path(realpath($path) ?: $path),
					is_dir($path) ? 'Yes' : 'MISSING',
				];
			}
		}
		foreach (AutoloadVezirion::$class as $ns => $path)
		{
			if (is_array($path)) $path = $path[0]; 
			$path = realpath($path) ?: $path;

			$tbody[] = [
				$ns,
				hkm_clean_path(realpath($path) ?: $path),
				is_dir($path) ? 'Yes' : 'MISSING',
			];
		}

		$thead = [
			'Namespace',
			'Path',
			'Found?',
		];

		CLI::table($tbody, $thead);
	}
}
