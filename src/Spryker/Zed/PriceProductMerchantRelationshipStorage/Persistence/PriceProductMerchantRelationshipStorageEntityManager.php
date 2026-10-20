<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PriceProductMerchantRelationshipStorage\Persistence;

use Generated\Shared\Transfer\PriceProductMerchantRelationshipStorageTransfer;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyMerchantRelationBusinessUnitStorage;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductAbstractMerchantRelationshipStorage;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductConcreteMerchantRelationshipStorage;
use Spryker\Shared\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageConfig as SharedPriceProductMerchantRelationshipStorageConfig;
use Spryker\Shared\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageConstants;
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

        $this->setPriceProductMerchantRelationshipStorageKey($priceProductAbstractMerchantRelationshipStorageEntity, $priceProductMerchantRelationshipStorageTransfer);

        $this->persist($priceProductAbstractMerchantRelationshipStorageEntity);
    }

    public function createPriceProductAbstract(
        PriceProductMerchantRelationshipStorageTransfer $priceProductMerchantRelationshipStorageTransfer
    ): void {
        $entity = (new SpyPriceProductAbstractMerchantRelationshipStorage())
            ->setFkProductAbstract($priceProductMerchantRelationshipStorageTransfer->getIdProductOrFail())
            ->setPriceKey($priceProductMerchantRelationshipStorageTransfer->getPriceKeyOrFail())
            ->setData($priceProductMerchantRelationshipStorageTransfer->getPrices())
            ->setIsSendingToQueue($this->getFactory()->getConfig()->isSendingToQueue());

        $this->setPriceProductMerchantRelationshipStorageKey($entity, $priceProductMerchantRelationshipStorageTransfer);

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

        $this->setPriceProductMerchantRelationshipStorageKey($priceProductConcreteMerchantRelationshipStorageEntity, $priceProductMerchantRelationshipStorageTransfer);

        $this->persist($priceProductConcreteMerchantRelationshipStorageEntity);
    }

    public function createPriceProductConcrete(
        PriceProductMerchantRelationshipStorageTransfer $priceProductMerchantRelationshipStorageTransfer
    ): void {
        $entity = (new SpyPriceProductConcreteMerchantRelationshipStorage())
            ->setFkProduct($priceProductMerchantRelationshipStorageTransfer->getIdProductOrFail())
            ->setPriceKey($priceProductMerchantRelationshipStorageTransfer->getPriceKeyOrFail())
            ->setData($priceProductMerchantRelationshipStorageTransfer->getPrices())
            ->setIsSendingToQueue($this->getFactory()->getConfig()->isSendingToQueue());

        $this->setPriceProductMerchantRelationshipStorageKey($entity, $priceProductMerchantRelationshipStorageTransfer);

        $this->persist($entity);
    }

    protected function setPriceProductMerchantRelationshipStorageKey(
        SpyPriceProductAbstractMerchantRelationshipStorage|SpyPriceProductConcreteMerchantRelationshipStorage $entity,
        PriceProductMerchantRelationshipStorageTransfer $priceProductMerchantRelationshipStorageTransfer
    ): void {
        if (!$this->getFactory()->getConfig()->isMerchantRelationPriceKeyEnabled()) {
            $entity->setFkCompanyBusinessUnit($priceProductMerchantRelationshipStorageTransfer->getIdCompanyBusinessUnitOrFail());

            return;
        }

        $entity
            ->setFkMerchantRelationship($priceProductMerchantRelationshipStorageTransfer->getIdMerchantRelationship())
            ->setFkCompanyBusinessUnit(SharedPriceProductMerchantRelationshipStorageConfig::ID_COMPANY_BUSINESS_UNIT_NONE);
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

    /**
     * @param array<int> $merchantRelationshipIds
     */
    public function saveMerchantRelationBusinessUnitStorage(
        int $idCompanyBusinessUnit,
        array $merchantRelationshipIds,
        ?SpyMerchantRelationBusinessUnitStorage $merchantRelationBusinessUnitStorageEntity
    ): void {
        if (!$merchantRelationshipIds) {
            $this->removeMerchantRelationBusinessUnitStorage($merchantRelationBusinessUnitStorageEntity);

            return;
        }

        $merchantRelationBusinessUnitStorageEntity = ($merchantRelationBusinessUnitStorageEntity ?? new SpyMerchantRelationBusinessUnitStorage())
            ->setFkCompanyBusinessUnit($idCompanyBusinessUnit)
            ->setData([PriceProductMerchantRelationshipStorageConstants::MERCHANT_RELATION_BUSINESS_UNIT_DATA_KEY_MERCHANT_RELATIONSHIP_IDS => $merchantRelationshipIds])
            ->setIsSendingToQueue($this->getFactory()->getConfig()->isSendingToQueue());

        $this->persist($merchantRelationBusinessUnitStorageEntity);
    }

    protected function removeMerchantRelationBusinessUnitStorage(
        ?SpyMerchantRelationBusinessUnitStorage $merchantRelationBusinessUnitStorageEntity
    ): void {
        if ($merchantRelationBusinessUnitStorageEntity === null) {
            return;
        }

        $merchantRelationBusinessUnitStorageEntity->setIsSendingToQueue($this->getFactory()->getConfig()->isSendingToQueue());

        $this->remove($merchantRelationBusinessUnitStorageEntity);
    }
}
