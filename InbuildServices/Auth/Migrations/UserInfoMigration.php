<?php

namespace Hkm_services\Auth\Migrations;

/**
 * @package Hkm_AuthUserInfoMigration
 * @version 2021-11-22-083014_UserInfoMigration
 */
/*
Migrate Name: Hkm_AuthUserInfoMigration
Migrate class: UserInfoMigration 
Version: 2021-11-22-083014_UserInfoMigration
*/

use Hkm_code\Database\Migration;

class UserInfoMigration extends Migration
{
	public static function UP()
	{
			self::$forge->addField([
				'user_id'          => [
						'type'           => 'BIGINT',
						'constraint'     => 20,
						'unsigned'       => true,
						'auto_increment' => true,
						'null' => false,
				],
				'username'       => [
						'type'       => 'VARCHAR',
						'constraint' => '255',
				],
				'email' => [
						'type' => 'VARCHAR',
						'null' => true,
						'constraint' => '250',

				],
				'userFullname' => [
					'type' => 'VARCHAR',
					'null' => true,
					'constraint' => '250',
                ],
				'password' => [
					'type' => 'VARCHAR',
					'null' => true,
					'constraint' => '250',
                ],
				'phone' => [
					'type' => 'VARCHAR',
					'null' => true,
					'constraint' => '250',
                ],
				'avatar' => [
					'type' => 'VARCHAR',
					'null' => true,
					'constraint' => '250',
                ],
				'gender' => [
					'type' => 'VARCHAR',
					'null' => true,
					'constraint' => '250',
                ],
				'birthDate' => [
					'type' => 'VARCHAR',
					'null' => true,
					'constraint' => '250',
                ],

				'verified' => [
					'type' => 'VARCHAR',
					'null' => true,
					'constraint' => '250',
                ],
				'remember_token' => [
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
		self::$forge->addKey('user_id', true, true);
		self::$forge->addKey('email', false,true);
		self::$forge->createTable('users_info');
	}

	public static function DOWN()
	{
		self::$forge->dropTable('users_info');
		
	}
}


	
//   ADD COLUMN avatar varchar(255) NOT NULL AFTER phone;
//   ADD COLUMN gender varchar(255) NOT NULL AFTER birthDate;
//   ADD COLUMN remember_token varchar(255) NOT NULL AFTER gender;