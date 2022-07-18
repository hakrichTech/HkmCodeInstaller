<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@Hkm_code.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\CLI;

use Throwable;
use Hkm_code\CLI\CLI;
use Hkm_code\Modules\LoadModules;
use Hkm_code\Vezirion\FileLocator;
use Symfony\Component\Process\Process;
use Hkm_code\Vezirion\AutoloadVezirion;

/**
 * GeneratorTrait contains a collection of methods
 * to build the commands that generates a file.
 */
trait GeneratorTrait
{
	/**
	 * Component Name
	 *
	 * @var string
	 */
	protected $component;

	/**
	 * File directory
	 *
	 * @var string
	 */
	protected $directory;

	/**
	 * View template name
	 *
	 * @var string
	 */
	protected $template;

	/**
	 * Language string key for required class names.
	 *
	 * @var string
	 */
	protected $classNameLang = '';

	/**
	 * Whether to require class name.
	 *
	 * @internal
	 *
	 * @var boolean
	 */
	private $hasClassName = true;

	/**
	 * Whether to sort class imports.
	 *
	 * @internal
	 *
	 * @var boolean
	 */
	private $sortImports = true;

	/**
	 * Whether the `--suffix` option has any effect.
	 *
	 * @internal
	 *
	 * @var boolean
	 */
	private $enabledSuffixing = true;

	/**
	 * The params array for easy access by other methods.
	 *
	 * @internal
	 *
	 * @var array
	 */
	private $params = [];
	private $appData = [];

	private $namespaceApp ;

	private $config;

	private $namespace;

	private $f =false;
	private $pst;
	private $app;
	private $sub;


    
	public function executeServices(array $params)
	{
		global $is_HKM_SERVER;
		$this->params = $params;
		$this->config = hkm_config('NewApp');
        
		$this->directory = $this->LookupDir();
		$this->generate_dir();

		CLI::newLine(0);
		CLI::write("[+] Config your new Services Dir", 'green');
		CLI::newLine(1);

		if (isset($GLOBALS['is_HKM_SERVER']) && $is_HKM_SERVER) {

			$hkmServer = Hkm_server_initialise($this->appData);
			$hkmServer::set_language($this->classNameLang)
			::set_database_group('system')
			::new_service_dir()
			::new_service_namespace($this->namespaceApp);
			$hkmServer::done_service();
		}

	}

	public function executeApp(array $params)
	{
		global $is_HKM_SERVER;
		$this->params = $params;
		$this->config = hkm_config('NewApp');
        
		$this->directory = $this->LookupDir();
		$this->generate_dir();
		
		CLI::newLine(0);
		CLI::write("[+] Config your new App", 'green');
		CLI::newLine(1);
		

		if (isset($GLOBALS['is_HKM_SERVER']) && $is_HKM_SERVER) {
			$hkmServer = Hkm_server_initialise($this->appData);
			$hkmServer::set_language($this->classNameLang)
		          ::set_database_group('system')
		          ::new_app_url();
			$this->appData = $hkmServer::$newApp;
			$composer = $this->findComposer();
 
			$this->buildAppPath($this->config::$templates['newApp']);
			$commands = [
				$composer.' dump-autoload',
				$composer.' install --no-scripts',
				$composer.' run-script post-install-cmd',
			];
			$this->Proccess_commands($commands,true);
			$this->Proccess_commands(['chmod +x ./App.php']);

			$hkmServer::database_setting();
			$this->appData = $hkmServer::$newApp;

			$this->Proccess_commands(['HkmPHP db:create '.$this->appData['newApp_databaseName']]);

			$hkmServer::admin_user();
			$this->appData = $hkmServer::$newApp;

			
			$this->buildAppPath($this->config::$templates['adminSeed']);
			$this->buildAppPath($this->config::$templates['configFile']);

			$this->Proccess_commands(['./App.php  migrate --all']);
			$this->Proccess_commands(['./App.php  db:seed AdminSeeder']);

			// unlink(getcwd()."/".$this->directory."/App/Database/Seeds/AdminSeeder.php");

			
			$hkmServer::build_app()
					::admin_adding()
					::admin_role(1);
			if ($hkmServer::$error) {
				foreach ($hkmServer::$errorData as $error ) {
					CLI::error("[ERROR: ]".$error, 'light_gray', 'red');
					CLI::newLine(0);
				}
			}else $hkmServer::done();
		}
		
		
		
		


	} 

