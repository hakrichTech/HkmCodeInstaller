<?php

namespace Hkm_services\Auth\Migrations;

/**
 * @package Hkm_AuthUserMetaMigration
 * @version 2021-12-08-003713_UserMetaMigration
 */
/*
Migrate Name: Hkm_AuthUserMetaMigration
Migrate class: UserMetaMigration 
Version: 2021-12-08-003713_UserMetaMigration
*/

use Hkm_code\Database\Migration;

class UserMetaMigration extends Migration
{
	public static function UP()
	{
			self::$forge->addField([
				'meta_id'          => [
					'type'           => 'BIGINT',
					'constraint'     => 20,
					'unsigned'       => true,
					'null' => false,
					'auto_increment' => true,
			   ],
				'umeta_id'       => [
					'type'       => 'BIGINT',
					'null' => true,
					'constraint' => 20,
			    ],
				'key' => [
					'type' => 'VARCHAR',
					'null' => true,
					'constraint' => 50,

			 	],

				 'content' => [
					'type' => 'TEXT',
					'null' => true
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
		self::$forge->addKey('meta_id', true,true);
		self::$forge->addKey('umeta_id', false,true);
		self::$forge->createTable('user_meta');
	}

	public static function DOWN()
	{
		self::$forge->dropTable('user_meta');
		
	}
}
