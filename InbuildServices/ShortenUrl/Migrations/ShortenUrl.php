<?php
namespace Hkm_services\ShortenUrl\Models;

/**
 * @package Hkm_ShortenUrlShortenUrl
 * @version 2021-09-30-133937_ShortenUrl
 */
/*
Migrate Name: Hkm_ShortenUrlShortenUrl
Migrate class: ShortenUrl
Version: 2021-09-30-133937_ShortenUrl
*/
use Hkm_code\Database\Migration;

class ShortenUrl extends Migration
{
	public static function UP()
	{
		self::$forge->addField([
				'id'          => [
						'type'           => 'BIGINT',
						'constraint'     => 30,
						'unsigned'       => true,
						'null' => false,
						'auto_increment' => true,
				],
				'shorten_url'       => [
						'type'       => 'VARCHAR',
						'constraint' => '255',
				],
				'full_url' => [
						'type' => 'VARCHAR',
						'constraint' => '1000',
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
		self::$forge->addKey('shorten_url', false,true);
		self::$forge->createTable('ShortenUrl');
		self::$forge->setAutoIncrementStartingValue(257);


	}

	public static function DOWN()
	{
		self::$forge->dropTable('ShortenUrl');
	}
}
