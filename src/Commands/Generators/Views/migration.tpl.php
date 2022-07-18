<@php

namespace {namespace};

use Hkm_code\Database\Migration;

class {class} extends Migration
{
<?php if ($session): ?>
	protected static $DBGroup = '<?= $DBGroup ?>';

	public static function UP()
	{
		self::$forge::addField([
			'id'         => ['type' => 'VARCHAR', 'constraint' => 128, 'null' => false],
			'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => false],
			'timestamp'  => ['type' => 'INT', 'unsigned' => true, 'null' => false, 'default' => 0],
			'data'       => ['type' => 'TEXT', 'null' => false, 'default' => ''],
		]);
	<?php if ($matchIP) : ?>
	self::$forge::addKey(['id', 'ip_address'], true);
	<?php else: ?>
	self::$forge::addKey('id', true);
	<?php endif ?>
	self::$forge::addKey('timestamp');
		self::$forge::createTable('<?= $table ?>', true);
	}

	public static function DOWN()
	{
		self::$forge::dropTable('<?= $table ?>', true);
	}
<?php else: ?>
	public static function UP()
	{
		//
	}

	public static function DOWN()
	{
		//
	}
<?php endif ?>
}
