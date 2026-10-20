<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\PriceProductMerchantRelationshipStorage\Business;

use Codeception\Stub;
use Codeception\Test\Unit;
use Generated\Shared\Transfer\SynchronizationMessageTransfer;
use Orm\Zed\MerchantRelationship\Persistence\SpyMerchantRelationshipToCompanyBusinessUnitQuery;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyMerchantRelationBusinessUnitStorage;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyMerchantRelationBusinessUnitStorageQuery;
use Spryker\Shared\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageConstants;
use Spryker\Zed\PriceProductMerchantRelationshipStorage\Business\Model\MerchantRelationBusinessUnitStorageWriter;
use Spryker\Zed\PriceProductMerchantRelationshipStorage\Persistence\PriceProductMerchantRelationshipStorageEntityManager;
use Spryker\Zed\PriceProductMerchantRelationshipStorage\Persistence\PriceProductMerchantRelationshipStoragePersistenceFactory;
use Spryker\Zed\PriceProductMerchantRelationshipStorage\Persistence\PriceProductMerchantRelationshipStorageRepository;
use Spryker\Zed\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageConfig;
use Spryker\Zed\Synchronization\Business\SynchronizationFacadeInterface;
use SprykerTest\Zed\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group PriceProductMerchantRelationshipStorage
 * @group Business
 * @group MerchantRelationBusinessUnitStorageWriterTest
 * Add your own group annotations below this line
 */
class MerchantRelationBusinessUnitStorageWriterTest extends Unit
{
    protected const string SYNCHRONIZATION_FACADE_LOCATOR_CACHE_KEY = 'synchronization-facade';

    protected const string RESOURCE_NAME = 'mrbu';

    protected const int ID_COMPANY_BUSINESS_UNIT_WITHOUT_MERCHANT_RELATIONSHIP = 990001;

    protected const int ID_STALE_MERCHANT_RELATIONSHIP = 990101;

    protected PriceProductMerchantRelationshipStorageBusinessTester $tester;

