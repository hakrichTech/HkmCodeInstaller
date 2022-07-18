<@php

namespace App\Database\Seeds;

use Hkm_code\I18n\Time;
use Hkm_code\Database\Seeder;

class AdminSeeder extends Seeder
{
	public static function RUN()
	{
		
		$data = [
			'username' => 'Admin',
			'email'    => '${email}',
			'userFullname' => "",
			'password' => password_hash('${password}', PASSWORD_DEFAULT),
			'phone' => "",
			'birthDate'=>"",
			'created_at' => Time::NOW(),
			'updated_at' => Time::NOW(),
			'deleted_at' => '',
		];

		

		// Using Query Builder
		self::$db::TABLE('users_info')->insert($data);
	}
}