<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PriceProductMerchantRelationshipStorage\Business\Writer;

use Spryker\Zed\PriceProductMerchantRelationshipStorage\Business\Model\MerchantRelationBusinessUnitStorageWriterInterface;
use Spryker\Zed\PriceProductMerchantRelationshipStorage\Business\Model\PriceProductAbstractStorageWriterInterface;
use Spryker\Zed\PriceProductMerchantRelationshipStorage\Business\Model\PriceProductConcreteStorageWriterInterface;
use Spryker\Zed\PriceProductMerchantRelationshipStorage\Dependency\Facade\PriceProductMerchantRelationshipStorageToEventBehaviorFacadeInterface;
use Spryker\Zed\PriceProductMerchantRelationshipStorage\Persistence\PriceProductMerchantRelationshipStorageRepositoryInterface;
use Spryker\Zed\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageConfig;

class MerchantRelationshipToCompanyBusinessUnitStorageWriter implements MerchantRelationshipToCompanyBusinessUnitStorageWriterInterface
{
    protected const string COL_FK_MERCHANT_RELATIONSHIP = 'spy_merchant_relationship_to_company_business_unit.fk_merchant_relationship';

    public function __construct(
        protected PriceProductMerchantRelationshipStorageToEventBehaviorFacadeInterface $eventBehaviorFacade,
        protected PriceProductAbstractStorageWriterInterface $priceProductAbstractStorageWriter,
        protected PriceProductConcreteStorageWriterInterface $priceProductConcreteStorageWriter,
        protected MerchantRelationBusinessUnitStorageWriterInterface $merchantRelationBusinessUnitStorageWriter,
        protected PriceProductMerchantRelationshipStorageRepositoryInterface $priceProductMerchantRelationshipStorageRepository,
        protected PriceProductMerchantRelationshipStorageConfig $priceProductMerchantRelationshipStorageConfig
    ) {
    }

    /**
     * @param array<\Generated\Shared\Transfer\EventEntityTransfer> $eventEntityTransfers
     *
     * @return void
     */
    public function writeCollectionByMerchantRelationshipToCompanyBusinessUnitEvents(array $eventEntityTransfers): void
    {
        $companyBusinessUnitIds = $this->eventBehaviorFacade->getEventTransferForeignKeys(
            $eventEntityTransfers,
            PriceProductMerchantRelationshipStorageConfig::COL_FK_COMPANY_BUSINESS_UNIT,
        );

        if (!$this->priceProductMerchantRelationshipStorageConfig->isMerchantRelationPriceKeyEnabled()) {
            $this->priceProductAbstractStorageWriter->publishByCompanyBusinessUnitIds($companyBusinessUnitIds);
            $this->priceProductConcreteStorageWriter->publishByCompanyBusinessUnitIds($companyBusinessUnitIds);

            return;
        }

        $companyBusinessUnitChunkSize = $this->priceProductMerchantRelationshipStorageConfig->getCompanyBusinessUnitChunkSize();

        foreach (array_chunk($companyBusinessUnitIds, $companyBusinessUnitChunkSize) as $companyBusinessUnitIdsChunk) {
            $this->merchantRelationBusinessUnitStorageWriter->publishByCompanyBusinessUnitIds($companyBusinessUnitIdsChunk);
        }

        $merchantRelationshipIds = array_values($this->eventBehaviorFacade->getEventTransferForeignKeys(
            $eventEntityTransfers,
            static::COL_FK_MERCHANT_RELATIONSHIP,
        ));

        if (!$merchantRelationshipIds) {
            return;
        }

        $linkedMerchantRelationshipIds = $this->priceProductMerchantRelationshipStorageRepository
            ->findMerchantRelationshipIdsWithCompanyBusinessUnit($merchantRelationshipIds);

        $this->publishAbstractPricesOfChangedMerchantRelationships($merchantRelationshipIds, $linkedMerchantRelationshipIds);
        $this->publishConcretePricesOfChangedMerchantRelationships($merchantRelationshipIds, $linkedMerchantRelationshipIds);
    }

    /**
     * @param array<int> $merchantRelationshipIds
     * @param array<int> $linkedMerchantRelationshipIds
     *
     * @return void
     */
    protected function publishAbstractPricesOfChangedMerchantRelationships(array $merchantRelationshipIds, array $linkedMerchantRelationshipIds): void
    {
        $changedMerchantRelationshipIds = $this->getChangedMerchantRelationshipIds(
            $linkedMerchantRelationshipIds,
            $this->priceProductMerchantRelationshipStorageRepository->findMerchantRelationshipIdsWithPriceProductAbstractStorage($merchantRelationshipIds),
        );

        if (!$changedMerchantRelationshipIds) {
            return;
        }

        $productAbstractIds = $this->priceProductMerchantRelationshipStorageRepository
            ->findProductAbstractIdsForReconciliationByMerchantRelationshipIds($changedMerchantRelationshipIds);

        foreach (array_chunk($productAbstractIds, $this->priceProductMerchantRelationshipStorageConfig->getProductChunkSize()) as $productAbstractIdsChunk) {
            $this->priceProductAbstractStorageWriter->publishAbstractPriceProductByProductAbstractIds($productAbstractIdsChunk);
        }
    }

    /**
     * @param array<int> $merchantRelationshipIds
     * @param array<int> $linkedMerchantRelationshipIds
     *
     * @return void
     */
    protected function publishConcretePricesOfChangedMerchantRelationships(array $merchantRelationshipIds, array $linkedMerchantRelationshipIds): void
    {
        $changedMerchantRelationshipIds = $this->getChangedMerchantRelationshipIds(
            $linkedMerchantRelationshipIds,
            $this->priceProductMerchantRelationshipStorageRepository->findMerchantRelationshipIdsWithPriceProductConcreteStorage($merchantRelationshipIds),
        );

        if (!$changedMerchantRelationshipIds) {
            return;
        }

        $productIds = $this->priceProductMerchantRelationshipStorageRepository
            ->findProductConcreteIdsForReconciliationByMerchantRelationshipIds($changedMerchantRelationshipIds);

        foreach (array_chunk($productIds, $this->priceProductMerchantRelationshipStorageConfig->getProductChunkSize()) as $productIdsChunk) {
            $this->priceProductConcreteStorageWriter->publishConcretePriceProductByProductIds($productIdsChunk);
        }
    }

    /**
     * @param array<int> $linkedMerchantRelationshipIds
     * @param array<int> $storedMerchantRelationshipIds
     *
     * @return array<int>
     */
    protected function getChangedMerchantRelationshipIds(array $linkedMerchantRelationshipIds, array $storedMerchantRelationshipIds): array
    {
        return array_values(array_merge(
            array_diff($linkedMerchantRelationshipIds, $storedMerchantRelationshipIds),
            array_diff($storedMerchantRelationshipIds, $linkedMerchantRelationshipIds),
        ));
    }
}
