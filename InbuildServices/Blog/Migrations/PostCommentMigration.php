<?php
namespace Hkm_services\Blog\Migrations;


/**
 * @package Hkm_BlogPostCommentMigration
 * @version 2021-11-26-223757_PostCommentMigration
 */
/*
Migrate Name: Hkm_BlogPostCommentMigration
Migrate class: PostCommentMigration 
Version: 2021-11-26-223757_PostCommentMigration
*/
use Hkm_code\Database\Migration;

class PostCommentMigration extends Migration
{
	public static function UP()
	{
			self::$forge->addField([
				'comment_id'          => [
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
				'user_id'       => [
					'type'       => 'BIGINT',
					'null' => false,
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
						'constraint' => '100',

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
		self::$forge->addKey('comment_id', true,true);
		self::$forge->addKey('post_id',true);
		self::$forge->addKey('parent_id', true);
		self::$forge->addKey('user_id', true);
		self::$forge->createTable('post_comment');
	}

	public static function DOWN()
	{
		self::$forge->dropTable('post_comment');
		
	}
}
