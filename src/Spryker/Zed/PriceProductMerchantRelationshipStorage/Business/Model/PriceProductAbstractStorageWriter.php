<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PriceProductMerchantRelationshipStorage\Business\Model;

use Generated\Shared\Transfer\PriceProductMerchantRelationshipStorageTransfer;

class PriceProductAbstractStorageWriter extends AbstractPriceProductMerchantRelationshipStorageWriter implements PriceProductAbstractStorageWriterInterface
{
    /**
     * @param array<int> $companyBusinessUnitIds
     *
     * @return void
     */
    public function publishByCompanyBusinessUnitIds(array $companyBusinessUnitIds): void
    {
        $companyBusinessUnitChunkSize = $this->priceProductMerchantRelationshipStorageConfig->getCompanyBusinessUnitChunkSize();
        $productChunkSize = $this->priceProductMerchantRelationshipStorageConfig->getProductChunkSize();

        foreach (array_chunk($companyBusinessUnitIds, $companyBusinessUnitChunkSize) as $companyBusinessUnitIdsChunk) {
            $this->merchantRelationBusinessUnitStorageWriter->publishByCompanyBusinessUnitIds($companyBusinessUnitIdsChunk);

            $productAbstractIds = $this->priceProductMerchantRelationshipStorageRepository
                ->findProductAbstractIdsForReconciliationByCompanyBusinessUnitIds($companyBusinessUnitIdsChunk);

            foreach (array_chunk($productAbstractIds, $productChunkSize) as $productAbstractIdsChunk) {
                $this->publishAbstractPriceProductByProductAbstractIds($productAbstractIdsChunk);
            }
        }
    }

    /**
     * @param array<int> $priceProductMerchantRelationshipIds
     *
     * @return void
     */
    public function publishAbstractPriceProductMerchantRelationship(array $priceProductMerchantRelationshipIds): void
    {
        $priceProductMerchantRelationshipStorageTransfers = $this->priceProductMerchantRelationshipStorageRepository
            ->findMerchantRelationshipProductAbstractPricesByIds($priceProductMerchantRelationshipIds);

        $priceKeys = array_map(function (PriceProductMerchantRelationshipStorageTransfer $priceProductMerchantRelationshipStorageTransfer) {
            return $priceProductMerchantRelationshipStorageTransfer->getPriceKeyOrFail();
        }, $priceProductMerchantRelationshipStorageTransfers);

        $existingStorageEntities = $this->priceProductMerchantRelationshipStorageRepository
            ->findExistingPriceProductAbstractMerchantRelationshipEntitiesByPriceKeys($priceKeys);

        $this->write($priceProductMerchantRelationshipStorageTransfers, $existingStorageEntities, true);
    }

    /**
     * @param array<int> $productAbstractIds
     *
     * @return void
     */
    public function publishAbstractPriceProductByProductAbstractIds(array $productAbstractIds): void
    {
        $priceProductMerchantRelationshipStorageTransfers = $this->priceProductMerchantRelationshipStorageRepository
            ->findMerchantRelationshipProductAbstractPricesDataByProductAbstractIds($productAbstractIds);

        $existingStorageEntities = $this->priceProductMerchantRelationshipStorageRepository
            ->findExistingPriceProductAbstractMerchantRelationshipEntitiesByProductAbstractIds($productAbstractIds);

        $this->write($priceProductMerchantRelationshipStorageTransfers, $existingStorageEntities);
    }

    /**
     * @deprecated Will be removed without replacement.
     *
     * @param array<mixed> $businessUnitProducts
     *
     * @return void
     */
    public function publishByBusinessUnitProducts(array $businessUnitProducts): void
    {
        $this->publishByCompanyBusinessUnitIds(array_keys($businessUnitProducts));
    }

