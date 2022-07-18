<?php

namespace Hkm_services\Tag\Models;

use Hkm_code\Model;

class TagModel extends Model
{
	protected static $DBGroup              = 'default';
	public static $table                   = 'tag';
	protected static $primaryKey           = 'tag_slug';
	protected static $useAutoIncrement     = true;
	protected static $insertID             = 0;
	protected static $returnType           = 'Hkm_services\Tag\Entities\TagEntities';
	protected static $useSoftDeletes       = true;
	protected static $protectFields        = true;
	protected static $allowedFields        = ['created_at','updated_at','deleted_at','tag_id','meta_title','tag_slug','tag_name'];

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
