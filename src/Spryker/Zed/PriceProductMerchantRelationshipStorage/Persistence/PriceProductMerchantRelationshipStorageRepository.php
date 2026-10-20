<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PriceProductMerchantRelationshipStorage\Persistence;

use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Generated\Shared\Transfer\FilterTransfer;
use Generated\Shared\Transfer\PriceProductMerchantRelationshipStorageTransfer;
use Generated\Shared\Transfer\PriceProductMerchantRelationshipValueTransfer;
use Orm\Zed\Currency\Persistence\Map\SpyCurrencyTableMap;
use Orm\Zed\MerchantRelationship\Persistence\Map\SpyMerchantRelationshipTableMap;
use Orm\Zed\MerchantRelationship\Persistence\Map\SpyMerchantRelationshipToCompanyBusinessUnitTableMap;
use Orm\Zed\PriceProduct\Persistence\Map\SpyPriceProductStoreTableMap;
use Orm\Zed\PriceProduct\Persistence\Map\SpyPriceTypeTableMap;
use Orm\Zed\PriceProductMerchantRelationship\Persistence\Map\SpyPriceProductMerchantRelationshipTableMap;
use Orm\Zed\PriceProductMerchantRelationship\Persistence\SpyPriceProductMerchantRelationshipQuery;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\Map\SpyPriceProductAbstractMerchantRelationshipStorageTableMap;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\Map\SpyPriceProductConcreteMerchantRelationshipStorageTableMap;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductAbstractMerchantRelationshipStorageQuery;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductConcreteMerchantRelationshipStorageQuery;
use Orm\Zed\Store\Persistence\Map\SpyStoreTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Spryker\Zed\Kernel\Persistence\AbstractRepository;
use Spryker\Zed\PropelOrm\Business\Model\Formatter\PropelArraySetFormatter;

/**
 * @method \Spryker\Zed\PriceProductMerchantRelationshipStorage\Persistence\PriceProductMerchantRelationshipStoragePersistenceFactory getFactory()
 */
class PriceProductMerchantRelationshipStorageRepository extends AbstractRepository implements PriceProductMerchantRelationshipStorageRepositoryInterface
{
    /**
     * @param array<int> $companyBusinessUnitIds
     *
     * @return array<int>
     */
    public function findProductAbstractIdsForReconciliationByCompanyBusinessUnitIds(array $companyBusinessUnitIds): array
    {
        $merchantRelationshipProductAbstractIds = $this->queryPriceProductMerchantRelationship($companyBusinessUnitIds)
            ->filterByFkProductAbstract(null, Criteria::ISNOTNULL)
            ->select([SpyPriceProductMerchantRelationshipTableMap::COL_FK_PRODUCT_ABSTRACT])
            ->distinct()
            ->find()
            ->getData();

        $storedProductAbstractIds = $this->filterStoredPricesByCompanyBusinessUnitIds(
            $this->getFactory()->createPriceProductAbstractMerchantRelationshipStorageQuery(),
            $companyBusinessUnitIds,
        )
            ->select([SpyPriceProductAbstractMerchantRelationshipStorageTableMap::COL_FK_PRODUCT_ABSTRACT])
            ->distinct()
            ->find()
            ->getData();

        return array_values(array_unique(array_map('intval', array_merge($merchantRelationshipProductAbstractIds, $storedProductAbstractIds))));
    }

    /**
     * @param array<int> $companyBusinessUnitIds
     *
     * @return array<int>
     */
    public function findProductConcreteIdsForReconciliationByCompanyBusinessUnitIds(array $companyBusinessUnitIds): array
    {
        $merchantRelationshipProductIds = $this->queryPriceProductMerchantRelationship($companyBusinessUnitIds)
            ->filterByFkProduct(null, Criteria::ISNOTNULL)
            ->select([SpyPriceProductMerchantRelationshipTableMap::COL_FK_PRODUCT])
            ->distinct()
            ->find()
            ->getData();

        $storedProductIds = $this->filterStoredPricesByCompanyBusinessUnitIds(
            $this->getFactory()->createPriceProductConcreteMerchantRelationshipStorageQuery(),
            $companyBusinessUnitIds,
        )
            ->select([SpyPriceProductConcreteMerchantRelationshipStorageTableMap::COL_FK_PRODUCT])
            ->distinct()
            ->find()
            ->getData();

        return array_values(array_unique(array_map('intval', array_merge($merchantRelationshipProductIds, $storedProductIds))));
    }