    /**
     * @var array<\Generated\Shared\Transfer\SynchronizationMessageTransfer>
     */
    protected array $synchronizationMessageTransfers = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->tester->ensurePriceProductMerchantRelationshipTableIsEmpty();
    }

    public function testPublishByCompanyBusinessUnitIdsSendsSynchronizationMessageOnCreateWhenSendingToQueueIsEnabled(): void
    {
        // Arrange
        $idCompanyBusinessUnit = $this->haveCompanyBusinessUnitWithMerchantRelationship();
        $this->captureSynchronizationMessages();

        // Act
        $this->createWriter(true)->publishByCompanyBusinessUnitIds([$idCompanyBusinessUnit]);

        // Assert
        $this->assertSame(1, $this->countLookupStorageEntities($idCompanyBusinessUnit));
        $this->assertCount(1, $this->getLookupSynchronizationMessageTransfers());
    }

    public function testPublishByCompanyBusinessUnitIdsDoesNotSendSynchronizationMessageOnCreateWhenSendingToQueueIsDisabled(): void
    {
        // Arrange
        $idCompanyBusinessUnit = $this->haveCompanyBusinessUnitWithMerchantRelationship();
        $this->captureSynchronizationMessages();

        // Act
        $this->createWriter(false)->publishByCompanyBusinessUnitIds([$idCompanyBusinessUnit]);

        // Assert
        $this->assertSame(1, $this->countLookupStorageEntities($idCompanyBusinessUnit));
        $this->assertCount(0, $this->getLookupSynchronizationMessageTransfers());
    }

    public function testPublishByCompanyBusinessUnitIdsDoesNotSendSynchronizationMessageOnUpdateWhenSendingToQueueIsDisabled(): void
    {
        // Arrange
        $idCompanyBusinessUnit = $this->haveCompanyBusinessUnitWithMerchantRelationship();
        $this->haveLookupStorageEntity($idCompanyBusinessUnit, [static::ID_STALE_MERCHANT_RELATIONSHIP]);
        $this->captureSynchronizationMessages();

        // Act
        $this->createWriter(false)->publishByCompanyBusinessUnitIds([$idCompanyBusinessUnit]);

        // Assert
        $this->assertNotSame(
            [static::ID_STALE_MERCHANT_RELATIONSHIP],
            $this->getLookupStorageIdMerchantRelationships($idCompanyBusinessUnit),
        );
        $this->assertCount(0, $this->getLookupSynchronizationMessageTransfers());
    }

    public function testPublishByCompanyBusinessUnitIdsDoesNotSendSynchronizationMessageOnRemoveWhenSendingToQueueIsDisabled(): void
    {
        // Arrange
        $this->haveLookupStorageEntity(static::ID_COMPANY_BUSINESS_UNIT_WITHOUT_MERCHANT_RELATIONSHIP, [static::ID_STALE_MERCHANT_RELATIONSHIP]);
        $this->captureSynchronizationMessages();

        // Act
        $this->createWriter(false)->publishByCompanyBusinessUnitIds([static::ID_COMPANY_BUSINESS_UNIT_WITHOUT_MERCHANT_RELATIONSHIP]);

        // Assert
        $this->assertSame(0, $this->countLookupStorageEntities(static::ID_COMPANY_BUSINESS_UNIT_WITHOUT_MERCHANT_RELATIONSHIP));
        $this->assertCount(0, $this->getLookupSynchronizationMessageTransfers());
    }

    public function testPublishByCompanyBusinessUnitIdsSendsSynchronizationMessageOnRemoveWhenSendingToQueueIsEnabled(): void
    {
        // Arrange
        $this->haveLookupStorageEntity(static::ID_COMPANY_BUSINESS_UNIT_WITHOUT_MERCHANT_RELATIONSHIP, [static::ID_STALE_MERCHANT_RELATIONSHIP]);
        $this->captureSynchronizationMessages();

        // Act
        $this->createWriter(true)->publishByCompanyBusinessUnitIds([static::ID_COMPANY_BUSINESS_UNIT_WITHOUT_MERCHANT_RELATIONSHIP]);

        // Assert
        $this->assertSame(0, $this->countLookupStorageEntities(static::ID_COMPANY_BUSINESS_UNIT_WITHOUT_MERCHANT_RELATIONSHIP));
        $this->assertCount(1, $this->getLookupSynchronizationMessageTransfers());
    }

    public function testPublishByCompanyBusinessUnitIdsCreatesOneLookupStorageEntityWhenCompanyBusinessUnitIdIsDuplicated(): void
    {
        // Arrange
        $idCompanyBusinessUnit = $this->haveCompanyBusinessUnitWithMerchantRelationship();

        // Act
        $this->createWriter(false)->publishByCompanyBusinessUnitIds([$idCompanyBusinessUnit, $idCompanyBusinessUnit]);

        // Assert
        $this->assertSame(1, $this->countLookupStorageEntities($idCompanyBusinessUnit));
    }

    protected function createWriter(bool $isSendingToQueue): MerchantRelationBusinessUnitStorageWriter
    {
        $config = Stub::make(PriceProductMerchantRelationshipStorageConfig::class, [
            'isSendingToQueue' => $isSendingToQueue,
            'isMerchantRelationPriceKeyEnabled' => true,
        ]);

        $persistenceFactory = new PriceProductMerchantRelationshipStoragePersistenceFactory();
        $persistenceFactory->setConfig($config);

        $entityManager = new PriceProductMerchantRelationshipStorageEntityManager();
        $entityManager->setFactory($persistenceFactory);

        $repository = new PriceProductMerchantRelationshipStorageRepository();
        $repository->setFactory($persistenceFactory);

        return new MerchantRelationBusinessUnitStorageWriter($repository, $entityManager, $config);
    }

    protected function captureSynchronizationMessages(): void
    {
        $synchronizationFacadeMock = $this->createMock(SynchronizationFacadeInterface::class);
        $synchronizationFacadeMock->method('addSynchronizationMessageToBuffer')
            ->willReturnCallback(function (SynchronizationMessageTransfer $synchronizationMessageTransfer): void {
                $this->synchronizationMessageTransfers[] = $synchronizationMessageTransfer;
            });

        $this->tester->addToLocatorCache(static::SYNCHRONIZATION_FACADE_LOCATOR_CACHE_KEY, $synchronizationFacadeMock);
    }

    /**
     * @return array<\Generated\Shared\Transfer\SynchronizationMessageTransfer>
     */
    protected function getLookupSynchronizationMessageTransfers(): array
    {
        return array_values(array_filter(
            $this->synchronizationMessageTransfers,
            fn (SynchronizationMessageTransfer $synchronizationMessageTransfer): bool => $synchronizationMessageTransfer->getResource() === static::RESOURCE_NAME,
        ));
    }

    protected function haveCompanyBusinessUnitWithMerchantRelationship(): int
    {
        $idMerchantRelationship = $this->tester->haveProductAbstractPriceProductMerchantRelationship()
            ->getPriceDimensionOrFail()
            ->getIdMerchantRelationshipOrFail();

        return SpyMerchantRelationshipToCompanyBusinessUnitQuery::create()
            ->filterByFkMerchantRelationship($idMerchantRelationship)
            ->findOne()
            ->getFkCompanyBusinessUnit();
    }

    /**
     * @param array<int> $merchantRelationshipIds
     */
    protected function haveLookupStorageEntity(int $idCompanyBusinessUnit, array $merchantRelationshipIds): void
    {
        (new SpyMerchantRelationBusinessUnitStorage())
            ->setFkCompanyBusinessUnit($idCompanyBusinessUnit)
            ->setData([PriceProductMerchantRelationshipStorageConstants::MERCHANT_RELATION_BUSINESS_UNIT_DATA_KEY_MERCHANT_RELATIONSHIP_IDS => $merchantRelationshipIds])
            ->save();
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
