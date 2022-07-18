<?php
namespace Hkm_services\WebsiteManager\Models;


use Hkm_code\Model;

class RolesModel extends Model
{
	public static $DBGroup                 = 'system';
	public static $table                   = 'Roles';
	protected static $primaryKey           = 'id';
	protected static $useAutoIncrement     = true;
	protected static $insertID             = 0;
	protected static $returnType           = 'array';
	protected static $useSoftDeletes       = false;
	protected static $protectFields        = false;
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
	protected static $allowCallbacks       = false;
	protected static $beforeInsert         = [];
	protected static $afterInsert          = [];
	protected static $beforeUpdate         = [];
	protected static $afterUpdate          = [];
	protected static $beforeFind           = ['checkCache'];
	protected static $afterFind            = ['setCache'];
	protected static $beforeDelete         = [];
	protected static $afterDelete          = [];

}
