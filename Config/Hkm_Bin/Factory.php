<?php

namespace Hkm_Config\Hkm_Bin;

use Hkm_code\Vezirion\BaseVezirion;

class Factory extends BaseVezirion
{
 /**
	 * Supplies a default set of options to merge for
	 * all unspecified factory components.
	 *
	 * @var array
	 */
	public static $default = [
		'component'  => null,
		'path'       => null,
		'instanceOf' => null,
		'getShared'  => true,
		'preferApp'  => true,
	];

	/**
	 * Specifies that Models should always favor child
	 * classes to allow easy extension of module Models.
	 *
	 * @var array
	 */
	public static $models = [
		'preferApp' => true,
	];

}