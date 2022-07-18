<?php
namespace Hkm_services\Admin\Controllers;


use Hkm_code\PluginBaseController;
use Hkm_services\HkmHtml\Hkm_Html;

class Admin extends PluginBaseController
{

    

	public static function DASHBOARD($page)
	{

		

		Hkm_Authpost_user(self::$data);
		
       

		hkm_add_filter('load_file_upload_script',function(){return true;});

		 
		hkm_Authpost_filestore();
		hkm_Authpost_category();
		hkm_Authpost_tag();


		switch ($page) {
			case 'profile':
				$view = hkm_view('Admin/header',self::$data)
		        .hkm_view('Admin/sidebar',self::$data)
				.hkm_view('Admin/pages/profile',self::$data);
				break;
			case 'dashboard':
				$view = hkm_view('Admin/header',self::$data)
		        .hkm_view('Admin/sidebar',self::$data)
				.hkm_view('Admin/pages/dashboard',self::$data);
				break;

            case 'filestore':
				
				Hkm_Html::SET_HEADER([
					'title'=>'Authpost - FileStore',
					'description' => 'no description',
					'image' => 'default.png',
					'url' => 'http://localhost/'
				])
				
				->setElement('footer','script',['src'=>"/assets/js/second.filestore.js",'indexPage'=>true]);

				$view = hkm_view('Admin/header',self::$data)
		        .hkm_view('Admin/sidebar',self::$data)
				.hkm_view('Admin/pages/FileStore',self::$data);
                break;
			case 'updates':
				hkm_Authpost_blogs();

                 Hkm_Html::SET_HEADER([
					'title'=>'Authpost - Updates',
					'description' => 'no description',
					'image' => 'default.png',
					'url' => 'http://localhost/'
				 ])
				 ->setElement('footer','script',['src'=>"/assets/js/body.add.modal.blog.js",'indexPage'=>true]);


				$view = hkm_view('Admin/header',self::$data)
		               .hkm_view('Admin/sidebar',self::$data)
				       .hkm_view('Admin/pages/blog',self::$data);



				break;
			
			default:
				# code...
				break;
		}

		
				$view .= hkm_view('Admin/footer',self::$data);

	   return $view;
	}

}