<?php

namespace Hkm_Config\Hkm_Bin;

use Hkm_code\Vezirion\BaseVezirion;
use Kint\Renderer\Renderer;

/**
 * --------------------------------------------------------------------------
 * Kint
 * --------------------------------------------------------------------------
 *
 * We use Kint's `RichRenderer` and `CLIRenderer`. This area contains options
 * that you can set to customize how Kint works for you.
 *
 * @see https://kint-php.github.io/kint/ for details on these settings.
 */
class Kint extends BaseVezirion
{
	/*
	|--------------------------------------------------------------------------
	| Global Settings
	|--------------------------------------------------------------------------
	*/

	public static $plugins = null;

	public static $maxDepth = 6;

	public static $displayCalledFrom = true;

	public static $expanded = false;

	/*
	|--------------------------------------------------------------------------
	| RichRenderer Settings
	|--------------------------------------------------------------------------
	*/
	public static $richTheme = 'aante-light.css';

	public static $richFolder = false;

	public static $richSort = Renderer::SORT_FULL;

	public static $richObjectPlugins = null;

	public static $richTabPlugins = null;

	/*
	|--------------------------------------------------------------------------
	| CLI Settings
	|--------------------------------------------------------------------------
	*/
	public static $cliColors = true;

	public static $cliForceUTF8 = false;

	public static $cliDetectWidth = true;

	public static $cliMinWidth = 40;
}
