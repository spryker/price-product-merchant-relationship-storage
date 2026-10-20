<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Client\PriceProductMerchantRelationshipStorage\Storage;

use Generated\Shared\Transfer\StoreTransfer;
use Generated\Shared\Transfer\SynchronizationDataTransfer;
use PHPUnit\Framework\TestCase;
use Spryker\Client\PriceProductMerchantRelationshipStorage\Dependency\Client\PriceProductMerchantRelationshipStorageToStoreClientInterface;
use Spryker\Client\PriceProductMerchantRelationshipStorage\Dependency\Service\PriceProductMerchantRelationshipToSynchronizationServiceInterface;
use Spryker\Client\PriceProductMerchantRelationshipStorage\Storage\PriceProductMerchantRelationshipKeyGenerator;
use Spryker\Service\Synchronization\Dependency\Plugin\SynchronizationKeyGeneratorPluginInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group PriceProductMerchantRelationshipStorage
 * @group Storage
 * @group PriceProductMerchantRelationshipKeyGeneratorTest
 * Add your own group annotations below this line
 */
class PriceProductMerchantRelationshipKeyGeneratorTest extends TestCase
{
    public function testGenerateKeyComposesStoreProductAndBusinessUnitReference(): void
    {
        // Arrange
        // Act
        $capturedDataTransfer = $this->generateKeyAndCaptureDataTransfer(
            static fn (PriceProductMerchantRelationshipKeyGenerator $keyGenerator) => $keyGenerator->generateKey(
                'price_product_abstract_merchant_relationship',
                7,
                5,
            ),
        );

        // Assert
        $this->assertSame('DE', $capturedDataTransfer->getStore());
        $this->assertSame('7:5', $capturedDataTransfer->getReference());
    }

    public function testGenerateKeyByMerchantRelationshipComposesStoreProductAndMarkedRelationshipReference(): void
    {
        // Arrange
        // Act
        $capturedDataTransfer = $this->generateKeyAndCaptureDataTransfer(
            static fn (PriceProductMerchantRelationshipKeyGenerator $keyGenerator) => $keyGenerator->generateKeyByMerchantRelationship(
                'price_product_abstract_merchant_relationship',
                7,
                2,
            ),
        );

        // Assert
        $this->assertSame('DE', $capturedDataTransfer->getStore());
        $this->assertSame('7:mr2', $capturedDataTransfer->getReference());
    }

    public function testGenerateKeyByMerchantRelationshipUsesCurrentStoreOfEachCall(): void
    {
        // Arrange
        $capturedStoreNames = [];

        $keyGeneratorPlugin = $this->createStub(SynchronizationKeyGeneratorPluginInterface::class);
        $keyGeneratorPlugin->method('generateKey')
            ->willReturnCallback(function (SynchronizationDataTransfer $dataTransfer) use (&$capturedStoreNames): string {
                $capturedStoreNames[] = $dataTransfer->getStore();

                return 'irrelevant';
            });

        $synchronizationService = $this->createStub(PriceProductMerchantRelationshipToSynchronizationServiceInterface::class);
        $synchronizationService->method('getStorageKeyBuilder')->willReturn($keyGeneratorPlugin);

        $storeClient = $this->createStub(PriceProductMerchantRelationshipStorageToStoreClientInterface::class);
        $storeClient->method('getCurrentStore')->willReturnOnConsecutiveCalls(
            (new StoreTransfer())->setName('DE'),
            (new StoreTransfer())->setName('AT'),
        );

        $keyGenerator = new PriceProductMerchantRelationshipKeyGenerator($synchronizationService, $storeClient);

        // Act
        $keyGenerator->generateKeyByMerchantRelationship('price_product_abstract_merchant_relationship', 7, 2);
        $keyGenerator->generateKeyByMerchantRelationship('price_product_abstract_merchant_relationship', 7, 2);

        // Assert
        $this->assertSame(['DE', 'AT'], $capturedStoreNames);
    }

    public function testGenerateKeyByCompanyBusinessUnitOmitsStoreAndUsesBareBusinessUnitReference(): void
    {
        // Arrange
        // Act
        $capturedDataTransfer = $this->generateKeyAndCaptureDataTransfer(
            static fn (PriceProductMerchantRelationshipKeyGenerator $keyGenerator) => $keyGenerator->generateKeyByCompanyBusinessUnit(
                'mrbu',
                5,
            ),
            expectStoreClientCalled: false,
        );

        // Assert
        $this->assertNull($capturedDataTransfer->getStore());
        $this->assertSame('5', $capturedDataTransfer->getReference());
    }

    protected function generateKeyAndCaptureDataTransfer(
        callable $invokeGenerator,
        bool $expectStoreClientCalled = true
    ): SynchronizationDataTransfer {
        $capturedDataTransfer = null;

        $keyGeneratorPlugin = $this->createStub(SynchronizationKeyGeneratorPluginInterface::class);
        $keyGeneratorPlugin->method('generateKey')
            ->willReturnCallback(function (SynchronizationDataTransfer $dataTransfer) use (&$capturedDataTransfer): string {
                $capturedDataTransfer = $dataTransfer;

                return 'irrelevant';
            });

        $synchronizationService = $this->createStub(PriceProductMerchantRelationshipToSynchronizationServiceInterface::class);
        $synchronizationService->method('getStorageKeyBuilder')->willReturn($keyGeneratorPlugin);

        $storeClient = $this->createMock(PriceProductMerchantRelationshipStorageToStoreClientInterface::class);
        $storeClient->expects($expectStoreClientCalled ? $this->once() : $this->never())
            ->method('getCurrentStore')
            ->willReturn((new StoreTransfer())->setName('DE'));

        $keyGenerator = new PriceProductMerchantRelationshipKeyGenerator($synchronizationService, $storeClient);
        $invokeGenerator($keyGenerator);

        return $capturedDataTransfer;
    }
}
