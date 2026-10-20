<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\PriceProductMerchantRelationshipStorage\Business\Facade;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Orm\Zed\MerchantRelationship\Persistence\SpyMerchantRelationshipToCompanyBusinessUnitQuery;
use Orm\Zed\PriceProductMerchantRelationship\Persistence\SpyPriceProductMerchantRelationshipQuery;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductAbstractMerchantRelationshipStorage;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductAbstractMerchantRelationshipStorageQuery;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductConcreteMerchantRelationshipStorage;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductConcreteMerchantRelationshipStorageQuery;
use Spryker\Shared\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageConfig as SharedPriceProductMerchantRelationshipStorageConfig;
use SprykerTest\Zed\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group PriceProductMerchantRelationshipStorage
 * @group Business
 * @group Facade
 * @group PublishPriceProductMerchantRelationshipKeyShapeTest
 * Add your own group annotations below this line
 */
class PublishPriceProductMerchantRelationshipKeyShapeTest extends Unit
{
    protected const int ID_STALE_MERCHANT_RELATIONSHIP = 990101;

    protected const int ID_STALE_COMPANY_BUSINESS_UNIT = 990001;

    protected const string STORE_NAME = 'DE';

    protected PriceProductMerchantRelationshipStorageBusinessTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tester->ensurePriceProductMerchantRelationshipTableIsEmpty();
    }

    public function testPublishAbstractPriceProductByProductAbstractIdsStoresBusinessUnitKeyShapeWhenFlagIsDisabled(): void
    {
        // Arrange
        $priceProductTransfer = $this->tester->haveProductAbstractPriceProductMerchantRelationship();
        $idProductAbstract = $priceProductTransfer->getIdProductAbstractOrFail();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship(
            $priceProductTransfer->getPriceDimensionOrFail()->getIdMerchantRelationshipOrFail(),
        );

        // Act
        $this->tester->createFacadeWithMerchantRelationPriceKeyFlag(false)->publishAbstractPriceProductByProductAbstractIds([$idProductAbstract]);

        // Assert
        $priceProductAbstractMerchantRelationshipStorageEntities = SpyPriceProductAbstractMerchantRelationshipStorageQuery::create()
            ->filterByFkProductAbstract($idProductAbstract)
            ->find()
            ->getData();
        $this->assertCount(1, $priceProductAbstractMerchantRelationshipStorageEntities);
        $this->assertNull($priceProductAbstractMerchantRelationshipStorageEntities[0]->getFkMerchantRelationship());
        $this->assertSame($idCompanyBusinessUnit, $priceProductAbstractMerchantRelationshipStorageEntities[0]->getFkCompanyBusinessUnit());
        $this->assertStringEndsWith(
            sprintf(':%d:%d', $idProductAbstract, $idCompanyBusinessUnit),
            $priceProductAbstractMerchantRelationshipStorageEntities[0]->getPriceKey(),
        );
    }

    public function testPublishConcretePriceProductByProductIdsStoresBusinessUnitKeyShapeWhenFlagIsDisabled(): void
    {
        // Arrange
        $priceProductTransfer = $this->tester->haveProductConcretePriceProductMerchantRelationship();
        $idProduct = $priceProductTransfer->getIdProductOrFail();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship(
            $priceProductTransfer->getPriceDimensionOrFail()->getIdMerchantRelationshipOrFail(),
        );

        // Act
        $this->tester->createFacadeWithMerchantRelationPriceKeyFlag(false)->publishConcretePriceProductByProductIds([$idProduct]);

        // Assert
        $priceProductConcreteMerchantRelationshipStorageEntities = SpyPriceProductConcreteMerchantRelationshipStorageQuery::create()
            ->filterByFkProduct($idProduct)
            ->find()
            ->getData();
        $this->assertCount(1, $priceProductConcreteMerchantRelationshipStorageEntities);
        $this->assertNull($priceProductConcreteMerchantRelationshipStorageEntities[0]->getFkMerchantRelationship());
        $this->assertSame($idCompanyBusinessUnit, $priceProductConcreteMerchantRelationshipStorageEntities[0]->getFkCompanyBusinessUnit());
        $this->assertStringEndsWith(
            sprintf(':%d:%d', $idProduct, $idCompanyBusinessUnit),
            $priceProductConcreteMerchantRelationshipStorageEntities[0]->getPriceKey(),
        );
    }

    public function testPublishAbstractPriceProductByProductAbstractIdsRemovesMerchantRelationshipKeyShapeRowWhenFlagIsDisabled(): void
    {
        // Arrange
        $idProductAbstract = $this->tester->haveProductAbstractPriceProductMerchantRelationship()->getIdProductAbstractOrFail();
        $staleStorageEntity = $this->haveAbstractStorageEntity($idProductAbstract, static::ID_STALE_MERCHANT_RELATIONSHIP);

        // Act
        $this->tester->createFacadeWithMerchantRelationPriceKeyFlag(false)->publishAbstractPriceProductByProductAbstractIds([$idProductAbstract]);

        // Assert
        $this->assertFalse($this->hasAbstractStorageEntity($staleStorageEntity));
    }

    public function testPublishAbstractPriceProductByProductAbstractIdsKeepsBusinessUnitKeyShapeRowWhenFlagIsEnabled(): void
    {
        // Arrange
        $idProductAbstract = $this->tester->haveProductAbstractPriceProductMerchantRelationship()->getIdProductAbstractOrFail();
        $legacyStorageEntity = $this->haveAbstractStorageEntity($idProductAbstract, null);

        // Act
        $this->tester->createFacadeWithMerchantRelationPriceKeyFlag(true)->publishAbstractPriceProductByProductAbstractIds([$idProductAbstract]);

        // Assert
        $this->assertTrue($this->hasAbstractStorageEntity($legacyStorageEntity));
    }

    public function testPublishConcretePriceProductByProductIdsRemovesMerchantRelationshipKeyShapeRowWhenFlagIsDisabled(): void
    {
        // Arrange
        $idProduct = $this->tester->haveProductConcretePriceProductMerchantRelationship()->getIdProductOrFail();
        $staleStorageEntity = $this->haveConcreteStorageEntity($idProduct, static::ID_STALE_MERCHANT_RELATIONSHIP);

        // Act
        $this->tester->createFacadeWithMerchantRelationPriceKeyFlag(false)->publishConcretePriceProductByProductIds([$idProduct]);

        // Assert
        $this->assertFalse($this->hasConcreteStorageEntity($staleStorageEntity));
    }

    public function testPublishConcretePriceProductByProductIdsKeepsBusinessUnitKeyShapeRowWhenFlagIsEnabled(): void
    {
        // Arrange
        $idProduct = $this->tester->haveProductConcretePriceProductMerchantRelationship()->getIdProductOrFail();
        $legacyStorageEntity = $this->haveConcreteStorageEntity($idProduct, null);

        // Act
        $this->tester->createFacadeWithMerchantRelationPriceKeyFlag(true)->publishConcretePriceProductByProductIds([$idProduct]);

        // Assert
        $this->assertTrue($this->hasConcreteStorageEntity($legacyStorageEntity));
    }

    public function testPublishAbstractPriceProductByProductAbstractIdsStoresNoBusinessUnitWhenFlagIsEnabled(): void
    {
        // Arrange
        $priceProductTransfer = $this->tester->haveProductAbstractPriceProductMerchantRelationship();
        $idProductAbstract = $priceProductTransfer->getIdProductAbstractOrFail();
        $idMerchantRelationship = $priceProductTransfer->getPriceDimensionOrFail()->getIdMerchantRelationshipOrFail();
        $this->haveAdditionalCompanyBusinessUnitForMerchantRelationship($idMerchantRelationship);

        // Act
        $this->tester->createFacadeWithMerchantRelationPriceKeyFlag(true)->publishAbstractPriceProductByProductAbstractIds([$idProductAbstract]);

        // Assert
        $priceProductAbstractMerchantRelationshipStorageEntities = SpyPriceProductAbstractMerchantRelationshipStorageQuery::create()
            ->filterByFkProductAbstract($idProductAbstract)
            ->find()
            ->getData();
        $this->assertCount(1, $priceProductAbstractMerchantRelationshipStorageEntities);
        $this->assertSame($idMerchantRelationship, $priceProductAbstractMerchantRelationshipStorageEntities[0]->getFkMerchantRelationship());
        $this->assertSame(SharedPriceProductMerchantRelationshipStorageConfig::ID_COMPANY_BUSINESS_UNIT_NONE, $priceProductAbstractMerchantRelationshipStorageEntities[0]->getFkCompanyBusinessUnit());
    }

    public function testFindMerchantRelationshipProductAbstractPricesDataByProductAbstractIdsReturnsNoBusinessUnitWhenFlagIsEnabled(): void
    {
        // Arrange
        $idProductAbstract = $this->tester->haveProductAbstractPriceProductMerchantRelationship()->getIdProductAbstractOrFail();

        // Act
        $priceProductMerchantRelationshipStorageTransfers = $this->tester->createRepositoryWithMerchantRelationPriceKeyFlag(true)
            ->findMerchantRelationshipProductAbstractPricesDataByProductAbstractIds([$idProductAbstract]);

        // Assert
        $this->assertCount(1, $priceProductMerchantRelationshipStorageTransfers);
        $this->assertNull(array_values($priceProductMerchantRelationshipStorageTransfers)[0]->getIdCompanyBusinessUnit());
    }

    public function testFindMerchantRelationshipProductAbstractPricesDataByProductAbstractIdsReturnsEveryPriceOnceWhenFlagIsEnabled(): void
    {
        // Arrange
        $priceProductTransfer = $this->tester->haveProductAbstractPriceProductMerchantRelationship();
        $idProductAbstract = $priceProductTransfer->getIdProductAbstractOrFail();
        $repository = $this->tester->createRepositoryWithMerchantRelationPriceKeyFlag(true);
        $expectedUngroupedPriceCount = $this->countUngroupedPrices($repository->findMerchantRelationshipProductAbstractPricesDataByProductAbstractIds([$idProductAbstract]));
        $this->haveAdditionalCompanyBusinessUnitForMerchantRelationship($priceProductTransfer->getPriceDimensionOrFail()->getIdMerchantRelationshipOrFail());

        // Act
        $priceProductMerchantRelationshipStorageTransfers = $repository->findMerchantRelationshipProductAbstractPricesDataByProductAbstractIds([$idProductAbstract]);

        // Assert
        $this->assertCount(1, $priceProductMerchantRelationshipStorageTransfers);
        $this->assertSame($expectedUngroupedPriceCount, $this->countUngroupedPrices($priceProductMerchantRelationshipStorageTransfers));
    }

    public function testFindMerchantRelationshipProductAbstractPricesDataByProductAbstractIdsSkipsMerchantRelationshipWithoutBusinessUnitWhenFlagIsEnabled(): void
    {
        // Arrange
        $priceProductTransfer = $this->tester->haveProductAbstractPriceProductMerchantRelationship();
        SpyMerchantRelationshipToCompanyBusinessUnitQuery::create()
            ->filterByFkMerchantRelationship($priceProductTransfer->getPriceDimensionOrFail()->getIdMerchantRelationshipOrFail())
            ->delete();

        // Act
        $priceProductMerchantRelationshipStorageTransfers = $this->tester->createRepositoryWithMerchantRelationPriceKeyFlag(true)
            ->findMerchantRelationshipProductAbstractPricesDataByProductAbstractIds([$priceProductTransfer->getIdProductAbstractOrFail()]);

        // Assert
        $this->assertSame([], $priceProductMerchantRelationshipStorageTransfers);
    }

    public function testPublishAbstractPriceProductByBusinessUnitsRemovesRowOfDeletedMerchantRelationshipPriceWhenFlagIsEnabled(): void
    {
        // Arrange
        $priceProductTransfer = $this->tester->haveProductAbstractPriceProductMerchantRelationship();
        $idProductAbstract = $priceProductTransfer->getIdProductAbstractOrFail();
        $idMerchantRelationship = $priceProductTransfer->getPriceDimensionOrFail()->getIdMerchantRelationshipOrFail();
        $idFirstCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship($idMerchantRelationship);
        $idSecondCompanyBusinessUnit = $this->haveAdditionalCompanyBusinessUnitForMerchantRelationship($idMerchantRelationship);
        $this->tester->createFacadeWithMerchantRelationPriceKeyFlag(true)->publishAbstractPriceProductByProductAbstractIds([$idProductAbstract]);
        $this->removeCompanyBusinessUnitFromMerchantRelationship($idFirstCompanyBusinessUnit, $idMerchantRelationship);
        SpyPriceProductMerchantRelationshipQuery::create()->filterByFkMerchantRelationship($idMerchantRelationship)->delete();

        // Act
        $this->tester->createFacadeWithMerchantRelationPriceKeyFlag(true)->publishAbstractPriceProductByBusinessUnits([$idSecondCompanyBusinessUnit]);

        // Assert
        $this->assertSame(0, SpyPriceProductAbstractMerchantRelationshipStorageQuery::create()->filterByFkProductAbstract($idProductAbstract)->count());
    }

    protected function haveAbstractStorageEntity(int $idProductAbstract, ?int $idMerchantRelationship): SpyPriceProductAbstractMerchantRelationshipStorage
    {
        $priceProductAbstractMerchantRelationshipStorageEntity = (new SpyPriceProductAbstractMerchantRelationshipStorage())
            ->setFkProductAbstract($idProductAbstract)
            ->setFkCompanyBusinessUnit(static::ID_STALE_COMPANY_BUSINESS_UNIT)
            ->setFkMerchantRelationship($idMerchantRelationship)
            ->setPriceKey($this->createPriceKey($idProductAbstract, $idMerchantRelationship))
            ->setData([]);
        $priceProductAbstractMerchantRelationshipStorageEntity->save();

        return $priceProductAbstractMerchantRelationshipStorageEntity;
    }

    protected function haveConcreteStorageEntity(int $idProduct, ?int $idMerchantRelationship): SpyPriceProductConcreteMerchantRelationshipStorage
    {
        $priceProductConcreteMerchantRelationshipStorageEntity = (new SpyPriceProductConcreteMerchantRelationshipStorage())
            ->setFkProduct($idProduct)
            ->setFkCompanyBusinessUnit(static::ID_STALE_COMPANY_BUSINESS_UNIT)
            ->setFkMerchantRelationship($idMerchantRelationship)
            ->setPriceKey($this->createPriceKey($idProduct, $idMerchantRelationship))
            ->setData([]);
        $priceProductConcreteMerchantRelationshipStorageEntity->save();

        return $priceProductConcreteMerchantRelationshipStorageEntity;
    }

    protected function createPriceKey(int $idProduct, ?int $idMerchantRelationship): string
    {
        if ($idMerchantRelationship === null) {
            return sprintf('%s:%d:%d', static::STORE_NAME, $idProduct, static::ID_STALE_COMPANY_BUSINESS_UNIT);
        }

        return sprintf('%s:%d:mr%d', static::STORE_NAME, $idProduct, $idMerchantRelationship);
    }

    protected function hasAbstractStorageEntity(SpyPriceProductAbstractMerchantRelationshipStorage $priceProductAbstractMerchantRelationshipStorageEntity): bool
    {
        return SpyPriceProductAbstractMerchantRelationshipStorageQuery::create()
            ->filterByPrimaryKey($priceProductAbstractMerchantRelationshipStorageEntity->getPrimaryKey())
            ->exists();
    }

    protected function hasConcreteStorageEntity(SpyPriceProductConcreteMerchantRelationshipStorage $priceProductConcreteMerchantRelationshipStorageEntity): bool
    {
        return SpyPriceProductConcreteMerchantRelationshipStorageQuery::create()
            ->filterByPrimaryKey($priceProductConcreteMerchantRelationshipStorageEntity->getPrimaryKey())
            ->exists();
    }

    protected function haveAdditionalCompanyBusinessUnitForMerchantRelationship(int $idMerchantRelationship): int
    {
        $idCompanyBusinessUnit = $this->tester->haveCompanyBusinessUnit([
            CompanyBusinessUnitTransfer::FK_COMPANY => $this->tester->haveCompany()->getIdCompany(),
        ])->getIdCompanyBusinessUnitOrFail();
        $this->tester->haveMerchantRelationshipToCompanyBusinessUnit($idCompanyBusinessUnit, $idMerchantRelationship);

        return $idCompanyBusinessUnit;
    }

    protected function removeCompanyBusinessUnitFromMerchantRelationship(int $idCompanyBusinessUnit, int $idMerchantRelationship): void
    {
        SpyMerchantRelationshipToCompanyBusinessUnitQuery::create()
            ->filterByFkCompanyBusinessUnit($idCompanyBusinessUnit)
            ->filterByFkMerchantRelationship($idMerchantRelationship)
            ->delete();
    }

    /**
     * @param array<\Generated\Shared\Transfer\PriceProductMerchantRelationshipStorageTransfer> $priceProductMerchantRelationshipStorageTransfers
     */
    protected function countUngroupedPrices(array $priceProductMerchantRelationshipStorageTransfers): int
    {
        $ungroupedPriceCount = 0;
        foreach ($priceProductMerchantRelationshipStorageTransfers as $priceProductMerchantRelationshipStorageTransfer) {
            $ungroupedPriceCount += $priceProductMerchantRelationshipStorageTransfer->getUngroupedPrices()->count();
        }

        return $ungroupedPriceCount;
    }

    protected function findCompanyBusinessUnitIdByMerchantRelationship(int $idMerchantRelationship): int
    {
        return SpyMerchantRelationshipToCompanyBusinessUnitQuery::create()
            ->filterByFkMerchantRelationship($idMerchantRelationship)
            ->findOne()
            ->getFkCompanyBusinessUnit();
    }
}
