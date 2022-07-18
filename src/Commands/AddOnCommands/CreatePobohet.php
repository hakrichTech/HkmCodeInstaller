<?php

namespace Hkm_code\Commands\AddOnCommands;

use Hkm_code\CLI\BaseCommand;
use Hkm_code\CLI\CLI;
use Hkm_code\Vezirion\ServicesSystem;

class CreatePobohet extends BaseCommand
{
	/**
	 * The Command's Group
	 *
	 * @var string
	 */
	protected $group = 'Commands';

	/**
	 * The Command's Name
	 *
	 * @var string
	 */
	protected $name = 'make:route';

	/**
	 * The Command's Description
	 *
	 * @var string
	 */
	protected $description = '';

	/**
	 * The Command's Usage
	 *
	 * @var string
	 */ 
	protected $usage = 'make:route <name>';

	/**
	 * The Command's Arguments
	 *
	 * @var array
	 */
	protected $arguments = [
		'name' => 'route name'
	];

	/**
	 * The Command's Options
	 *
	 * @var array
	 */
	protected $options = [
		'--controller' => 'Route namespace by Default APP_NAMESPACE',
		'--method' => 'Route namespace by Default APP_NAMESPACE',
		'--namespace' => 'Route namespace by Default APP_NAMESPACE',
		'--suffix' => 'ff',
	];

	/**
	 * Actually execute a command.
	 *
	 * @param array $params
	 */
	public function run(array $params)
	{
	
        CLI::newLine();
		CLI::write(CLI::color(hkm_setpad('[!] Placeholders to use:',90,3,2),'green'));
        $options = ['filter','redirect','namespace','as','priority','subdomain','hostname','offset'];
		$placeholders =['(:any) = .*','(:all) = *','(:alphanum) = [a-zA-Z0-9]','(:num) = [0-9]','(:alpha) = [a-zA-Z]'];
		foreach ($placeholders as $placeholder) {
			CLI::write(CLI::color(hkm_setpad($placeholder,90,3,8),'green'));
		}
		CLI::newLine();
		CLI::write(CLI::color(hkm_setpad('[!] Options: '.join(',',$options),90,3,2),'green'));		
		CLI::write(CLI::color(hkm_setpad('[!] How to write options:',90,3,2),'green'));
		CLI::write(CLI::color(hkm_setpad('Ex: filter=helper,...',90,3,9),'green'));
		CLI::newLine();



		
			$name = isset($params[0])?$params[0]:CLI::prompt(hkm_setpad('Route',5,0,2), null, 'required');
		    CLI::newLine(0);
			$type = CLI::prompt(hkm_setpad('Route type',5,0,2),['get','cli','post','put','delete','trace','connect','options','head'],null);
		    CLI::newLine(0);
		    $controller = trim(str_replace('/', '\\', CLI::getOption('controller') ?? 'Controllers\Home'), '\\');
            $method = trim(CLI::getOption('method') ?? CLI::prompt(hkm_setPad('Method',5,0,2),[], null), '\\');
            $options = trim(CLI::prompt(hkm_setPad('Oprions',5,0,2), [], null));

			$arr = [
				'from' =>$name,
				'to' => ucfirst(ltrim($controller,"\\")),
				'type'=>$type,
				'app_name'=>APP_NAME,
				'uniq'=>$name,
				'method' =>empty($method)?'index':$method,
				'options'=>str_replace(',',"'-,-'",$options)."'-,-'"
			];

			 $msg = ServicesSystem::ROUTES()::CLI_CREATE_ROUTE($arr);
             CLI::newLine();
			 
			 CLI::write(CLI::color(hkm_setPad('[+] Route :'.$name.' '.$msg,5,0,2),'green'));


		

		
	}
}
