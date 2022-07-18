<?php

namespace Hkm_code\Files;

interface PluginInterface
{
    /**
     * @param  Thumbnail $phpthumb
     * @return Thumbnail
     */
    public static function EXECUTE($phpthumb);
}
