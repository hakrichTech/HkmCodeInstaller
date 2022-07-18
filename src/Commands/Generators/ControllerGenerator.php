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
use Hkm_code\CLI\CLI;
use Hkm_code\CLI\GeneratorTrait;

/**
 * Generates a skeleton controller file.
 */
class ControllerGenerator extends BaseCommand
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
	protected $name = 'make:controller';

	/**
	 * The Command's Description
	 *
	 * @var string
	 */
	protected $description = 'Generates a new controller file.';

	/**
	 * The Command's Usage
	 *
	 * @var string
	 */
	protected $usage = 'make:controller <name> [options]';

	/**
	 * The Command's Arguments
	 *
	 * @var array
	 */
	protected $arguments = [
		'name' => 'The controller class name.',
	];

	/**
	 * The Command's Options
	 *
	 * @var array
	 */
	protected $options = [
		'--bare'      => 'Extends from Hkm_code\Controller instead of BaseController.',
		'--restful'   => 'Extends from a RESTful resource, Options: [controller, presenter]. Default: "controller".',
		'--namespace' => 'Set root namespace. Default: "APP_NAMESPACE".',
		'--suffix'    => 'Append the component title to the class name (e.g. User => UserController).',
		'--force'     => 'Force overwrite existing file.',
	];

	/**
	 * Actually execute a command.
	 *
	 * @param array $params
	 */
	public function run(array $params)
	{
		$this->component = 'Controller';
		$this->directory = 'Controllers';
		$this->template  = 'controller.tpl.php';

		$this->classNameLang = 'CLI.generator.className.controller';
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
		if (APP_NAMESPACE != 'App')$app_name = APP_NAMESPACE;
		if ($this->app != APP_NAME){
			$ap = $this->app.'HakrichApp';
			if ($ap != APP_NAME) $app_name = $this->app;
		}
		
		$bare = $this->getOption('bare');
		$rest = $this->getOption('restful');
		$app_name = $this->getOption('namespace')??$app_name;

		$useStatement = trim($app_name, '\\') . '\Controllers\BaseController';
		$extends      = 'BaseController';

		// Gets the appropriate parent class to extend.
		if ($bare || $rest)
		{
			if ($bare)
			{
				$useStatement = 'Hkm_code\Controller';
				$extends      = 'Controller';
			}
			elseif ($rest)
			{
				$rest = is_string($rest) ? $rest : 'controller';

				if (! in_array($rest, ['controller', 'presenter'], true))
				{
					// @codeCoverageIgnoreStart
					$rest = CLI::prompt(hkm_lang('CLI.generator.parentClass'), ['controller', 'presenter'], 'required');
					CLI::newLine();
					// @codeCoverageIgnoreEnd
				}

				if ($rest === 'controller')
				{
					$useStatement = 'Hkm_code\RESTful\ResourceController';
					$extends      = 'ResourceController';
				}
				elseif ($rest === 'presenter')
				{
					$useStatement = 'Hkm_code\RESTful\ResourcePresenter';
					$extends      = 'ResourcePresenter';
				}
			}
		}

		return $this->parseTemplate(
			$class,
			['{useStatement}', '{extends}'],
			[$useStatement, $extends],
			['type' => $rest]
		);
	}
}
