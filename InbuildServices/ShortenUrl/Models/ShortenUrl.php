<?php

namespace Hkm_services\ShortenUrl\Models;

use Hkm_code\Model;

class ShortenUrl extends Model
{
	protected static $DBGroup              = 'default';
	public static $table                = 'ShortenUrl';
	protected static $primaryKey           = 'shorten_url';
	protected static $useAutoIncrement     = true;
	protected static $insertID             = 0;
	protected static $returnType           = 'Hkm_services\ShortenUrl\Entities\ShortenUrl';
	protected static $useSoftDeletes       = false;
	protected static $protectFields        = true;
	protected static $allowedFields        = ['shorten_url','full_url','clicks','created_at','updated_at','deleted_at','id'];

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
