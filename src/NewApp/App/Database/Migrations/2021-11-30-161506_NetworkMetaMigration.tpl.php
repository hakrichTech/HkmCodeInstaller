<@php

namespace App\Database\Migrations;

use Hkm_code\Database\Migration;

class NetworkMetaMigration extends Migration
{

		public static function UP()
	{

		self::$forge->addField([
			'meta_id'          => [
				'type'           => 'BIGINT',
				'constraint'     => 20,
				'unsigned'       => true,
				'auto_increment' => true,
			],
			'site_id'       => [
				'type'           => 'BIGINT',
				'constraint'     => 20,
				'default' => 0
			],
			'meta_key'       => [
				'type'       => 'VARCHAR',
				'constraint' => 255,
				'null' => true
			],
			'meta_value' => [
				'type' => 'LONGTEXT',

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
		self::$forge->addKey('site_id', true,);
		self::$forge->createTable('network_meta');
	}

	public static function DOWN()
	{
		self::$forge->dropTable('network_meta');
		
	}
}
