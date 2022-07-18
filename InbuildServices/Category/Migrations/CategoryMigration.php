<?php
namespace Hkm_services\Category\Migrations;
/**
 * @package Hkm_CategoryCategoryMigration
 * @version 2021-11-26-230321_CategoryMigration
 */
/*
Migrate Name: Hkm_CategoryCategoryMigration
Migrate class: CategoryMigration 
Version: 2021-11-26-230321_CategoryMigration
*/
use Hkm_code\Database\Migration;

class CategoryMigration extends Migration
{
	public static function UP()
	{
			self::$forge->addField([
				'category_id'          => [
						'type'           => 'BIGINT',
						'constraint'     => 20,
						'unsigned'       => true,
						'auto_increment' => true,
				], 
				'name' => [
						'type' => 'VARCHAR',
						'null' => true,
						'constraint' => '100',

				],
				'meta_title' => [
					'type' => 'VARCHAR',
					'null' => true,
					'constraint' => '100',

			   ],
			   'slug' => [
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
		self::$forge->addKey('category_id', true,true);
		self::$forge->addKey('parent_id', true,true);
		self::$forge->createTable('category');
	}

	public static function DOWN()
	{
		self::$forge->dropTable('category');
		
	}
}

