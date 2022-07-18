<?php
namespace Hkm_services\WebsiteManager\Migrations;

/**
 * @package HKMManagerAdminRoles
 * @version 2021-11-22-111904_AdminRoles
 */
/*
Migrate Name: HKMManagerAdminRoles
Migrate class: AdminRoles 
Version: 2021-11-22-111904_AdminRoles
*/

use Hkm_code\Database\Migration;

class AdminRoles extends Migration
{
	public static function UP()
	{

		self::$forge->addField([
			'id'          => [
				'type'           => 'BIGINT',
				'constraint'     => 20,
				'unsigned'       => true,
				'null' => false,
				'auto_increment' => true,
			],
			'admin_id'       => [
				'type'       => 'BIGINT',
				'constraint' => 20,
				'unsigned'       => true,
			],
			'role_id'       => [
				'type'       => 'BIGINT',
				'constraint' => 20,
				'unsigned'       => true,
			],
			'created_at' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
			'updated_at' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
			'deleted_at' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],

		]);
		self::$forge->addKey('id', true,true);
		self::$forge->addKey('admin_id', false,true);
		self::$forge->createTable('AdminRoles');
	}

	public static function DOWN()
	{
		self::$forge->dropTable('AdminRoles');
	}
}
