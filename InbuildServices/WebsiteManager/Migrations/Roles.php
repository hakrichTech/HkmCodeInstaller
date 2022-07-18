<?php
namespace Hkm_services\WebsiteManager\Migrations;


/**
 * @package HKMManagerRoles
 * @version 2021-11-22-083220_Roles
 */
/*
Migrate Name: HKMManagerRoles
Migrate class: Roles 
Version: 2021-11-22-083220_Roles
*/

use Hkm_code\Database\Migration;

class Roles extends Migration
{
	public static function UP()
	{

		self::$forge->addField([
			'id'          => [
				'type'           => 'BIGINT',
				'constraint'     => 20,
				'unsigned'       => true,
				'auto_increment' => true,
			],
			'role'       => [
					'type'       => 'VARCHAR',
					'null'       => true,
					'constraint' => '2',
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
	self::$forge->createTable('Roles');


	}

	public static function DOWN()
	{
		self::$forge->dropTable('Roles');
		
	}
}
