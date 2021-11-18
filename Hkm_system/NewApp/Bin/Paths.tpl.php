<@php

namespace {VezirionNamespace};

class Paths
{
	
	public static $systemDirectory = '{SystemDir}';
	public static $VezirionDirectory = __DIR__ ;
	public static $rootDirectory = __DIR__ . '/../..';
	public static $appDirectory = __DIR__ . '/../../App';

	/* ---------------------------------------------------------------
	 * WRITABLE DIRECTORY NAME
	 * ---------------------------------------------------------------
	 *
	 * This variable must contain the name of your "writable" directory.
	 * The writable directory allows you to group all directories that
	 * need write permission to a single place that can be tucked away
	 * for maximum security, keeping it out of the app and/or
	 * system directories.
	 *
	 * @var string
	 */
	public static $writableDirectory = __DIR__ . '/../../writable';

	public static $buildDirectory = __DIR__ . '/../../Build';
	public static $newAppDirectory = __DIR__ . '/../../..';
	public static $viewDirectory = __DIR__ . '/../Views';
}
