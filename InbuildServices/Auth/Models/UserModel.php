<?php

namespace Hkm_services\Auth\Models;


use Hkm_code\Model;

class UserModel extends Model
{
	protected static $DBGroup              = 'default';
	public static $table                   = 'users_info';
	protected static $primaryKey           = 'email';
	protected static $useAutoIncrement     = true;
	protected static $insertID             = 0;
	protected static $returnType           = 'Hkm_Auth\\HkmUser';
	protected static $useSoftDeletes       = false;
	protected static $protectFields        = true;
	protected static $allowedFields        = ['users_id','username','deleted_at','updated_at','email', 'password', 'created_at','userFullname','phone','birthDate'];

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
	protected static $beforeInsert         = [];
	protected static $afterInsert          = [];
	protected static $beforeUpdate         = [];
	protected static $afterUpdate          = [];
	protected static $beforeFind           = ['checkCache'];
	protected static $afterFind            = ['setCache'];
	protected static $beforeDelete         = [];
	protected static $afterDelete          = [];
}
