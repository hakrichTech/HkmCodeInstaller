<?php

namespace Hkm_Config\Hkm_Bin;

use Hkm_code\Vezirion\BaseVezirion;

class Honeypot extends BaseVezirion
{
	/**
	 * Makes Honeypot visible or not to human
	 *
	 * @var boolean
	 */
	public static $hidden = true;

	/**
	 * Honeypot Label Content
	 *
	 * @var string
	 */
	public static $label = 'Fill This Field';

	/**
	 * Honeypot Field Name
	 *
	 * @var string
	 */
	public static $name = 'honeypot';

	/**
	 * Honeypot HTML Template
	 *
	 * @var string
	 */
	public static $template = '<label>{label}</label><input type="text" name="{name}" value=""/>';

	/**
	 * Honeypot container
	 *
	 * @var string
	 */
	public static $container = '<div style="display:none">{template}</div>';
}
