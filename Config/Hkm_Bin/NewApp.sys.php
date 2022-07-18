<?php

namespace Hkm_Config\Hkm_Bin;

use Hkm_code\Vezirion\BaseVezirion;

class NewApp extends BaseVezirion
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
		'newApp'   => 'Hkm_code/NewApp',
		'adminSeed'   => 'Hkm_code/NewAdminSeed',
		'configFile'   => 'Hkm_code/NewConfigFile',
	];

    public static $directories =[
        'App'=>['Controllers','Views','Runtime_filter','Database'=>['Migrations','Seeds'],'Filters','Models'],
        'Hkm_Bin'=>['Boot'],
        'public' => ['asset','js','css'],
        'writable'=>['cache','debugger','logs','session','uploads']
    ];


    public static $version = 0.1;
    public static $AppNamespace;
    public static $VezirionNamespace;
    public static $AppName;

}