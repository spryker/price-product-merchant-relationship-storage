<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\PriceProductMerchantRelationshipStorage\Communication\Plugin\Event\Listener;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\EventEntityTransfer;
use Orm\Zed\MerchantRelationship\Persistence\SpyMerchantRelationshipToCompanyBusinessUnitQuery;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductAbstractMerchantRelationshipStorage;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductAbstractMerchantRelationshipStorageQuery;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductConcreteMerchantRelationshipStorage;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductConcreteMerchantRelationshipStorageQuery;
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

    /**
     * @var \SprykerTest\Zed\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageCommunicationTester
     */
    protected $tester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tester->ensurePriceProductMerchantRelationshipTableIsEmpty();
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
        (new MerchantRelationshipListener())->handleBulk(
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
        (new MerchantRelationshipListener())->handleBulk(
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
        (new MerchantRelationshipListener())->handleBulk(
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

        (new MerchantRelationshipListener())->handleBulk(
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
        (new MerchantRelationshipListener())->handleBulk(
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
        (new MerchantRelationshipListener())->handleBulk(
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

        (new MerchantRelationshipListener())->handleBulk(
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
        (new MerchantRelationshipListener())->handleBulk(
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
        $this->tester->getFactory()->createPriceProductAbstractStorageWriter()->publishByCompanyBusinessUnitIds($companyBusinessUnitIds);

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
        $this->tester->getFactory()->createPriceProductConcreteStorageWriter()->publishByCompanyBusinessUnitIds($companyBusinessUnitIds);

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

        (new MerchantRelationshipListener())->handleBulk(
            $this->createEventEntityTransfers($companyBusinessUnitIds),
            MerchantRelationshipEvents::ENTITY_SPY_MERCHANT_RELATIONSHIP_TO_COMPANY_BUSINESS_UNIT_UPDATE,
        );

        $singleChunkPriceKeys = $this->getAbstractStoragePriceKeysForCompanyBusinessUnitIds($companyBusinessUnitIds);
        $this->assertNotEmpty($singleChunkPriceKeys, 'Expected the single-chunk publish to produce storage entities.');

        $this->deleteAbstractStorageEntities($companyBusinessUnitIds);

        // Act
        $this->tester->mockConfigMethod('getCompanyBusinessUnitChunkSize', static::CHUNK_SIZE);
        $this->tester->mockConfigMethod('getProductChunkSize', static::CHUNK_SIZE);

        $this->tester->getFactory()->createPriceProductAbstractStorageWriter()->publishByCompanyBusinessUnitIds($companyBusinessUnitIds);

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
            ->delete();
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
        $priceProductAbstractMerchantRelationshipStorageEntities = SpyPriceProductAbstractMerchantRelationshipStorageQuery::create()
            ->filterByFkCompanyBusinessUnit($idCompanyBusinessUnit)
            ->find();

        foreach ($priceProductAbstractMerchantRelationshipStorageEntities as $priceProductAbstractMerchantRelationshipStorageEntity) {
            $priceKeys[] = $priceProductAbstractMerchantRelationshipStorageEntity->getPriceKey();
        }

        return $priceKeys;
    }

    /**
     * @param int $idCompanyBusinessUnit
     *
     * @return array<string>
     */
    protected function getConcreteStoragePriceKeys(int $idCompanyBusinessUnit): array
    {
        $priceKeys = [];
        $priceProductConcreteMerchantRelationshipStorageEntities = SpyPriceProductConcreteMerchantRelationshipStorageQuery::create()
            ->filterByFkCompanyBusinessUnit($idCompanyBusinessUnit)
            ->find();

        foreach ($priceProductConcreteMerchantRelationshipStorageEntities as $priceProductConcreteMerchantRelationshipStorageEntity) {
            $priceKeys[] = $priceProductConcreteMerchantRelationshipStorageEntity->getPriceKey();
        }

        return $priceKeys;
    }

    protected function duplicateAbstractStorageEntities(int $idCompanyBusinessUnit): void
    {
        $priceProductAbstractMerchantRelationshipStorageEntities = SpyPriceProductAbstractMerchantRelationshipStorageQuery::create()
            ->filterByFkCompanyBusinessUnit($idCompanyBusinessUnit)
            ->find();

        foreach ($priceProductAbstractMerchantRelationshipStorageEntities as $priceProductAbstractMerchantRelationshipStorageEntity) {
            (new SpyPriceProductAbstractMerchantRelationshipStorage())
                ->setFkProductAbstract($priceProductAbstractMerchantRelationshipStorageEntity->getFkProductAbstract())
                ->setFkCompanyBusinessUnit($priceProductAbstractMerchantRelationshipStorageEntity->getFkCompanyBusinessUnit())
                ->setPriceKey($priceProductAbstractMerchantRelationshipStorageEntity->getPriceKey())
                ->setKey($priceProductAbstractMerchantRelationshipStorageEntity->getKey())
                ->setData($priceProductAbstractMerchantRelationshipStorageEntity->getData())
                ->save();
        }
    }

    protected function duplicateConcreteStorageEntities(int $idCompanyBusinessUnit): void
    {
        $priceProductConcreteMerchantRelationshipStorageEntities = SpyPriceProductConcreteMerchantRelationshipStorageQuery::create()
            ->filterByFkCompanyBusinessUnit($idCompanyBusinessUnit)
            ->find();

        foreach ($priceProductConcreteMerchantRelationshipStorageEntities as $priceProductConcreteMerchantRelationshipStorageEntity) {
            (new SpyPriceProductConcreteMerchantRelationshipStorage())
                ->setFkProduct($priceProductConcreteMerchantRelationshipStorageEntity->getFkProduct())
                ->setFkCompanyBusinessUnit($priceProductConcreteMerchantRelationshipStorageEntity->getFkCompanyBusinessUnit())
                ->setPriceKey($priceProductConcreteMerchantRelationshipStorageEntity->getPriceKey())
                ->setKey($priceProductConcreteMerchantRelationshipStorageEntity->getKey())
                ->setData($priceProductConcreteMerchantRelationshipStorageEntity->getData())
                ->save();
        }
    }

    protected function createEventEntityTransfer(int $idCompanyBusinessUnit): EventEntityTransfer
    {
        return (new EventEntityTransfer())->setForeignKeys([
            PriceProductMerchantRelationshipStorageConfig::COL_FK_COMPANY_BUSINESS_UNIT => $idCompanyBusinessUnit,
        ]);
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
        return SpyPriceProductAbstractMerchantRelationshipStorageQuery::create()
            ->filterByFkCompanyBusinessUnit($idCompanyBusinessUnit)
            ->count();
    }

    protected function countConcreteStorageEntities(int $idCompanyBusinessUnit): int
    {
        return SpyPriceProductConcreteMerchantRelationshipStorageQuery::create()
            ->filterByFkCompanyBusinessUnit($idCompanyBusinessUnit)
            ->count();
    }
}
