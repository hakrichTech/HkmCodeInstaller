<?php
namespace Hkm_services\WebsiteManager\Models;






use Hkm_code\Model;

class AppPluginsModel extends Model
{
	public static $DBGroup                 = 'system';
	public static $table                   = 'AppPlugins';
	protected static $primaryKey           = 'app_id';
	protected static $useAutoIncrement     = true;
	protected static $insertID             = 0;
	protected static $returnType           = 'array';
	protected static $useSoftDeletes       = true;
	protected static $protectFields        = false;
	protected static $allowedFields        = ['created_at','updated_at','deleted_at','id','user','token','timestamp','hash','type'];

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
