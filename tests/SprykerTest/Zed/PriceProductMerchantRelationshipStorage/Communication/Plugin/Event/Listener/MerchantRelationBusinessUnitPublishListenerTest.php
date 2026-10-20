<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\PriceProductMerchantRelationshipStorage\Communication\Plugin\Event\Listener;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\EventEntityTransfer;
use Orm\Zed\MerchantRelationship\Persistence\SpyMerchantRelationshipToCompanyBusinessUnitQuery;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyMerchantRelationBusinessUnitStorageQuery;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductAbstractMerchantRelationshipStorageQuery;
use Spryker\Shared\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageConfig;
use Spryker\Shared\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageConstants;
use Spryker\Zed\PriceProductMerchantRelationshipStorage\Communication\Plugin\Event\Listener\MerchantRelationBusinessUnitPublishListener;
use SprykerTest\Zed\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageCommunicationTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group PriceProductMerchantRelationshipStorage
 * @group Communication
 * @group Plugin
 * @group Event
 * @group Listener
 * @group MerchantRelationBusinessUnitPublishListenerTest
 * Add your own group annotations below this line
 */
class MerchantRelationBusinessUnitPublishListenerTest extends Unit
{
    protected PriceProductMerchantRelationshipStorageCommunicationTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tester->ensurePriceProductMerchantRelationshipTableIsEmpty();
    }

    public function testHandleBulkPublishesLookupForBusinessUnit(): void
    {
        // Arrange
        $priceProductTransfer = $this->tester->haveProductAbstractPriceProductMerchantRelationship();
        $idMerchantRelationship = $priceProductTransfer->getPriceDimensionOrFail()->getIdMerchantRelationshipOrFail();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship($idMerchantRelationship);

        // Act
        $this->handleBulk([$idCompanyBusinessUnit], true);

        // Assert
        $this->assertSame([$idMerchantRelationship], $this->getLookupStorageIdMerchantRelationships($idCompanyBusinessUnit));
    }

    public function testHandleBulkPublishesEveryMerchantRelationshipOfBusinessUnit(): void
    {
        // Arrange
        $idFirstMerchantRelationship = $this->tester->haveProductAbstractPriceProductMerchantRelationship()
            ->getPriceDimensionOrFail()
            ->getIdMerchantRelationshipOrFail();
        $idSecondMerchantRelationship = $this->tester->haveProductAbstractPriceProductMerchantRelationship()
            ->getPriceDimensionOrFail()
            ->getIdMerchantRelationshipOrFail();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship($idFirstMerchantRelationship);
        $this->tester->haveMerchantRelationshipToCompanyBusinessUnit($idCompanyBusinessUnit, $idSecondMerchantRelationship);

        // Act
        $this->handleBulk([$idCompanyBusinessUnit], true);

        // Assert
        $idMerchantRelationships = $this->getLookupStorageIdMerchantRelationships($idCompanyBusinessUnit);
        sort($idMerchantRelationships);
        $this->assertSame([$idFirstMerchantRelationship, $idSecondMerchantRelationship], $idMerchantRelationships);
    }

    public function testHandleBulkDeletesLookupWhenBusinessUnitHasNoMerchantRelationshipAssigned(): void
    {
        // Arrange
        $priceProductTransfer = $this->tester->haveProductAbstractPriceProductMerchantRelationship();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship(
            $priceProductTransfer->getPriceDimensionOrFail()->getIdMerchantRelationshipOrFail(),
        );
        $this->handleBulk([$idCompanyBusinessUnit], true);
        $this->assertSame(1, $this->countLookupStorageEntities($idCompanyBusinessUnit));

        SpyMerchantRelationshipToCompanyBusinessUnitQuery::create()
            ->filterByFkCompanyBusinessUnit($idCompanyBusinessUnit)
            ->delete();

        // Act
        $this->handleBulk([$idCompanyBusinessUnit], true);

        // Assert
        $this->assertSame(0, $this->countLookupStorageEntities($idCompanyBusinessUnit));
    }

    public function testHandleBulkDoesNotPublishPrices(): void
    {
        // Arrange
        $priceProductTransfer = $this->tester->haveProductAbstractPriceProductMerchantRelationship();
        $idMerchantRelationship = $priceProductTransfer->getPriceDimensionOrFail()->getIdMerchantRelationshipOrFail();

        // Act
        $this->handleBulk([$this->findCompanyBusinessUnitIdByMerchantRelationship($idMerchantRelationship)], true);

        // Assert
        $this->assertSame(
            0,
            SpyPriceProductAbstractMerchantRelationshipStorageQuery::create()->filterByFkMerchantRelationship($idMerchantRelationship)->count(),
        );
    }

    public function testHandleBulkDoesNotPublishLookupWhenFlagIsDisabled(): void
    {
        // Arrange
        $priceProductTransfer = $this->tester->haveProductAbstractPriceProductMerchantRelationship();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship(
            $priceProductTransfer->getPriceDimensionOrFail()->getIdMerchantRelationshipOrFail(),
        );

        // Act
        $this->handleBulk([$idCompanyBusinessUnit], false);

        // Assert
        $this->assertSame(0, $this->countLookupStorageEntities($idCompanyBusinessUnit));
    }

    /**
     * @param array<int> $companyBusinessUnitIds
     */
    protected function handleBulk(array $companyBusinessUnitIds, bool $isMerchantRelationPriceKeyEnabled): void
    {
        $eventEntityTransfers = [];
        foreach ($companyBusinessUnitIds as $idCompanyBusinessUnit) {
            $eventEntityTransfers[] = (new EventEntityTransfer())->setId($idCompanyBusinessUnit);
        }

        $merchantRelationBusinessUnitPublishListener = new MerchantRelationBusinessUnitPublishListener();
        $merchantRelationBusinessUnitPublishListener->setBusinessFactory($this->tester->createBusinessFactoryWithMerchantRelationPriceKeyFlag($isMerchantRelationPriceKeyEnabled));
        $merchantRelationBusinessUnitPublishListener->handleBulk(
            $eventEntityTransfers,
            PriceProductMerchantRelationshipStorageConfig::MERCHANT_RELATION_BUSINESS_UNIT_PUBLISH,
        );
    }

    protected function findCompanyBusinessUnitIdByMerchantRelationship(int $idMerchantRelationship): int
    {
        return SpyMerchantRelationshipToCompanyBusinessUnitQuery::create()
            ->filterByFkMerchantRelationship($idMerchantRelationship)
            ->findOne()
            ->getFkCompanyBusinessUnit();
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
