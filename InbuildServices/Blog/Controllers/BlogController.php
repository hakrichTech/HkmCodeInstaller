<?php
namespace Hkm_services\Blog\Controllers;

use Hkm_code\PluginBaseController;
use Hkm_services\Auth\HkmUserInterface;
use Hkm_services\Blog\Entities\BlogEntities;
use Hkm_services\HkmHtml\Html_Convertor;
use Hkm_services\Utility;

class BlogController extends PluginBaseController
{
    public static function CREATE_BLOG()
    {

        try {
            Hkm_Authpost_user();

            /**
             * @var HkmUserInterface $current_user
             */
            global $current_user;

            $data = [
                'user_id'=>$current_user->getAuthIdentifier(),
                'title'=>ucfirst(htmlentities($_POST['title'])),
                'categories'=>htmlentities($_POST['categories']??''),
                'tags'=>htmlentities($_POST['tags']??''),
                'content'=>htmlentities($_POST['content']),
                'meta_title'=> ucfirst(htmlentities($_POST['title'])),
                'slug'=> Utility::SLUG(htmlentities($_POST['title'])),
                'summary'=> htmlentities($_POST['summary']),
            ];

            if (isset($_POST['headerImage']) && !empty($_POST['headerImage'])) {
                $data['headerImage'] = $_POST['headerImage'];
            }

            $newBlog = new BlogEntities();
            $id = $newBlog->init($data);
            $newBlog->save();
            return self::$response::SET_JSON(["error"=>false,
                                              "pst"=>$id
                                            ]);

        } catch (\Throwable $th) {
            return self::$response::SET_JSON(["error"=>true]);
        }
       

    }

    public static function VIEW_BLOG($categ, $blog)
    {
        Html_Convertor::convert("
        [:imgRight src:http://localhost:2105/file/9e9830dbbab7626129f7 alt:helo_atribute figcaption:hello_then /]
        [:h4] What is pneumonia? [:/h4]
        [:p]  Pneumonia is an infection in one or both of your lungs caused by bacteria, viruses or fungi. When there is an infection in the lungs, several things happen, including: [:/p]
        [:ul] 
        [:li]Your airways swell (become inflamed)[:/li]
        [:li]The air sacs in the lungs fill with mucus and other fluids [:/li]
        [:/ul]

        [:h4] How do the lungs work? [:/h4]
        [:p]Your lungs’ main job is to get oxygen into your blood and remove carbon dioxide. This happens during breathing. You breathe 12 to 20 times per minute when you are not sick. When you breathe in, air travels down the back of your throat and passes through your voice box and into your windpipe (trachea). Your trachea splits into two air passages (bronchial tubes). One bronchial tube leads to the left lung, the other to the right lung. For the lungs to perform their best, the airways need to be open as you breathe in and out. Swelling (inflammation) and mucus can make it harder to move air through the airways, making it harder to breathe. This leads to shortness of breath, difficulty breathing and feeling more tired than normal.
        [:/p]

        [:h4]How common is pneumonia?[:/h4]
        [:p]Approximately 1 million adults in the United States are hospitalized each year for pneumonia and 50,000 die from the disease. It is the second most common reason for being admitted to the hospital -- childbirth is number one. Pneumonia is the most common reason children are admitted to the hospital in the United States. Seniors who are hospitalized for pneumonia face a higher risk of death compared to any of the top 10 other reasons for hospitalization.
        [:/p]

        [:h4]Is pneumonia contagious?[:/h4]
        [:p]Certain types of pneumonia are contagious (spread from person to person). Pneumonia caused by bacteria or viruses can be contagious when the disease-carrying organisms are breathed into your lungs. However, not everyone who is exposed to the germs that cause pneumonia will develop it. Pneumonia caused by fungi are not contagious. The fungi are in soil, which becomes airborne and inhaled, but it is not spread from person to person. 
        [:/p]

        [:h4]How is pneumonia spread from person to person?[:/h4]
        [:p]Pneumonia is spread when droplets of fluid containing the pneumonia bacteria or virus are launched in the air when someone coughs or sneezes and then inhaled by others. You can also get pneumonia from touching an object previously touched by the person with pneumonia (transferring the germs) or touching a tissue used by the infected person and then touching your mouth or nose. 
        [:/p]

        [:h4]How long do I remain contagious if I have pneumonia?[:/h4]
        [:p]If you have bacterial pneumonia, you are still considered contagious until about the second day after starting to take antibiotics and you no longer have a fever (if you had one). If you have viral pneumonia, you are still considered contagious until you feel better and have been free of fever for several days.  
        [:/p]

        ");

        $view = hkm_view('header_no_session',self::$data)
		        .hkm_view('view_blog',self::$data)
				.hkm_view('footer_no_session',self::$data);
        return $view;
    }
}




