<@php

namespace {namespace};

use Hkm_code\Model;

class {class} extends Model
{
	protected static $DBGroup              = '{DBGroup}';
	public static $table                = '{table}';
	protected static $primaryKey           = 'id';
	protected static $useAutoIncrement     = true;
	protected static $insertID             = 0;
	protected static $returnType           = '{return}';
	protected static $useSoftDeletes       = false;
	protected static $protectFields        = true;
	protected static $allowedFields        = [];

	// Dates
	protected static $useTimestamps        = false;
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
