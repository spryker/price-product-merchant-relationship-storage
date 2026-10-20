<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\PriceProductMerchantRelationshipStorage\Communication\Plugin\Synchronization;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\EventEntityTransfer;
use Generated\Shared\Transfer\SynchronizationDataTransfer;
use Orm\Zed\MerchantRelationship\Persistence\SpyMerchantRelationshipToCompanyBusinessUnitQuery;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyMerchantRelationBusinessUnitStorage;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyMerchantRelationBusinessUnitStorageQuery;
use Spryker\Shared\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageConfig as SharedPriceProductMerchantRelationshipStorageConfig;
use Spryker\Zed\PriceProductMerchantRelationshipStorage\Communication\Plugin\Event\Listener\MerchantRelationBusinessUnitPublishListener;
use Spryker\Zed\PriceProductMerchantRelationshipStorage\Communication\Plugin\Synchronization\MerchantRelationBusinessUnitSynchronizationDataBulkPlugin;
use SprykerTest\Zed\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageCommunicationTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group PriceProductMerchantRelationshipStorage
 * @group Communication
 * @group Plugin
 * @group Synchronization
 * @group MerchantRelationBusinessUnitSynchronizationDataBulkPluginTest
 * Add your own group annotations below this line
 */
class MerchantRelationBusinessUnitSynchronizationDataBulkPluginTest extends Unit
{
    protected const string RESOURCE_NAME = 'mrbu';

    protected const string QUEUE_NAME = 'sync.storage.price';

    protected const string DATA_KEY_MERCHANT_RELATIONSHIP_IDS = 'idmr';

    protected PriceProductMerchantRelationshipStorageCommunicationTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tester->ensurePriceProductMerchantRelationshipTableIsEmpty();
    }

    public function testGetResourceNameReturnsLookupResourceName(): void
    {
        // Act
        $resourceName = (new MerchantRelationBusinessUnitSynchronizationDataBulkPlugin())->getResourceName();

        // Assert
        $this->assertSame(static::RESOURCE_NAME, $resourceName);
    }

    public function testHasStoreReturnsFalse(): void
    {
        // Act
        $hasStore = (new MerchantRelationBusinessUnitSynchronizationDataBulkPlugin())->hasStore();

        // Assert
        $this->assertFalse($hasStore);
    }

    public function testGetQueueNameReturnsPriceStorageSynchronizationQueue(): void
    {
        // Act
        $queueName = (new MerchantRelationBusinessUnitSynchronizationDataBulkPlugin())->getQueueName();

        // Assert
        $this->assertSame(static::QUEUE_NAME, $queueName);
    }

    public function testGetParamsReturnsEmptyArray(): void
    {
        // Act
        $params = (new MerchantRelationBusinessUnitSynchronizationDataBulkPlugin())->getParams();

        // Assert
        $this->assertSame([], $params);
    }

    public function testGetDataReturnsLookupKeyAndMerchantRelationshipIds(): void
    {
        // Arrange
        $idMerchantRelationship = $this->tester->haveProductAbstractPriceProductMerchantRelationship()
            ->getPriceDimensionOrFail()
            ->getIdMerchantRelationshipOrFail();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship($idMerchantRelationship);
        $merchantRelationBusinessUnitStorageEntity = $this->publishLookup($idCompanyBusinessUnit);

        // Act
        $synchronizationDataTransfers = (new MerchantRelationBusinessUnitSynchronizationDataBulkPlugin())->getData(
            0,
            10,
            [(int)$merchantRelationBusinessUnitStorageEntity->getIdMerchantRelationBusinessUnitStorage()],
        );

        // Assert
        $this->assertCount(1, $synchronizationDataTransfers);
        $this->assertSame($this->generateClientLookupKey($idCompanyBusinessUnit), $synchronizationDataTransfers[0]->getKey());
        $this->assertSame(
            [static::DATA_KEY_MERCHANT_RELATIONSHIP_IDS => [$idMerchantRelationship]],
            $synchronizationDataTransfers[0]->getData(),
        );
    }

    /**
     * The Client reads the lookup with the key from `PriceProductMerchantRelationshipKeyGenerator::generateKeyByCompanyBusinessUnit()`,
     * which builds it from the resource name and the bare business unit ID, without a store.
     */
    public function testGetDataReturnsKeyThatClientKeyGeneratorBuilds(): void
    {
        // Arrange
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship(
            $this->tester->haveProductAbstractPriceProductMerchantRelationship()->getPriceDimensionOrFail()->getIdMerchantRelationshipOrFail(),
        );
        $merchantRelationBusinessUnitStorageEntity = $this->publishLookup($idCompanyBusinessUnit);

        // Act
        $synchronizationDataTransfers = (new MerchantRelationBusinessUnitSynchronizationDataBulkPlugin())->getData(
            0,
            10,
            [(int)$merchantRelationBusinessUnitStorageEntity->getIdMerchantRelationBusinessUnitStorage()],
        );

        // Assert
        $this->assertSame(sprintf('%s:%d', static::RESOURCE_NAME, $idCompanyBusinessUnit), $synchronizationDataTransfers[0]->getKey());
    }

    public function testGetDataReturnsLookupsAccordingToOffsetAndLimit(): void
    {
        // Arrange
        $merchantRelationBusinessUnitStorageIds = [];
        for ($i = 0; $i < 3; $i++) {
            $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship(
                $this->tester->haveProductAbstractPriceProductMerchantRelationship()->getPriceDimensionOrFail()->getIdMerchantRelationshipOrFail(),
            );
            $merchantRelationBusinessUnitStorageIds[] = (int)$this->publishLookup($idCompanyBusinessUnit)->getIdMerchantRelationBusinessUnitStorage();
        }

        // Act
        $synchronizationDataTransfers = (new MerchantRelationBusinessUnitSynchronizationDataBulkPlugin())->getData(1, 1, $merchantRelationBusinessUnitStorageIds);

        // Assert
        $this->assertCount(1, $synchronizationDataTransfers);
    }

    protected function publishLookup(int $idCompanyBusinessUnit): SpyMerchantRelationBusinessUnitStorage
    {
        $merchantRelationBusinessUnitPublishListener = new MerchantRelationBusinessUnitPublishListener();
        $merchantRelationBusinessUnitPublishListener->setBusinessFactory($this->tester->createBusinessFactoryWithMerchantRelationPriceKeyFlag(true));
        $merchantRelationBusinessUnitPublishListener->handleBulk(
            [(new EventEntityTransfer())->setId($idCompanyBusinessUnit)],
            SharedPriceProductMerchantRelationshipStorageConfig::MERCHANT_RELATION_BUSINESS_UNIT_PUBLISH,
        );

        return SpyMerchantRelationBusinessUnitStorageQuery::create()
            ->filterByFkCompanyBusinessUnit($idCompanyBusinessUnit)
            ->findOne();
    }

    protected function generateClientLookupKey(int $idCompanyBusinessUnit): string
    {
        return $this->tester->getLocator()
            ->synchronization()
            ->service()
            ->getStorageKeyBuilder(static::RESOURCE_NAME)
            ->generateKey((new SynchronizationDataTransfer())->setReference((string)$idCompanyBusinessUnit));
    }

    protected function findCompanyBusinessUnitIdByMerchantRelationship(int $idMerchantRelationship): int
    {
        return SpyMerchantRelationshipToCompanyBusinessUnitQuery::create()
            ->filterByFkMerchantRelationship($idMerchantRelationship)
            ->findOne()
            ->getFkCompanyBusinessUnit();
    }
}
