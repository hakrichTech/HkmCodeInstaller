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
 * Generates a skeleton config file.
 */
class ConfigGenerator extends BaseCommand
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
	protected $name = 'make:config';

	/**
	 * The Command's Description
	 *
	 * @var string
	 */
	protected $description = 'Generates a new config file.';

	/**
	 * The Command's Usage
	 *
	 * @var string
	 */
	protected $usage = 'make:config <name> [options]';

	/**
	 * The Command's Arguments
	 *
	 * @var array
	 */
	protected $arguments = [
		'name' => 'The config class name.',
	];

	/**
	 * The Command's Options
	 *
	 * @var array
	 */
	protected $options = [
		'--namespace' => 'Set root namespace. Default: "SYSTEM_NAMESPACE".',
		'--suffix'    => 'Append the component title to the class name (e.g. User => UserConfig).',
		'--force'     => 'Force overwrite existing file.',
	];

	/**
	 * Actually execute a command.
	 *
	 * @param array $params
	 */
	public function run(array $params)
	{
		$this->component = 'Config';
		$this->directory = 'Vezirion\Settings';
		$this->template  = 'config.tpl.php';

		$this->classNameLang = 'CLI.generator.className.config';
		$this->execute($params);
	}

	/**
	 * Prepare options and do the necessary replacements.
	 *
	 * @param string $class
	 *
	 * @return string
	 */
	protected function prepare(string $class): string
	{
		$namespace = $this->getOption('namespace') ?? "Hkm_code";

		if ($namespace === "Hkm_code")
		{
			$class = substr($class, strlen($namespace . '\\'));
		}

		return $this->parseTemplate($class);
	}
}
