<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\PriceProductMerchantRelationshipStorage\Dependency\Client;

/**
 * @deprecated Will be removed in the next major. Use {@link \Spryker\Client\Storage\StorageClientInterface} directly.
 */
interface PriceProductMerchantRelationshipStorageToStorageClientInterface
{
    /**
     * @param string $key
     *
     * @return mixed
     */
    public function get($key);

    /**
     * @param array<string> $keys
     *
     * @return array<string, mixed>
     */
    public function getMulti(array $keys): array;
}
