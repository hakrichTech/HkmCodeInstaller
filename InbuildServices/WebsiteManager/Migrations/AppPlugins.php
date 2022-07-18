<?php
namespace Hkm_services\WebsiteManager\Migrations;



/**
 * @package HKMManagerAppPlugins
 * @version 2021-11-23-025212_AppPlugins
 */
/*
Migrate Name: HKMManagerAppPlugins
Migrate class: AppPlugins 
Version: 2021-11-23-025212_AppPlugins
*/

use Hkm_code\Database\Migration;

class AppPlugins extends Migration
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
			'app_id'       => [
				'type'       => 'BIGINT',
				'constraint' => 20,
			],
			'plugin_id'       => [
				'type'       => 'BIGINT',
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
		self::$forge->addKey('pluggin_id', false, true);
		self::$forge->createTable('AppPlugins');
	}

	public static function DOWN()
	{
		self::$forge->dropTable('AppPlugins');
	}
}
