<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\PriceProductMerchantRelationshipStorage\Storage;

use Spryker\Client\PriceProductMerchantRelationshipStorage\Dependency\Client\PriceProductMerchantRelationshipStorageToStorageClientInterface;
use Spryker\Client\Storage\Redis\Service;

class MerchantRelationshipPriceStorageReader implements MerchantRelationshipPriceStorageReaderInterface
{
    protected const string KEY_PRICES = 'prices';

    public function __construct(protected PriceProductMerchantRelationshipStorageToStorageClientInterface $storageClient)
    {
    }

    /**
     * @param array<string> $keys
     *
     * @return array<int|string, mixed>
     */
    public function findMergedPricesByKeys(array $keys): array
    {
        $keyValues = $this->storageClient->getMulti($keys);
        $mergedPrices = [];

        foreach ($keys as $key) {
            $prices = $this->findPrices($keyValues[Service::KV_PREFIX . $key] ?? null);

            $mergedPrices = array_replace_recursive($mergedPrices, $prices);
        }

        return $mergedPrices;
    }

    /**
     * @return array<int|string, mixed>
     */
    protected function findPrices(mixed $rawValue): array
    {
        if (!is_string($rawValue)) {
            return [];
        }

        $decodedValue = json_decode($rawValue, true);

        if (!is_array($decodedValue) || !isset($decodedValue[static::KEY_PRICES]) || !is_array($decodedValue[static::KEY_PRICES])) {
            return [];
        }

        return $decodedValue[static::KEY_PRICES];
    }
}
