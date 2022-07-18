<?php

namespace Hkm_services\Auth\Migrations;

/**
 * @package Hkm_AuthAddressMigration
 * @version 2021-11-24-083014_AddressoMigration
 */
/*
Migrate Name: Hkm_AuthAddressMigration
Migrate class: AddressMigration 
Version: 2021-11-24-083014_AddressMigration
*/

use Hkm_code\Database\Migration;

class AddressMigration extends Migration
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
				'user_id'       => [
						'type'       => 'VARCHAR',
						'constraint' => '255',
				],
				'country' => [
						'type' => 'VARCHAR',
						'null' => true,
						'constraint' => '250',

				],
				'state' => [
					'type' => 'VARCHAR',
					'null' => true,
					'constraint' => '250',
                ],
				'postalcode' => [
					'type' => 'VARCHAR',
					'null' => true,
					'constraint' => '250',
                ],
				'address' => [
					'type' => 'VARCHAR',
					'null' => true,
					'constraint' => '250',
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
		self::$forge->addKey('user_id', false,true);
		self::$forge->createTable('user_address');
	}

	public static function DOWN()
	{
		self::$forge->dropTable('user_address');
		
	}
}


