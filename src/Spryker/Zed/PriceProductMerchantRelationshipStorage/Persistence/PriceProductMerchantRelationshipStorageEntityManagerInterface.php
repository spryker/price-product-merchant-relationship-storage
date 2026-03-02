<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PriceProductMerchantRelationshipStorage\Persistence;

use Generated\Shared\Transfer\PriceProductMerchantRelationshipStorageTransfer;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductAbstractMerchantRelationshipStorage;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductConcreteMerchantRelationshipStorage;

interface PriceProductMerchantRelationshipStorageEntityManagerInterface
{
    public function updatePriceProductAbstract(
        PriceProductMerchantRelationshipStorageTransfer $priceProductMerchantRelationshipStorageTransfer,
        SpyPriceProductAbstractMerchantRelationshipStorage $priceProductAbstractMerchantRelationshipStorageEntity
    ): void;

    public function createPriceProductAbstract(
        PriceProductMerchantRelationshipStorageTransfer $priceProductMerchantRelationshipStorageTransfer
    ): void;

    /**
     * @param array<\Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductAbstractMerchantRelationshipStorage> $priceProductAbstractMerchantRelationshipStorageEntities
     *
     * @return void
     */
    public function deletePriceProductAbstractEntities(
        array $priceProductAbstractMerchantRelationshipStorageEntities
    ): void;

    public function updatePriceProductConcrete(
        PriceProductMerchantRelationshipStorageTransfer $priceProductMerchantRelationshipStorageTransfer,
        SpyPriceProductConcreteMerchantRelationshipStorage $priceProductConcreteMerchantRelationshipStorageEntity
    ): void;

    public function createPriceProductConcrete(
        PriceProductMerchantRelationshipStorageTransfer $priceProductMerchantRelationshipStorageTransfer
    ): void;

    /**
     * @param array<\Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductConcreteMerchantRelationshipStorage> $priceProductConcreteMerchantRelationshipStorageEntities
     *
     * @return void
     */
    public function deletePriceProductConcreteEntities(
        array $priceProductConcreteMerchantRelationshipStorageEntities
    ): void;
}
