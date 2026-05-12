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
use Spryker\Zed\Propel\Persistence\BatchProcessor\ActiveRecordBatchProcessorTrait;

/**
 * @method \Spryker\Zed\PriceProductMerchantRelationshipStorage\Persistence\PriceProductMerchantRelationshipStoragePersistenceFactory getFactory()
 */
class PriceProductMerchantRelationshipStorageEntityManager extends AbstractEntityManager implements PriceProductMerchantRelationshipStorageEntityManagerInterface
{
    use ActiveRecordBatchProcessorTrait;

    public function updatePriceProductAbstract(
        PriceProductMerchantRelationshipStorageTransfer $priceProductMerchantRelationshipStorageTransfer,
        SpyPriceProductAbstractMerchantRelationshipStorage $priceProductAbstractMerchantRelationshipStorageEntity
    ): void {
        $priceProductAbstractMerchantRelationshipStorageEntity
            ->setData($priceProductMerchantRelationshipStorageTransfer->getPrices())
            ->setIsSendingToQueue($this->getFactory()->getConfig()->isSendingToQueue());

        $this->persist($priceProductAbstractMerchantRelationshipStorageEntity);
    }

    public function createPriceProductAbstract(
        PriceProductMerchantRelationshipStorageTransfer $priceProductMerchantRelationshipStorageTransfer
    ): void {
        $entity = (new SpyPriceProductAbstractMerchantRelationshipStorage())
            ->setFkProductAbstract($priceProductMerchantRelationshipStorageTransfer->getIdProduct())
            ->setFkCompanyBusinessUnit($priceProductMerchantRelationshipStorageTransfer->getIdCompanyBusinessUnit())
            ->setPriceKey($priceProductMerchantRelationshipStorageTransfer->getPriceKey())
            ->setData($priceProductMerchantRelationshipStorageTransfer->getPrices())
            ->setIsSendingToQueue($this->getFactory()->getConfig()->isSendingToQueue());

        $this->persist($entity);
    }

    /**
     * @param array<\Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductAbstractMerchantRelationshipStorage> $priceProductAbstractMerchantRelationshipStorageEntities
     *
     * @return void
     */
    public function deletePriceProductAbstractEntities(
        array $priceProductAbstractMerchantRelationshipStorageEntities
    ): void {
        foreach ($priceProductAbstractMerchantRelationshipStorageEntities as $entity) {
            $this->remove($entity);
        }
    }

    public function updatePriceProductConcrete(
        PriceProductMerchantRelationshipStorageTransfer $priceProductMerchantRelationshipStorageTransfer,
        SpyPriceProductConcreteMerchantRelationshipStorage $priceProductConcreteMerchantRelationshipStorageEntity
    ): void {
        $priceProductConcreteMerchantRelationshipStorageEntity
            ->setData($priceProductMerchantRelationshipStorageTransfer->getPrices())
            ->setIsSendingToQueue($this->getFactory()->getConfig()->isSendingToQueue());

        $this->persist($priceProductConcreteMerchantRelationshipStorageEntity);
    }

    public function createPriceProductConcrete(
        PriceProductMerchantRelationshipStorageTransfer $priceProductMerchantRelationshipStorageTransfer
    ): void {
        $entity = (new SpyPriceProductConcreteMerchantRelationshipStorage())
            ->setFkProduct($priceProductMerchantRelationshipStorageTransfer->getIdProduct())
            ->setFkCompanyBusinessUnit($priceProductMerchantRelationshipStorageTransfer->getIdCompanyBusinessUnit())
            ->setPriceKey($priceProductMerchantRelationshipStorageTransfer->getPriceKey())
            ->setData($priceProductMerchantRelationshipStorageTransfer->getPrices())
            ->setIsSendingToQueue($this->getFactory()->getConfig()->isSendingToQueue());

        $this->persist($entity);
    }

    /**
     * @param array<\Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductConcreteMerchantRelationshipStorage> $priceProductConcreteMerchantRelationshipStorageEntities
     *
     * @return void
     */
    public function deletePriceProductConcreteEntities(
        array $priceProductConcreteMerchantRelationshipStorageEntities
    ): void {
        foreach ($priceProductConcreteMerchantRelationshipStorageEntities as $entity) {
            $this->remove($entity);
        }
    }
}
