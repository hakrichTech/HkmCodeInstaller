<?php

namespace Hkm_code\Vezirion;

class AutoloadVezirion
{

	public static $psr4 = [];

	
	public static $classmap = [];

	public static $files = [];

	
	


	
	protected static $coreFiles = [];

	/**
	 * Constructor.
	 *
	 * Merge the built-in and developer-configured psr4 and classmap,
	 * with preference to the developer ones.
	 */
	public function __construct()
	{
		
	}
	
	  
	  public static function LOAD_ENGINE_HKM_v2(string $name, string $version)
	  {
		
		// if (strpos(PHP_SAPI, BOOT) === 0){
			if ($name == '.') {
			  define('__APP__DIR__', 'System'.$version);
			  $system_config = require_once __DIR__ . '/HkmSystem.php';
			  $system_config();
			}else{
			  define('__APP__DIR__', $name);
	  
			  $system_config = require_once __DIR__ . '/HkmSystem.php';
			   $system_config();
			}
		// }else{
		//   die("The cli tool is not supported when running php-cgi. It needs php-cli to function!\n\n");
		// }
	  
	  }
}
