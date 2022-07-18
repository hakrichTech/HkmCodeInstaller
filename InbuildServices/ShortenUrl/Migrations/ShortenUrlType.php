<?php

namespace Hkm_services\ShortenUrl\Migrationsc;


/**
 * @package Hkm_ShortenUrlShortenUrlType
 * @version 2021-09-30-133939_ShortenUrlType
 */
/*
Migrate Name: Hkm_ShortenUrlShortenUrlType
Migrate class: ShortenUrlType
Version: 2021-09-30-133939_ShortenUrlType
*/
use Hkm_code\Database\Migration;

class ShortenUrlType extends Migration
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
				'shorten_url_id'       => [
					'type'           => 'BIGINT',
					'constraint'     => 30,
					'unsigned'       => true,
				],
				'type' => [
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
		self::$forge->addKey('id', true, true);
		self::$forge->addKey('shorten_url_id', false, true);
		self::$forge->createTable('ShortenUrlType');
		self::$forge->setAutoIncrementStartingValue(25708);

	}

	public static function DOWN()
	{
		self::$forge->dropTable('ShortenUrlType');
	}
}
