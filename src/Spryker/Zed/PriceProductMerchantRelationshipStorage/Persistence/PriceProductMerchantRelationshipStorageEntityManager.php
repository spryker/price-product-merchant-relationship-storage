<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PriceProductMerchantRelationshipStorage\Persistence;

use Generated\Shared\Transfer\PriceProductMerchantRelationshipStorageTransfer;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductAbstractMerchantRelationshipStorage;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductConcreteMerchantRelationshipStorage;
use Spryker\Zed\Kernel\Persistence\AbstractEntityManager;

/**
 * @method \Spryker\Zed\PriceProductMerchantRelationshipStorage\Persistence\PriceProductMerchantRelationshipStoragePersistenceFactory getFactory()
 */
class PriceProductMerchantRelationshipStorageEntityManager extends AbstractEntityManager implements PriceProductMerchantRelationshipStorageEntityManagerInterface
{
    /**
     * @param \Generated\Shared\Transfer\PriceProductMerchantRelationshipStorageTransfer $priceProductMerchantRelationshipStorageTransfer
     * @param \Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductAbstractMerchantRelationshipStorage $priceProductAbstractMerchantRelationshipStorageEntity
     *
     * @return void
     */
    public function updatePriceProductAbstract(
        PriceProductMerchantRelationshipStorageTransfer $priceProductMerchantRelationshipStorageTransfer,
        SpyPriceProductAbstractMerchantRelationshipStorage $priceProductAbstractMerchantRelationshipStorageEntity
    ): void {
        $priceProductAbstractMerchantRelationshipStorageEntity
            ->setData($priceProductMerchantRelationshipStorageTransfer->getPrices())
            ->setIsSendingToQueue($this->getFactory()->getConfig()->isSendingToQueue())
            ->save();
    }

    /**
     * @param \Generated\Shared\Transfer\PriceProductMerchantRelationshipStorageTransfer $priceProductMerchantRelationshipStorageTransfer
     *
     * @return void
     */
    public function createPriceProductAbstract(
        PriceProductMerchantRelationshipStorageTransfer $priceProductMerchantRelationshipStorageTransfer
    ): void {
        (new SpyPriceProductAbstractMerchantRelationshipStorage())
            ->setFkProductAbstract($priceProductMerchantRelationshipStorageTransfer->getIdProduct())
            ->setFkCompanyBusinessUnit($priceProductMerchantRelationshipStorageTransfer->getIdCompanyBusinessUnit())
            ->setPriceKey($priceProductMerchantRelationshipStorageTransfer->getPriceKey())
            ->setData($priceProductMerchantRelationshipStorageTransfer->getPrices())
            ->setIsSendingToQueue($this->getFactory()->getConfig()->isSendingToQueue())
            ->save();
    }

    /**
     * @param array<\Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductAbstractMerchantRelationshipStorage> $priceProductAbstractMerchantRelationshipStorageEntities
     *
     * @return void
     */
    public function deletePriceProductAbstractEntities(
        array $priceProductAbstractMerchantRelationshipStorageEntities
    ): void {
        $storageEntityIds = [];
        foreach ($priceProductAbstractMerchantRelationshipStorageEntities as $priceProductAbstractMerchantRelationshipStorageEntity) {
            $storageEntityIds[] = $priceProductAbstractMerchantRelationshipStorageEntity->getIdPriceProductAbstractMerchantRelationshipStorage();
        }

        /** @var \Propel\Runtime\Collection\ObjectCollection $priceProductAbstractMerchantRelationshipStorageCollection */
        $priceProductAbstractMerchantRelationshipStorageCollection = $this->getFactory()
            ->createPriceProductAbstractMerchantRelationshipStorageQuery()
            ->filterByIdPriceProductAbstractMerchantRelationshipStorage_In($storageEntityIds)
            ->find();
        $priceProductAbstractMerchantRelationshipStorageCollection->delete();
    }

    /**
     * @param \Generated\Shared\Transfer\PriceProductMerchantRelationshipStorageTransfer $priceProductMerchantRelationshipStorageTransfer
     * @param \Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductConcreteMerchantRelationshipStorage $priceProductConcreteMerchantRelationshipStorageEntity
     *
     * @return void
     */
    public function updatePriceProductConcrete(
        PriceProductMerchantRelationshipStorageTransfer $priceProductMerchantRelationshipStorageTransfer,
        SpyPriceProductConcreteMerchantRelationshipStorage $priceProductConcreteMerchantRelationshipStorageEntity
    ): void {
        $priceProductConcreteMerchantRelationshipStorageEntity
            ->setData($priceProductMerchantRelationshipStorageTransfer->getPrices())
            ->setIsSendingToQueue($this->getFactory()->getConfig()->isSendingToQueue())
            ->save();
    }

    /**
     * @param \Generated\Shared\Transfer\PriceProductMerchantRelationshipStorageTransfer $priceProductMerchantRelationshipStorageTransfer
     *
     * @return void
     */
    public function createPriceProductConcrete(
        PriceProductMerchantRelationshipStorageTransfer $priceProductMerchantRelationshipStorageTransfer
    ): void {
        (new SpyPriceProductConcreteMerchantRelationshipStorage())
            ->setFkProduct($priceProductMerchantRelationshipStorageTransfer->getIdProduct())
            ->setFkCompanyBusinessUnit($priceProductMerchantRelationshipStorageTransfer->getIdCompanyBusinessUnit())
            ->setPriceKey($priceProductMerchantRelationshipStorageTransfer->getPriceKey())
            ->setData($priceProductMerchantRelationshipStorageTransfer->getPrices())
            ->setIsSendingToQueue($this->getFactory()->getConfig()->isSendingToQueue())
            ->save();
    }

    /**
     * @param array<\Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductConcreteMerchantRelationshipStorage> $priceProductConcreteMerchantRelationshipStorageEntities
     *
     * @return void
     */
    public function deletePriceProductConcreteEntities(
        array $priceProductConcreteMerchantRelationshipStorageEntities
    ): void {
        $storageEntityIds = [];
        foreach ($priceProductConcreteMerchantRelationshipStorageEntities as $priceProductConcreteMerchantRelationshipStorageEntity) {
            $storageEntityIds[] = $priceProductConcreteMerchantRelationshipStorageEntity->getIdPriceProductConcreteMerchantRelationshipStorage();
        }

        /** @var \Propel\Runtime\Collection\ObjectCollection $priceProductConcreteMerchantRelationshipStorageCollection */
        $priceProductConcreteMerchantRelationshipStorageCollection = $this->getFactory()
            ->createPriceProductConcreteMerchantRelationshipStorageQuery()
            ->filterByIdPriceProductConcreteMerchantRelationshipStorage_In($storageEntityIds)
            ->find();
        $priceProductConcreteMerchantRelationshipStorageCollection->delete();
    }
}
