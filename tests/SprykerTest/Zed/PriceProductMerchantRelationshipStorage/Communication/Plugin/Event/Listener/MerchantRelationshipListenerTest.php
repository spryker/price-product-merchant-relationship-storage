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
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductAbstractMerchantRelationshipStorage;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductAbstractMerchantRelationshipStorageQuery;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductConcreteMerchantRelationshipStorage;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductConcreteMerchantRelationshipStorageQuery;
use Spryker\Shared\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageConstants;
use Spryker\Zed\MerchantRelationship\Dependency\MerchantRelationshipEvents;
use Spryker\Zed\PriceProductMerchantRelationshipStorage\Communication\Plugin\Event\Listener\MerchantRelationshipListener;
use Spryker\Zed\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageConfig;

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
 * @group MerchantRelationshipListenerTest
 * Add your own group annotations below this line
 */
class MerchantRelationshipListenerTest extends Unit
{
    /**
     * @var int<1, max>
     */
    protected const int CHUNK_SIZE = 1;

    /**
     * @var int<1, max>
     */
    protected const int BUSINESS_UNIT_COUNT = 3;

    protected const string COL_FK_MERCHANT_RELATIONSHIP = 'spy_merchant_relationship_to_company_business_unit.fk_merchant_relationship';

