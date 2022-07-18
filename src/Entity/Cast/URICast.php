<?php

/**
 * This file is part of Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@hakrichteam.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Hkm_code\Entity\Cast;

use Hkm_code\HTTP\URI;

/**
 * Class URICast
 */
class URICast extends BaseCast
{
    /**
     * {@inheritDoc}
     */
    public static function get($value, array $params = []): URI
    {
        return $value instanceof URI ? $value : new URI($value);
    }
}
