<?php
namespace Hkm_services\Auth\Migrations;

/**
 * @package Hkm_AuthAuthRequestMigration
 * @version 2021-11-23-025211_AuthRequestMigration
 */
/*
Migrate Name: Hkm_AuthAuthRequestMigration
Migrate class: AuthRequestMigration 
Version: 2021-11-23-025211_AuthRequestMigration
*/
use Hkm_code\Database\Migration;

class AuthRequestMigration extends Migration
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
			'hash' => [
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

			'type' => [
				'type' => 'BIGINT',
				'null' => true,
				'constraint' => '20',
			],
			'token' => [
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
		self::$forge->addKey('user', false, true);
		self::$forge->addKey('token', false, true);
		self::$forge->createTable('requests');
	}

	public static function DOWN()
	{
		self::$forge->dropTable('requests');
	}
}
