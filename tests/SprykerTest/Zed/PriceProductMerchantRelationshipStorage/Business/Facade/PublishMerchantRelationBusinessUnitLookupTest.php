<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\PriceProductMerchantRelationshipStorage\Business\Facade;

use Codeception\Test\Unit;
use Orm\Zed\MerchantRelationship\Persistence\SpyMerchantRelationshipToCompanyBusinessUnitQuery;
use Orm\Zed\PriceProductMerchantRelationship\Persistence\SpyPriceProductMerchantRelationshipQuery;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyMerchantRelationBusinessUnitStorageQuery;
use Spryker\Shared\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageConstants;
use SprykerTest\Zed\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group PriceProductMerchantRelationshipStorage
 * @group Business
 * @group Facade
 * @group PublishMerchantRelationBusinessUnitLookupTest
 * Add your own group annotations below this line
 */
class PublishMerchantRelationBusinessUnitLookupTest extends Unit
{
    /**
     * @var \SprykerTest\Zed\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageBusinessTester
     */
    protected PriceProductMerchantRelationshipStorageBusinessTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tester->ensurePriceProductMerchantRelationshipTableIsEmpty();
    }

    public function testPublishAbstractPriceProductByProductAbstractIdsDoesNotPublishLookup(): void
    {
        // Arrange
        $priceProductTransfer = $this->tester->haveProductAbstractPriceProductMerchantRelationship();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship(
            $priceProductTransfer->getPriceDimensionOrFail()->getIdMerchantRelationshipOrFail(),
        );

        // Act
        $this->tester->createFacadeWithMerchantRelationPriceKeyFlag(true)->publishAbstractPriceProductByProductAbstractIds([$priceProductTransfer->getIdProductAbstractOrFail()]);

        // Assert
        $this->assertSame(0, $this->countLookupStorageEntities($idCompanyBusinessUnit));
    }

    public function testPublishConcretePriceProductByProductIdsDoesNotPublishLookup(): void
    {
        // Arrange
        $priceProductTransfer = $this->tester->haveProductConcretePriceProductMerchantRelationship();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship(
            $priceProductTransfer->getPriceDimensionOrFail()->getIdMerchantRelationshipOrFail(),
        );

        // Act
        $this->tester->createFacadeWithMerchantRelationPriceKeyFlag(true)->publishConcretePriceProductByProductIds([$priceProductTransfer->getIdProductOrFail()]);

        // Assert
        $this->assertSame(0, $this->countLookupStorageEntities($idCompanyBusinessUnit));
    }

    public function testPublishAbstractPriceProductMerchantRelationshipDoesNotPublishLookup(): void
    {
        // Arrange
        $priceProductTransfer = $this->tester->haveProductAbstractPriceProductMerchantRelationship();
        $idMerchantRelationship = $priceProductTransfer->getPriceDimensionOrFail()->getIdMerchantRelationshipOrFail();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship($idMerchantRelationship);

        // Act
        $this->tester->createFacadeWithMerchantRelationPriceKeyFlag(true)->publishAbstractPriceProductMerchantRelationship(
            $this->findPriceProductMerchantRelationshipIds($idMerchantRelationship),
        );

        // Assert
        $this->assertSame(0, $this->countLookupStorageEntities($idCompanyBusinessUnit));
    }

    public function testPublishAbstractPriceProductByBusinessUnitsPublishesLookupForBusinessUnit(): void
    {
        // Arrange
        $priceProductTransfer = $this->tester->haveProductAbstractPriceProductMerchantRelationship();
        $idMerchantRelationship = $priceProductTransfer->getPriceDimensionOrFail()->getIdMerchantRelationshipOrFail();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship($idMerchantRelationship);

        // Act
        $this->tester->createFacadeWithMerchantRelationPriceKeyFlag(true)->publishAbstractPriceProductByBusinessUnits([$idCompanyBusinessUnit]);

        // Assert
        $this->assertSame([$idMerchantRelationship], $this->getLookupStorageIdMerchantRelationships($idCompanyBusinessUnit));
    }

    public function testPublishAbstractPriceProductByBusinessUnitsDeletesLookupWhenBusinessUnitHasNoMerchantRelationshipAssigned(): void
    {
        // Arrange
        $priceProductTransfer = $this->tester->haveProductAbstractPriceProductMerchantRelationship();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship(
            $priceProductTransfer->getPriceDimensionOrFail()->getIdMerchantRelationshipOrFail(),
        );
        $this->tester->createFacadeWithMerchantRelationPriceKeyFlag(true)->publishAbstractPriceProductByBusinessUnits([$idCompanyBusinessUnit]);
        $this->assertSame(1, $this->countLookupStorageEntities($idCompanyBusinessUnit));

        SpyMerchantRelationshipToCompanyBusinessUnitQuery::create()
            ->filterByFkCompanyBusinessUnit($idCompanyBusinessUnit)
            ->delete();

        // Act
        $this->tester->createFacadeWithMerchantRelationPriceKeyFlag(true)->publishAbstractPriceProductByBusinessUnits([$idCompanyBusinessUnit]);

        // Assert
        $this->assertSame(0, $this->countLookupStorageEntities($idCompanyBusinessUnit));
    }

    public function testPublishAbstractPriceProductByBusinessUnitsDoesNotPublishLookupWhenFlagIsDisabled(): void
    {
        // Arrange

        $priceProductTransfer = $this->tester->haveProductAbstractPriceProductMerchantRelationship();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship(
            $priceProductTransfer->getPriceDimensionOrFail()->getIdMerchantRelationshipOrFail(),
        );

        // Act
        $this->tester->createFacadeWithMerchantRelationPriceKeyFlag(false)->publishAbstractPriceProductByBusinessUnits([$idCompanyBusinessUnit]);

        // Assert
        $this->assertSame(0, $this->countLookupStorageEntities($idCompanyBusinessUnit));
    }

    protected function findCompanyBusinessUnitIdByMerchantRelationship(int $idMerchantRelationship): int
    {
        return SpyMerchantRelationshipToCompanyBusinessUnitQuery::create()
            ->filterByFkMerchantRelationship($idMerchantRelationship)
            ->findOne()
            ->getFkCompanyBusinessUnit();
    }

    /**
     * @return array<int>
     */
    protected function findPriceProductMerchantRelationshipIds(int $idMerchantRelationship): array
    {
        $priceProductMerchantRelationshipIds = [];
        foreach (SpyPriceProductMerchantRelationshipQuery::create()->filterByFkMerchantRelationship($idMerchantRelationship)->find() as $priceProductMerchantRelationshipEntity) {
            $priceProductMerchantRelationshipIds[] = $priceProductMerchantRelationshipEntity->getIdPriceProductMerchantRelationship();
        }

        return $priceProductMerchantRelationshipIds;
    }

    protected function countLookupStorageEntities(int $idCompanyBusinessUnit): int
    {
        return SpyMerchantRelationBusinessUnitStorageQuery::create()
            ->filterByFkCompanyBusinessUnit($idCompanyBusinessUnit)
            ->count();
    }

    /**
     * @return array<int>
     */
    protected function getLookupStorageIdMerchantRelationships(int $idCompanyBusinessUnit): array
    {
        $merchantRelationBusinessUnitStorageEntity = SpyMerchantRelationBusinessUnitStorageQuery::create()
            ->filterByFkCompanyBusinessUnit($idCompanyBusinessUnit)
            ->findOne();

        if ($merchantRelationBusinessUnitStorageEntity === null) {
            return [];
        }

        return $merchantRelationBusinessUnitStorageEntity->getData()[PriceProductMerchantRelationshipStorageConstants::MERCHANT_RELATION_BUSINESS_UNIT_DATA_KEY_MERCHANT_RELATIONSHIP_IDS] ?? [];
    }
}
