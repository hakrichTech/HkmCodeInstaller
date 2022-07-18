<?php

namespace Hkm_services\Auth\Models;


use Hkm_code\Model;

class UserAddressModel extends Model
{
	protected static $DBGroup              = 'default';
	public static $table                   = 'user_address';
	protected static $primaryKey           = 'user_id';
	protected static $useAutoIncrement     = true;
	protected static $insertID             = 0;
	protected static $returnType           = 'Hkm_Auth\\HkmUserAddress';
	protected static $useSoftDeletes       = false;
	protected static $protectFields        = false;
	protected static $allowedFields        = ['deleted_at','updated_at', 'created_at'];

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
