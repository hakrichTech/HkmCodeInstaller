<?php
namespace Hkm_Blog\Controllers;

use Hkm_code\PluginBaseController;

class BlogApi extends PluginBaseController
{
    public static function API($some)
    {

        $body=['error'=>"true"];
        if ($some == EVERYTHING) {
            $blogs = Authpost_Blogs(null,true,true);
            
            $blogs = array_map(function($value){
                hkm_helper('text');
                $blog = (object) $value;
                $value['author'] = $value['author']->userFullname;
                $value['summary'] = highlight_styles($blog->summary,true);
                $value['content'] = highlight_styles($blog->content,true);
                $value['created_at'] = hkm_string_to_time($blog->created_at)::DIFFERENCE()::HUMANIZE();
                $value['updated_at'] = hkm_string_to_time($blog->updated_at)::DIFFERENCE()::HUMANIZE();
                $imageHeader = "f";
                if (is_array($blog->metas)) {
                    foreach ($blog->metas as $meta) {
                        if ($meta['name']=="header_minature") {
                        $imageHeader = $meta['content'];
                        }
                    }
                }
                $value['headerImage'] = $imageHeader;
                unset($value['metas']);
                unset($value['tags']);
                unset($value['tags_count']);
                unset($value['metas_count']);
                unset($value['status']);
                unset($value['url']);
                unset($value['meta_title']);
                return $value;
            },$blogs);

            $body = $blogs;

        }
        if ($some == BLOG_ID) {
			$token = self::$request::GET_GET_POST('token');
			$payload = Auth_validate_token($token);
            if(Blog_check($payload->post_id)){
                $value = Blog($payload->post_id,true);
                $blog = (object) $value;
                $value['summary'] = highlight_styles($blog->summary,true);
                $value['content'] = highlight_styles($blog->content,true);
                $value['created_at'] = hkm_string_to_time($blog->created_at)::DIFFERENCE()::HUMANIZE();
                $value['updated_at'] = hkm_string_to_time($blog->updated_at)::DIFFERENCE()::HUMANIZE();
                $imageHeader = "f";
                if (is_array($blog->metas)) {
                    foreach ($blog->metas as $meta) {
                        if ($meta['name']=="header_image") {
                        $imageHeader = $meta['content'];
                        }
                    }
                }
                $value['headerImage'] = $imageHeader;
                unset($value['metas']);
                unset($value['tags_count']);
                unset($value['metas_count']);
                unset($value['status']);
                unset($value['url']);
                unset($value['meta_title']);
                $body = $value;
            }
            
            
        }

        return self::$response::SET_JSON($body);
    }
}
