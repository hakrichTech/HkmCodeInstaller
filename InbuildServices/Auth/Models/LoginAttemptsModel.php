<?php
namespace Hkm_services\Auth\Models;

use Hkm_code\Model;
use Hkm_code\Vezirion\ServicesSystem;

class LoginAttemptsModel extends Model
{
	protected static $DBGroup              = 'default';
	public static $table                   = 'LoginAttempts';
	protected static $primaryKey           = 'user';
	protected static $useAutoIncrement     = true;
	protected static $insertID             = 0;
	protected static $returnType           = 'array';
	protected static $useSoftDeletes       = false;
	protected static $protectFields        = true;
	protected static $allowedFields        = ['created_at','updated_at','deleted_at','id','user','ip','timestamp'];

	// Dates
	protected static $useTimestamps        = true;
	protected static $dateFormat           = 'datetime';
	protected static $createdField         = 'created_at';
	protected static $updatedField         = 'updated_at';
	protected static $deletedField         = 'deleted_at';

	// Validation
	protected static $validationRules      = [];
	protected static $validationMessages   = [];
	protected static $skipValidation       = false;
	protected static $cleanValidationRules = true;

	// Callbacks
	protected static $allowCallbacks       = true;
	protected static $beforeInsert         = ['getIp'];
	protected static $afterInsert          = [];
	protected static $beforeUpdate         = ['getIp'];
	protected static $afterUpdate          = [];
	protected static $beforeFind           = ['checkCache'];
	protected static $afterFind            = ['setCache'];
	protected static $beforeDelete         = [];
	protected static $afterDelete          = [];

	protected static function getIp(array $data)
	{
		$request = ServicesSystem::REQUEST();

			$data['data']['ip'] = $request::GET_IP_ADDRESS();
          return $data;		
	}
}
