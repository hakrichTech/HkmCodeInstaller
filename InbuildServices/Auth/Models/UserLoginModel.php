<?php

namespace Hkm_services\Auth\Models;


use Hkm_code\Model;
use Hkm_code\Vezirion\ServicesSystem;

class UserLoginModel extends Model
{
	protected static $DBGroup              = 'default';
	public static $table                   = 'Login_recoards';
	protected static $primaryKey           = 'user_login';
	protected static $useAutoIncrement     = true;
	protected static $insertID             = 0;
	protected static $returnType           = 'array';
	protected static $useSoftDeletes       = false;
	protected static $protectFields        = true;
	protected static $allowedFields        = ['user_login','ip_address','device','browser','password','created_at','updated_at','deleted_at'];

	// Dates
	protected static $useTimestamps        = true;
	protected static $dateFormat           = 'datetime';
	protected static $createdField         = 'created_at';
	protected static $updatedField         = 'updated_at';
	protected static $deletedField         = 'deleted_at';

	// Validation
	protected static $validationRules      = [];
	protected static $validationMessages   = [];
	protected static $skipValidation       = true;
	protected static $cleanValidationRules = true;

	// Callbacks
	protected static $allowCallbacks       = true;
	protected static $beforeInsert         = ['getUserAgentDeviceAndBrowserIp'];
	protected static $afterInsert          = [];
	protected static $beforeUpdate         = ['getUserAgentDeviceAndBrowserIp'];
	protected static $afterUpdate          = [];
	protected static $beforeFind           = ['checkCache'];
	protected static $afterFind            = ['setCache'];
	protected static $beforeDelete         = [];
	protected static $afterDelete          = [];


	

	
	protected static function getUserAgentDeviceAndBrowserIp(array $data)
	{
		$request = ServicesSystem::REQUEST();

			$data['data']['ip_address'] = $request::GET_IP_ADDRESS();
			$agent = $request::GET_USER_AGENT();
			if($agent::IS_MOBILE())$data['data']['device'] =$agent::GET_MOBILE();
			elseif ($agent::IS_ROBOT())$data['data']['device'] =$agent::GET_ROBOT();
            else $data['data']['device'] = "Computer";

			$data['data']['browser'] =$agent::GET_BROWSER();
			$data['data']['platform'] = $agent::GET_PLATFORM();
          return $data;		
	}

	protected static function INITIALIZE()
	{
	}
}
