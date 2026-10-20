<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\PriceProductMerchantRelationshipStorage\Communication\Console;

use Codeception\Test\Unit;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyMerchantRelationBusinessUnitStorage;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyMerchantRelationBusinessUnitStorageQuery;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductAbstractMerchantRelationshipStorage;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductAbstractMerchantRelationshipStorageQuery;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductConcreteMerchantRelationshipStorage;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductConcreteMerchantRelationshipStorageQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Spryker\Shared\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageConstants;
use Spryker\Zed\PriceProductMerchantRelationshipStorage\Communication\Console\PriceProductMerchantRelationshipStorageCleanupConsole;
use Spryker\Zed\PriceProductMerchantRelationshipStorage\Communication\PriceProductMerchantRelationshipStorageCommunicationFactory;
use Spryker\Zed\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageDependencyProvider;
use Spryker\Zed\Synchronization\Business\SynchronizationFacadeInterface;
use SprykerTest\Zed\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageCommunicationTester;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group PriceProductMerchantRelationshipStorage
 * @group Communication
 * @group Console
 * @group PriceProductMerchantRelationshipStorageCleanupConsoleTest
 * Add your own group annotations below this line
 */
class PriceProductMerchantRelationshipStorageCleanupConsoleTest extends Unit
{
    protected const string COMMAND_NAME = 'price-product-merchant-relationship-storage:cleanup';

    protected const string OPTION_FORCE = '--force';

    protected const string OPTION_CHUNK_SIZE = '--chunk-size';

    protected const int ID_COMPANY_BUSINESS_UNIT = 990001;

    protected const int ID_SECOND_COMPANY_BUSINESS_UNIT = 990002;

    protected const int ID_MERCHANT_RELATIONSHIP = 990101;

    protected const int ID_PRODUCT_ABSTRACT = 990201;

    protected const int ID_PRODUCT_CONCRETE = 990301;

    protected const string STORE_NAME = 'DE';

    protected PriceProductMerchantRelationshipStorageCommunicationTester $tester;

    protected function _before(): void
    {
        parent::_before();

        $this->tester->setDependency(
            PriceProductMerchantRelationshipStorageDependencyProvider::FACADE_SYNCHRONIZATION,
            $this->createMock(SynchronizationFacadeInterface::class),
        );
    }

    public function testExecuteWithFlagEnabledDeletesOnlyBusinessUnitShapeRows(): void
    {
        // Arrange
        $businessUnitShapeAbstractStorageEntity = $this->createAbstractStorageEntity(null);
        $merchantRelationshipShapeAbstractStorageEntity = $this->createAbstractStorageEntity(static::ID_MERCHANT_RELATIONSHIP);
        $businessUnitShapeConcreteStorageEntity = $this->createConcreteStorageEntity(null);
        $merchantRelationshipShapeConcreteStorageEntity = $this->createConcreteStorageEntity(static::ID_MERCHANT_RELATIONSHIP);
        $merchantRelationBusinessUnitStorageEntity = $this->createLookupStorageEntity(static::ID_COMPANY_BUSINESS_UNIT);

        // Act
        $commandTester = $this->executeCommand(true, [static::OPTION_FORCE => true]);

        // Assert
        $this->assertSame(PriceProductMerchantRelationshipStorageCleanupConsole::CODE_SUCCESS, $commandTester->getStatusCode());
        $this->assertFalse($this->hasAbstractStorageEntity($businessUnitShapeAbstractStorageEntity));
        $this->assertFalse($this->hasConcreteStorageEntity($businessUnitShapeConcreteStorageEntity));
        $this->assertTrue($this->hasAbstractStorageEntity($merchantRelationshipShapeAbstractStorageEntity));
        $this->assertTrue($this->hasConcreteStorageEntity($merchantRelationshipShapeConcreteStorageEntity));
        $this->assertTrue($this->hasLookupStorageEntity($merchantRelationBusinessUnitStorageEntity));
    }

