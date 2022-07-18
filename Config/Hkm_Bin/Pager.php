<?php

namespace Hkm_Config\Hkm_Bin;

use Hkm_code\Vezirion\BaseVezirion;

class Pager extends BaseVezirion
{
	/**
	 * --------------------------------------------------------------------------
	 * Templates
	 * --------------------------------------------------------------------------
	 *
	 * Pagination links are rendered out using views to configure their
	 * appearance. This array contains aliases and the view names to
	 * use when rendering the links.
	 *
	 * Within each view, the Pager object will be available as $pager,
	 * and the desired group as $pagerGroup;
	 *
	 * @var array<string, string>
	 */
	public static $templates = [
		'default_full'   => 'Hkm_code\Pager\Views\default_full',
		'default_simple' => 'Hkm_code\Pager\Views\default_simple',
		'default_head'   => 'Hkm_code\Pager\Views\default_head',
	];

	/**
	 * --------------------------------------------------------------------------
	 * Items Per Page
	 * --------------------------------------------------------------------------
	 *
	 * The default number of results shown in a single page.
	 *
	 * @var integer
	 */
	public static $perPage = 20;
}
