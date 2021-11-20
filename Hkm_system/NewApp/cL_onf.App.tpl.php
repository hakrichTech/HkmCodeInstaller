#!/opt/lampp/bin/php
<@php

define('BOOT', 'cli'); 
define('SYSTEM', true);
define('HKM_DEBUG', true);

$engine = '{appname}';
$version = '0.1';


$pathsConfig = 'Bin/Paths.php';

require realpath($pathsConfig) ?: $pathsConfig;

define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);

$paths = new {VezirionNamespace}\Paths();

$bootstrap = rtrim($paths::$systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';
$app       = require realpath($bootstrap) ?: $bootstrap;
$console = new Hkm_code\CLI\Terminal($app);

// // We want errors to be shown when using it from the CLI.
error_reporting(-1);
ini_set('display_errors', '1'); 

// Show basic information before we do anything else.
if (is_int($suppress = array_search('--no-header', $_SERVER['argv'], true)))
{
	unset($_SERVER['argv'][$suppress]); // @codeCoverageIgnore
	$suppress = true; 
}

$console::SHOW_HEADER($suppress);

$response = $console::RUN();

if ($response::GET_STATUS_CODE() >= 300)
{
	exit($response::GET_STATUS_CODE());
}
