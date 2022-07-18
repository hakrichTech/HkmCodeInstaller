<?php
namespace Hkm_services\Blog\Migrations;

/**
 * @package Hkm_BlogPostMetaMigration
 * @version 2021-11-26-225212_PostMetaMigration
 */
/*
Migrate Name: Hkm_BlogPostMetaMigration
Migrate class: PostMetaMigration 
Version: 2021-11-26-225212_PostMetaMigration
*/
use Hkm_code\Database\Migration;

class PostMetaMigration extends Migration
{
	public static function UP()
	{
			self::$forge->addField([
				'meta_id'          => [
					'type'           => 'BIGINT',
					'constraint'     => 20,
					'unsigned'       => true,
					'null' => false,
					'auto_increment' => true,
			   ],
				'post_id'  => [
					'type' => 'BIGINT',
					'null' => false,
					'constraint' => 20,
			    ],
				'name' => [
					'type' => 'VARCHAR',
					'null' => true,
					'constraint' => 50,

			 	],

				 'content' => [
					'type' => 'TEXT',
					'null' => true
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
		self::$forge->addKey('post_id', true);
		self::$forge->createTable('post_meta');
	}

	public static function DOWN()
	{
		self::$forge->dropTable('post_meta');
		
	}
}
