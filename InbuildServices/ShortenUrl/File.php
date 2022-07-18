<?php
namespace Hkm_services\ShortenUrl;



use Hkm_code\Entity\Entity;
use Hkm_code\Vezirion\ServicesSystem;

/**
 * User API: File class
 *
 * @package HkmCode
 * @subpackage File
 */


class File extends Entity implements FileInterface
{

    protected $datamap = [];
	protected $dates   = [
		'created_at',
		'updated_at',
		'deleted_at',
	];

	protected $casts   = [];

	use FileTrait;
    protected static $recentyUpload=[];


	public function setId($value)
	{
		$this->attributes['id'] = $value;

		return $this;
	}
 
	public function setPath($value)
	{
		$this->attributes['path'] = $value;
		return $this;
	}

	public function setOriginalDimensions(string $value)
	{
		$this->attributes['original_dimensions'] = $value;
		return $this;
	}

	public function setThumbnailDimensions(string $value)
	{
		$this->attributes['thumbnail_dimensions'] = $value;
		return $this;
	}

	public function setType(string $value)
	{
		$this->attributes['type'] = $value;
		return $this;
	}
	public function setDownload(string $value)
	{
		$this->attributes['download'] = $value;
		return $this;
	}

	public function setViewFile(string $value)
	{
		$this->attributes['view_file'] = $value;
		return $this;
	}

	public function getViews()
	{
		return $this->attributes['view_file'];
	}

	public function getDownload()
	{
		return $this->attributes['download'];
	}
	public function getOriginalDimensions()
	{
		return $this->attributes['original_dimensions'];
	}
	public function getThumbnailDimensions()
	{
		return $this->attributes['thumbnail_dimensions'];
	}

	public function getType()
	{
		return $this->attributes['type'];
	}

    public function setName($value)
	{
		$this->attributes['name'] = $value;
		return $this;
	}

	public function setSize($value)
	{
		$this->attributes['size'] = $value;
		return $this;
	}
	public function getFileSize()
	{
		return $this->attributes['size'];
	}

	public function getFilename()
	{
		return $this->attributes['name'];
	}
	public function getFilePath()
	{
		return $this->attributes['path'];
	}




	/**
	 * Get the unique identifier for the file.
	 *
	 * @return mixed
	 */
	public function getFileIdentifier()
	{
		return $this->attributes['id'];
	}

	/**
	 * Dynamically access the user's attributes.
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	public function __get($key)
	{
		return $this->attributes[$key];
	}

	/**
	 * Dynamically set an attribute on the user.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return void
	 */
	public function __set($key, $value=null)
	{
		$this->attributes[$key] = $value;
	}

	/**
	 * Dynamically check if a value is set on the user.
	 *
	 * @param  string  $key
	 * @return bool
	 */
	public function __isset($key):bool
	{
		return isset($this->attributes[$key]);
	}

	/**
	 * Dynamically unset a value on the user.
	 *
	 * @param  string  $key
	 * @return void
	 */
	public function __unset( string $key):void
	{
		unset($this->attributes[$key]);
	}

	public function jsonSync()
	{

		
        $icon = 'fa fa-file-o FILE';
		if(isset(self::$filesType[$this->getType()])){
            $df = self::$filesType[$this->getType()];
			$ry = explode(' ',$df);
			$gb = UCfirst($ry[0]);
			if(isset(self::$typesFile[$gb]))self::$typesFile[$gb][] = $this->getFilename();
			else self::$typesFile['Document'][] = $this->getFilename();
			$icon = self::$icons[$gb];

		}else{
			$va = explode('/',$this->getType());
			$tva = ucfirst($va[0]);
			if(isset(self::$typesFile[$tva])){
				 self::$typesFile[$tva][] = $this->getFilename();
				$icon = self::$icons[$tva];
			}
			else {
				self::$typesFile['Others'][] = $this->getFilename();
				$icon = self::$icons['Others'];
			}
		}
		$date = $this->getCreatedAt("M Y");

		self::$datesData[$date][] = $this->getFilename();
        hkm_helper('url');
		$ar = [
			$this->getFilename()=>[
				'file'=>[
					'filename'=>$this->getFilename(),
					'size'=>$this->getFileSize(),
					'uploaded on'=>$this->getCreatedAt(),
					'type'=>$this->getType(),
					'dimensions'=>$this->getOriginalDimensions(),
					'thumbnailDimensions'=>$this->getThumbnailDimensions(),
				],
				'other'=>[
					'downloads'=>$this->getDownload(),
					'views'=>$this->getViews(),
                    'link'=>hkm_site_url('/file/'.$this->getFilePath())
				],
				'code'=>$this->getFilePath(),
				'icon'=>$icon
			]
			
		];

		if(count(self::$recentyUpload) < 5)self::$recentyUpload[]=$this->getFilename();
		
		if(isset(self::$arrayData['file'])) self::$arrayData['file'] = array_merge(self::$arrayData['file'],$ar);
		else self::$arrayData['file'] = $ar;
		
		if (isset(self::$jsonData['file'])) self::$jsonData['file']  = array_merge(self::$jsonData['file'],$ar);
		else self::$jsonData['file'] = $ar;

		
		return [];

	}
	public static function jsonReturn()
	{
		$body = ServicesSystem::FORMAT()::GET_FORMATTER("application/json")::FORMAT(self::$jsonData['file']??[]);
		return $body;
	}

	public static function jsonFilesDates()
	{
		$body = ServicesSystem::FORMAT()::GET_FORMATTER("application/json")::FORMAT(self::$datesData??[]);
		return $body;
	}
	
	public static function jsonFileTypes()
	{
		$body = ServicesSystem::FORMAT()::GET_FORMATTER("application/json")::FORMAT(self::$typesFile??[]);
		return $body;
	}
	public static function arrayReturn()
	{
		return self::$arrayData['file']??[];
	}
	public static function jsonReturnLatest()
	{
		$body = ServicesSystem::FORMAT()::GET_FORMATTER("application/json")::FORMAT(self::$recentyUpload??[]);
		return $body;
	}
}
 