    /**
     * @template TPriceProductMerchantRelationshipStorageQuery of \Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductAbstractMerchantRelationshipStorageQuery|\Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductConcreteMerchantRelationshipStorageQuery
     *
     * @param TPriceProductMerchantRelationshipStorageQuery $priceProductMerchantRelationshipStorageQuery
     * @param array<int> $companyBusinessUnitIds
     *
     * @return TPriceProductMerchantRelationshipStorageQuery
     */
    protected function filterStoredPricesByCompanyBusinessUnitIds(
        SpyPriceProductAbstractMerchantRelationshipStorageQuery|SpyPriceProductConcreteMerchantRelationshipStorageQuery $priceProductMerchantRelationshipStorageQuery,
        array $companyBusinessUnitIds
    ): SpyPriceProductAbstractMerchantRelationshipStorageQuery|SpyPriceProductConcreteMerchantRelationshipStorageQuery {
        if (!$this->getFactory()->getConfig()->isMerchantRelationPriceKeyEnabled()) {
            return $priceProductMerchantRelationshipStorageQuery->filterByFkCompanyBusinessUnit_In($companyBusinessUnitIds);
        }

        $merchantRelationshipIds = array_merge([], ...array_values($this->findMerchantRelationshipIdsGroupedByCompanyBusinessUnitIds($companyBusinessUnitIds)));

        return $priceProductMerchantRelationshipStorageQuery->filterByFkMerchantRelationship_In(array_unique($merchantRelationshipIds));
    }

    /**
     * @param array<int> $priceProductMerchantRelationshipIds
     *
     * @return array<\Generated\Shared\Transfer\PriceProductMerchantRelationshipStorageTransfer>
     */
    public function findMerchantRelationshipProductConcretePricesDataByIds(array $priceProductMerchantRelationshipIds): array
    {
        $priceProductMerchantRelationshipsQuery = $this->queryPriceProductMerchantRelationshipPrices()
            ->filterByIdPriceProductMerchantRelationship_In($priceProductMerchantRelationshipIds)
            ->filterByFkProduct(null, Criteria::ISNOTNULL);

        return $this->findPriceProductMerchantRelationshipStorageTransfers(
            $this->withPriceProductConcreteData($priceProductMerchantRelationshipsQuery),
        );
    }

    /**
     * @param array<int> $productIds
     *
     * @return array<\Generated\Shared\Transfer\PriceProductMerchantRelationshipStorageTransfer>
     */
    public function findMerchantRelationshipProductConcretePricesDataByProductIds(array $productIds): array
    {
        $priceProductMerchantRelationshipsQuery = $this->queryPriceProductMerchantRelationshipPrices()
            ->filterByFkProduct_In($productIds);

        return $this->findPriceProductMerchantRelationshipStorageTransfers(
            $this->withPriceProductConcreteData($priceProductMerchantRelationshipsQuery),
        );
    }

    /**
     * @param array<int> $priceProductMerchantRelationshipIds
     *
     * @return array<\Generated\Shared\Transfer\PriceProductMerchantRelationshipStorageTransfer>
     */
    public function findMerchantRelationshipProductAbstractPricesByIds(array $priceProductMerchantRelationshipIds): array
    {
        $priceProductMerchantRelationshipsQuery = $this->queryPriceProductMerchantRelationshipPrices()
            ->filterByIdPriceProductMerchantRelationship_In($priceProductMerchantRelationshipIds)
            ->filterByFkProductAbstract(null, Criteria::ISNOTNULL);

        return $this->findPriceProductMerchantRelationshipStorageTransfers(
            $this->withPriceProductAbstractData($priceProductMerchantRelationshipsQuery),
        );
    }

    /**
     * @param array<int> $productAbstractIds
     *
     * @return array<\Generated\Shared\Transfer\PriceProductMerchantRelationshipStorageTransfer>
     */
    public function findMerchantRelationshipProductAbstractPricesDataByProductAbstractIds(array $productAbstractIds): array
    {
        $priceProductMerchantRelationshipsQuery = $this->queryPriceProductMerchantRelationshipPrices()
            ->filterByFkProductAbstract_In($productAbstractIds);

        return $this->findPriceProductMerchantRelationshipStorageTransfers(
            $this->withPriceProductAbstractData($priceProductMerchantRelationshipsQuery),
        );
    }

