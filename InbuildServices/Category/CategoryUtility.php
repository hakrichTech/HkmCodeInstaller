<?php
namespace Hkm_services\Category;

use Hkm_services\Utility;

class CategoryUtility extends Utility
{
    
    /**
     * Converts input into array
     *
     * @param string|array $categoryNames
     * @return array
     */
    public static function MAKE_CATEG_ARRAY($categoryNames)
    {
        if(is_array($categoryNames) && count($categoryNames) == 1) {
            $categoryNames = reset($categoryNames);
        }

        if(is_string($categoryNames)) {
            $categoryNames = explode(',', $categoryNames);
        } elseif(!is_array($categoryNames)) {
            $categoryNames = array(null);
        }

        $categoryNames = array_map('trim', $categoryNames);

        return array_values($categoryNames);
    }
}
