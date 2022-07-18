<?php
namespace Hkm_services\Category\Migrations;

/**
 * @package Hkm_CategoryCategoryMigrationSub
 * @version 2021-11-26-230321_CategoryMigrationSub
 */
/*
Migrate Name: Hkm_CategoryCategoryMigrationSub
Migrate class: CategoryMigrationSub
Version: 2021-11-26-230321_CategoryMigrationSub
*/
use Hkm_code\Database\Migration;

class CategoryMigrationSub extends Migration
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
				'category_parent'          => [
					'type'           => 'BIGINT',
					'constraint'     => 20,
					'unsigned'       => true,
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
		self::$forge->addKey('category_parent',true);
		self::$forge->createTable('category_sub');
	}

	public static function DOWN()
	{
		self::$forge->dropTable('category_sub');
		
	}
}

