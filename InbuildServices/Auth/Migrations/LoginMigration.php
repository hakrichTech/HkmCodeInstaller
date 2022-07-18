<?php
namespace Hkm_services\Auth\Migrations;

/**
 * @package Hkm_AuthLoginMigration
 * @version 2021-11-22-083230_LoginMigration
 */
/*
Migrate Name: Hkm_AuthLoginMigration
Migrate class: LoginMigration 
Version: 2021-11-22-083230_LoginMigration
*/

use Hkm_code\Database\Migration;

class LoginMigration extends Migration
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
			'user_login'       => [
					'type'       => 'VARCHAR',
					'constraint' => '255',
			],
			'ip_address'       => [
				'type'       => 'VARCHAR',
				'constraint' => '255',
		    ],
			'device'       => [
				'type'       => 'VARCHAR',
				'constraint' => '255',
		    ],
			'browser'       => [
				'type'       => 'VARCHAR',
				'constraint' => '255',
		    ],
			'platform'       => [
				'type'       => 'VARCHAR',
				'constraint' => '255',
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
	self::$forge->addKey('user_login', false, true);
	self::$forge->createTable('Login_recoards');


	}

	public static function DOWN()
	{
		self::$forge->dropTable('Login_recoards');
		
	}
}
