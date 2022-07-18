<?php
namespace Hkm_services\Auth\Migrations;

/**
 * @package Hkm_AuthLoginAttempts
 * @version 2021-11-22-080019_LoginAttempts
 */
/*
Migrate Name: Hkm_AuthLoginAttempts
Migrate class: LoginAttempts 
Version: 2021-11-22-080019_LoginAttempts
*/

use Hkm_code\Database\Migration;

class LoginAttempts extends Migration
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
			'user'       => [
				'type'       => 'BIGINT',
				'constraint' => 20,
			],
			'ip' => [
				'type' => 'VARCHAR',
				'null' => true,
				'constraint' => '250',

			],
			'timestamp' => [
				'type' => 'INT',
				'unsigned' => true,
				'null' => true,
				'constraint' => '10',
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
		self::$forge->addKey('user', false, true);
		self::$forge->createTable('LoginAttempts');
	}

	public static function DOWN()
	{
		self::$forge->dropTable('LoginAttempts');
	}
}
