<?php

class Paths
{
	
	public static $systemDirectory = __DIR__ ;
	public static $VezirionDirectory = __DIR__ .'/../Hkm_Bin';
	public static $rootDirectory = __DIR__ . '/..';
	public static $appDirectory = __DIR__ . '/../App';

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
	public static $writableDirectory = __DIR__ . '/../writable';

	public static $buildDirectory = __DIR__ . '/../Build';
	public static $projectDirectory = __DIR__ . '/../..';
	public static $viewDirectory = __DIR__ . '/../Views';
}