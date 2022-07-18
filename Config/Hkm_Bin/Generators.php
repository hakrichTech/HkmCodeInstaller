<?php

namespace Hkm_Config\Hkm_Bin;

use Hkm_code\Vezirion\BaseVezirion;

class Generators extends BaseVezirion
{
	/**
	 * --------------------------------------------------------------------------
	 * Generator Commands' Views
	 * --------------------------------------------------------------------------
	 *
	 * This array defines the mapping of generator commands to the view files
	 * they are using. If you need to customize them for your own, copy these
	 * view files in your own folder and indicate the location here.
	 *
	 * You will notice that the views have special placeholders enclosed in
	 * curly braces `{...}`. These placeholders are used internally by the
	 * generator commands in processing replacements, thus you are warned
	 * not to delete them or modify the names. If you will do so, you may
	 * end up disrupting the scaffolding process and throw errors.
	 *
	 * YOU HAVE BEEN WARNED!
	 *
	 * @var array<string, string>
	 */
	public static $views = [
		'make:command'      => 'Hkm_code\Commands\Generators\Views\command.tpl.php',
		'make:controller'   => 'Hkm_code\Commands\Generators\Views\controller.tpl.php',
		'make:entity'       => 'Hkm_code\Commands\Generators\Views\entity.tpl.php',
		'make:filter'       => 'Hkm_code\Commands\Generators\Views\filter.tpl.php',
		'make:migration'    => 'Hkm_code\Commands\Generators\Views\migration.tpl.php',
		'make:model'        => 'Hkm_code\Commands\Generators\Views\model.tpl.php',
		'make:seeder'       => 'Hkm_code\Commands\Generators\Views\seeder.tpl.php',
		'make:validation'   => 'Hkm_code\Commands\Generators\Views\validation.tpl.php',
		'session:migration' => 'Hkm_code\Commands\Generators\Views\migration.tpl.php',
	];
}
