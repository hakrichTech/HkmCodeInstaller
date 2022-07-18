<?php
namespace Hkm_services\Blog\Migrations;

/**
 * @package Hkm_BlogPostMigration
 * @version 2021-11-26-222320_PostMigration
 */
/*
Migrate Name: Hkm_BlogPostMigration
Migrate class: PostMigration 
Version: 2021-11-26-222320_PostMigration
*/


use Hkm_code\Database\Migration;

class PostMigration extends Migration
{
	public static function UP()
	{
			self::$forge->addField([
				'post_id'          => [
						'type'           => 'BIGINT',
						'constraint'     => 20,
						'null' => false,
						'unsigned'       => true,
						'auto_increment' => true,
				],
				'user_id'       => [
						'type'       => 'BIGINT',
						'null' => true,
						'constraint' => 20,
				],
				'parent_id' => [
					'type' => 'BIGINT',
					'null' => false,
					'constraint' => 20,

			    ],
				'title' => [
						'type' => 'VARCHAR',
						'null' => true,
						'constraint' => '75',

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
				'summary' => [
					'type' => 'TINYTEXT',
					'null' => true,
                ],
				'published' => [
					'type' => 'TINYINT',
					'default' => 0,
					'constraint' => 1,
                ],

				'content' => [
					'type' => 'TEXT',
					'null' => true,
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
		self::$forge->addKey('post_id', true,true);
		self::$forge->addKey('parent_id',true);
		self::$forge->createTable('post');
	}

	public static function DOWN()
	{
		self::$forge->dropTable('post');
		
	}
}
