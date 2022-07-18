<?php
namespace Hkm_services\Auth\Migrations;

/**
 * @package Hkm_AuthTokenStoreMigration
 * @version 2021-11-22-111954_TokenStoreMigration
 */
/*
Migrate Name: Hkm_AuthTokenStoreMigration
Migrate class: TokenStoreMigration 
Version: 2021-11-22-111954_TokenStoreMigration
*/
use Hkm_code\Database\Migration;

class TokenStoreMigration extends Migration
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
			'token' => [
				'type' => 'VARCHAR',
				'null' => true,
				'constraint' => '250',

			],

			'valid' => [
				'type' => 'VARCHAR',
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
		self::$forge->addKey('token', false, true);
		self::$forge->createTable('TokenStore');
	}

	public static function DOWN()
	{
		self::$forge->dropTable('TokenStore');
	}
}
