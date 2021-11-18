<?php

/**
 * This file is part of Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Hkm_code\Entity\Cast;

/**
 * Interface CastInterface
 */
interface CastInterface
{
    /**
     * Get
     *
     * @param mixed $value  Data
     * @param array $params Additional param
     *
     * @return mixed
     */
    public static function get($value, array $params = []);

    /**
     * Set
     *
     * @param mixed $value  Data
     * @param array $params Additional param
     *
     * @return mixed
     */
    public static function set($value, array $params = []);
}
