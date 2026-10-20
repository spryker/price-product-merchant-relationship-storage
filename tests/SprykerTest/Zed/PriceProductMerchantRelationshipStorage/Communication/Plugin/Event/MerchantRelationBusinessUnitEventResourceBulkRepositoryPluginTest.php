<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\PriceProductMerchantRelationshipStorage\Communication\Plugin\Event;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Orm\Zed\MerchantRelationship\Persistence\SpyMerchantRelationshipToCompanyBusinessUnitQuery;
use Spryker\Zed\PriceProductMerchantRelationshipStorage\Communication\Plugin\Event\MerchantRelationBusinessUnitEventResourceBulkRepositoryPlugin;
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
 * @group MerchantRelationBusinessUnitEventResourceBulkRepositoryPluginTest
 * Add your own group annotations below this line
 */
class MerchantRelationBusinessUnitEventResourceBulkRepositoryPluginTest extends Unit
{
    protected const string ID_COLUMN_SEPARATOR = '.';

    protected PriceProductMerchantRelationshipStorageCommunicationTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tester->ensurePriceProductMerchantRelationshipTableIsEmpty();
        $this->tester->ensureMerchantRelationshipToCompanyBusinessUnitTableIsEmpty();
    }

    public function testGetDataReturnsEveryBusinessUnitOnceWhenBusinessUnitHasTwoMerchantRelationships(): void
    {
        // Arrange
        $idFirstMerchantRelationship = $this->tester->haveProductAbstractPriceProductMerchantRelationship()
            ->getPriceDimensionOrFail()
            ->getIdMerchantRelationshipOrFail();
        $idSecondMerchantRelationship = $this->tester->haveProductAbstractPriceProductMerchantRelationship()
            ->getPriceDimensionOrFail()
            ->getIdMerchantRelationshipOrFail();
        $idFirstCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship($idFirstMerchantRelationship);
        $idSecondCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship($idSecondMerchantRelationship);
        $this->tester->haveMerchantRelationshipToCompanyBusinessUnit($idFirstCompanyBusinessUnit, $idSecondMerchantRelationship);

        // Act
        $companyBusinessUnitTransfers = (new MerchantRelationBusinessUnitEventResourceBulkRepositoryPlugin())->getData(0, 10);

        // Assert
        $expectedCompanyBusinessUnitIds = [$idFirstCompanyBusinessUnit, $idSecondCompanyBusinessUnit];
        sort($expectedCompanyBusinessUnitIds);
        $this->assertSame($expectedCompanyBusinessUnitIds, $this->getCompanyBusinessUnitIds($companyBusinessUnitTransfers));
    }

    public function testGetDataReturnsBusinessUnitsAccordingToOffsetAndLimit(): void
    {
        // Arrange
        $companyBusinessUnitIds = [];
        for ($i = 0; $i < 3; $i++) {
            $companyBusinessUnitIds[] = $this->findCompanyBusinessUnitIdByMerchantRelationship(
                $this->tester->haveProductAbstractPriceProductMerchantRelationship()->getPriceDimensionOrFail()->getIdMerchantRelationshipOrFail(),
            );
        }
        sort($companyBusinessUnitIds);

        // Act
        $companyBusinessUnitTransfers = (new MerchantRelationBusinessUnitEventResourceBulkRepositoryPlugin())->getData(1, 1);

        // Assert
        $this->assertSame([$companyBusinessUnitIds[1]], $this->getCompanyBusinessUnitIds($companyBusinessUnitTransfers));
    }

    public function testGetIdColumnNameMatchesTransferProperty(): void
    {
        // Arrange
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship(
            $this->tester->haveProductAbstractPriceProductMerchantRelationship()->getPriceDimensionOrFail()->getIdMerchantRelationshipOrFail(),
        );
        $plugin = new MerchantRelationBusinessUnitEventResourceBulkRepositoryPlugin();

        // Act
        $companyBusinessUnitTransfers = $plugin->getData(0, 1);

        // Assert
        $idColumnNameParts = explode(static::ID_COLUMN_SEPARATOR, (string)$plugin->getIdColumnName());
        $this->assertSame($idCompanyBusinessUnit, $companyBusinessUnitTransfers[0]->modifiedToArray()[$idColumnNameParts[1]]);
    }

    /**
     * @param array<\Generated\Shared\Transfer\CompanyBusinessUnitTransfer> $companyBusinessUnitTransfers
     *
     * @return array<int>
     */
    protected function getCompanyBusinessUnitIds(array $companyBusinessUnitTransfers): array
    {
        return array_map(
            fn (CompanyBusinessUnitTransfer $companyBusinessUnitTransfer): int => $companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
            $companyBusinessUnitTransfers,
        );
    }

    protected function findCompanyBusinessUnitIdByMerchantRelationship(int $idMerchantRelationship): int
    {
        return SpyMerchantRelationshipToCompanyBusinessUnitQuery::create()
            ->filterByFkMerchantRelationship($idMerchantRelationship)
            ->findOne()
            ->getFkCompanyBusinessUnit();
    }
}
