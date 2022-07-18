<?php
namespace Hkm_services\WebsiteManager\Migrations;


/**
 * @package HKMManagerPlugins
 * @version 2021-12-08-003714_Plugins
 */
/*
Migrate Name: HKMManagerPlugins
Migrate class: Plugins 
Version: 2021-12-08-003714_Plugins
*/
use Hkm_code\Database\Migration;

class Plugins extends Migration
{
	public static function UP()
	{
			self::$forge->addField([
				'plugin_id'          => [
					'type'           => 'BIGINT',
					'constraint'     => 20,
					'unsigned'       => true,
					'null' => false,
					'auto_increment' => true,
			   ],
				'plugin_name' => [
					'type' => 'VARCHAR',
					'null' => true,
					'constraint' => 50,

			 	],
				 'plugin_path' => [
					'type' => 'VARCHAR',
					'null' => true,
					'constraint' => 50,

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
		self::$forge->addKey('plugin_id', true,true);
		self::$forge->addKey('plugin_name', false,true);
		self::$forge->createTable('Plugins');
	}

	public static function DOWN()
	{
		self::$forge->dropTable('Plugins');
		
	}
}
