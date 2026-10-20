<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\PriceProductMerchantRelationshipStorage\Storage;

use Spryker\Client\PriceProductMerchantRelationshipStorage\Dependency\Client\PriceProductMerchantRelationshipStorageToStorageClientInterface;
use Spryker\Shared\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageConstants;

class MerchantRelationBusinessUnitLookupReader implements MerchantRelationBusinessUnitLookupReaderInterface
{
    public function __construct(
        protected PriceProductMerchantRelationshipStorageToStorageClientInterface $storageClient,
        protected PriceProductMerchantRelationshipKeyGeneratorInterface $keyGenerator
    ) {
    }

    public function findMerchantRelationshipIdsByCompanyBusinessUnitId(int $idCompanyBusinessUnit): array
    {
        $key = $this->keyGenerator->generateKeyByCompanyBusinessUnit(
            PriceProductMerchantRelationshipStorageConstants::MERCHANT_RELATION_BUSINESS_UNIT_RESOURCE_NAME,
            $idCompanyBusinessUnit,
        );

        $data = $this->storageClient->get($key);

        if (!$data) {
            return [];
        }

        return $data[PriceProductMerchantRelationshipStorageConstants::MERCHANT_RELATION_BUSINESS_UNIT_DATA_KEY_MERCHANT_RELATIONSHIP_IDS] ?? [];
    }
}