    /**
     * @param array<\Generated\Shared\Transfer\PriceProductMerchantRelationshipStorageTransfer> $priceProductMerchantRelationshipStorageTransfers
     * @param array<\Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductAbstractMerchantRelationshipStorage> $existingStorageEntities
     * @param bool $mergePrices
     *
     * @return void
     */
    protected function write(
        array $priceProductMerchantRelationshipStorageTransfers,
        array $existingStorageEntities = [],
        bool $mergePrices = false
    ): void {
        $existingStorageEntities = $this->mapStorageEntitiesByPriceKey($existingStorageEntities);
        $priceProductMerchantRelationshipStorageTransfers = $this->executePriceProductMerchantRelationshipStorageFilterPlugins($priceProductMerchantRelationshipStorageTransfers);

        foreach ($priceProductMerchantRelationshipStorageTransfers as $priceProductMerchantRelationshipStorageTransfer) {
            $priceProductMerchantRelationshipStorageTransfer = $this->groupPrices(
                $priceProductMerchantRelationshipStorageTransfer,
                $existingStorageEntities,
                $mergePrices,
            );

            if (!$priceProductMerchantRelationshipStorageTransfer->getPrices()) {
                continue;
            }

            $priceKey = $priceProductMerchantRelationshipStorageTransfer->getPriceKey();

            if (isset($existingStorageEntities[$priceKey])) {
                $duplicateStorageEntities = $existingStorageEntities[$priceKey];
                $survivorStorageEntity = array_shift($duplicateStorageEntities);

                if ($survivorStorageEntity === null) {
                    unset($existingStorageEntities[$priceKey]);

                    continue;
                }

                $this->priceProductMerchantRelationshipStorageEntityManager->updatePriceProductAbstract(
                    $priceProductMerchantRelationshipStorageTransfer,
                    $survivorStorageEntity,
                );

                $this->priceProductMerchantRelationshipStorageEntityManager
                    ->deletePriceProductAbstractEntities($duplicateStorageEntities);

                unset($existingStorageEntities[$priceKey]);

                continue;
            }

            $this->priceProductMerchantRelationshipStorageEntityManager->createPriceProductAbstract(
                $priceProductMerchantRelationshipStorageTransfer,
            );

            unset($existingStorageEntities[$priceKey]);
        }

        $this->priceProductMerchantRelationshipStorageEntityManager
            ->deletePriceProductAbstractEntities($this->flattenStorageEntitiesGroupedByPriceKey($existingStorageEntities));

        $this->priceProductMerchantRelationshipStorageEntityManager->commit();
    }

    /**
     * @param array<\Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductAbstractMerchantRelationshipStorage> $priceProductMerchantRelationshipStorageEntities
     *
     * @return array<string, array<\Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductAbstractMerchantRelationshipStorage>>
     */
    protected function mapStorageEntitiesByPriceKey(array $priceProductMerchantRelationshipStorageEntities): array
    {
        $mappedPriceProductAbstractMerchantRelationshipStorageEntities = [];
        foreach ($priceProductMerchantRelationshipStorageEntities as $priceProductMerchantRelationshipStorageEntity) {
            $mappedPriceProductAbstractMerchantRelationshipStorageEntities[$priceProductMerchantRelationshipStorageEntity->getPriceKey()][] = $priceProductMerchantRelationshipStorageEntity;
        }

        return $mappedPriceProductAbstractMerchantRelationshipStorageEntities;
    }

    /**
     * @param \Generated\Shared\Transfer\PriceProductMerchantRelationshipStorageTransfer $priceProductMerchantRelationshipStorageTransfer
     * @param array<string, array<\Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductAbstractMerchantRelationshipStorage>> $existingStorageEntities
     * @param bool $mergePrices
     *
     * @return \Generated\Shared\Transfer\PriceProductMerchantRelationshipStorageTransfer
     */
    protected function groupPrices(
        PriceProductMerchantRelationshipStorageTransfer $priceProductMerchantRelationshipStorageTransfer,
        array $existingStorageEntities = [],
        bool $mergePrices = false
    ): PriceProductMerchantRelationshipStorageTransfer {
        $priceProductMerchantRelationshipStorageTransfer = $this->priceGrouper->groupPricesData($priceProductMerchantRelationshipStorageTransfer);

        if (!$mergePrices) {
            return $priceProductMerchantRelationshipStorageTransfer;
        }

        return $this->priceGrouper->groupPricesData(
            $priceProductMerchantRelationshipStorageTransfer,
            $this->getExistingPricesDataForPriceKey($existingStorageEntities, $priceProductMerchantRelationshipStorageTransfer->getPriceKeyOrFail()),
        );
    }

    /**
     * @param array<string, array<\Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductAbstractMerchantRelationshipStorage>> $existingStorageEntities
     * @param string $priceKey
     *
     * @return array<mixed>
     */
    protected function getExistingPricesDataForPriceKey(array $existingStorageEntities, string $priceKey): array
    {
        if (!isset($existingStorageEntities[$priceKey])) {
            return [];
        }

        $storageEntity = reset($existingStorageEntities[$priceKey]);

        if ($storageEntity === false) {
            return [];
        }

        return $storageEntity->getData();
    }
}
