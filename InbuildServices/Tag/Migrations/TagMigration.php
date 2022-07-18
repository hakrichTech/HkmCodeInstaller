<?php
namespace Hkm_services\Tag\Migrations;
/**
 * @package Hkm_TagTagMigration
 * @version 2021-11-26-230344_TagMigration
 */
/*
Migrate Name: Hkm_TagTagMigration
Migrate class: TagMigration 
Version: 2021-11-26-230344_TagMigration
*/

use Hkm_code\Database\Migration;

class TagMigration extends Migration
{
	public static function UP()
	{
			self::$forge->addField([
				'tag_id'          => [
						'type'           => 'BIGINT',
						'constraint'     => 20,
						'unsigned'       => true,
						'auto_increment' => true,
				],
				'tag_name' => [
						'type' => 'VARCHAR',
						'null' => true,
						'constraint' => '100',

				],
				'meta_title' => [
					'type' => 'VARCHAR',
					'null' => true,
					'constraint' => '100',

			   ],
			   'tag_slug' => [
					'type' => 'VARCHAR',
					'null' => true,
					'constraint' => '100',

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
		self::$forge->addKey('tag_id', true,true);
		self::$forge->createTable('tag');
	}

	public static function DOWN()
	{
		self::$forge->dropTable('tag');
	}
}	
	