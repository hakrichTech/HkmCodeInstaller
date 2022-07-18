<?php
namespace Hkm_services\WebsiteManager\Migrations;



/**
 * @package HKMManagerNewApp
 * @version 2021-11-22-080039_NewApp
 */
/*
Migrate Name: HKMManagerNewApp
Migrate class: NewApp 
Version: 2021-11-22-080039_NewApp
*/

use Hkm_code\Database\Migration;

class NewApp extends Migration
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
			'appName'       => [
				'type'       => 'VARCHAR',
				'null' => true,
				'constraint' => '250'
			],
			'appPath' => [
				'type' => 'VARCHAR',
				'null' => true,
				'constraint' => '250',

			],
			'appUrl' => [
				'type' => 'VARCHAR',
				'null' => true,
				'constraint' => '250',

			],
			'appDatabase' => [
				'type' => 'VARCHAR',
				'null' => true,
				'constraint' => '250',

			],
			'appDbUser' => [
				'type' => 'VARCHAR',
				'null' => true,
				'constraint' => '250',

			],
			
			'appPassword' => [
				'type' => 'VARCHAR',
				'null' => true,
				'constraint' => '250',

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
		self::$forge->addKey('appUrl', false, true);
		self::$forge->createTable('NewApp');
	}

	public static function DOWN()
	{
		self::$forge->dropTable('NewApp');
	}
}
