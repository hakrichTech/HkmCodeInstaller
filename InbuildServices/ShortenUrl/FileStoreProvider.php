<?php
namespace Hkm_services\ShortenUrl;

use Hkm_code\Database\BaseBuilder;
use Hkm_code\Vezirion\ServicesSystem;
use Hkm_services\Auth\HkmUserInterface;
use Hkm_services\ShortenUrl\Models\FileModel;
use Hkm_services\ShortenUrl\Models\ShortenUrlType;

class FileStoreProvider implements FileStoreProviderInterface {

	/**
	 * The active database connection.
	 *
	 * @var FileModel
	 */
	protected $conn;

    /**
	 * The active database connection.
	 *
	 * @var BaseBuilder

	 */
    protected $connBuilder;

	/**
	 * Create a new database user provider.
	 *
	 * @return void
	 */
	public function __construct()
	{
        $model = new FileModel();
        $model::CHECK_ENGINE();



        $this->connBuilder = $model::BUILDER();
        $this->conn = $model;



	}

	public function retrieveById($identifier)
	{
		$file = $this->connBuilder->select('File_store.user_id,File_store.name,File_store.path,File_store.size,File_store.created_at,File_store.updated_at,File_store.deleted_at, s.type, 
		File_store.o_dimensions as original_dimensions, File_store.t_dimensions as thumbnail_dimensions, File_store.view_file, File_store.download')
		->join('ShortenUrlType as s', 's.shorten_url_id = File_store.id', 'left')
		->where('File_store.id', $identifier)
		->where('File_store.deleted_at', '')
		->orderBy('File_store.created_at','DESC')
		->get()
		->getFirstRow('Hkm_services\ShortenUrl\File');

		if ( ! is_null($file))
		{
            return $file;
		}return null;
	}

	public function retrieveByUser($userId)
	{
		$file = $this->connBuilder->select('File_store.user_id,File_store.name,File_store.path,File_store.size,File_store.created_at,File_store.updated_at,File_store.deleted_at, s.type, 
		File_store.o_dimensions as original_dimensions, File_store.t_dimensions as thumbnail_dimensions, File_store.view_file, File_store.download')
		->join('ShortenUrlType as s', 's.shorten_url_id = File_store.id', 'left')
		->where('File_store.user_id', $userId)
		->where('File_store.deleted_at', '')
		->orderBy('File_store.created_at','DESC')
		->get()
		->getFirstRow('Hkm_services\ShortenUrl\File');

		if ( ! is_null($file))
		{
            return $file;
		}return null;
	}

	public function retrieveByname(string $name)
	{
		$file = $this->connBuilder->select('File_store.user_id,File_store.name,File_store.path,File_store.size,File_store.created_at,File_store.updated_at,File_store.deleted_at, s.type, 
		File_store.o_dimensions as original_dimensions, File_store.t_dimensions as thumbnail_dimensions, File_store.view_file, File_store.download')
		->join('ShortenUrlType as s', 's.shorten_url_id = File_store.id', 'left')
		->where('File_store.name', $name)
		->where('File_store.deleted_at', '')
		->orderBy('File_store.created_at','DESC')
		->get()
		->getResult('Hkm_services\ShortenUrl\File');

		if ( ! is_null($file))
		{
            return $file;
		}return [];
	}

	public function retrieveByShorten(string $shorten)
	{
		$file = $this->connBuilder->select('File_store.user_id,File_store.name,File_store.path,File_store.size,File_store.created_at,File_store.updated_at,File_store.deleted_at, s.type, 
		File_store.o_dimensions as original_dimensions, File_store.t_dimensions as thumbnail_dimensions, File_store.view_file, File_store.download')
		->join('ShortenUrlType as s', 's.shorten_url_id = File_store.id', 'left')
		->where('File_store.path', $shorten)
		->where('File_store.deleted_at', '')
		->orderBy('File_store.created_at','DESC')
		->get()
		->getResult('Hkm_services\ShortenUrl\File');

		if ( ! is_null($file))
		{
            return $file;
		}return [];
	}

	public function retrieveAll()
	{
		$file = $this->connBuilder->select('File_store.user_id, File_store.name,File_store.path,File_store.size,File_store.created_at,File_store.updated_at,File_store.deleted_at, s.type, 
		File_store.o_dimensions as original_dimensions, File_store.t_dimensions as thumbnail_dimensions, File_store.view_file, File_store.download')
		->join('ShortenUrlType as s', 's.shorten_url_id = File_store.id', 'left')
		->where('File_store.deleted_at', '')
		->orderBy('File_store.created_at','DESC')
		->get()
		->getResult('Hkm_services\ShortenUrl\File');
		
		if ( ! is_null($file))
		{
            return $file;
		}return [];
	}

	public function upload($fileType,$filekey)
	{
		/**
		 * @var HkmUserInterface $current_user
		 */
		global $current_user;

		$file = ServicesSystem::REQUEST()::GET_FILE($filekey);
		$reurn = [];

		if (! $file::HAS_MOVED()) {
			$filename = $file::STORE();
			$filepath = WRITEPATH . 'uploads/' . $filename;
			hkm_helper('hkm_ShortenUrl');
			$url = ShortenUrl_insert($filepath);
			
			$reurn['url'] = $url;
			$reurn['size'] = $file::GET_SIZE();
			$reurn['status'] = true;  
			$reurn['o_dims'] = 'unknown';
			$reurn['t_dims'] = 'unknown';

			if(!is_null($file::THUMBNAIL())){
				$reurn['min']=$file::THUMBNAIL();
				$reurn['o_dims'] = $file::ORIGINAL_FILE_DIMENSIONS();
			    $reurn['t_dims'] = $file::THUMBNAIL_FILE_DIMENSIONS();
			}
         
			$file_name =  $_FILES['upload']['name'];
			$original_name =  $_FILES['upload']['name'];

			$i = 1;
			$fou = false;
			while (chech_file_name($file_name)) {
					$fou = true;
					$file_name = explode('.',$original_name);
					if(count($file_name)>1)$file_name[count($file_name)-2] = $file_name[count($file_name)-2]."(".$i.")";
					else $file_name[count($file_name)-1] = $file_name[count($file_name)-1]+"("+$i+")";
					$file_name = implode('.',$file_name);
					$i++;
			}

		   $model = new FileModel();
		   $model::CHECK_ENGINE();
		   
		   if (isset($_POST['overwrite'])) {
			 if($fou) $model::UPDATE($original_name,['name'=>$original_name."_overwrite"]);
		   }
		   $id = $model::INSERT([
			    'user_id' => $current_user->getAuthIdentifier(),
				'name'=> isset($_POST['overwrite'])?$original_name:$file_name,
				'path' => $url,
				'size'=>$file::GET_SIZE(),
				'o_dimensions'=> $reurn['o_dims'],
				't_dimensions'=> $reurn['t_dims']
			   ]);

			$model = new ShortenUrlType();
			$model::CHECK_ENGINE();
			$model::INSERT([
				'shorten_url_id'=>$id,
				'type'=>$fileType
			]);
			$reurn['fileid'] = $id;
            $this->retrieveByShorten($url);
			return $reurn;

		} else {
			$reurn['status'] = false;
			$reurn['error'] = 'The file has already been moved.';

		}

	}

	

}
