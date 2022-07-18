<?php
namespace Hkm_services\ShortenUrl\Models;

/**
 * @package Hkm_ShortenUrlFiles
 * @version 2022-03-12-014114_Files
 */
/*
Migrate Name: Hkm_ShortenUrlFiles
Migrate class: Files
Version: 2022-03-12-014114_Files
*/

use Hkm_code\Database\Migration;

class Files extends Migration
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
			'user_id'          => [
				'type'           => 'BIGINT',
				'constraint'     => 20
			],
			'name'       => [
				'type'       => 'VARCHAR',
				'constraint' => 200,
				'default' => ''
			],
			'path' => [
				'type' => 'VARCHAR',
				'constraint' => '200',
				'default'=>''

			],
			'size' => [
				'type' => 'VARCHAR',
				'constraint' => '200',
				'default'=>''

			],
			'o_dimensions' => [
				'type' => 'VARCHAR',
				'constraint' => '200',
				'default'=>''

			],
			't_dimensions' => [
				'type' => 'VARCHAR',
				'constraint' => '200',
				'default'=>''

			],
			'show' => [
				'type' => 'BIGINT',
				'constraint' => '30',
			],
			'download' => [
				'type' => 'BIGINT',
				'constraint' => '30',
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
		self::$forge->addKey('path', false, true);
		self::$forge->addKey('name', false, true);
		self::$forge->createTable('File_store');
	}

	public static function DOWN()
	{
		self::$forge->dropTable('File_store');
		
	}
}
