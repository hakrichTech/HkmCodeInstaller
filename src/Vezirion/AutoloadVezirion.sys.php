<?php

namespace Hkm_code\Vezirion;

use Hkm_code\Modules\LoadModules;

class AutoloadVezirion
{

	public static $psr4 = [];

	
	public static $classmap = [];
	public static $class= [];


	public static $files = [];

	
	


	
	protected static $coreFiles = [];
	protected static $coreClass = [];

	/**
	 * Constructor.
	 *
	 * Merge the built-in and developer-configured psr4 and classmap,
	 * with preference to the developer ones.
	 */
	public function __construct()
	{
		
	}
	
	  
	  public static function LOAD_ENGINE_HKM(string $name, string $version)
	  {
		
		// if (strpos(PHP_SAPI, BOOT) === 0){

			if ($name == '.') {

			  define('__APP__DIR__', 'HkmCode V'.$version);
			  $system_config = require_once __DIR__ . '/HkmSystem.php';
			  $m = $system_config();
			  self::$classmap = $m;
			  self::$coreClass = $m['system'];
			  self::$class = $m['app'];
			  self::$coreFiles = LoadModules::$systemFiles;
			  self::$files = LoadModules::$Files;
			}else{
			  define('__APP__DIR__', str_replace("HkmCode","",$name));
	  
			  $system_config = require_once __DIR__ . '/HkmSystem.php';
			  $m = $system_config();
			  self::$classmap = $m;
			  self::$coreClass = $m['system'];
			  self::$class = $m['app'];
			  self::$coreFiles = LoadModules::$systemFiles;
			  self::$files = LoadModules::$Files;
			}
		// }else{
		//   die("The cli tool is not supported when running php-cgi. It needs php-cli to function!\n\n");
		// }
	  
	  }
}
