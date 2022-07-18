<?php

/**
 * This file is part of Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@Hkm_code.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Hkm_code\Encryption;

use Hkm_code\Encryption\Exceptions\EncryptionException;

/**
 * Hkm_code Encryption Handler
 *
 * Provides two-way keyed encryption
 */
interface EncrypterInterface
{
    /**
     * Encrypt - convert plaintext into ciphertext
     *
     * @param string            $data   Input data
     * @param array|string|null $params Overridden parameters, specifically the key
     *
     * @throws EncryptionException
     *
     * @return string
     */
    public function encrypt($data, $params = null);

    /**
     * Decrypt - convert ciphertext into plaintext
     *
     * @param string            $data   Encrypted data
     * @param array|string|null $params Overridden parameters, specifically the key
     *
     * @throws EncryptionException
     *
     * @return string
     */
    public function decrypt($data, $params = null);
}
