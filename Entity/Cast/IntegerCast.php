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
 * Class IntegerCast
 */
class IntegerCast extends BaseCast
{
    /**
     * {@inheritDoc}
     */
    public static function get($value, array $params = []): int
    {
        return (int) $value;
    }
}
