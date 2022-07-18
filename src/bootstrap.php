<?php


use Hkm_Config\config_system;
use Hkm_code\Vezirion\AutoloadVezirion;
use Hkm_code\Vezirion\ServicesSystem;

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
	define('CREATE_PATH', realpath(rtrim($paths::$projectDirectory, '\\/ ')) . DIRECTORY_SEPARATOR);
}


define('HKM_LANG_DIR',SYSTEMROOTPATH.'APIs/src/Language/mo');

if (! defined('APP_NAMESPACE'))
{ 
	require_once VEZIRIONPATH . 'Constants.php';
}


if (! class_exists('Hkm_code\Vezirion\AutoloadVezirion', false))
{
	
	require_once SYSTEMPATH . 'Modules/Modules.sys.php';
	require_once SYSTEMPATH . 'Modules/LoadModules.sys.php';
	require_once SYSTEMPATH . 'Vezirion/AutoloadVezirion.sys.php';
}



if (!defined('HKMLANG')) {
    define('HKMLANG','en');
}





AutoloadVezirion::LOAD_ENGINE_HKM(ucfirst($engine),$version);




$yaml = new config_system(ROOTPATH);
$yaml::LOAD();

defined('ENVIRONMENT') || define('ENVIRONMENT', $_SERVER['HKM_ENVIRONMENT'] ?? 'development'); // @codeCoverageIgnore    
$app = ServicesSystem::SETUP();

$app::INITIALIZE();

__RegesterAsServicesHelpers();


if(is_file(APPPATH."planin.php")){
	require_once APPPATH."planin.php";
}

return $app;