    public function testExecuteWithFlagDisabledDeletesMerchantRelationshipShapeRowsAndLookupRows(): void
    {
        // Arrange
        $businessUnitShapeAbstractStorageEntity = $this->createAbstractStorageEntity(null);
        $merchantRelationshipShapeAbstractStorageEntity = $this->createAbstractStorageEntity(static::ID_MERCHANT_RELATIONSHIP);
        $businessUnitShapeConcreteStorageEntity = $this->createConcreteStorageEntity(null);
        $merchantRelationshipShapeConcreteStorageEntity = $this->createConcreteStorageEntity(static::ID_MERCHANT_RELATIONSHIP);
        $merchantRelationBusinessUnitStorageEntity = $this->createLookupStorageEntity(static::ID_COMPANY_BUSINESS_UNIT);

        // Act
        $commandTester = $this->executeCommand(false, [static::OPTION_FORCE => true]);

        // Assert
        $this->assertSame(PriceProductMerchantRelationshipStorageCleanupConsole::CODE_SUCCESS, $commandTester->getStatusCode());
        $this->assertFalse($this->hasAbstractStorageEntity($merchantRelationshipShapeAbstractStorageEntity));
        $this->assertFalse($this->hasConcreteStorageEntity($merchantRelationshipShapeConcreteStorageEntity));
        $this->assertFalse($this->hasLookupStorageEntity($merchantRelationBusinessUnitStorageEntity));
        $this->assertTrue($this->hasAbstractStorageEntity($businessUnitShapeAbstractStorageEntity));
        $this->assertTrue($this->hasConcreteStorageEntity($businessUnitShapeConcreteStorageEntity));
    }

    public function testExecuteDeletesAllRowsWhenRowCountExceedsChunkSize(): void
    {
        // Arrange
        $firstMerchantRelationBusinessUnitStorageEntity = $this->createLookupStorageEntity(static::ID_COMPANY_BUSINESS_UNIT);
        $secondMerchantRelationBusinessUnitStorageEntity = $this->createLookupStorageEntity(static::ID_SECOND_COMPANY_BUSINESS_UNIT);
        $firstAbstractStorageEntity = $this->createAbstractStorageEntity(static::ID_MERCHANT_RELATIONSHIP);
        $secondAbstractStorageEntity = $this->createAbstractStorageEntity(static::ID_MERCHANT_RELATIONSHIP + 1);

        // Act
        $commandTester = $this->executeCommand(false, [static::OPTION_FORCE => true, static::OPTION_CHUNK_SIZE => '1']);

        // Assert
        $this->assertSame(PriceProductMerchantRelationshipStorageCleanupConsole::CODE_SUCCESS, $commandTester->getStatusCode());
        $this->assertFalse($this->hasLookupStorageEntity($firstMerchantRelationBusinessUnitStorageEntity));
        $this->assertFalse($this->hasLookupStorageEntity($secondMerchantRelationBusinessUnitStorageEntity));
        $this->assertFalse($this->hasAbstractStorageEntity($firstAbstractStorageEntity));
        $this->assertFalse($this->hasAbstractStorageEntity($secondAbstractStorageEntity));
    }

