<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@hakrichteam.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Database\Exceptions;

/**
 * Provides a domain-level interface for broad capture
 * of all database-related exceptions.
 *
 * catch (\Hkm_code\Database\Exceptions\ExceptionInterface) { ... }
 */
interface ExceptionInterface extends \Hkm_code\Exceptions\ExceptionInterface
{
}
