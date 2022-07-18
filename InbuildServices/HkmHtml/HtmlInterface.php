<?php
namespace Hkm_services\HkmHtml;

interface HtmlInterface
{
    /**
	 * Header
	 *
	 * get Header 
	 *
	 *
	 * @return string
	 */
    public static function GET_HEADER():string;

    /**
	 * Header
	 *
	 * set Header 
	 *
     * @var array $data
	 *
	 * @return HtmlInterface
	 */
    public static function SET_HEADER(array $data) :ElementInterface;

    /**
	 * Body
	 *
	 * set Body 
	 *
     * @var array $data
	 *
	 * @return HtmlInterface
	 */
    public static function SET_BODY(array $data): HtmlInterface;

    /**
	 * Body
	 *
	 * get Body 
	 *
	 *
	 * @return string
	 */
    public static function GET_BODY():string;

    /**
	 * Footer
	 *
	 * set Footer 
	 *
     * @var array $data
	 *
	 * @return HtmlInterface
	 */
    public static function SET_FOOTER(array $data): HtmlInterface;

    /**
	 * Footer
	 *
	 * get Footer 
	 *
	 *
	 * @return string
	 */
    public static function GET_FOOTER() :string;
}