    public function testExecuteFlushesSynchronizationMessagesAfterEachChunk(): void
    {
        // Arrange
        $this->createLookupStorageEntity(static::ID_COMPANY_BUSINESS_UNIT);
        $this->createLookupStorageEntity(static::ID_SECOND_COMPANY_BUSINESS_UNIT);
        $this->createAbstractStorageEntity(static::ID_MERCHANT_RELATIONSHIP);
        $this->createAbstractStorageEntity(static::ID_MERCHANT_RELATIONSHIP + 1);
        $merchantRelationshipShapeRowCount = SpyPriceProductAbstractMerchantRelationshipStorageQuery::create()->filterByFkMerchantRelationship(null, Criteria::ISNOTNULL)->count()
            + SpyPriceProductConcreteMerchantRelationshipStorageQuery::create()->filterByFkMerchantRelationship(null, Criteria::ISNOTNULL)->count()
            + SpyMerchantRelationBusinessUnitStorageQuery::create()->count();

        $synchronizationFacadeMock = $this->createMock(SynchronizationFacadeInterface::class);
        $this->tester->setDependency(PriceProductMerchantRelationshipStorageDependencyProvider::FACADE_SYNCHRONIZATION, $synchronizationFacadeMock);

        // Expect
        $synchronizationFacadeMock->expects($this->exactly($merchantRelationshipShapeRowCount))->method('flushSynchronizationMessagesFromBuffer');

        // Act
        $this->executeCommand(false, [static::OPTION_FORCE => true, static::OPTION_CHUNK_SIZE => '1']);
    }

    public function testExecuteDeletesRowsWhenUserConfirms(): void
    {
        // Arrange
        $businessUnitShapeAbstractStorageEntity = $this->createAbstractStorageEntity(null);
        $commandTester = $this->createCommandTester(true);
        $commandTester->setInputs(['y']);

        // Act
        $commandTester->execute([]);

        // Assert
        $this->assertSame(PriceProductMerchantRelationshipStorageCleanupConsole::CODE_SUCCESS, $commandTester->getStatusCode());
        $this->assertFalse($this->hasAbstractStorageEntity($businessUnitShapeAbstractStorageEntity));
    }

    public function testExecuteDoesNotDeleteRowsWhenUserDeclines(): void
    {
        // Arrange
        $businessUnitShapeAbstractStorageEntity = $this->createAbstractStorageEntity(null);
        $commandTester = $this->createCommandTester(true);
        $commandTester->setInputs(['n']);

        // Act
        $commandTester->execute([]);

        // Assert
        $this->assertSame(PriceProductMerchantRelationshipStorageCleanupConsole::CODE_SUCCESS, $commandTester->getStatusCode());
        $this->assertStringContainsString('Operation cancelled.', $commandTester->getDisplay());
        $this->assertTrue($this->hasAbstractStorageEntity($businessUnitShapeAbstractStorageEntity));
    }

    public function testExecuteReturnsErrorAndDoesNotDeleteRowsWhenNonInteractiveWithoutForce(): void
    {
        // Arrange
        $merchantRelationBusinessUnitStorageEntity = $this->createLookupStorageEntity(static::ID_COMPANY_BUSINESS_UNIT);

        // Act
        $commandTester = $this->createCommandTester(false);
        $commandTester->execute([], ['interactive' => false]);

        // Assert
        $this->assertSame(PriceProductMerchantRelationshipStorageCleanupConsole::CODE_ERROR, $commandTester->getStatusCode());
        $this->assertTrue($this->hasLookupStorageEntity($merchantRelationBusinessUnitStorageEntity));
    }

    public function testExecuteReturnsErrorWhenChunkSizeIsNotPositive(): void
    {
        // Arrange
        $businessUnitShapeAbstractStorageEntity = $this->createAbstractStorageEntity(null);

        // Act
        $commandTester = $this->executeCommand(true, [static::OPTION_FORCE => true, static::OPTION_CHUNK_SIZE => '0']);

        // Assert
        $this->assertSame(PriceProductMerchantRelationshipStorageCleanupConsole::CODE_ERROR, $commandTester->getStatusCode());
        $this->assertTrue($this->hasAbstractStorageEntity($businessUnitShapeAbstractStorageEntity));
    }

    /**
     * @param array<string, mixed> $input
     */
    protected function executeCommand(bool $isMerchantRelationPriceKeyEnabled, array $input): CommandTester
    {
        $commandTester = $this->createCommandTester($isMerchantRelationPriceKeyEnabled);
        $commandTester->execute($input);

        return $commandTester;
    }

