<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PriceProductMerchantRelationshipStorage\Business\Model;

use Spryker\Zed\PriceProductMerchantRelationshipStorage\Persistence\PriceProductMerchantRelationshipStorageEntityManagerInterface;
use Spryker\Zed\PriceProductMerchantRelationshipStorage\Persistence\PriceProductMerchantRelationshipStorageRepositoryInterface;
use Spryker\Zed\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageConfig;

class MerchantRelationBusinessUnitStorageWriter implements MerchantRelationBusinessUnitStorageWriterInterface
{
    public function __construct(
        protected PriceProductMerchantRelationshipStorageRepositoryInterface $repository,
        protected PriceProductMerchantRelationshipStorageEntityManagerInterface $entityManager,
        protected PriceProductMerchantRelationshipStorageConfig $config
    ) {
    }

    /**
     * @param array<int> $companyBusinessUnitIds
     */
    public function publishByCompanyBusinessUnitIds(array $companyBusinessUnitIds): void
    {
        if (!$this->config->isMerchantRelationPriceKeyEnabled()) {
            return;
        }

        $companyBusinessUnitIds = array_unique($companyBusinessUnitIds);
        $groupedMerchantRelationshipIds = $this->repository->findMerchantRelationshipIdsGroupedByCompanyBusinessUnitIds($companyBusinessUnitIds);
        $existingEntities = $this->mapEntitiesByCompanyBusinessUnitId(
            $this->repository->findExistingMerchantRelationBusinessUnitStorageEntitiesByCompanyBusinessUnitIds($companyBusinessUnitIds),
        );

        foreach ($companyBusinessUnitIds as $idCompanyBusinessUnit) {
            $this->entityManager->saveMerchantRelationBusinessUnitStorage(
                $idCompanyBusinessUnit,
                $groupedMerchantRelationshipIds[$idCompanyBusinessUnit] ?? [],
                $existingEntities[$idCompanyBusinessUnit] ?? null,
            );
        }

        $this->entityManager->commit();
    }

    /**
     * @param array<\Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyMerchantRelationBusinessUnitStorage> $merchantRelationBusinessUnitStorageEntities
     *
     * @return array<int, \Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyMerchantRelationBusinessUnitStorage>
     */
    protected function mapEntitiesByCompanyBusinessUnitId(array $merchantRelationBusinessUnitStorageEntities): array
    {
        $mappedEntities = [];
        foreach ($merchantRelationBusinessUnitStorageEntities as $merchantRelationBusinessUnitStorageEntity) {
            $mappedEntities[$merchantRelationBusinessUnitStorageEntity->getFkCompanyBusinessUnit()] = $merchantRelationBusinessUnitStorageEntity;
        }

        return $mappedEntities;
    }
}
