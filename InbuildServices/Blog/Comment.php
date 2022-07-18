<?php
namespace Hkm_Blog;

use Hkm_code\I18n\Time;
use Hkm_code\TextFormat;
use Hkm_Blog\Models\PostCommentModel;

class Comment
{
    public static $fresh_comment;
    public static $parse_comment = null;
    /**
	 * The amount of found comments for the current query.
	 *
	 * @var int
	 */
	public static $found_comments = 0;

    function __construct($comment = null)
    {

        if (!is_null($comment)) {
            self::$fresh_comment = $comment;
            self::$parse_comment = self::QUERY();
        }else self::$fresh_comment = null;
 
    }

    public static function QUERY()
    {
        self::$fresh_comment['id'] = self::$fresh_comment['parent_id'];
        unset(self::$fresh_comment['parent_id']);
        self::$fresh_comment['author'] = self::$fresh_comment['user_id'];
        unset(self::$fresh_comment['user_id']);
        self::_POST_AUTHOR();
        self::_POST_TIMES_OBJECT('created_at');
        self::_POST_TIMES_OBJECT('updated_at');
        self::_POST_TIMES_OBJECT('deleted_at');
        switch (self::$fresh_comment['published']) {
            case '0':
                self::$fresh_comment['status'] = "Not Published";
                break;
            case '1':
                self::$fresh_comment['status'] = "Published";
                break;
            default:
                self::$fresh_comment['status'] = "Not Published";
                break;
        }
        unset(self::$fresh_comment['published']);

        $model = new PostCommentModel();
        $build = $model::BUILDER();
        $build->select('*');
        $build->where('post_id',self::$fresh_comment['id']);
        $res = $build->get()->getResult('array');

        if (count($res) > 0) {
            self::$found_comments = count($res);
            self::$fresh_comment['SubComments'] = $res;
            self::$fresh_comment['SubComments_count'] = self::$found_comments;
          }
          if (isset(self::$fresh_comment['SubComments'])) {
              $comments_fresh = self::$fresh_comment['SubComments'];
              $comments_parse = [];
              foreach ($comments_fresh as $comment) {
                  $comments_parse[] = (new Comment($comment))::RESULT();
              }
              self::$fresh_comment['SubComments'] = $comments_parse;
  
          }else self::$fresh_comment['SubComments'] = null;
        return self::$fresh_comment;
    }
    public static function RESULT()
    {
        if(!is_null(self::$parse_comment)) return self::$parse_comment;
        else null;
    }
    protected static function _POST_AUTHOR()
	{
		if (!is_null(self::$fresh_comment)) {
			hkm_helper('hkm_Auth');
			self::$fresh_comment['author'] = Auth_get_user(self::$fresh_comment['author']);
		}
	}
	protected static function _POST_TIMES_OBJECT($key)
	{
		if (!is_null(self::$fresh_comment)) {
			if (!empty(self::$fresh_comment[$key])) {
				$time = explode(' ',self::$fresh_comment[$key]);
				@list($hour,$minutes,$second) = explode(':',$time[1]);
				@list($year,$month,$day) = explode('-',$time[0]);
				self::$fresh_comment[$key] = Time::CREATE((int) $year,(int) $month,(int) $day, (int) $hour, (int) $minutes, (int) $second);
			}

			
		}
	}
	
}
