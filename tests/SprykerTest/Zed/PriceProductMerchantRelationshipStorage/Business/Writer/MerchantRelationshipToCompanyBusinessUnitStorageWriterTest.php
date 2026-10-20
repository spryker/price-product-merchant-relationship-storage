<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\PriceProductMerchantRelationshipStorage\Business\Writer;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\EventEntityTransfer;
use Orm\Zed\MerchantRelationship\Persistence\SpyMerchantRelationshipQuery;
use Orm\Zed\MerchantRelationship\Persistence\SpyMerchantRelationshipToCompanyBusinessUnitQuery;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyMerchantRelationBusinessUnitStorageQuery;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductAbstractMerchantRelationshipStorageQuery;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductConcreteMerchantRelationshipStorageQuery;
use Spryker\Zed\PriceProductMerchantRelationshipStorage\Business\Writer\MerchantRelationshipToCompanyBusinessUnitStorageWriterInterface;
use Spryker\Zed\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageConfig;
use SprykerTest\Zed\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group PriceProductMerchantRelationshipStorage
 * @group Business
 * @group Writer
 * @group MerchantRelationshipToCompanyBusinessUnitStorageWriterTest
 * Add your own group annotations below this line
 */
class MerchantRelationshipToCompanyBusinessUnitStorageWriterTest extends Unit
{
    protected const string COL_FK_MERCHANT_RELATIONSHIP = 'spy_merchant_relationship_to_company_business_unit.fk_merchant_relationship';

    protected const array STORAGE_DATA_MARKER = ['marker' => 'not-republished'];