    protected function createCommandTester(bool $isMerchantRelationPriceKeyEnabled): CommandTester
    {
        $priceProductMerchantRelationshipStorageFacade = $this->tester->createFacadeWithMerchantRelationPriceKeyFlag($isMerchantRelationPriceKeyEnabled);
        $communicationFactory = new PriceProductMerchantRelationshipStorageCommunicationFactory();
        $communicationFactory->setConfig($this->tester->getModuleConfig());

        $priceProductMerchantRelationshipStorageCleanupConsole = new PriceProductMerchantRelationshipStorageCleanupConsole();
        $priceProductMerchantRelationshipStorageCleanupConsole->setFacade($priceProductMerchantRelationshipStorageFacade);
        $priceProductMerchantRelationshipStorageCleanupConsole->setFactory($communicationFactory);

        $application = new Application();
        $application->addCommand($priceProductMerchantRelationshipStorageCleanupConsole);

        return new CommandTester($application->find(static::COMMAND_NAME));
    }

    protected function createAbstractStorageEntity(?int $idMerchantRelationship): SpyPriceProductAbstractMerchantRelationshipStorage
    {
        $priceProductAbstractMerchantRelationshipStorageEntity = (new SpyPriceProductAbstractMerchantRelationshipStorage())
            ->setFkProductAbstract(static::ID_PRODUCT_ABSTRACT)
            ->setFkCompanyBusinessUnit(static::ID_COMPANY_BUSINESS_UNIT)
            ->setFkMerchantRelationship($idMerchantRelationship)
            ->setPriceKey($this->createPriceKey(static::ID_PRODUCT_ABSTRACT, $idMerchantRelationship))
            ->setData([]);
        $priceProductAbstractMerchantRelationshipStorageEntity->save();

        return $priceProductAbstractMerchantRelationshipStorageEntity;
    }

    protected function createConcreteStorageEntity(?int $idMerchantRelationship): SpyPriceProductConcreteMerchantRelationshipStorage
    {
        $priceProductConcreteMerchantRelationshipStorageEntity = (new SpyPriceProductConcreteMerchantRelationshipStorage())
            ->setFkProduct(static::ID_PRODUCT_CONCRETE)
            ->setFkCompanyBusinessUnit(static::ID_COMPANY_BUSINESS_UNIT)
            ->setFkMerchantRelationship($idMerchantRelationship)
            ->setPriceKey($this->createPriceKey(static::ID_PRODUCT_CONCRETE, $idMerchantRelationship))
            ->setData([]);
        $priceProductConcreteMerchantRelationshipStorageEntity->save();

        return $priceProductConcreteMerchantRelationshipStorageEntity;
    }

    protected function createLookupStorageEntity(int $idCompanyBusinessUnit): SpyMerchantRelationBusinessUnitStorage
    {
        $merchantRelationBusinessUnitStorageEntity = (new SpyMerchantRelationBusinessUnitStorage())
            ->setFkCompanyBusinessUnit($idCompanyBusinessUnit)
            ->setData([
                PriceProductMerchantRelationshipStorageConstants::MERCHANT_RELATION_BUSINESS_UNIT_DATA_KEY_MERCHANT_RELATIONSHIP_IDS => [static::ID_MERCHANT_RELATIONSHIP],
            ]);
        $merchantRelationBusinessUnitStorageEntity->save();

        return $merchantRelationBusinessUnitStorageEntity;
    }

    protected function createPriceKey(int $idProduct, ?int $idMerchantRelationship): string
    {
        if ($idMerchantRelationship === null) {
            return sprintf('%s:%d:%d', static::STORE_NAME, $idProduct, static::ID_COMPANY_BUSINESS_UNIT);
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

    protected function hasLookupStorageEntity(SpyMerchantRelationBusinessUnitStorage $merchantRelationBusinessUnitStorageEntity): bool
    {
        return SpyMerchantRelationBusinessUnitStorageQuery::create()
            ->filterByPrimaryKey($merchantRelationBusinessUnitStorageEntity->getPrimaryKey())
            ->exists();
    }
}