    /**
     * @param array<string> $priceKeys
     *
     * @return array<\Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductAbstractMerchantRelationshipStorage>
     */
    public function findExistingPriceProductAbstractMerchantRelationshipEntitiesByPriceKeys(array $priceKeys): array
    {
        return $this->getFactory()
            ->createPriceProductAbstractMerchantRelationshipStorageQuery()
            ->filterByPriceKey_In($priceKeys)
            ->find()
            ->getData();
    }

    /**
     * @param array<int> $productAbstractIds
     *
     * @return array<\Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductAbstractMerchantRelationshipStorage>
     */
    public function findExistingPriceProductAbstractMerchantRelationshipEntitiesByProductAbstractIds(array $productAbstractIds): array
    {
        $priceProductAbstractMerchantRelationshipStorageQuery = $this->getFactory()
            ->createPriceProductAbstractMerchantRelationshipStorageQuery()
            ->filterByFkProductAbstract_In($productAbstractIds);

        if ($this->getFactory()->getConfig()->isMerchantRelationPriceKeyEnabled()) {
            $priceProductAbstractMerchantRelationshipStorageQuery->filterByFkMerchantRelationship(null, Criteria::ISNOTNULL);
        }

        return $priceProductAbstractMerchantRelationshipStorageQuery
            ->find()
            ->getData();
    }

    /**
     * @param array<string> $priceKeys
     *
     * @return array<\Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductConcreteMerchantRelationshipStorage>
     */
    public function findExistingPriceProductConcreteMerchantRelationshipEntitiesByPriceKeys(array $priceKeys): array
    {
        return $this->getFactory()
            ->createPriceProductConcreteMerchantRelationshipStorageQuery()
            ->filterByPriceKey_In($priceKeys)
            ->find()
            ->getData();
    }

    /**
     * @param array<int> $productIds
     *
     * @return array<\Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductConcreteMerchantRelationshipStorage>
     */
    public function findExistingPriceProductConcreteMerchantRelationshipEntitiesByProductIds(array $productIds): array
    {
        $priceProductConcreteMerchantRelationshipStorageQuery = $this->getFactory()
            ->createPriceProductConcreteMerchantRelationshipStorageQuery()
            ->filterByFkProduct_In($productIds);

        if ($this->getFactory()->getConfig()->isMerchantRelationPriceKeyEnabled()) {
            $priceProductConcreteMerchantRelationshipStorageQuery->filterByFkMerchantRelationship(null, Criteria::ISNOTNULL);
        }

        return $priceProductConcreteMerchantRelationshipStorageQuery
            ->find()
            ->getData();
    }

    /**
     * @return array<\Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductConcreteMerchantRelationshipStorage>
     */
    public function findAllPriceProductConcreteMerchantRelationshipStorageEntities(): array
    {
        return $this->getFactory()
            ->createPriceProductConcreteMerchantRelationshipStorageQuery()
            ->find()
            ->getArrayCopy();
    }

    /**
     * @param array<int> $priceProductConcreteMerchantRelationshipStorageEntityIds
     *
     * @return array<\Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductConcreteMerchantRelationshipStorage>
     */
    public function findPriceProductConcreteMerchantRelationshipStorageEntitiesByIds(array $priceProductConcreteMerchantRelationshipStorageEntityIds): array
    {
        return $this->getFactory()
            ->createPriceProductConcreteMerchantRelationshipStorageQuery()
            ->filterByIdPriceProductConcreteMerchantRelationshipStorage_In($priceProductConcreteMerchantRelationshipStorageEntityIds)
            ->find()
            ->getArrayCopy();
    }

    /**
     * @return array<\Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductAbstractMerchantRelationshipStorage>
     */
    public function findAllPriceProductAbstractMerchantRelationshipStorageEntities(): array
    {
        return $this->getFactory()
            ->createPriceProductAbstractMerchantRelationshipStorageQuery()
            ->find()
            ->getArrayCopy();
    }

