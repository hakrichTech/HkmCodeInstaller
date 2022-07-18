<?php
namespace Hkm_services\Blog\Migrations;

/**
 * @package Hkm_BlogPostTagMigration
 * @version 2021-11-26-224824_PostTagMigration
 */
/*
Migrate Name: Hkm_BlogPostTagMigration
Migrate class: PostTagMigration 
Version: 2021-11-26-224824_PostTagMigration
*/

use Hkm_code\Database\Migration;

class PostTagMigration extends Migration
{
	public static function UP()
	{
			self::$forge->addField([
				'id'       => [
					'type'       => 'BIGINT',
					'unsigned'       => true,
					'auto_increment' => true,
					'null' => false,
					'constraint' => 20,
			    ],
				'post_id'       => [
					'type'       => 'BIGINT',
					'null' => false,
					'constraint' => 20,
			    ],
				'tag_id' => [
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
		self::$forge->addKey('id', true, true);
		self::$forge->addKey('post_id',true);
		self::$forge->addKey('tag_id', true);
		self::$forge->createTable('post_tag');
	}

	public static function DOWN()
	{
		self::$forge->dropTable('post_tag');
		
	}
}
