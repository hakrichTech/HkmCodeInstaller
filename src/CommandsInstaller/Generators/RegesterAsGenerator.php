<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@Hkm_code.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\CommandsInstaller\Generators;

use Hkm_code\CLI\BaseCommand;
use Hkm_code\CLI\CLI;
use Hkm_code\CLI\GeneratorTrait;
use Hkm_code\Modules\LoadModules;
use Hkm_code\Vezirion\AutoloadVezirion;
use Hkm_code\Vezirion\ServicesSystem;

/**
 * Generates a skeleton controller file.
 */
class RegesterAsGenerator extends BaseCommand
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
	protected $name = 'regester:as';

	/**
	 * The Command's Description
	 *
	 * @var string
	 */
	protected $description = 'Generates a new regester file.';

	/**
	 * The Command's Usage
	 *
	 * @var string
	 */
	protected $usage = 'regester:as <name> [options]  ';

	/**
	 * The Command's Arguments
	 *
	 * @var array
	 */
	protected $arguments = [
		'name' => 'The regester class name.',
	];

	/**
	 * The Command's Options
	 *
	 * @var array
	 */
	protected $options = [
		'--namespace' => 'Set root namespace. Default: "APP_NAMESPACE".',
		'--force'     => 'Force overwrite existing file.',
		'--service'     => 'Regester as service.',
		'-o'     => 'outside service',
		'-s'     => 'Regester as service.',
	];

	public function checkAs()
	{
		$search = [];
		$replace = [];
		$service = $this->getOption('s')||$this->getOption('service');
		$n = '';
		if($service){
			$this->component = 'RegesterAsService';
			$this->directory = '';
			$this->template  = 'RegesterAsService.tpl.php';
		    $this->classNameLang = 'CLI.generator.className.RegesterAsService';
			$name = strtoupper(empty($this->params)?trim(CLI::prompt('Service name ', null, 'required')):$this->params[0]);
			while (!empty(ServicesSystem::{$name}())) {
			    CLI::newLine();
				CLI::error(hkm_lang('CLI.generator.serviceExist', [$name]), 'light_gray', 'red');
			    CLI::newLine(0);
			    $name = strtoupper(trim(CLI::prompt('Service name ', null, 'required')));
			}
			$search[]='{name}';
			$replace[]=$name;
			$n = $name;
		}
		return [$search,$replace,$n];
	}
	/**
	 * Actually execute a command.
	 *
	 * @param array $params
	 */
	public function run(array $params)
	{
		
		$this->exc($params);
		$this->prepare('');
	}

	/**
	 * Builds the file path from the class name.
	 *
	 * @param string $class
	 *
	 * 
	 */
	protected function buildRegesterPath(string $namesp)
	{
		

		if ($this->f) {
			if (empty($this->pst)) $namespace = trim(str_replace('/', '\\', $namesp ?? APP_NAMESPACE), '\\');
			else $namespace = trim(str_replace('/', '\\', $namesp ?? $this->app), '\\');

            
			// Check if the namespace is actually defined and we are not just typing gibberish.
			$d = array_merge(AutoloadVezirion::$classmap['system'],AutoloadVezirion::$class);
			if (!empty($this->pst)) $d = array_merge($d,LoadModules::APP_NAMESPACE($this->app)??[]);
			$namespaces = explode('\\',$namespace);

			$base = $d[$namespaces[0]."\\"];
			$pathR = $base[0];
			$namespaceR = $namespaces[0];
			$classR = implode("\\",$namespaces); 
			

			if (isset($namespaces[1])) {
			 unset($namespaces[0]);
             $base[0] .=DIRECTORY_SEPARATOR.implode('/',$namespaces);
			}

			$base = is_array($base)?$base:[];

			if (!$base = reset($base))
			{
				if (empty($this->pst)) {
					CLI::error(hkm_lang('CLI.namespaceNotDefined', [$namespace]), 'light_gray', 'red');
					CLI::newLine();

					return '';
				}
				
			}
			
             $base = realpath($base) ?: $base;
            
			$file = $base . '.php';
			if(is_file($file)){
				return[
					"path" => $pathR,
					"namespace" => $namespaceR,
					"class" => $classR
				];
			}else{
				CLI::error(hkm_lang('CLI.namespaceNotDefined', [$namespace]), 'light_gray', 'red');
					CLI::newLine();

					return '';
			}

		}else exit;

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
		$check = $this->checkAs();
		$search = $check[0];
		$replace = $check[1];
		$name = $check[2]; 
		if (APP_NAMESPACE != 'App')$app_name = APP_NAMESPACE;
		if ($this->app != APP_NAME){
			$ap = $this->app.'HakrichApp';
			if ($ap != APP_NAME) $app_name = $this->app;
		}

		$app_name = $this->getOption('namespace')??$app_name;

		

		$namespace = trim($app_name, '\\') ;
		// Get the file path from class name.
		$path = $this->buildRegesterPath($namespace);


		

		$rts = $path;
		$path = $rts['path'].DIRECTORY_SEPARATOR.$this->component.".php";

		// Check if path is empty.
		if (empty($path))
		{
			return '';
		}

		$isFile = is_file($path);
		// Check if the directory to save the file is existing.
		$dir = dirname($path);

		if (! is_dir($dir))
		{
			mkdir($dir, 0755, true);
		}

		hkm_helper('filesystem');


		

		// Overwriting files unknowingly is a serious annoyance, So we'll check if
		// we are duplicating things, If 'force' option is not supplied, we bail.
		if ($isFile)
		{
			$classname = ServicesSystem::LOCATOR()::GET_CLASS_NAME($path);
			$classname = new $classname();
			$services = $classname->name;
			if(is_array($services)){
				$search[] = " = ['".implode("','",$services)."']";

				$services[] = $name; 

			}else{
				$search[] = " = \"".$services."\"";
				$services = [$services,$name];
			}
			$replace[] = " = ['".implode("','",$services)."']";

			
			$this->template = "serviceMethod.tpl.php";


			$method = $this->parseTemplateInstaller(
				$rts['class'],
				$search,
				$replace,
				[]
			);
			
			// public string $name = "HOOK"
			$this->template = str_replace("<?php","<@php",file_get_contents($path));
			$search[] = "// {addon Service}";
			$replace[] = $method;
			$template = $this->parseStringTemplateInstaller(
				$rts['class'],
				$search,
				$replace,
				[]
			);


		}else{
			$template = $this->parseTemplateInstaller(
				$rts['class'],
				$search,
				$replace,
				[]
			);
		}

		


		if ($this->sortImports && preg_match('/(?P<imports>(?:^use [^;]+;$\n?)+)/m', $template, $match))
		{
			$imports = explode("\n", trim($match['imports']));
			sort($imports);

			$template = str_replace(trim($match['imports']), implode("\n", $imports), $template);
		}


		// Build the class based on the details we have, We'll be getting our file
		// contents from the template, and then we'll do the necessary replacements.
		if (! hkm_write_file($path, $template))
		{
			// @codeCoverageIgnoreStart
			CLI::error(hkm_lang('CLI.generator.fileError', [hkm_clean_path($path)]), 'light_gray', 'red');
			CLI::newLine();

			return '';
			// @codeCoverageIgnoreEnd
		}

		if ($isFile)
		{
			CLI::write(hkm_lang('CLI.generator.serviceFileUpdate', [hkm_clean_path($path)]), 'yellow');
			CLI::newLine();

			return '';
		}

		CLI::write(hkm_lang('CLI.generator.serviceFileCreate', [hkm_clean_path($path)]), 'green');
		CLI::newLine();

        return "";
		
	}
}
