<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PriceProductMerchantRelationshipStorage\Persistence\Mapper\PriceProductMerchantRelationship;

use Generated\Shared\Transfer\PriceProductMerchantRelationshipTransfer;
use Orm\Zed\PriceProductMerchantRelationship\Persistence\SpyPriceProductMerchantRelationship;

class PriceProductMerchantRelationshipMapper implements PriceProductMerchantRelationshipMapperInterface
{
    /**
     * @param \Orm\Zed\PriceProductMerchantRelationship\Persistence\SpyPriceProductMerchantRelationship $priceProductMerchantRelationshipEntity
     * @param \Generated\Shared\Transfer\PriceProductMerchantRelationshipTransfer $priceProductMerchantRelationshipTransfer
     *
     * @return \Generated\Shared\Transfer\PriceProductMerchantRelationshipTransfer
     */
    public function mapEntityToPriceProductMerchantRelationshipTransfer(
        SpyPriceProductMerchantRelationship $priceProductMerchantRelationshipEntity,
        PriceProductMerchantRelationshipTransfer $priceProductMerchantRelationshipTransfer
    ): PriceProductMerchantRelationshipTransfer {
        return $priceProductMerchantRelationshipTransfer->fromArray($priceProductMerchantRelationshipEntity->toArray(), true);
    }

    /**
     * @param \Orm\Zed\PriceProductMerchantRelationship\Persistence\SpyPriceProductMerchantRelationship[] $priceProductMerchantRelationshipEntityTransfers
     *
     * @return \Generated\Shared\Transfer\PriceProductMerchantRelationshipTransfer[]
     */
    public function mapEntitiesToPriceProductMerchantRelationshipTransferCollection(array $priceProductMerchantRelationshipEntityTransfers): array
    {
        $priceProductMerchantRelationshipTransferCollection = [];

        foreach ($priceProductMerchantRelationshipEntityTransfers as $priceProductMerchantRelationshipEntityTransfer) {
            $priceProductMerchantRelationshipTransferCollection[] = $this->mapEntityToPriceProductMerchantRelationshipTransfer(
                $priceProductMerchantRelationshipEntityTransfer,
                new PriceProductMerchantRelationshipTransfer()
            );
        }

        return $priceProductMerchantRelationshipTransferCollection;
    }
}
