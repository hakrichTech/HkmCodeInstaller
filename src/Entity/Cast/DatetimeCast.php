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

use Hkm_code\I18n\Time;
use DateTime;
use Exception;

/**
 * Class DatetimeCast
 */
class DatetimeCast extends BaseCast
{
    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public static function get($value, array $params = [])
    {
        if ($value instanceof Time) {
            return $value;
        }

        if ($value instanceof DateTime) {
            return Time::INSTANCE($value);
        }

        if (is_numeric($value)) {
            return Time::CREATE_FROM_TIMESTAMP($value);
        }

        if (is_string($value)) {
            return Time::PARSE($value);
        }

        return $value;
    }
}