    protected PriceProductMerchantRelationshipStorageBusinessTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tester->ensurePriceProductMerchantRelationshipTableIsEmpty();
    }

    public function testWriteCollectionPublishesAbstractPricesWhenMerchantRelationshipGetsFirstBusinessUnitAndFlagEnabled(): void
    {
        // Arrange
        $idMerchantRelationship = $this->tester->haveProductAbstractPriceProductMerchantRelationship()
            ->getPriceDimensionOrFail()
            ->getIdMerchantRelationshipOrFail();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship($idMerchantRelationship);
        $abstractStorageEntityCountBefore = $this->countAbstractStorageEntitiesByMerchantRelationship($idMerchantRelationship);

        // Act
        $this->createWriter(true)->writeCollectionByMerchantRelationshipToCompanyBusinessUnitEvents([
            $this->createEventEntityTransfer($idCompanyBusinessUnit, $idMerchantRelationship),
        ]);

        // Assert
        $this->assertGreaterThan($abstractStorageEntityCountBefore, $this->countAbstractStorageEntitiesByMerchantRelationship($idMerchantRelationship));
    }

    public function testWriteCollectionPublishesConcretePricesWhenMerchantRelationshipGetsFirstBusinessUnitAndFlagEnabled(): void
    {
        // Arrange
        $idMerchantRelationship = $this->tester->haveProductConcretePriceProductMerchantRelationship()
            ->getPriceDimensionOrFail()
            ->getIdMerchantRelationshipOrFail();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship($idMerchantRelationship);
        $concreteStorageEntityCountBefore = $this->countConcreteStorageEntitiesByMerchantRelationship($idMerchantRelationship);

        // Act
        $this->createWriter(true)->writeCollectionByMerchantRelationshipToCompanyBusinessUnitEvents([
            $this->createEventEntityTransfer($idCompanyBusinessUnit, $idMerchantRelationship),
        ]);

        // Assert
        $this->assertGreaterThan($concreteStorageEntityCountBefore, $this->countConcreteStorageEntitiesByMerchantRelationship($idMerchantRelationship));
    }

    public function testWriteCollectionPublishesLookupForBusinessUnitWhenFlagEnabled(): void
    {
        // Arrange
        $idMerchantRelationship = $this->tester->haveProductAbstractPriceProductMerchantRelationship()
            ->getPriceDimensionOrFail()
            ->getIdMerchantRelationshipOrFail();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship($idMerchantRelationship);

        // Act
        $this->createWriter(true)->writeCollectionByMerchantRelationshipToCompanyBusinessUnitEvents([
            $this->createEventEntityTransfer($idCompanyBusinessUnit, $idMerchantRelationship),
        ]);

        // Assert
        $this->assertSame(1, $this->countLookupStorageEntities($idCompanyBusinessUnit));
    }

    public function testWriteCollectionDoesNotRepublishAbstractPricesOfMerchantRelationshipWithStoredPricesWhenFlagEnabled(): void
    {
        // Arrange
        $idMerchantRelationship = $this->tester->haveProductAbstractPriceProductMerchantRelationship()
            ->getPriceDimensionOrFail()
            ->getIdMerchantRelationshipOrFail();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship($idMerchantRelationship);
        $eventEntityTransfers = [$this->createEventEntityTransfer($idCompanyBusinessUnit, $idMerchantRelationship)];

        $this->createWriter(true)->writeCollectionByMerchantRelationshipToCompanyBusinessUnitEvents($eventEntityTransfers);
        $this->markAbstractStorageEntities($idMerchantRelationship);

        // Act
        $this->createWriter(true)->writeCollectionByMerchantRelationshipToCompanyBusinessUnitEvents($eventEntityTransfers);

        // Assert
        $this->assertSame([static::STORAGE_DATA_MARKER], $this->getAbstractStorageDataByMerchantRelationship($idMerchantRelationship));
    }

    public function testWriteCollectionDoesNotRepublishConcretePricesOfMerchantRelationshipWithStoredPricesWhenFlagEnabled(): void
    {
        // Arrange
        $idMerchantRelationship = $this->tester->haveProductConcretePriceProductMerchantRelationship()
            ->getPriceDimensionOrFail()
            ->getIdMerchantRelationshipOrFail();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship($idMerchantRelationship);
        $eventEntityTransfers = [$this->createEventEntityTransfer($idCompanyBusinessUnit, $idMerchantRelationship)];

        $this->createWriter(true)->writeCollectionByMerchantRelationshipToCompanyBusinessUnitEvents($eventEntityTransfers);
        $this->markConcreteStorageEntities($idMerchantRelationship);

        // Act
        $this->createWriter(true)->writeCollectionByMerchantRelationshipToCompanyBusinessUnitEvents($eventEntityTransfers);

        // Assert
        $this->assertSame([static::STORAGE_DATA_MARKER], $this->getConcreteStorageDataByMerchantRelationship($idMerchantRelationship));
    }

    public function testWriteCollectionDeletesAbstractPricesWhenMerchantRelationshipLosesLastBusinessUnitAndFlagEnabled(): void
    {
        // Arrange
        $idMerchantRelationship = $this->tester->haveProductAbstractPriceProductMerchantRelationship()
            ->getPriceDimensionOrFail()
            ->getIdMerchantRelationshipOrFail();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship($idMerchantRelationship);
        $eventEntityTransfers = [$this->createEventEntityTransfer($idCompanyBusinessUnit, $idMerchantRelationship)];

        $this->createWriter(true)->writeCollectionByMerchantRelationshipToCompanyBusinessUnitEvents($eventEntityTransfers);
        $this->assertGreaterThan(0, $this->countAbstractStorageEntitiesByMerchantRelationship($idMerchantRelationship));
        $this->deleteMerchantRelationshipToCompanyBusinessUnits($idMerchantRelationship);

        // Act
        $this->createWriter(true)->writeCollectionByMerchantRelationshipToCompanyBusinessUnitEvents($eventEntityTransfers);

        // Assert
        $this->assertSame(0, $this->countAbstractStorageEntitiesByMerchantRelationship($idMerchantRelationship));
    }

    public function testWriteCollectionDeletesConcretePricesWhenMerchantRelationshipLosesLastBusinessUnitAndFlagEnabled(): void
    {
        // Arrange
        $idMerchantRelationship = $this->tester->haveProductConcretePriceProductMerchantRelationship()
            ->getPriceDimensionOrFail()
            ->getIdMerchantRelationshipOrFail();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship($idMerchantRelationship);
        $eventEntityTransfers = [$this->createEventEntityTransfer($idCompanyBusinessUnit, $idMerchantRelationship)];

        $this->createWriter(true)->writeCollectionByMerchantRelationshipToCompanyBusinessUnitEvents($eventEntityTransfers);
        $this->assertGreaterThan(0, $this->countConcreteStorageEntitiesByMerchantRelationship($idMerchantRelationship));
        $this->deleteMerchantRelationshipToCompanyBusinessUnits($idMerchantRelationship);

        // Act
        $this->createWriter(true)->writeCollectionByMerchantRelationshipToCompanyBusinessUnitEvents($eventEntityTransfers);

        // Assert
        $this->assertSame(0, $this->countConcreteStorageEntitiesByMerchantRelationship($idMerchantRelationship));
    }

    public function testWriteCollectionDeletesAbstractPricesWhenMerchantRelationshipIsDeletedAndFlagEnabled(): void
    {
        // Arrange
        $idMerchantRelationship = $this->tester->haveProductAbstractPriceProductMerchantRelationship()
            ->getPriceDimensionOrFail()
            ->getIdMerchantRelationshipOrFail();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship($idMerchantRelationship);
        $eventEntityTransfers = [$this->createEventEntityTransfer($idCompanyBusinessUnit, $idMerchantRelationship)];

        $this->createWriter(true)->writeCollectionByMerchantRelationshipToCompanyBusinessUnitEvents($eventEntityTransfers);
        $this->assertGreaterThan(0, $this->countAbstractStorageEntitiesByMerchantRelationship($idMerchantRelationship));
        $this->deleteMerchantRelationship($idMerchantRelationship);

        // Act
        $this->createWriter(true)->writeCollectionByMerchantRelationshipToCompanyBusinessUnitEvents($eventEntityTransfers);

        // Assert
        $this->assertSame(0, $this->countAbstractStorageEntitiesByMerchantRelationship($idMerchantRelationship));
    }

    public function testWriteCollectionDeletesConcretePricesWhenMerchantRelationshipIsDeletedAndFlagEnabled(): void
    {
        // Arrange
        $idMerchantRelationship = $this->tester->haveProductConcretePriceProductMerchantRelationship()
            ->getPriceDimensionOrFail()
            ->getIdMerchantRelationshipOrFail();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship($idMerchantRelationship);
        $eventEntityTransfers = [$this->createEventEntityTransfer($idCompanyBusinessUnit, $idMerchantRelationship)];

        $this->createWriter(true)->writeCollectionByMerchantRelationshipToCompanyBusinessUnitEvents($eventEntityTransfers);
        $this->assertGreaterThan(0, $this->countConcreteStorageEntitiesByMerchantRelationship($idMerchantRelationship));
        $this->deleteMerchantRelationship($idMerchantRelationship);

        // Act
        $this->createWriter(true)->writeCollectionByMerchantRelationshipToCompanyBusinessUnitEvents($eventEntityTransfers);

        // Assert
        $this->assertSame(0, $this->countConcreteStorageEntitiesByMerchantRelationship($idMerchantRelationship));
    }

    public function testWriteCollectionDoesNotPublishAbstractPricesWhenEventHasNoMerchantRelationshipAndFlagEnabled(): void
    {
        // Arrange
        $idMerchantRelationship = $this->tester->haveProductAbstractPriceProductMerchantRelationship()
            ->getPriceDimensionOrFail()
            ->getIdMerchantRelationshipOrFail();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship($idMerchantRelationship);

        // Act
        $this->createWriter(true)->writeCollectionByMerchantRelationshipToCompanyBusinessUnitEvents([
            $this->createEventEntityTransfer($idCompanyBusinessUnit),
        ]);

        // Assert
        $this->assertSame(0, $this->countAbstractStorageEntitiesByMerchantRelationship($idMerchantRelationship));
        $this->assertSame(1, $this->countLookupStorageEntities($idCompanyBusinessUnit));
    }

    public function testWriteCollectionDoesNotPublishConcretePricesWhenEventHasNoMerchantRelationshipAndFlagEnabled(): void
    {
        // Arrange
        $idMerchantRelationship = $this->tester->haveProductConcretePriceProductMerchantRelationship()
            ->getPriceDimensionOrFail()
            ->getIdMerchantRelationshipOrFail();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship($idMerchantRelationship);

        // Act
        $this->createWriter(true)->writeCollectionByMerchantRelationshipToCompanyBusinessUnitEvents([
            $this->createEventEntityTransfer($idCompanyBusinessUnit),
        ]);

        // Assert
        $this->assertSame(0, $this->countConcreteStorageEntitiesByMerchantRelationship($idMerchantRelationship));
        $this->assertSame(1, $this->countLookupStorageEntities($idCompanyBusinessUnit));
    }

    public function testWriteCollectionPublishesAbstractPricesByBusinessUnitWithoutLookupWhenFlagDisabled(): void
    {
        // Arrange
        $idMerchantRelationship = $this->tester->haveProductAbstractPriceProductMerchantRelationship()
            ->getPriceDimensionOrFail()
            ->getIdMerchantRelationshipOrFail();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship($idMerchantRelationship);
        $abstractStorageEntityCountBefore = $this->countAbstractStorageEntitiesByCompanyBusinessUnit($idCompanyBusinessUnit);

        // Act
        $this->createWriter(false)->writeCollectionByMerchantRelationshipToCompanyBusinessUnitEvents([
            $this->createEventEntityTransfer($idCompanyBusinessUnit, $idMerchantRelationship),
        ]);

        // Assert
        $this->assertGreaterThan($abstractStorageEntityCountBefore, $this->countAbstractStorageEntitiesByCompanyBusinessUnit($idCompanyBusinessUnit));
        $this->assertSame(0, $this->countLookupStorageEntities($idCompanyBusinessUnit));
    }

    public function testWriteCollectionPublishesConcretePricesByBusinessUnitWithoutLookupWhenFlagDisabled(): void
    {
        // Arrange
        $idMerchantRelationship = $this->tester->haveProductConcretePriceProductMerchantRelationship()
            ->getPriceDimensionOrFail()
            ->getIdMerchantRelationshipOrFail();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship($idMerchantRelationship);
        $concreteStorageEntityCountBefore = $this->countConcreteStorageEntitiesByCompanyBusinessUnit($idCompanyBusinessUnit);

        // Act
        $this->createWriter(false)->writeCollectionByMerchantRelationshipToCompanyBusinessUnitEvents([
            $this->createEventEntityTransfer($idCompanyBusinessUnit, $idMerchantRelationship),
        ]);

        // Assert
        $this->assertGreaterThan($concreteStorageEntityCountBefore, $this->countConcreteStorageEntitiesByCompanyBusinessUnit($idCompanyBusinessUnit));
        $this->assertSame(0, $this->countLookupStorageEntities($idCompanyBusinessUnit));
    }

    protected function createWriter(bool $isMerchantRelationPriceKeyEnabled): MerchantRelationshipToCompanyBusinessUnitStorageWriterInterface
    {
        return $this->tester
            ->createBusinessFactoryWithMerchantRelationPriceKeyFlag($isMerchantRelationPriceKeyEnabled)
            ->createMerchantRelationshipToCompanyBusinessUnitStorageWriter();
    }

    protected function createEventEntityTransfer(int $idCompanyBusinessUnit, ?int $idMerchantRelationship = null): EventEntityTransfer
    {
        $foreignKeys = [
            PriceProductMerchantRelationshipStorageConfig::COL_FK_COMPANY_BUSINESS_UNIT => $idCompanyBusinessUnit,
        ];

        if ($idMerchantRelationship !== null) {
            $foreignKeys[static::COL_FK_MERCHANT_RELATIONSHIP] = $idMerchantRelationship;
        }

        return (new EventEntityTransfer())->setForeignKeys($foreignKeys);
    }

    protected function findCompanyBusinessUnitIdByMerchantRelationship(int $idMerchantRelationship): int
    {
        return SpyMerchantRelationshipToCompanyBusinessUnitQuery::create()
            ->filterByFkMerchantRelationship($idMerchantRelationship)
            ->findOne()
            ->getFkCompanyBusinessUnit();
    }

    protected function deleteMerchantRelationshipToCompanyBusinessUnits(int $idMerchantRelationship): void
    {
        SpyMerchantRelationshipToCompanyBusinessUnitQuery::create()
            ->filterByFkMerchantRelationship($idMerchantRelationship)
            ->delete();
    }

    protected function deleteMerchantRelationship(int $idMerchantRelationship): void
    {
        $this->deleteMerchantRelationshipToCompanyBusinessUnits($idMerchantRelationship);

        SpyMerchantRelationshipQuery::create()
            ->filterByIdMerchantRelationship($idMerchantRelationship)
            ->delete();
    }

    protected function markAbstractStorageEntities(int $idMerchantRelationship): void
    {
        $priceProductAbstractMerchantRelationshipStorageEntities = SpyPriceProductAbstractMerchantRelationshipStorageQuery::create()
            ->filterByFkMerchantRelationship($idMerchantRelationship)
            ->find();

        foreach ($priceProductAbstractMerchantRelationshipStorageEntities as $priceProductAbstractMerchantRelationshipStorageEntity) {
            $priceProductAbstractMerchantRelationshipStorageEntity->setData(static::STORAGE_DATA_MARKER)->save();
        }
    }

    /**
     * @return array<array<string, mixed>>
     */
    protected function getAbstractStorageDataByMerchantRelationship(int $idMerchantRelationship): array
    {
        $storageData = [];

        $priceProductAbstractMerchantRelationshipStorageEntities = SpyPriceProductAbstractMerchantRelationshipStorageQuery::create()
            ->filterByFkMerchantRelationship($idMerchantRelationship)
            ->find();

        foreach ($priceProductAbstractMerchantRelationshipStorageEntities as $priceProductAbstractMerchantRelationshipStorageEntity) {
            $storageData[] = $priceProductAbstractMerchantRelationshipStorageEntity->getData();
        }

        return $storageData;
    }

    protected function markConcreteStorageEntities(int $idMerchantRelationship): void
    {
        $priceProductConcreteMerchantRelationshipStorageEntities = SpyPriceProductConcreteMerchantRelationshipStorageQuery::create()
            ->filterByFkMerchantRelationship($idMerchantRelationship)
            ->find();

        foreach ($priceProductConcreteMerchantRelationshipStorageEntities as $priceProductConcreteMerchantRelationshipStorageEntity) {
            $priceProductConcreteMerchantRelationshipStorageEntity->setData(static::STORAGE_DATA_MARKER)->save();
        }
    }

    /**
     * @return array<array<string, mixed>>
     */
    protected function getConcreteStorageDataByMerchantRelationship(int $idMerchantRelationship): array
    {
        $storageData = [];

        $priceProductConcreteMerchantRelationshipStorageEntities = SpyPriceProductConcreteMerchantRelationshipStorageQuery::create()
            ->filterByFkMerchantRelationship($idMerchantRelationship)
            ->find();

        foreach ($priceProductConcreteMerchantRelationshipStorageEntities as $priceProductConcreteMerchantRelationshipStorageEntity) {
            $storageData[] = $priceProductConcreteMerchantRelationshipStorageEntity->getData();
        }

        return $storageData;
    }

    protected function countAbstractStorageEntitiesByMerchantRelationship(int $idMerchantRelationship): int
    {
        return SpyPriceProductAbstractMerchantRelationshipStorageQuery::create()
            ->filterByFkMerchantRelationship($idMerchantRelationship)
            ->count();
    }

    protected function countAbstractStorageEntitiesByCompanyBusinessUnit(int $idCompanyBusinessUnit): int
    {
        return SpyPriceProductAbstractMerchantRelationshipStorageQuery::create()
            ->filterByFkCompanyBusinessUnit($idCompanyBusinessUnit)
            ->count();
    }

    protected function countConcreteStorageEntitiesByMerchantRelationship(int $idMerchantRelationship): int
    {
        return SpyPriceProductConcreteMerchantRelationshipStorageQuery::create()
            ->filterByFkMerchantRelationship($idMerchantRelationship)
            ->count();
    }

    protected function countConcreteStorageEntitiesByCompanyBusinessUnit(int $idCompanyBusinessUnit): int
    {
        return SpyPriceProductConcreteMerchantRelationshipStorageQuery::create()
            ->filterByFkCompanyBusinessUnit($idCompanyBusinessUnit)
            ->count();
    }

    protected function countLookupStorageEntities(int $idCompanyBusinessUnit): int
    {
        return SpyMerchantRelationBusinessUnitStorageQuery::create()
            ->filterByFkCompanyBusinessUnit($idCompanyBusinessUnit)
            ->count();
    }
}
