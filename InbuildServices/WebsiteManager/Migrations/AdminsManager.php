<?php
namespace Hkm_services\WebsiteManager\Migrations;


/**
 * @package HKMManagerAdminsManager
 * @version 2021-11-22-083024_AdminsManager
 */
/*
Migrate Name: HKMManagerAdminsManager
Migrate class: AdminsManager 
Version: 2021-11-22-083024_AdminsManager
*/

use Hkm_code\Database\Migration;

class AdminsManager extends Migration
{
	public static function UP()
	{
			self::$forge->addField([
				'admin_id'          => [
						'type'           => 'BIGINT',
						'constraint'     => 20,
						'unsigned'       => true,
						'null' => false,
						'auto_increment' => true,
				],
				'user_id'       => [
					'type' => 'VARCHAR',
					'constraint' => '255',

				],
				'app_id'       => [
					'type'           => 'BIGINT',
					'constraint'     => 20,
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
		self::$forge->addKey('admin_id', true,true);
		self::$forge->addKey('app_id', false,true);
		self::$forge->createTable('AdminsManager');
	}

	public static function DOWN()
	{
		self::$forge->dropTable('AdminsManager');
		
	}
}
