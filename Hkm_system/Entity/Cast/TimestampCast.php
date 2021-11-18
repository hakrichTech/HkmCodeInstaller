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

use Hkm_code\Entity\Exceptions\CastException;

/**
 * Class TimestampCast
 */
class TimestampCast extends BaseCast
{
    /**
     * {@inheritDoc}
     */
    public static function get($value, array $params = [])
    {
        $value = strtotime($value);

        if ($value === false) {
            throw CastException::forInvalidTimestamp();
        }

        return $value;
    }
}
