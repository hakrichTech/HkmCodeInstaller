<?php

namespace Hkm_Config\Hkm_Bin;

use Hkm_code\Vezirion\BaseVezirion;
use Hkm_code\Images\Handlers\GDHandler;
use Hkm_code\Images\Handlers\ImageMagickHandler;

class Images extends BaseVezirion
{
	/**
	 * Default handler used if no other handler is specified.
	 *
	 * @var string
	 */
	public static $defaultHandler = 'gd';

	/**
	 * The path to the image library.
	 * Required for ImageMagick, GraphicsMagick, or NetPBM.
	 *
	 * @var string
	 */
	public static $libraryPath = '/usr/local/bin/convert';

	/**
	 * The available handler classes.
	 *
	 * @var array<string, string>
	 */
	public static $handlers = [
		'gd'      => GDHandler::class,
		'imagick' => ImageMagickHandler::class,
	];
}
