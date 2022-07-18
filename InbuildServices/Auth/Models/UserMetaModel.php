<?php

namespace Hkm_services\Auth\Models;


use Hkm_code\Model;

class UserMetaModel extends Model
{
	protected static $DBGroup              = 'default';
	public static $table                   = 'user_meta';
	protected static $primaryKey           = 'meta_id';
	protected static $useAutoIncrement     = true;
	protected static $insertID             = 0;
	protected static $returnType           = 'array';
	protected static $useSoftDeletes       = true;
	protected static $protectFields        = true;
	protected static $allowedFields        = ['meta_id','umeta_id','name','content','created_at','updated_at','deleted_at'];

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