    /**
     * @param array<int> $priceProductAbstractMerchantRelationshipStorageEntityIds
     *
     * @return array<\Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductAbstractMerchantRelationshipStorage>
     */
    public function findPriceProductAbstractMerchantRelationshipStorageEntitiesByIds(array $priceProductAbstractMerchantRelationshipStorageEntityIds): array
    {
        return $this->getFactory()
            ->createPriceProductAbstractMerchantRelationshipStorageQuery()
            ->filterByIdPriceProductAbstractMerchantRelationshipStorage_In($priceProductAbstractMerchantRelationshipStorageEntityIds)
            ->find()
            ->getArrayCopy();
    }

    /**
     * @param array<int> $filterByCompanyBusinessUnitIds
     *
     * @return \Orm\Zed\PriceProductMerchantRelationship\Persistence\SpyPriceProductMerchantRelationshipQuery
     */
    protected function queryPriceProductMerchantRelationship(array $filterByCompanyBusinessUnitIds = []): SpyPriceProductMerchantRelationshipQuery
    {
        $priceProductMerchantRelationshipQuery = $this->addPriceProductStoreJoins(
            $this->getFactory()->getPropelPriceProductMerchantRelationshipQuery(),
        );

        return $this->addMerchantRelationshipJoin($priceProductMerchantRelationshipQuery, $filterByCompanyBusinessUnitIds);
    }

    protected function queryPriceProductMerchantRelationshipPrices(): SpyPriceProductMerchantRelationshipQuery
    {
        if (!$this->getFactory()->getConfig()->isMerchantRelationPriceKeyEnabled()) {
            return $this->queryPriceProductMerchantRelationship();
        }

        $priceProductMerchantRelationshipQuery = $this->addPriceProductStoreJoins($this->getFactory()->getPropelPriceProductMerchantRelationshipQuery());

        $priceProductMerchantRelationshipQuery
            ->useMerchantRelationshipQuery()
                ->useSpyMerchantRelationshipToCompanyBusinessUnitExistsQuery()
                ->endUse()
            ->endUse();

        return $priceProductMerchantRelationshipQuery;
    }

    /**
     * @return array<\Generated\Shared\Transfer\PriceProductMerchantRelationshipStorageTransfer>
     */
    protected function findPriceProductMerchantRelationshipStorageTransfers(ModelCriteria $modelCriteria): array
    {
        $priceProductMerchantRelationships = $modelCriteria
            ->setFormatter(new PropelArraySetFormatter())
            ->find();

        return $this->getFactory()
            ->createCompanyBusinessUnitPriceProductMapper()
            ->mapPriceProductMerchantRelationshipArrayToTransfers($priceProductMerchantRelationships);
    }

    /**
     * @module Store
     * @module Currency
     * @module PriceProduct
     *
     * @param \Orm\Zed\PriceProductMerchantRelationship\Persistence\SpyPriceProductMerchantRelationshipQuery $priceProductMerchantRelationshipQuery
     *
     * @return \Orm\Zed\PriceProductMerchantRelationship\Persistence\SpyPriceProductMerchantRelationshipQuery
     */
    protected function addPriceProductStoreJoins(
        SpyPriceProductMerchantRelationshipQuery $priceProductMerchantRelationshipQuery
    ): SpyPriceProductMerchantRelationshipQuery {
        $priceProductMerchantRelationshipQuery
            ->usePriceProductStoreQuery()
                ->innerJoinStore()
                ->innerJoinCurrency()
                ->usePriceProductQuery()
                    ->innerJoinPriceType()
                ->endUse()
            ->endUse();

        return $priceProductMerchantRelationshipQuery;
    }

