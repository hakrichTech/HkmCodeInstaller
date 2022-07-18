<?php
namespace Hkm_services\Blog\Migrations;

/**
 * @package Hkm_BlogPostCategoryMigration
 * @version 2021-11-26-225901_PostCategoryMigration
 */
/*
Migrate Name: Hkm_BlogPostCategoryMigration
Migrate class: PostCategoryMigration 
Version: 2021-11-26-225901_PostCategoryMigration
*/

use Hkm_code\Database\Migration;

class PostCategoryMigration extends Migration
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
				'post_id'       => [
					'type'       => 'BIGINT',
					'null' => false,
					'constraint' => 20,
			    ],
				'category_id' => [
					'type' => 'BIGINT',
					'null' => false,
					'constraint' => 20,

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
		self::$forge->addKey('post_id', true);
		self::$forge->addKey('category_id', true);
		self::$forge->createTable('post_category');
	}

	public static function DOWN()
	{
		self::$forge->dropTable('post_category');
		
	}
}
