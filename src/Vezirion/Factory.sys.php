<?php

/**
 * This file is part of the hakrichteam 4 framework.
 *
 * (c) hakrichteam Foundation <admin@hakrichteam.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Vezirion;

/**
 * Factories Configuration file.
 *
 * Provides overriding directives for how
 * Factories should handle discovery and
 * instantiation of specific components.
 * Each property should correspond to the
 * lowercase, plural component name.
 */
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
	public $models = [
		'preferApp' => true,
	];
}