    /**
     * @module MerchantRelationship
     *
     * @param \Orm\Zed\PriceProductMerchantRelationship\Persistence\SpyPriceProductMerchantRelationshipQuery $priceProductMerchantRelationshipQuery
     * @param array<int> $companyBusinessUnitIds
     *
     * @return \Orm\Zed\PriceProductMerchantRelationship\Persistence\SpyPriceProductMerchantRelationshipQuery
     */
    protected function addMerchantRelationshipJoin(
        SpyPriceProductMerchantRelationshipQuery $priceProductMerchantRelationshipQuery,
        array $companyBusinessUnitIds = []
    ): SpyPriceProductMerchantRelationshipQuery {
        $merchantRelationshipToCompanyBusinessUnitQuery = $priceProductMerchantRelationshipQuery
            ->useMerchantRelationshipQuery()
                ->useSpyMerchantRelationshipToCompanyBusinessUnitQuery();

        if ($companyBusinessUnitIds) {
            $merchantRelationshipToCompanyBusinessUnitQuery
                ->filterByFkCompanyBusinessUnit_In($companyBusinessUnitIds);
        }

        $merchantRelationshipToCompanyBusinessUnitQuery
            ->endUse()
            ->endUse();

        return $priceProductMerchantRelationshipQuery;
    }

    protected function withPriceProductAbstractData(ModelCriteria $modelCriteria): ModelCriteria
    {
        return $this->withPriceProductData($modelCriteria)
            ->withColumn(SpyPriceProductMerchantRelationshipTableMap::COL_FK_PRODUCT_ABSTRACT, PriceProductMerchantRelationshipStorageTransfer::ID_PRODUCT)
            ->withColumn(SpyMerchantRelationshipTableMap::COL_FK_MERCHANT, PriceProductMerchantRelationshipValueTransfer::FK_MERCHANT);
    }

    protected function withPriceProductConcreteData(ModelCriteria $modelCriteria): ModelCriteria
    {
        return $this->withPriceProductData($modelCriteria)
            ->withColumn(SpyPriceProductMerchantRelationshipTableMap::COL_FK_PRODUCT, PriceProductMerchantRelationshipStorageTransfer::ID_PRODUCT)
            ->withColumn(SpyMerchantRelationshipTableMap::COL_FK_MERCHANT, PriceProductMerchantRelationshipValueTransfer::FK_MERCHANT);
    }

    /**
     * @module Store
     * @module Currency
     * @module PriceProduct
     * @module MerchantRelationship
     * @module PriceProductMerchantRelationship
     *
     * @param \Propel\Runtime\ActiveQuery\ModelCriteria $modelCriteria
     *
     * @return \Propel\Runtime\ActiveQuery\ModelCriteria
     */
    protected function withPriceProductData(ModelCriteria $modelCriteria): ModelCriteria
    {
        $modelCriteria = $modelCriteria
            ->withColumn(SpyStoreTableMap::COL_NAME, PriceProductMerchantRelationshipStorageTransfer::STORE_NAME)
            ->withColumn(SpyCurrencyTableMap::COL_CODE, PriceProductMerchantRelationshipValueTransfer::CURRENCY_CODE)
            ->withColumn(SpyPriceTypeTableMap::COL_NAME, PriceProductMerchantRelationshipValueTransfer::PRICE_TYPE)
            ->withColumn(SpyPriceProductStoreTableMap::COL_PRICE_DATA, PriceProductMerchantRelationshipValueTransfer::PRICE_DATA)
            ->withColumn(SpyPriceProductStoreTableMap::COL_GROSS_PRICE, PriceProductMerchantRelationshipValueTransfer::GROSS_PRICE)
            ->withColumn(SpyPriceProductStoreTableMap::COL_NET_PRICE, PriceProductMerchantRelationshipValueTransfer::NET_PRICE);

        if ($this->getFactory()->getConfig()->isMerchantRelationPriceKeyEnabled()) {
            return $modelCriteria
                ->withColumn(SpyPriceProductMerchantRelationshipTableMap::COL_FK_MERCHANT_RELATIONSHIP, PriceProductMerchantRelationshipValueTransfer::ID_MERCHANT_RELATIONSHIP);
        }

        return $modelCriteria
            ->withColumn(SpyMerchantRelationshipToCompanyBusinessUnitTableMap::COL_FK_COMPANY_BUSINESS_UNIT, PriceProductMerchantRelationshipStorageTransfer::ID_COMPANY_BUSINESS_UNIT)
            ->withColumn(SpyMerchantRelationshipToCompanyBusinessUnitTableMap::COL_FK_MERCHANT_RELATIONSHIP, PriceProductMerchantRelationshipValueTransfer::ID_MERCHANT_RELATIONSHIP);
    }

