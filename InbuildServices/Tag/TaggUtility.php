<?php
namespace Hkm_services\Tag;

use Hkm_services\Utility;

class TaggUtility extends Utility
{
    /**
     * Converts input into array
     *
     * @param string|array $tagNames
     * @return array
     */
    public static function MAKE_TAG_ARRAY($tagNames)
    {
        if(is_array($tagNames) && count($tagNames) == 1) {
            $tagNames = reset($tagNames);
        }

        if(is_string($tagNames)) {
            $tagNames = explode(',', $tagNames);
        } elseif(!is_array($tagNames)) {
            $tagNames = array(null);
        }

        $tagNames = array_map('trim', $tagNames);

        return array_values($tagNames);
    }
}
