<?php

namespace Hkm_Config\Hkm_Bin;

use Hkm_code\Validation\CreditCardRules;
use Hkm_code\Validation\FileRules;
use Hkm_code\Validation\FormatRules;
use Hkm_code\Validation\Rules;

class Validation
{
	//--------------------------------------------------------------------
	// Setup
	//--------------------------------------------------------------------

	/**
	 * Stores the classes that contain the
	 * rules that are available.
	 *
	 * @var string[]
	 */
	public static $ruleSets = [
		Rules::class,
		FormatRules::class,
		FileRules::class,
		CreditCardRules::class,
	];
 
	/**
	 * Specifies the views that are used to display the
	 * errors.
	 *
	 * @var array<string, string>
	 */
	public static $templates = [
		'list'   => 'Hkm_code\Validation\Views\list',
		'single' => 'Hkm_code\Validation\Views\single',
	];

	//--------------------------------------------------------------------
	// Rules
	//--------------------------------------------------------------------
}