    /**
     * @param \Generated\Shared\Transfer\FilterTransfer $filterTransfer
     * @param array<int> $priceProductConcreteMerchantRelationshipStorageIds
     *
     * @return array<\Generated\Shared\Transfer\SpyPriceProductConcreteMerchantRelationshipStorageEntityTransfer>
     */
    public function findFilteredPriceProductConcreteMerchantRelationshipStorageEntities(
        FilterTransfer $filterTransfer,
        array $priceProductConcreteMerchantRelationshipStorageIds = []
    ): array {
        $query = $this->getFactory()->createPriceProductConcreteMerchantRelationshipStorageQuery();

        if ($priceProductConcreteMerchantRelationshipStorageIds) {
            $query->filterByIdPriceProductConcreteMerchantRelationshipStorage_In($priceProductConcreteMerchantRelationshipStorageIds);
        }

        return $this->buildQueryFromCriteria($query, $filterTransfer)->find();
    }

    /**
     * @param \Generated\Shared\Transfer\FilterTransfer $filterTransfer
     * @param array<int> $priceProductAbstractMerchantRelationshipStorageIds
     *
     * @return array<\Generated\Shared\Transfer\SpyPriceProductAbstractMerchantRelationshipStorageEntityTransfer>
     */
    public function findFilteredPriceProductAbstractMerchantRelationshipStorageEntities(
        FilterTransfer $filterTransfer,
        array $priceProductAbstractMerchantRelationshipStorageIds = []
    ): array {
        $query = $this->getFactory()->createPriceProductAbstractMerchantRelationshipStorageQuery();

        if ($priceProductAbstractMerchantRelationshipStorageIds) {
            $query->filterByIdPriceProductAbstractMerchantRelationshipStorage_In($priceProductAbstractMerchantRelationshipStorageIds);
        }

        return $this->buildQueryFromCriteria($query, $filterTransfer)->find();
    }

    /**
     * @param \Generated\Shared\Transfer\FilterTransfer $filterTransfer
     *
     * @return array<\Generated\Shared\Transfer\PriceProductMerchantRelationshipTransfer>
     */
    public function getFilteredPriceProductConcreteMerchantRelationships(FilterTransfer $filterTransfer): array
    {
        $priceProductMerchantRelationshipQuery = $this->queryPriceProductMerchantRelationship()
            ->filterByFkProduct(null, Criteria::ISNOTNULL)
            ->groupByIdPriceProductMerchantRelationship();

        $priceProductMerchantRelationshipEntityTransfers = $this->buildQueryFromCriteria($priceProductMerchantRelationshipQuery, $filterTransfer)
            ->setFormatter(ModelCriteria::FORMAT_OBJECT)
            ->find();

        return $this->getFactory()
            ->createPriceProductMerchantRelationshipMapper()
            ->mapEntitiesToPriceProductMerchantRelationshipTransferCollection(
                $priceProductMerchantRelationshipEntityTransfers->getData(),
            );
    }

    /**
     * @param \Generated\Shared\Transfer\FilterTransfer $filterTransfer
     *
     * @return array<\Generated\Shared\Transfer\PriceProductMerchantRelationshipTransfer>
     */
    public function getFilteredPriceProductAbstractMerchantRelationships(FilterTransfer $filterTransfer): array
    {
        $priceProductMerchantRelationshipQuery = $this->queryPriceProductMerchantRelationship()
            ->filterByFkProductAbstract(null, Criteria::ISNOTNULL)
            ->groupByIdPriceProductMerchantRelationship();

        $priceProductMerchantRelationshipEntityTransfers = $this->buildQueryFromCriteria($priceProductMerchantRelationshipQuery, $filterTransfer)
            ->setFormatter(ModelCriteria::FORMAT_OBJECT)
            ->find();

        return $this->getFactory()
            ->createPriceProductMerchantRelationshipMapper()
            ->mapEntitiesToPriceProductMerchantRelationshipTransferCollection(
                $priceProductMerchantRelationshipEntityTransfers->getData(),
            );
    }

