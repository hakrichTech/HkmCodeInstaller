<@php

namespace App\Database\Migrations;

use Hkm_code\Database\Migration;

class NetworkMigration extends Migration
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
			'name'       => [
				'type'       => 'VARCHAR',
				'constraint' => 200,
				'default' => ''
			],
			'domain'       => [
				'type'       => 'VARCHAR',
				'constraint' => 200,
				'default' => ''
			],
			'path' => [
				'type' => 'VARCHAR',
				'constraint' => '200',
				'default'=>''

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
		self::$forge->addKey('domain', true, true);
		self::$forge->createTable('hkmCode_site');
	}

	public static function DOWN()
	{
		self::$forge->dropTable('hkmCode_site');
	}
}
