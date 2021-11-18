<?php

use Hkm_code\Vezirion\DotEnv;
use Hkm_code\Vezirion\AutoloadVezirion;
use Hkm_code\Vezirion\Services;

if (! defined('ROOTPATH'))
{
	define('ROOTPATH', realpath(rtrim($paths::$rootDirectory, '\\/ ')) . DIRECTORY_SEPARATOR);
}

if (! defined('SYSTEMROOTPATH'))
{
	define('SYSTEMROOTPATH', realpath(__DIR__."/../"). DIRECTORY_SEPARATOR);
}

if (! defined('SYSTEMPATH'))
{
	define('SYSTEMPATH', realpath(rtrim($paths::$systemDirectory, '\\/ ')) . DIRECTORY_SEPARATOR);
}


if (! defined('BUILDPATH'))
{
	define('BUILDPATH', realpath(rtrim($paths::$buildDirectory, '\\/ ')) . DIRECTORY_SEPARATOR);
}
if (! defined('APPPATH'))
{
	define('APPPATH', realpath(rtrim($paths::$appDirectory, '\\/ ')) . DIRECTORY_SEPARATOR);
}

if (! defined('VEZIRIONPATH'))
{
	define('VEZIRIONPATH', realpath(rtrim($paths::$VezirionDirectory, '\\/ ')) . DIRECTORY_SEPARATOR);
}

if (! defined('WRITEPATH'))
{
	define('WRITEPATH', realpath(rtrim($paths::$writableDirectory, '\\/ ')) . DIRECTORY_SEPARATOR);
}

if (! defined('CREATE_PATH'))
{
	define('CREATE_PATH', realpath(rtrim($paths::$newAppDirectory, '\\/ ')) . DIRECTORY_SEPARATOR);
}





require_once SYSTEMPATH . 'Planin.php';
if (is_file(APPPATH."Planin.php")) {
   require_once APPPATH . 'Planin.php';
	
}


if (! class_exists('Hkm_code\Vezirion\AutoloadVezirion', false))
{
	require_once SYSTEMPATH . 'Modules/Modules.sys.php';
	require_once SYSTEMPATH . 'Modules/LoadModules.sys.php';
	require_once SYSTEMPATH . 'Vezirion/AutoloadVezirion.sys.php';
}


AutoloadVezirion::LOAD_ENGINE_HKM_v2($engine,$version);

$env = new DotEnv(ROOTPATH);
$env::LOAD();

if (! defined('APP_NAMESPACE'))
{ 
	require_once VEZIRIONPATH . 'Constants.php';
}

$app = Services::SETUP();

$app::INITIALIZE();

return $app;