	public function Proccess_commands($commands, $composer = false)
	{
		$commands = array_map(function ($value) use ($composer){
            if ($composer) return $value.' --no-ansi';
			else return $value;
		}, $commands);
  
        
		$process = new Process(implode(' && ', $commands), getcwd().'/'.$this->directory, null, null, null);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            $process->setTty(true);
        }

		$cli = new CLI;

        $process->run(function ($type, $line) use ($cli) {
            $cli::write($line,"green");
        });

		CLI::newLine(1);
	}
	 /**
     * Get the composer command for the environment.
     *
     * @return string
     */
    protected function findComposer()
    {
        if (file_exists(getcwd().'/composer.phar')) {
            return '"'.PHP_BINARY.'" composer.phar';
        }

        return 'composer';
    }
	/**
     * Verify that the application does not already exist.
     *
     * @param  string  $directory
     * @return void
     */
    protected function verifyApplicationDoesntExist($directory)
    {
        if ((is_dir($directory) || is_file($directory)) && $directory != getcwd()) {
            CLI::error('Application already exists!','light_gray', 'red');
			exit;
        }
    }


	public function generate_dir()
	{
		$this->config::$AppName = $this->directory;
		$this->config::$AppNamespace = $this->namespaceApp;
		$this->config::$VezirionNamespace = 'Config'.$this->directory;

		$this->verifyApplicationDoesntExist(
			$dir = getcwd().'/'.$this->directory);

		if (! is_dir($dir))
		{
			mkdir($dir, 0777, true);
		}
		$this->appData['newApp_path'] = $dir;
	}
	public function buildAppPath($template)
	{
		$dir = $this->appData['newApp_path'];
        
		$paths = FileLocator::LIST_FILES($template);


		hkm_helper('filesystem');

		foreach ($paths as $path) {
			$p = explode('NewApp',$path);
			if (!isset($p[1]))$p = explode('NewAdminSeed',$path);
			if (!isset($p[1]))$p = explode('NewConfigFile',$path);
			$realP = $p[1];
			if (strpos($realP,'php')!==false) {
				$f = str_replace('/','\\',ltrim($realP,'/'));
                $templ = $template."\\{$f}";
				$createdPath = str_replace('.tpl','',$realP);
				if (strpos($realP,'.png')||strpos($realP,'.svg')||strpos($realP,'.xml')||strpos($realP,'.css')||strpos($realP,'.json')||strpos($realP,'.js')||strpos($realP,'L_onf.')||strpos($realP,'.txt')||strpos($realP,'.png')||strpos($realP,'.html')) {
					$createdPath = str_replace(['.php','cL_onf'],'',$createdPath);

				}
				$createdPath = rtrim($dir,'/').DIRECTORY_SEPARATOR.ltrim($createdPath,"/");
	             
		        $isFile = is_file($createdPath);
				$dirn = dirname($createdPath);
				if (! is_dir($dirn))
				{
					mkdir($dirn, 0777, true);
				}


				// Overwriting files unknowingly is a serious annoyance, So we'll check if
				// we are duplicating things, If 'force' option is not supplied, we bail.
				if (! $this->getOption('force') && $isFile)
				{
					CLI::error(hkm_lang('CLI.generator.fileExist', [hkm_clean_path($createdPath)]), 'light_gray', 'red');
					CLI::newLine(0);

				}else{
					// Build the class based on the details we have, We'll be getting our file
					// contents from the template, and then we'll do the necessary replacements.
					if (strpos($createdPath,'png')!==false) {
						copy(str_replace(["\\",'Hkm_code/'],["/",SYSTEMPATH],$templ),$createdPath);
					}else{

					if (! hkm_write_file($createdPath, $this->buildContentNewAPP($templ)))
					{
						// @codeCoverageIgnoreStart
						CLI::error(hkm_lang('CLI.generator.fileError', [hkm_clean_path($createdPath)]), 'light_gray', 'red');
						CLI::newLine(0);
						// @codeCoverageIgnoreEnd
					}else{
						CLI::write(hkm_lang('CLI.generator.fileCreate', [hkm_clean_path($createdPath)]), 'green');
		                CLI::newLine(0);
					}
				}

				}

					
				
			}
			
		}



		

	}

	protected function excPre()
	{

		$re = $this->AppName();

		$this->f = $re[0];
		$this->app = $re[2];
		$this->pst = $re[1]; 


		
		if (array_key_exists($this->getOption('namespace')."\\",AutoloadVezirion::$classmap['system'])		)
		{
			// @codeCoverageIgnoreStart
			CLI::write(hkm_lang('CLI.generator.usingCINamespace',[$this->getOption('namespace')]), 'yellow');
			CLI::newLine();

			if (CLI::prompt('Are you sure you want to continue?', ['y', 'n'], 'required') === 'n')
			{
				CLI::newLine();
				CLI::write(hkm_lang('CLI.generator.cancelOperation'), 'yellow');
				CLI::newLine();

				return;
			}

			CLI::newLine();
			// @codeCoverageIgnoreEnd
		}

	}

	protected function exc(array $params)
	{
		$this->excPre();


		$this->params = $params;


		
	}

	/**
	 * Execute the command.
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	protected function execute(array $params): void
	{
		$this->excPre();

		$this->params = $params;
        
		


		// Get the fully qualified class name from the input.
		$class = $this->qualifyClassName();


		// Get the file path from class name.
		$path = $this->buildPath($class);




		// Check if path is empty.
		if (empty($path))
		{
			return;
		}

		$isFile = is_file($path);

		// Overwriting files unknowingly is a serious annoyance, So we'll check if
		// we are duplicating things, If 'force' option is not supplied, we bail.
		if (! $this->getOption('force') && $isFile)
		{
			CLI::error(hkm_lang('CLI.generator.fileExist', [hkm_clean_path($path)]), 'light_gray', 'red');
			CLI::newLine();

			return;
		}

		// Check if the directory to save the file is existing.
		$dir = dirname($path);

		if (! is_dir($dir))
		{
			mkdir($dir, 0755, true);
		}

		hkm_helper('filesystem');

		// Build the class based on the details we have, We'll be getting our file
		// contents from the template, and then we'll do the necessary replacements.
		if (! hkm_write_file($path, $this->buildContent($class)))
		{
			// @codeCoverageIgnoreStart
			CLI::error(hkm_lang('CLI.generator.fileError', [hkm_clean_path($path)]), 'light_gray', 'red');
			CLI::newLine();

			return;
			// @codeCoverageIgnoreEnd
		}

		if ($this->getOption('force') && $isFile)
		{
			CLI::write(hkm_lang('CLI.generator.fileOverwrite', [hkm_clean_path($path)]), 'yellow');
			CLI::newLine();

			return;
		}

		CLI::write(hkm_lang('CLI.generator.fileCreate', [hkm_clean_path($path)]), 'green');
		CLI::newLine();
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
		return $this->parseTemplate($class);
	}

	/**
	 * Change file basename before saving.
	 *
	 * Useful for components where the file name has a date.
	 *
	 * @param string $filename
	 *
	 * @return string
	 */
	protected function basename(string $filename): string
	{
		return basename($filename);
	}

	/**
	 * Parses the class name and checks if it is already qualified.
	 *
	 * @return string
	 */
	protected function qualifyClassName(): string
	{
		// Gets the class name from input.
		$class = $this->params[0] ?? CLI::getSegment(2);

		if (is_null($class) && $this->hasClassName)
		{
			// @codeCoverageIgnoreStart
			$nameLang = $this->classNameLang ?: 'CLI.generator.className.default';
			$class    = CLI::prompt(hkm_lang($nameLang), null, 'required');
			CLI::newLine();
			// @codeCoverageIgnoreEnd
		} 
		
		hkm_helper('inflector');

		$component = singular($this->component);

		/**
		 * @see https://regex101.com/r/a5KNCR/1
		 */
		$pattern = sprintf('/([a-z][a-z0-9_\/\\\\]+)(%s)/i', $component);

		if (preg_match($pattern, $class, $matches) === 1)
		{
			$class = $matches[1] . ucfirst($matches[2]);
		}

		if ($this->enabledSuffixing && $this->getOption('suffix') && ! strripos($class, $component))
		{
			$class .= ucfirst($component);
		}

		// Trims input, normalize separators, and ensure that all paths are in Pascalcase.
		$class = ltrim(implode('\\', array_map('pascalize', explode('\\', str_replace('/', '\\', trim($class))))), '\\/');

		// Gets the namespace from input.
		$namespace = trim(str_replace('/', '\\', $this->getOption('namespace') ?? APP_NAMESPACE), '\\');
        
		

		if (strncmp($class, $namespace, strlen($namespace)) === 0)
		{
			return $class; // @codeCoverageIgnore
		}
		if (!empty($this->pst)) {
			if ($namespace == APP_NAMESPACE && $namespace != $this->app) {
				$namespace = '';
				$this->namespace = $this->app;
			}else $this->namespace = '';
		  }

		return $namespace . '\\' . $this->directory . '\\' . str_replace('/', '\\', $class);
	}
 

	public function LookupDir()
	{
		// Gets the class name from input.
		$class = $this->params[0] ?? CLI::getSegment(2);
         
		if (is_null($class) && $this->hasClassName)
		{
			// @codeCoverageIgnoreStart
			$nameLang = $this->classNameLang ?: 'CLI.generator.className.AppName';
			$class    = CLI::prompt(hkm_lang($nameLang), null, 'required');
			CLI::newLine();
			

			// @codeCoverageIgnoreEnd
		}

		$this->appData['newApp_name'] = $class;

		$class = hkm_arraging_folder_Name($class);

		hkm_helper('inflector');
		

		$component = singular($this->component);

		/**
		 * @see https://regex101.com/r/a5KNCR/1
		 */
		$pattern = sprintf('/([a-z][a-z0-9_\/\\\\]+)(%s)/i', $component);

		if (preg_match($pattern, $class, $matches) === 1)
		{
			$class = $matches[1] . ucfirst($matches[2]);
		}

		if ($this->enabledSuffixing && $this->getOption('suffix') && ! strripos($class, $component))
		{
			$class .= ucfirst($component);
		}

		// Trims input, normalize separators, and ensure that all paths are in Pascalcase.
		$class = ltrim(implode('\\', array_map('pascalize', explode('\\', str_replace('/', '\\', trim($class))))), '\\/');

		// Gets the namespace from input.
		$namespace = trim(str_replace('/', '\\', $this->getOption('namespace') ?? $class), '\\');
		$this->namespaceApp = $namespace;
		if (strncmp($class, $namespace, strlen($namespace)) === 0)
		{
			return $class; // @codeCoverageIgnore
		}
		CLI::newLine(0);


		return $namespace ;


	}

	/**
	 * Gets the generator view as defined in the `Generators::$views`,
	 * with fallback to `$template` when the defined view does not exist.
	 *
	 * @param array $data Data to be passed to the view.
	 *
	 * @return string
	 */
	protected function renderTemplate(array $data = []): string
	{
		try
		{
			if (isset(hkm_config('Generators')::$views[$this->name])) {
				return hkm_view(hkm_config('Generators')::$views[$this->name], $data, ['debug' => false]);
			}else{
				
				return hkm_view("Commands\Generators\Views\\{$this->template}", $data, ['debug' => false]);
			}

		}
		catch (Throwable $e)
		{
			
			hkm_log_message('error', $e->getMessage());
            return "none";
		}
	}

		/**
	 * Gets the generator view as defined in the `Generators::$views`,
	 * with fallback to `$template` when the defined view does not exist.
	 *
	 * @param array $data Data to be passed to the view.
	 *
	 * @return string
	 */
	protected function renderTemplateInstaller(array $data = []): string
	{
		try
		{
			if (isset(hkm_config('Generators')::$views[$this->name])) {
				return hkm_view(hkm_config('Generators')::$views[$this->name], $data, ['debug' => false]);
			}else{
				
				return hkm_view("CommandsInstaller\Generators\Views\\{$this->template}", $data, ['debug' => false]);
			}

		}
		catch (Throwable $e)
		{
			hkm_log_message('error', $e->getMessage());
            return "none";
		}
	}

		/**
	 * Gets the generator view as defined in the `Generators::$views`,
	 * with fallback to `$template` when the defined view does not exist.
	 *
	 * @param array $data Data to be passed to the view.
	 *
	 * @return string
	 */
	protected function renderStringTemplateInstaller(array $data = []): string
	{
		try
		{
				
			return hkm_view_string($this->template, $data, ['debug' => false]);

		}
		catch (Throwable $e)
		{
			echo $e;exit;
			hkm_log_message('error', $e->getMessage());
            return "none";
		}
	}


		/**
	 * Performs pseudo-variables contained within view file.
	 *
	 * @param string $class
	 * @param array  $search
	 * @param array  $replace
	 * @param array  $data
	 *
	 * @return string
	 */
	protected function parseStringTemplateInstaller(string $class, array $search = [], array $replace = [], array $data = []): string
	{
		if (empty($this->namespace)) $this->namespace = explode("\\",$class)[0];

		$namespace = str_replace("/","\\",trim(implode('\\', array_slice(explode('\\', $class), 0, -1)), '\\'));
		

		
		
		$search[]  = '<@php';
		$search[]  = '{namespace}';
		$search[]  = '{class}'; 
		$replace[] = '<?php';
		$replace[] = $namespace;
		$replace[] = str_replace($namespace . '\\', '', $class);


		return str_replace($search, $replace, $this->renderStringTemplateInstaller($data));
	}


		/**
	 * Performs pseudo-variables contained within view file.
	 *
	 * @param string $class
	 * @param array  $search
	 * @param array  $replace
	 * @param array  $data
	 *
	 * @return string
	 */
	protected function parseTemplateInstaller(string $class, array $search = [], array $replace = [], array $data = []): string
	{
		$namespace = explode("\\",$class)[0];
		if (empty($this->namespace)) $this->namespace = $namespace;
		$class = str_replace("/","\\",trim($class,"\\"));
		$class = explode("\\",$class);
		$class = count($class)>2?"\\".implode('\\',$class):(count($class) == 2?$class[1]:$class[0]) ;

		

		
		
		$search[]  = '<@php';
		$search[]  = '{namespace}';
		$search[]  = '{class}'; 
		$replace[] = '<?php';
		$replace[] = $namespace;
		$replace[] = $class;


		return str_replace($search, $replace, $this->renderTemplateInstaller($data));
	}

	/**
	 * Performs pseudo-variables contained within view file.
	 *
	 * @param string $class
	 * @param array  $search
	 * @param array  $replace
	 * @param array  $data
	 *
	 * @return string
	 */
	protected function parseTemplate(string $class, array $search = [], array $replace = [], array $data = []): string
	{
		if (!empty($this->namespace)) $class = $this->namespace.$class;

		$namespace = str_replace("/","\\",trim(implode('\\', array_slice(explode('\\', $class), 0, -1)), '\\'));
		

		
		
		$search[]  = '<@php';
		$search[]  = '{namespace}';
		$search[]  = '{class}'; 
		$replace[] = '<?php';
		$replace[] = $namespace;
		$replace[] = str_replace($namespace . '\\', '', $class);


		return str_replace($search, $replace, $this->renderTemplate($data));
	}

	/**
	 * Builds the contents for class being generated, doing all
	 * the replacements necessary, and alphabetically sorts the
	 * imports for a given template.
	 *
	 * @param string $class
	 *
	 * @return string
	 */
	protected function buildContent(string $class): string
	{
		$template = $this->prepare($class);

		if ($this->sortImports && preg_match('/(?P<imports>(?:^use [^;]+;$\n?)+)/m', $template, $match))
		{
			$imports = explode("\n", trim($match['imports']));
			sort($imports);

			return str_replace(trim($match['imports']), implode("\n", $imports), $template);
		}

		return $template;
	}

	public function buildContentNewAPP(string $path)
	{
		$template = $this->prepareNewApp($path);
		if ($this->sortImports && preg_match('/(?P<imports>(?:^use [^;]+;$\n?)+)/m', $template, $match))
		{
			$imports = explode("\n", trim($match['imports']));
			sort($imports);

			return str_replace(trim($match['imports']), implode("\n", $imports), $template);
		}

		return $template;

	}

	/**
	 * Prepare options and do the necessary replacements.
	 *
	 * @param string $class
	 *
	 * @return string
	 */
	protected function prepareNewApp(string $class): string
	{
		return $this->parseTemplateNewApp($class);
	}

	/**
	 * Performs pseudo-variables contained within view file.
	 *
	 * @param string $class
	 * @param array  $search
	 * @param array  $replace
	 * @param array  $data
	 *
	 * @return string
	 */
	protected function parseTemplateNewApp(string $class, array $search = [], array $replace = [], array $data = []): string
	{
		// Retrieves the namespace part from the fully qualified class name.
		$search[]  = '{auth_key}';
		$search[]  = '{secure_auth_key}';
		$search[]  = '{logged_in_key}';
		$search[]  = '{nonce_key}';
		$search[]  = '{auth_salt}';
		$search[]  = '{secure_auth_salt}';
		$search[]  = '{looged_in_salt}';
		$search[]  = '{secret_key}';
		$search[]  = '{secret_salt}';
		$search[]  = '<@php';
		$search[]  = '<@xml';
		$search[]  = '<@=';
		$search[]  = '{AppName}';
		$search[]  = '{phpCli}';
		$search[]  = '{appname}';
		$search[]  = '{AppNamespace}';
		$search[]  = '{VezirionNamespace}';
		$search[]  = '{SystemDir}';
		$search[]  = '${email}';
		$search[]  = '${password}';
		$search[]  = '${url}';
		$search[]  = '${dbname}';
		$search[]  = '${dbuser}';
		$search[]  = '${dbpass}';
		$replace[] = generateRandomKey(25);
		$replace[] = generateRandomKey(35);
		$replace[] = generateRandomKey(15);
		$replace[] = generateRandomKey(25);
		$replace[] = generateRandomKey(25);
		$replace[] = generateRandomKey(25);
		$replace[] = generateRandomKey(25);
		$replace[] = generateRandomKey(25);
		$replace[] = generateRandomKey(25);
		$replace[] = '<?php';
		$replace[] = '<?xml';
		$replace[] = '<?=';
		$replace[] = $this->config::$AppName;
		$replace[] = '#!/usr/bin/php';
		$replace[] = strtolower($this->config::$AppName);
		$replace[] = $this->config::$AppNamespace;
		$replace[] = $this->config::$VezirionNamespace;
		$replace[] = __DIR__."/../";
		$replace[] =  isset($this->appData['newApp_Admin'])?$this->appData['newApp_Admin']:"";
		$replace[] =  isset($this->appData['newApp_Admin_password'])?$this->appData['newApp_Admin_password']:"";
        $replace[] =  isset($this->appData['newApp_url'])?$this->appData['newApp_url']:"";
		$replace[] =  isset($this->appData['newApp_databaseName'])?$this->appData['newApp_databaseName']:"";
		$replace[] =  isset($this->appData['newApp_databaseUser'])?$this->appData['newApp_databaseUser']:"";
		$replace[] =  isset($this->appData['newApp_databasePass'])?$this->appData['newApp_databasePass']:"";

		return str_replace($search, $replace, $this->renderTemplateNewApp($data,$class));
	}

	/**
	 * Gets the generator view as defined in the `Generators::$views`,
	 * with fallback to `$template` when the defined view does not exist.
	 *
	 * @param array $data Data to be passed to the view.
	 *
	 * @return string
	 */
	protected function renderTemplateNewApp(array $data,$viewTempl): string
	{
		try
		{
			return hkm_view($viewTempl, $data, ['debug' => false]);

		}
		catch (Throwable $e)
		{
			hkm_log_message('error', $e->getMessage());

			return hkm_view($viewTempl, $data, ['debug' => false]);

		}
	}

	

	/**
	 * Builds the file path from the class name.
	 *
	 * @param string $class
	 *
	 * @return string
	 */
	protected function buildPath(string $class): string
	{
		

		if ($this->f) {
			if (empty($this->pst)) $namespace = trim(str_replace('/', '\\', $this->getOption('namespace') ?? APP_NAMESPACE), '\\');
			else $namespace = trim(str_replace('/', '\\', $this->getOption('namespace') ?? $this->app), '\\');
            
			// Check if the namespace is actually defined and we are not just typing gibberish.
			$d = array_merge(AutoloadVezirion::$classmap['system'],AutoloadVezirion::$class);
			if (!empty($this->pst)) $d = array_merge($d,LoadModules::APP_NAMESPACE($this->app)??[]);
			$namespaces = explode('\\',$namespace);

			$base = $d[$namespaces[0]."\\"];
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
            
			$file = $base . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, trim(str_replace($namespace . '\\', '', $class), '\\')) . '.php';

			return implode(DIRECTORY_SEPARATOR, array_slice(explode(DIRECTORY_SEPARATOR, $file), 0, -1)) . DIRECTORY_SEPARATOR . $this->basename($file);
		}else exit;

	}

	/**
	 * Allows child generators to modify the internal `$hasClassName` flag.
	 *
	 * @param boolean $hasClassName
	 *
	 * @return $this
	 */
	protected function setHasClassName(bool $hasClassName)
	{
		$this->hasClassName = $hasClassName;

		return $this;
	}

	/**
	 * Allows child generators to modify the internal `$sortImports` flag.
	 *
	 * @param boolean $sortImports
	 *
	 * @return $this
	 */
	protected function setSortImports(bool $sortImports)
	{
		$this->sortImports = $sortImports;

		return $this;
	}

	/**
	 * Allows child generators to modify the internal `$enabledSuffixing` flag.
	 *
	 * @param boolean $enabledSuffixing
	 *
	 * @return $this
	 */
	protected function setEnabledSuffixing(bool $enabledSuffixing)
	{
		$this->enabledSuffixing = $enabledSuffixing;

		return $this;
	}

	/**
	 * Gets a single command-line option. Returns TRUE if the option exists,
	 * but doesn't have a value, and is simply acting as a flag.
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	protected function getOption(string $name)
	{
		if (! array_key_exists($name, $this->params))
		{
			return CLI::getOption($name);
		}

		return is_null($this->params[$name]) ? true : $this->params[$name];
	}
}
