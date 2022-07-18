<?php

namespace Hkm_services\Blog\Models;

use Hkm_code\Model;

class PostTagModel extends Model
{
	protected static $DBGroup              = 'default';
	public static $table                   = 'post_tag';
	protected static $primaryKey           = 'post_id';
	protected static $useAutoIncrement     = true;
	protected static $insertID             = 0;
	protected static $returnType           = '\Hkm_services\Blog\Entities\BlogTagEntities';
	protected static $useSoftDeletes       = true;
	protected static $protectFields        = true;
	protected static $allowedFields        = ['created_at','deleted_at','updated_at','post_id','tag_id'];

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