    /**
     * @param array<int> $companyBusinessUnitIds
     *
     * @return array<int, array<int>>
     */
    public function findMerchantRelationshipIdsGroupedByCompanyBusinessUnitIds(array $companyBusinessUnitIds): array
    {
        $merchantRelationshipToCompanyBusinessUnitQuery = $this->getFactory()
            ->getPropelMerchantRelationshipToCompanyBusinessUnitQuery()
            ->filterByFkCompanyBusinessUnit_In($companyBusinessUnitIds)
            ->select([
                SpyMerchantRelationshipToCompanyBusinessUnitTableMap::COL_FK_COMPANY_BUSINESS_UNIT,
                SpyMerchantRelationshipToCompanyBusinessUnitTableMap::COL_FK_MERCHANT_RELATIONSHIP,
            ])
            ->setFormatter(new PropelArraySetFormatter());

        $groupedMerchantRelationshipIds = [];
        foreach ($merchantRelationshipToCompanyBusinessUnitQuery->find() as $merchantRelationshipToCompanyBusinessUnitRow) {
            $idCompanyBusinessUnit = $merchantRelationshipToCompanyBusinessUnitRow[SpyMerchantRelationshipToCompanyBusinessUnitTableMap::COL_FK_COMPANY_BUSINESS_UNIT];
            $groupedMerchantRelationshipIds[$idCompanyBusinessUnit][] = $merchantRelationshipToCompanyBusinessUnitRow[SpyMerchantRelationshipToCompanyBusinessUnitTableMap::COL_FK_MERCHANT_RELATIONSHIP];
        }

        return $groupedMerchantRelationshipIds;
    }

    /**
     * @module MerchantRelationship
     *
     * @param array<int> $merchantRelationshipIds
     *
     * @return array<int>
     */
    public function findMerchantRelationshipIdsWithCompanyBusinessUnit(array $merchantRelationshipIds): array
    {
        $merchantRelationshipIds = $this->getFactory()
            ->getPropelMerchantRelationshipToCompanyBusinessUnitQuery()
            ->filterByFkMerchantRelationship_In($merchantRelationshipIds)
            ->select([SpyMerchantRelationshipToCompanyBusinessUnitTableMap::COL_FK_MERCHANT_RELATIONSHIP])
            ->distinct()
            ->find()
            ->getData();

        return array_map('intval', $merchantRelationshipIds);
    }

    /**
     * @param array<int> $merchantRelationshipIds
     *
     * @return array<int>
     */
    public function findMerchantRelationshipIdsWithPriceProductAbstractStorage(array $merchantRelationshipIds): array
    {
        $merchantRelationshipIds = $this->getFactory()
            ->createPriceProductAbstractMerchantRelationshipStorageQuery()
            ->filterByFkMerchantRelationship_In($merchantRelationshipIds)
            ->select([SpyPriceProductAbstractMerchantRelationshipStorageTableMap::COL_FK_MERCHANT_RELATIONSHIP])
            ->distinct()
            ->find()
            ->getData();

        return array_map('intval', $merchantRelationshipIds);
    }

    /**
     * @param array<int> $merchantRelationshipIds
     *
     * @return array<int>
     */
    public function findMerchantRelationshipIdsWithPriceProductConcreteStorage(array $merchantRelationshipIds): array
    {
        $merchantRelationshipIds = $this->getFactory()
            ->createPriceProductConcreteMerchantRelationshipStorageQuery()
            ->filterByFkMerchantRelationship_In($merchantRelationshipIds)
            ->select([SpyPriceProductConcreteMerchantRelationshipStorageTableMap::COL_FK_MERCHANT_RELATIONSHIP])
            ->distinct()
            ->find()
            ->getData();

        return array_map('intval', $merchantRelationshipIds);
    }

    /**
     * @module PriceProductMerchantRelationship
     *
     * @param array<int> $merchantRelationshipIds
     *
     * @return array<int>
     */
    public function findProductAbstractIdsForReconciliationByMerchantRelationshipIds(array $merchantRelationshipIds): array
    {
        $merchantRelationshipProductAbstractIds = $this->getFactory()
            ->getPropelPriceProductMerchantRelationshipQuery()
            ->filterByFkMerchantRelationship_In($merchantRelationshipIds)
            ->filterByFkProductAbstract(null, Criteria::ISNOTNULL)
            ->select([SpyPriceProductMerchantRelationshipTableMap::COL_FK_PRODUCT_ABSTRACT])
            ->distinct()
            ->find()
            ->getData();

        $storedProductAbstractIds = $this->getFactory()
            ->createPriceProductAbstractMerchantRelationshipStorageQuery()
            ->filterByFkMerchantRelationship_In($merchantRelationshipIds)
            ->select([SpyPriceProductAbstractMerchantRelationshipStorageTableMap::COL_FK_PRODUCT_ABSTRACT])
            ->distinct()
            ->find()
            ->getData();

        return array_values(array_unique(array_map('intval', array_merge($merchantRelationshipProductAbstractIds, $storedProductAbstractIds))));
    }