    /**
     * @var \SprykerTest\Zed\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageCommunicationTester
     */
    protected $tester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tester->ensurePriceProductMerchantRelationshipTableIsEmpty();
    }

    protected function createListener(bool $isMerchantRelationPriceKeyEnabled): MerchantRelationshipListener
    {
        $merchantRelationshipListener = new MerchantRelationshipListener();
        $merchantRelationshipListener->setBusinessFactory($this->tester->createBusinessFactoryWithMerchantRelationPriceKeyFlag($isMerchantRelationPriceKeyEnabled));

        return $merchantRelationshipListener;
    }

    public function testHandleBulkPublishesAbstractPriceStorageForBusinessUnit(): void
    {
        // Arrange
        $priceProductTransfer = $this->tester->haveProductAbstractPriceProductMerchantRelationship();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship(
            $priceProductTransfer->getPriceDimensionOrFail()->getIdMerchantRelationshipOrFail(),
        );

        $this->assertSame(
            0,
            $this->countAbstractStorageEntities($idCompanyBusinessUnit),
            'Expected no abstract storage entities before publishing.',
        );

        // Act
        $this->createListener(true)->handleBulk(
            [$this->createEventEntityTransfer($idCompanyBusinessUnit)],
            MerchantRelationshipEvents::ENTITY_SPY_MERCHANT_RELATIONSHIP_TO_COMPANY_BUSINESS_UNIT_UPDATE,
        );

        // Assert
        $this->assertGreaterThan(
            0,
            $this->countAbstractStorageEntities($idCompanyBusinessUnit),
            'Expected abstract storage entities to be published for the business unit.',
        );
    }

    public function testHandleBulkPublishesMerchantRelationBusinessUnitLookupForBusinessUnit(): void
    {
        // Arrange
        $priceProductTransfer = $this->tester->haveProductAbstractPriceProductMerchantRelationship();
        $idMerchantRelationship = $priceProductTransfer->getPriceDimensionOrFail()->getIdMerchantRelationshipOrFail();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship($idMerchantRelationship);

        $this->assertSame(
            0,
            $this->countLookupStorageEntities($idCompanyBusinessUnit),
            'Expected no lookup storage entity before publishing.',
        );

        // Act
        $this->createListener(true)->handleBulk(
            [$this->createEventEntityTransfer($idCompanyBusinessUnit)],
            MerchantRelationshipEvents::ENTITY_SPY_MERCHANT_RELATIONSHIP_TO_COMPANY_BUSINESS_UNIT_UPDATE,
        );

        // Assert
        $this->assertSame(
            [$idMerchantRelationship],
            $this->getLookupStorageIdMerchantRelationships($idCompanyBusinessUnit),
            'Expected the lookup storage entity to list the business unit\'s merchant relationship.',
        );
    }

    public function testHandleBulkDoesNotPublishMerchantRelationBusinessUnitLookupWhenFlagDisabled(): void
    {
        // Arrange
        $priceProductTransfer = $this->tester->haveProductAbstractPriceProductMerchantRelationship();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship(
            $priceProductTransfer->getPriceDimensionOrFail()->getIdMerchantRelationshipOrFail(),
        );

        // Act
        $this->createListener(false)->handleBulk(
            [$this->createEventEntityTransfer($idCompanyBusinessUnit)],
            MerchantRelationshipEvents::ENTITY_SPY_MERCHANT_RELATIONSHIP_TO_COMPANY_BUSINESS_UNIT_UPDATE,
        );

        // Assert
        $this->assertSame(
            0,
            $this->countLookupStorageEntities($idCompanyBusinessUnit),
            'Expected no lookup storage entity to be published while the flag is disabled.',
        );
    }

    public function testHandleBulkRepublishUpdatesTheSameLookupRowInsteadOfDuplicatingIt(): void
    {
        // Arrange
        $priceProductTransfer = $this->tester->haveProductAbstractPriceProductMerchantRelationship();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship(
            $priceProductTransfer->getPriceDimensionOrFail()->getIdMerchantRelationshipOrFail(),
        );

        $this->createListener(true)->handleBulk(
            [$this->createEventEntityTransfer($idCompanyBusinessUnit)],
            MerchantRelationshipEvents::ENTITY_SPY_MERCHANT_RELATIONSHIP_TO_COMPANY_BUSINESS_UNIT_UPDATE,
        );

        $this->assertSame(1, $this->countLookupStorageEntities($idCompanyBusinessUnit));

        // Act
        $this->createListener(true)->handleBulk(
            [$this->createEventEntityTransfer($idCompanyBusinessUnit)],
            MerchantRelationshipEvents::ENTITY_SPY_MERCHANT_RELATIONSHIP_TO_COMPANY_BUSINESS_UNIT_UPDATE,
        );

        // Assert
        $this->assertSame(
            1,
            $this->countLookupStorageEntities($idCompanyBusinessUnit),
            'Expected the re-publish to update the existing lookup row, not create a second one.',
        );
    }

    public function testHandleBulkPublishesConcretePriceStorageForBusinessUnit(): void
    {
        // Arrange
        $priceProductTransfer = $this->tester->haveProductConcretePriceProductMerchantRelationship();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship(
            $priceProductTransfer->getPriceDimensionOrFail()->getIdMerchantRelationshipOrFail(),
        );

        $this->assertSame(
            0,
            $this->countConcreteStorageEntities($idCompanyBusinessUnit),
            'Expected no concrete storage entities before publishing.',
        );

        // Act
        $this->createListener(true)->handleBulk(
            [$this->createEventEntityTransfer($idCompanyBusinessUnit)],
            MerchantRelationshipEvents::ENTITY_SPY_MERCHANT_RELATIONSHIP_TO_COMPANY_BUSINESS_UNIT_UPDATE,
        );

        // Assert
        $this->assertGreaterThan(
            0,
            $this->countConcreteStorageEntities($idCompanyBusinessUnit),
            'Expected concrete storage entities to be published for the business unit.',
        );
    }

    public function testHandleBulkStoresSingleAbstractStorageEntityPerPriceKeyOnFirstPublish(): void
    {
        // Arrange
        $priceProductTransfer = $this->tester->haveProductAbstractPriceProductMerchantRelationship();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship(
            $priceProductTransfer->getPriceDimensionOrFail()->getIdMerchantRelationshipOrFail(),
        );

        // Act
        $this->createListener(true)->handleBulk(
            [$this->createEventEntityTransfer($idCompanyBusinessUnit)],
            MerchantRelationshipEvents::ENTITY_SPY_MERCHANT_RELATIONSHIP_TO_COMPANY_BUSINESS_UNIT_UPDATE,
        );

        // Assert
        $abstractStoragePriceKeys = $this->getAbstractStoragePriceKeys($idCompanyBusinessUnit);
        $this->assertNotEmpty($abstractStoragePriceKeys, 'Expected abstract storage entities after the first publish.');
        $this->assertSame(
            count(array_unique($abstractStoragePriceKeys)),
            count($abstractStoragePriceKeys),
            'Expected exactly one abstract storage entity per price key on the first publish.',
        );
    }

    public function testHandleBulkCollapsesDuplicateAbstractStorageEntitiesToSinglePerPriceKey(): void
    {
        // Arrange
        $priceProductTransfer = $this->tester->haveProductAbstractPriceProductMerchantRelationship();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship(
            $priceProductTransfer->getPriceDimensionOrFail()->getIdMerchantRelationshipOrFail(),
        );

        $this->createListener(false)->handleBulk(
            [$this->createEventEntityTransfer($idCompanyBusinessUnit)],
            MerchantRelationshipEvents::ENTITY_SPY_MERCHANT_RELATIONSHIP_TO_COMPANY_BUSINESS_UNIT_UPDATE,
        );

        $expectedPriceKeyCount = count(array_unique($this->getAbstractStoragePriceKeys($idCompanyBusinessUnit)));
        $this->duplicateAbstractStorageEntities($idCompanyBusinessUnit);

        $this->assertGreaterThan(
            $expectedPriceKeyCount,
            $this->countAbstractStorageEntities($idCompanyBusinessUnit),
            'Expected duplicated abstract storage entities to be present before the re-publish.',
        );

        // Act
        $this->createListener(false)->handleBulk(
            [$this->createEventEntityTransfer($idCompanyBusinessUnit)],
            MerchantRelationshipEvents::ENTITY_SPY_MERCHANT_RELATIONSHIP_TO_COMPANY_BUSINESS_UNIT_UPDATE,
        );

        // Assert
        $abstractStoragePriceKeys = $this->getAbstractStoragePriceKeys($idCompanyBusinessUnit);
        $this->assertSame(
            $expectedPriceKeyCount,
            count($abstractStoragePriceKeys),
            'Expected the re-publish to collapse duplicates back to one abstract storage entity per price key.',
        );
        $this->assertSame(
            count(array_unique($abstractStoragePriceKeys)),
            count($abstractStoragePriceKeys),
            'Expected no duplicate abstract storage price keys after the re-publish.',
        );
    }

    public function testHandleBulkStoresSingleConcreteStorageEntityPerPriceKeyOnFirstPublish(): void
    {
        // Arrange
        $priceProductTransfer = $this->tester->haveProductConcretePriceProductMerchantRelationship();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship(
            $priceProductTransfer->getPriceDimensionOrFail()->getIdMerchantRelationshipOrFail(),
        );

        // Act
        $this->createListener(true)->handleBulk(
            [$this->createEventEntityTransfer($idCompanyBusinessUnit)],
            MerchantRelationshipEvents::ENTITY_SPY_MERCHANT_RELATIONSHIP_TO_COMPANY_BUSINESS_UNIT_UPDATE,
        );

        // Assert
        $concreteStoragePriceKeys = $this->getConcreteStoragePriceKeys($idCompanyBusinessUnit);
        $this->assertNotEmpty($concreteStoragePriceKeys, 'Expected concrete storage entities after the first publish.');
        $this->assertSame(
            count(array_unique($concreteStoragePriceKeys)),
            count($concreteStoragePriceKeys),
            'Expected exactly one concrete storage entity per price key on the first publish.',
        );
    }

    public function testHandleBulkCollapsesDuplicateConcreteStorageEntitiesToSinglePerPriceKey(): void
    {
        // Arrange
        $priceProductTransfer = $this->tester->haveProductConcretePriceProductMerchantRelationship();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship(
            $priceProductTransfer->getPriceDimensionOrFail()->getIdMerchantRelationshipOrFail(),
        );

        $this->createListener(false)->handleBulk(
            [$this->createEventEntityTransfer($idCompanyBusinessUnit)],
            MerchantRelationshipEvents::ENTITY_SPY_MERCHANT_RELATIONSHIP_TO_COMPANY_BUSINESS_UNIT_UPDATE,
        );

        $expectedPriceKeyCount = count(array_unique($this->getConcreteStoragePriceKeys($idCompanyBusinessUnit)));
        $this->duplicateConcreteStorageEntities($idCompanyBusinessUnit);

        $this->assertGreaterThan(
            $expectedPriceKeyCount,
            $this->countConcreteStorageEntities($idCompanyBusinessUnit),
            'Expected duplicated concrete storage entities to be present before the re-publish.',
        );

        // Act
        $this->createListener(false)->handleBulk(
            [$this->createEventEntityTransfer($idCompanyBusinessUnit)],
            MerchantRelationshipEvents::ENTITY_SPY_MERCHANT_RELATIONSHIP_TO_COMPANY_BUSINESS_UNIT_UPDATE,
        );

        // Assert
        $concreteStoragePriceKeys = $this->getConcreteStoragePriceKeys($idCompanyBusinessUnit);
        $this->assertSame(
            $expectedPriceKeyCount,
            count($concreteStoragePriceKeys),
            'Expected the re-publish to collapse duplicates back to one concrete storage entity per price key.',
        );
        $this->assertSame(
            count(array_unique($concreteStoragePriceKeys)),
            count($concreteStoragePriceKeys),
            'Expected no duplicate concrete storage price keys after the re-publish.',
        );
    }

    public function testHandleBulkDoesNotRepublishAbstractPricesOfMerchantRelationshipWithStoredPricesWhenFlagEnabled(): void
    {
        // Arrange
        $priceProductTransfer = $this->tester->haveProductAbstractPriceProductMerchantRelationship();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship(
            $priceProductTransfer->getPriceDimensionOrFail()->getIdMerchantRelationshipOrFail(),
        );

        $this->createListener(true)->handleBulk(
            [$this->createEventEntityTransfer($idCompanyBusinessUnit)],
            MerchantRelationshipEvents::ENTITY_SPY_MERCHANT_RELATIONSHIP_TO_COMPANY_BUSINESS_UNIT_CREATE,
        );

        $this->duplicateAbstractStorageEntities($idCompanyBusinessUnit);
        $expectedAbstractStorageEntityCount = $this->countAbstractStorageEntities($idCompanyBusinessUnit);

        // Act
        $this->createListener(true)->handleBulk(
            [$this->createEventEntityTransfer($idCompanyBusinessUnit)],
            MerchantRelationshipEvents::ENTITY_SPY_MERCHANT_RELATIONSHIP_TO_COMPANY_BUSINESS_UNIT_UPDATE,
        );

        // Assert
        $this->assertSame(
            $expectedAbstractStorageEntityCount,
            $this->countAbstractStorageEntities($idCompanyBusinessUnit),
            'Expected the abstract prices of a merchant relationship with stored prices not to be republished.',
        );
    }

    public function testHandleBulkDeletesAbstractPricesWhenMerchantRelationshipLosesLastBusinessUnitAndFlagEnabled(): void
    {
        // Arrange
        $priceProductTransfer = $this->tester->haveProductAbstractPriceProductMerchantRelationship();
        $idMerchantRelationship = $priceProductTransfer->getPriceDimensionOrFail()->getIdMerchantRelationshipOrFail();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship($idMerchantRelationship);

        $this->createListener(true)->handleBulk(
            [$this->createEventEntityTransfer($idCompanyBusinessUnit)],
            MerchantRelationshipEvents::ENTITY_SPY_MERCHANT_RELATIONSHIP_TO_COMPANY_BUSINESS_UNIT_CREATE,
        );

        $this->assertGreaterThan(0, $this->countAbstractStorageEntitiesByMerchantRelationship($idMerchantRelationship));

        $this->deleteMerchantRelationshipToCompanyBusinessUnit($idMerchantRelationship);

        // Act
        $this->createListener(true)->handleBulk(
            [$this->createEventEntityTransfer($idCompanyBusinessUnit, $idMerchantRelationship)],
            MerchantRelationshipEvents::ENTITY_SPY_MERCHANT_RELATIONSHIP_TO_COMPANY_BUSINESS_UNIT_DELETE,
        );

        // Assert
        $this->assertSame(
            0,
            $this->countAbstractStorageEntitiesByMerchantRelationship($idMerchantRelationship),
            'Expected the abstract prices to be deleted when the merchant relationship loses its last business unit.',
        );
    }

    public function testHandleBulkDeletesConcretePricesWhenMerchantRelationshipLosesLastBusinessUnitAndFlagEnabled(): void
    {
        // Arrange
        $priceProductTransfer = $this->tester->haveProductConcretePriceProductMerchantRelationship();
        $idMerchantRelationship = $priceProductTransfer->getPriceDimensionOrFail()->getIdMerchantRelationshipOrFail();
        $idCompanyBusinessUnit = $this->findCompanyBusinessUnitIdByMerchantRelationship($idMerchantRelationship);

        $this->createListener(true)->handleBulk(
            [$this->createEventEntityTransfer($idCompanyBusinessUnit)],
            MerchantRelationshipEvents::ENTITY_SPY_MERCHANT_RELATIONSHIP_TO_COMPANY_BUSINESS_UNIT_CREATE,
        );

        $this->assertGreaterThan(0, $this->countConcreteStorageEntitiesByMerchantRelationship($idMerchantRelationship));

        $this->deleteMerchantRelationshipToCompanyBusinessUnit($idMerchantRelationship);

        // Act
        $this->createListener(true)->handleBulk(
            [$this->createEventEntityTransfer($idCompanyBusinessUnit, $idMerchantRelationship)],
            MerchantRelationshipEvents::ENTITY_SPY_MERCHANT_RELATIONSHIP_TO_COMPANY_BUSINESS_UNIT_DELETE,
        );

        // Assert
        $this->assertSame(
            0,
            $this->countConcreteStorageEntitiesByMerchantRelationship($idMerchantRelationship),
            'Expected the concrete prices to be deleted when the merchant relationship loses its last business unit.',
        );
    }

    public function testHandleBulkPublishesAbstractPriceStorageForEveryBusinessUnitAcrossMultipleChunks(): void
    {
        // Arrange
        $this->tester->mockConfigMethod('getCompanyBusinessUnitChunkSize', static::CHUNK_SIZE);
        $this->tester->mockConfigMethod('getProductChunkSize', static::CHUNK_SIZE);

        $companyBusinessUnitIds = $this->haveAbstractPriceCompanyBusinessUnitIds(static::BUSINESS_UNIT_COUNT);

        $this->assertGreaterThan(
            static::CHUNK_SIZE,
            count($companyBusinessUnitIds),
            'Expected the fixture to span more than one chunk.',
        );

        // Act
        $this->tester->createBusinessFactoryWithMerchantRelationPriceKeyFlag(true)->createPriceProductAbstractStorageWriter()->publishByCompanyBusinessUnitIds($companyBusinessUnitIds);

        // Assert
        foreach ($companyBusinessUnitIds as $idCompanyBusinessUnit) {
            $this->assertGreaterThan(
                0,
                $this->countAbstractStorageEntities($idCompanyBusinessUnit),
                sprintf('Expected abstract storage entities for business unit %d in every chunk.', $idCompanyBusinessUnit),
            );
        }
    }

    public function testHandleBulkPublishesConcretePriceStorageForEveryBusinessUnitAcrossMultipleChunks(): void
    {
        // Arrange
        $this->tester->mockConfigMethod('getCompanyBusinessUnitChunkSize', static::CHUNK_SIZE);
        $this->tester->mockConfigMethod('getProductChunkSize', static::CHUNK_SIZE);

        $companyBusinessUnitIds = $this->haveConcretePriceCompanyBusinessUnitIds(static::BUSINESS_UNIT_COUNT);

        $this->assertGreaterThan(
            static::CHUNK_SIZE,
            count($companyBusinessUnitIds),
            'Expected the fixture to span more than one chunk.',
        );

        // Act
        $this->tester->createBusinessFactoryWithMerchantRelationPriceKeyFlag(true)->createPriceProductConcreteStorageWriter()->publishByCompanyBusinessUnitIds($companyBusinessUnitIds);

        // Assert
        foreach ($companyBusinessUnitIds as $idCompanyBusinessUnit) {
            $this->assertGreaterThan(
                0,
                $this->countConcreteStorageEntities($idCompanyBusinessUnit),
                sprintf('Expected concrete storage entities for business unit %d in every chunk.', $idCompanyBusinessUnit),
            );
        }
    }

    public function testHandleBulkProducesSameAbstractStorageStateRegardlessOfChunkSize(): void
    {
        // Arrange
        $companyBusinessUnitIds = $this->haveAbstractPriceCompanyBusinessUnitIds(static::BUSINESS_UNIT_COUNT);

        $this->createListener(true)->handleBulk(
            $this->createEventEntityTransfers($companyBusinessUnitIds),
            MerchantRelationshipEvents::ENTITY_SPY_MERCHANT_RELATIONSHIP_TO_COMPANY_BUSINESS_UNIT_UPDATE,
        );

        $singleChunkPriceKeys = $this->getAbstractStoragePriceKeysForCompanyBusinessUnitIds($companyBusinessUnitIds);
        $this->assertNotEmpty($singleChunkPriceKeys, 'Expected the single-chunk publish to produce storage entities.');

        $this->deleteAbstractStorageEntities($companyBusinessUnitIds);

        // Act
        $this->tester->mockConfigMethod('getCompanyBusinessUnitChunkSize', static::CHUNK_SIZE);
        $this->tester->mockConfigMethod('getProductChunkSize', static::CHUNK_SIZE);

        $this->tester->createBusinessFactoryWithMerchantRelationPriceKeyFlag(true)->createPriceProductAbstractStorageWriter()->publishByCompanyBusinessUnitIds($companyBusinessUnitIds);

        // Assert
        $this->assertSame(
            $singleChunkPriceKeys,
            $this->getAbstractStoragePriceKeysForCompanyBusinessUnitIds($companyBusinessUnitIds),
            'Expected chunked publishing to produce the same storage state as a single chunk.',
        );
    }

    /**
     * @param int $count
     *
     * @return array<int>
     */
    protected function haveAbstractPriceCompanyBusinessUnitIds(int $count): array
    {
        $companyBusinessUnitIds = [];
        for ($i = 0; $i < $count; $i++) {
            $priceProductTransfer = $this->tester->haveProductAbstractPriceProductMerchantRelationship();
            $companyBusinessUnitIds[] = $this->findCompanyBusinessUnitIdByMerchantRelationship(
                $priceProductTransfer->getPriceDimensionOrFail()->getIdMerchantRelationshipOrFail(),
            );
        }

        return $companyBusinessUnitIds;
    }

    /**
     * @param int $count
     *
     * @return array<int>
     */
    protected function haveConcretePriceCompanyBusinessUnitIds(int $count): array
    {
        $companyBusinessUnitIds = [];
        for ($i = 0; $i < $count; $i++) {
            $priceProductTransfer = $this->tester->haveProductConcretePriceProductMerchantRelationship();
            $companyBusinessUnitIds[] = $this->findCompanyBusinessUnitIdByMerchantRelationship(
                $priceProductTransfer->getPriceDimensionOrFail()->getIdMerchantRelationshipOrFail(),
            );
        }

        return $companyBusinessUnitIds;
    }

    /**
     * @param array<int> $companyBusinessUnitIds
     *
     * @return array<\Generated\Shared\Transfer\EventEntityTransfer>
     */
    protected function createEventEntityTransfers(array $companyBusinessUnitIds): array
    {
        $eventEntityTransfers = [];
        foreach ($companyBusinessUnitIds as $idCompanyBusinessUnit) {
            $eventEntityTransfers[] = $this->createEventEntityTransfer($idCompanyBusinessUnit);
        }

        return $eventEntityTransfers;
    }

    /**
     * @param array<int> $companyBusinessUnitIds
     *
     * @return void
     */
    protected function deleteAbstractStorageEntities(array $companyBusinessUnitIds): void
    {
        SpyPriceProductAbstractMerchantRelationshipStorageQuery::create()
            ->filterByFkCompanyBusinessUnit_In($companyBusinessUnitIds)
            ->_or()
            ->filterByFkMerchantRelationship_In($this->resolveMerchantRelationshipIds($companyBusinessUnitIds))
            ->delete();
    }

    /**
     * @param array<int> $companyBusinessUnitIds
     *
     * @return array<int>
     */
    protected function resolveMerchantRelationshipIds(array $companyBusinessUnitIds): array
    {
        return array_map(
            fn (int $idCompanyBusinessUnit): int => $this->resolveMerchantRelationshipId($idCompanyBusinessUnit),
            $companyBusinessUnitIds,
        );
    }

    protected function resolveMerchantRelationshipId(int $idCompanyBusinessUnit): int
    {
        $merchantRelationshipToCompanyBusinessUnitEntity = SpyMerchantRelationshipToCompanyBusinessUnitQuery::create()
            ->filterByFkCompanyBusinessUnit($idCompanyBusinessUnit)
            ->findOne();

        return $merchantRelationshipToCompanyBusinessUnitEntity->getFkMerchantRelationship();
    }

    /**
     * The write side keys by merchant relationship when the flag is on (§3.1) and by business unit otherwise;
     * these helpers query whichever discriminator is currently active so the fixtures stay flag-agnostic.
     */
    protected function isMerchantRelationPriceKeyEnabled(): bool
    {
        return $this->tester->getFactory()->getConfig()->isMerchantRelationPriceKeyEnabled();
    }

    /**
     * @param array<int> $companyBusinessUnitIds
     *
     * @return array<string>
     */
    protected function getAbstractStoragePriceKeysForCompanyBusinessUnitIds(array $companyBusinessUnitIds): array
    {
        $priceKeys = [];
        foreach ($companyBusinessUnitIds as $idCompanyBusinessUnit) {
            $priceKeys = array_merge($priceKeys, $this->getAbstractStoragePriceKeys($idCompanyBusinessUnit));
        }

        sort($priceKeys);

        return $priceKeys;
    }

    /**
     * @param int $idCompanyBusinessUnit
     *
     * @return array<string>
     */
    protected function getAbstractStoragePriceKeys(int $idCompanyBusinessUnit): array
    {
        $priceKeys = [];

        foreach ($this->findAbstractStorageEntities($idCompanyBusinessUnit) as $priceProductAbstractMerchantRelationshipStorageEntity) {
            $priceKeys[] = $priceProductAbstractMerchantRelationshipStorageEntity->getPriceKey();
        }

        return $priceKeys;
    }

    /**
     * @return array<\Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductAbstractMerchantRelationshipStorage>
     */
    protected function findAbstractStorageEntities(int $idCompanyBusinessUnit): array
    {
        $query = SpyPriceProductAbstractMerchantRelationshipStorageQuery::create();

        if ($this->isMerchantRelationPriceKeyEnabled()) {
            $query->filterByFkMerchantRelationship($this->resolveMerchantRelationshipId($idCompanyBusinessUnit));
        } else {
            $query->filterByFkCompanyBusinessUnit($idCompanyBusinessUnit);
        }

        return $query->find()->getData();
    }

    /**
     * @param int $idCompanyBusinessUnit
     *
     * @return array<string>
     */
    protected function getConcreteStoragePriceKeys(int $idCompanyBusinessUnit): array
    {
        $priceKeys = [];

        foreach ($this->findConcreteStorageEntities($idCompanyBusinessUnit) as $priceProductConcreteMerchantRelationshipStorageEntity) {
            $priceKeys[] = $priceProductConcreteMerchantRelationshipStorageEntity->getPriceKey();
        }

        return $priceKeys;
    }

    /**
     * @return array<\Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductConcreteMerchantRelationshipStorage>
     */
    protected function findConcreteStorageEntities(int $idCompanyBusinessUnit): array
    {
        $query = SpyPriceProductConcreteMerchantRelationshipStorageQuery::create();

        if ($this->isMerchantRelationPriceKeyEnabled()) {
            $query->filterByFkMerchantRelationship($this->resolveMerchantRelationshipId($idCompanyBusinessUnit));
        } else {
            $query->filterByFkCompanyBusinessUnit($idCompanyBusinessUnit);
        }

        return $query->find()->getData();
    }

    protected function duplicateAbstractStorageEntities(int $idCompanyBusinessUnit): void
    {
        foreach ($this->findAbstractStorageEntities($idCompanyBusinessUnit) as $priceProductAbstractMerchantRelationshipStorageEntity) {
            (new SpyPriceProductAbstractMerchantRelationshipStorage())
                ->setFkProductAbstract($priceProductAbstractMerchantRelationshipStorageEntity->getFkProductAbstract())
                ->setFkCompanyBusinessUnit($priceProductAbstractMerchantRelationshipStorageEntity->getFkCompanyBusinessUnit())
                ->setFkMerchantRelationship($priceProductAbstractMerchantRelationshipStorageEntity->getFkMerchantRelationship())
                ->setPriceKey($priceProductAbstractMerchantRelationshipStorageEntity->getPriceKey())
                ->setKey($priceProductAbstractMerchantRelationshipStorageEntity->getKey())
                ->setData($priceProductAbstractMerchantRelationshipStorageEntity->getData())
                ->save();
        }
    }

    protected function duplicateConcreteStorageEntities(int $idCompanyBusinessUnit): void
    {
        foreach ($this->findConcreteStorageEntities($idCompanyBusinessUnit) as $priceProductConcreteMerchantRelationshipStorageEntity) {
            (new SpyPriceProductConcreteMerchantRelationshipStorage())
                ->setFkProduct($priceProductConcreteMerchantRelationshipStorageEntity->getFkProduct())
                ->setFkCompanyBusinessUnit($priceProductConcreteMerchantRelationshipStorageEntity->getFkCompanyBusinessUnit())
                ->setFkMerchantRelationship($priceProductConcreteMerchantRelationshipStorageEntity->getFkMerchantRelationship())
                ->setPriceKey($priceProductConcreteMerchantRelationshipStorageEntity->getPriceKey())
                ->setKey($priceProductConcreteMerchantRelationshipStorageEntity->getKey())
                ->setData($priceProductConcreteMerchantRelationshipStorageEntity->getData())
                ->save();
        }
    }

    protected function createEventEntityTransfer(int $idCompanyBusinessUnit, ?int $idMerchantRelationship = null): EventEntityTransfer
    {
        return (new EventEntityTransfer())->setForeignKeys([
            PriceProductMerchantRelationshipStorageConfig::COL_FK_COMPANY_BUSINESS_UNIT => $idCompanyBusinessUnit,
            static::COL_FK_MERCHANT_RELATIONSHIP => $idMerchantRelationship ?? $this->resolveMerchantRelationshipId($idCompanyBusinessUnit),
        ]);
    }

    protected function deleteMerchantRelationshipToCompanyBusinessUnit(int $idMerchantRelationship): void
    {
        SpyMerchantRelationshipToCompanyBusinessUnitQuery::create()
            ->filterByFkMerchantRelationship($idMerchantRelationship)
            ->delete();
    }

    protected function countAbstractStorageEntitiesByMerchantRelationship(int $idMerchantRelationship): int
    {
        return SpyPriceProductAbstractMerchantRelationshipStorageQuery::create()
            ->filterByFkMerchantRelationship($idMerchantRelationship)
            ->count();
    }

    protected function countConcreteStorageEntitiesByMerchantRelationship(int $idMerchantRelationship): int
    {
        return SpyPriceProductConcreteMerchantRelationshipStorageQuery::create()
            ->filterByFkMerchantRelationship($idMerchantRelationship)
            ->count();
    }

    protected function findCompanyBusinessUnitIdByMerchantRelationship(int $idMerchantRelationship): int
    {
        $merchantRelationshipToCompanyBusinessUnitEntity = SpyMerchantRelationshipToCompanyBusinessUnitQuery::create()
            ->filterByFkMerchantRelationship($idMerchantRelationship)
            ->findOne();

        return $merchantRelationshipToCompanyBusinessUnitEntity->getFkCompanyBusinessUnit();
    }

    protected function countAbstractStorageEntities(int $idCompanyBusinessUnit): int
    {
        return count($this->findAbstractStorageEntities($idCompanyBusinessUnit));
    }

    protected function countConcreteStorageEntities(int $idCompanyBusinessUnit): int
    {
        return count($this->findConcreteStorageEntities($idCompanyBusinessUnit));
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
