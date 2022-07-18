<?php

namespace Hkm_services\Category\Models;

use Hkm_code\Model;

class CategorySubModel extends Model
{
	protected static $DBGroup              = 'default';
	public static $table                   = 'category_sub';
	protected static $primaryKey           = 'category_parent';
	protected static $useAutoIncrement     = true;
	protected static $insertID             = 0;
	protected static $returnType           = 'array';
	protected static $useSoftDeletes       = true;
	protected static $protectFields        = true;
	protected static $allowedFields        = ['category_id','category_parent','created_at','deleted_at','updated_at','name','meta_title','slug'];

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
	protected static $beforeFind           = [];
	protected static $afterFind            = [];
	protected static $beforeDelete         = [];
	protected static $afterDelete          = [];
}
