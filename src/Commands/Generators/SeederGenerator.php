<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@Hkm_code.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Commands\Generators;

use Hkm_code\CLI\BaseCommand;
use Hkm_code\CLI\GeneratorTrait;

/**
 * Generates a skeleton seeder file.
 */
class SeederGenerator extends BaseCommand
{
	use GeneratorTrait;

	/**
	 * The Command's Group
	 *
	 * @var string
	 */
	protected $group = 'Generators';

	/**
	 * The Command's Name
	 *
	 * @var string
	 */
	protected $name = 'make:seeder';

	/**
	 * The Command's Description
	 *
	 * @var string
	 */
	protected $description = 'Generates a new seeder file.';

	/**
	 * The Command's Usage
	 *
	 * @var string
	 */
	protected $usage = 'make:seeder <name> [options]';

	/**
	 * The Command's Arguments
	 *
	 * @var array
	 */
	protected $arguments = [
		'name' => 'The seeder class name.',
	];

	/**
	 * The Command's Options
	 *
	 * @var array
	 */
	protected $options = [
		'--namespace' => 'Set root namespace. Default: "APP_NAMESPACE".',
		'--suffix'    => 'Append the component title to the class name (e.g. User => UserSeeder).',
		'--force'     => 'Force overwrite existing file.',
	];

	/**
	 * Actually execute a command.
	 *
	 * @param array $params
	 */
	public function run(array $params)
	{
		$this->component = 'Seeder';
		$this->directory = 'Database\Seeds';
		$this->template  = 'seeder.tpl.php';

		$this->classNameLang = 'CLI.generator.className.seeder';
		$this->execute($params);
	}
}
