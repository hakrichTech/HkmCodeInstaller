<?php

namespace Hkm_services\Blog\Models;

use Hkm_code\Model;

class PostModel extends Model
{
	protected static $DBGroup              = 'default';
	public static $table                   = 'post';
	protected static $primaryKey           = 'parent_id';
	protected static $useAutoIncrement     = true;
	protected static $insertID             = 0;
	protected static $returnType           = 'Hkm_services\Blog\Entities\BlogEntities';
	protected static $useSoftDeletes       = true;
	protected static $protectFields        = true;
	protected static $allowedFields        = ['created_at','deleted_at','updated_at','parent_id','user_id','title','meta_title','slug','summary','content','published'];

	// Dates
	protected static $useTimestamps        = true;
	protected static $dateFormat           = 'timestamp';
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