    /**
     * @module PriceProductMerchantRelationship
     *
     * @param array<int> $merchantRelationshipIds
     *
     * @return array<int>
     */
    public function findProductConcreteIdsForReconciliationByMerchantRelationshipIds(array $merchantRelationshipIds): array
    {
        $merchantRelationshipProductIds = $this->getFactory()
            ->getPropelPriceProductMerchantRelationshipQuery()
            ->filterByFkMerchantRelationship_In($merchantRelationshipIds)
            ->filterByFkProduct(null, Criteria::ISNOTNULL)
            ->select([SpyPriceProductMerchantRelationshipTableMap::COL_FK_PRODUCT])
            ->distinct()
            ->find()
            ->getData();

        $storedProductIds = $this->getFactory()
            ->createPriceProductConcreteMerchantRelationshipStorageQuery()
            ->filterByFkMerchantRelationship_In($merchantRelationshipIds)
            ->select([SpyPriceProductConcreteMerchantRelationshipStorageTableMap::COL_FK_PRODUCT])
            ->distinct()
            ->find()
            ->getData();

        return array_values(array_unique(array_map('intval', array_merge($merchantRelationshipProductIds, $storedProductIds))));
    }

    /**
     * @module MerchantRelationship
     *
     * @return array<\Generated\Shared\Transfer\CompanyBusinessUnitTransfer>
     */
    public function getFilteredCompanyBusinessUnitsWithMerchantRelationship(FilterTransfer $filterTransfer): array
    {
        $companyBusinessUnitIds = $this->getFactory()
            ->getPropelMerchantRelationshipToCompanyBusinessUnitQuery()
            ->select([SpyMerchantRelationshipToCompanyBusinessUnitTableMap::COL_FK_COMPANY_BUSINESS_UNIT])
            ->distinct()
            ->orderByFkCompanyBusinessUnit()
            ->offset($filterTransfer->getOffsetOrFail())
            ->limit($filterTransfer->getLimitOrFail())
            ->find()
            ->getData();

        $companyBusinessUnitTransfers = [];
        foreach ($companyBusinessUnitIds as $idCompanyBusinessUnit) {
            $companyBusinessUnitTransfers[] = (new CompanyBusinessUnitTransfer())->setIdCompanyBusinessUnit((int)$idCompanyBusinessUnit);
        }

        return $companyBusinessUnitTransfers;
    }

    /**
     * @param array<int> $companyBusinessUnitIds
     *
     * @return array<\Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyMerchantRelationBusinessUnitStorage>
     */
    public function findExistingMerchantRelationBusinessUnitStorageEntitiesByCompanyBusinessUnitIds(array $companyBusinessUnitIds): array
    {
        return $this->getFactory()
            ->createMerchantRelationBusinessUnitStorageQuery()
            ->filterByFkCompanyBusinessUnit_In($companyBusinessUnitIds)
            ->find()
            ->getData();
    }

    /**
     * @param array<int> $merchantRelationBusinessUnitStorageIds
     *
     * @return array<\Generated\Shared\Transfer\SpyMerchantRelationBusinessUnitStorageEntityTransfer>
     */
    public function findFilteredMerchantRelationBusinessUnitStorageEntities(
        FilterTransfer $filterTransfer,
        array $merchantRelationBusinessUnitStorageIds = []
    ): array {
        $query = $this->getFactory()->createMerchantRelationBusinessUnitStorageQuery();

        if ($merchantRelationBusinessUnitStorageIds) {
            $query->filterByIdMerchantRelationBusinessUnitStorage_In($merchantRelationBusinessUnitStorageIds);
        }

        return $this->buildQueryFromCriteria($query, $filterTransfer)->find();
    }
}
