<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Client\PriceProductMerchantRelationshipStorage\Storage;

use Generated\Shared\Transfer\PriceProductTransfer;
use PHPUnit\Framework\TestCase;
use Spryker\Client\PriceProductMerchantRelationshipStorage\Dependency\Client\PriceProductMerchantRelationshipStorageToStorageClientInterface;
use Spryker\Client\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageConfig;
use Spryker\Client\PriceProductMerchantRelationshipStorage\Storage\MerchantRelationBusinessUnitLookupReaderInterface;
use Spryker\Client\PriceProductMerchantRelationshipStorage\Storage\MerchantRelationshipPriceStorageReader;
use Spryker\Client\PriceProductMerchantRelationshipStorage\Storage\PriceProductMapperInterface;
use Spryker\Client\PriceProductMerchantRelationshipStorage\Storage\PriceProductMerchantRelationshipConcreteReader;
use Spryker\Client\PriceProductMerchantRelationshipStorage\Storage\PriceProductMerchantRelationshipKeyGeneratorInterface;
use Spryker\Client\Storage\Redis\Service;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group PriceProductMerchantRelationshipStorage
 * @group Storage
 * @group PriceProductMerchantRelationshipConcreteReaderTest
 * Add your own group annotations below this line
 */
class PriceProductMerchantRelationshipConcreteReaderTest extends TestCase
{
    public function testFindPriceMerchantRelationshipConcreteUsesLegacyBusinessUnitKeyWhenFlagDisabled(): void
    {
        // Arrange
        $priceProductMerchantRelationshipKeyGenerator = $this->createMock(PriceProductMerchantRelationshipKeyGeneratorInterface::class);
        $priceProductMerchantRelationshipKeyGenerator->expects($this->once())
            ->method('generateKey')
            ->with('price_product_concrete_merchant_relationship', 7, 5)
            ->willReturn('kv:price_product_concrete_merchant_relationship:br:7:5');

        $storageClient = $this->createMock(PriceProductMerchantRelationshipStorageToStorageClientInterface::class);
        $storageClient->method('get')
            ->with('kv:price_product_concrete_merchant_relationship:br:7:5')
            ->willReturn(['prices' => ['2' => ['BRL' => ['GROSS_MODE' => ['DEFAULT' => 100]]]]]);
        $storageClient->expects($this->never())->method('getMulti');

        $priceProductMapper = $this->createMock(PriceProductMapperInterface::class);
        $priceProductMapper->expects($this->once())
            ->method('mapPriceProductStorageTransferToPriceProductTransfers')
            ->willReturn([new PriceProductTransfer()]);

        $config = $this->createStub(PriceProductMerchantRelationshipStorageConfig::class);
        $config->method('isMerchantRelationPriceKeyEnabled')->willReturn(false);

        $reader = new PriceProductMerchantRelationshipConcreteReader(
            $storageClient,
            $priceProductMerchantRelationshipKeyGenerator,
            $priceProductMapper,
            $config,
            $this->createStub(MerchantRelationBusinessUnitLookupReaderInterface::class),
            new MerchantRelationshipPriceStorageReader($storageClient),
        );

        // Act
        $priceProductTransfers = $reader->findPriceMerchantRelationshipConcrete(7, 5);

        // Assert
        $this->assertCount(1, $priceProductTransfers);
    }

    public function testFindPriceMerchantRelationshipConcreteMergesPricesAcrossRelationshipKeysWhenFlagEnabled(): void
    {
        // Arrange
        $priceProductMerchantRelationshipKeyGenerator = $this->createStub(PriceProductMerchantRelationshipKeyGeneratorInterface::class);
        $priceProductMerchantRelationshipKeyGenerator->method('generateKeyByMerchantRelationship')
            ->willReturnMap([
                ['price_product_concrete_merchant_relationship', 7, 2, 'price_product_concrete_merchant_relationship:br:7:mr2'],
            ]);

        $lookupReader = $this->createStub(MerchantRelationBusinessUnitLookupReaderInterface::class);
        $lookupReader->method('findMerchantRelationshipIdsByCompanyBusinessUnitId')->willReturn([2]);

        $storageClient = $this->createMock(PriceProductMerchantRelationshipStorageToStorageClientInterface::class);
        $storageClient->expects($this->once())
            ->method('getMulti')
            ->with(['price_product_concrete_merchant_relationship:br:7:mr2'])
            ->willReturn([
                Service::KV_PREFIX . 'price_product_concrete_merchant_relationship:br:7:mr2' => json_encode([
                    'prices' => ['2' => ['BRL' => ['GROSS_MODE' => ['DEFAULT' => 100]]]],
                ]),
            ]);

        $priceProductMapper = $this->createMock(PriceProductMapperInterface::class);
        $priceProductMapper->expects($this->once())
            ->method('mapPriceProductStorageTransferToPriceProductTransfers')
            ->willReturn([new PriceProductTransfer()]);

        $config = $this->createStub(PriceProductMerchantRelationshipStorageConfig::class);
        $config->method('isMerchantRelationPriceKeyEnabled')->willReturn(true);

        $reader = new PriceProductMerchantRelationshipConcreteReader(
            $storageClient,
            $priceProductMerchantRelationshipKeyGenerator,
            $priceProductMapper,
            $config,
            $lookupReader,
            new MerchantRelationshipPriceStorageReader($storageClient),
        );

        // Act
        $priceProductTransfers = $reader->findPriceMerchantRelationshipConcrete(7, 5);

        // Assert
        $this->assertCount(1, $priceProductTransfers);
    }

    public function testFindPriceMerchantRelationshipConcreteFallsBackToBusinessUnitKeyWhenFallbackEnabledAndNoMerchantRelationshipPrices(): void
    {
        // Arrange
        $priceProductMerchantRelationshipKeyGenerator = $this->createMock(PriceProductMerchantRelationshipKeyGeneratorInterface::class);
        $priceProductMerchantRelationshipKeyGenerator->method('generateKeyByMerchantRelationship')
            ->willReturn('price_product_concrete_merchant_relationship:br:7:mr2');
        $priceProductMerchantRelationshipKeyGenerator->expects($this->once())
            ->method('generateKey')
            ->with('price_product_concrete_merchant_relationship', 7, 5)
            ->willReturn('price_product_concrete_merchant_relationship:br:7:5');

        $lookupReader = $this->createStub(MerchantRelationBusinessUnitLookupReaderInterface::class);
        $lookupReader->method('findMerchantRelationshipIdsByCompanyBusinessUnitId')->willReturn([2]);

        $storageClient = $this->createMock(PriceProductMerchantRelationshipStorageToStorageClientInterface::class);
        $storageClient->method('getMulti')
            ->willReturn([Service::KV_PREFIX . 'price_product_concrete_merchant_relationship:br:7:mr2' => 0]);
        $storageClient->expects($this->once())
            ->method('get')
            ->with('price_product_concrete_merchant_relationship:br:7:5')
            ->willReturn(['prices' => ['2' => ['BRL' => ['GROSS_MODE' => ['DEFAULT' => 100]]]]]);

        $priceProductMapper = $this->createMock(PriceProductMapperInterface::class);
        $priceProductMapper->expects($this->once())
            ->method('mapPriceProductStorageTransferToPriceProductTransfers')
            ->willReturn([new PriceProductTransfer()]);

        $reader = $this->createReaderWithFlagEnabled(
            $storageClient,
            $lookupReader,
            $priceProductMerchantRelationshipKeyGenerator,
            $priceProductMapper,
            true,
        );

        // Act
        $priceProductTransfers = $reader->findPriceMerchantRelationshipConcrete(7, 5);

        // Assert
        $this->assertCount(1, $priceProductTransfers);
    }

    public function testFindPriceMerchantRelationshipConcreteDoesNotFallBackWhenFallbackEnabledAndMerchantRelationshipPricesFound(): void
    {
        // Arrange
        $priceProductMerchantRelationshipKeyGenerator = $this->createMock(PriceProductMerchantRelationshipKeyGeneratorInterface::class);
        $priceProductMerchantRelationshipKeyGenerator->method('generateKeyByMerchantRelationship')
            ->willReturn('price_product_concrete_merchant_relationship:br:7:mr2');
        $priceProductMerchantRelationshipKeyGenerator->expects($this->never())->method('generateKey');

        $lookupReader = $this->createStub(MerchantRelationBusinessUnitLookupReaderInterface::class);
        $lookupReader->method('findMerchantRelationshipIdsByCompanyBusinessUnitId')->willReturn([2]);

        $storageClient = $this->createMock(PriceProductMerchantRelationshipStorageToStorageClientInterface::class);
        $storageClient->method('getMulti')
            ->willReturn([
                Service::KV_PREFIX . 'price_product_concrete_merchant_relationship:br:7:mr2' => json_encode([
                    'prices' => ['2' => ['BRL' => ['GROSS_MODE' => ['DEFAULT' => 100]]]],
                ]),
            ]);
        $storageClient->expects($this->never())->method('get');

        $priceProductMapper = $this->createStub(PriceProductMapperInterface::class);
        $priceProductMapper->method('mapPriceProductStorageTransferToPriceProductTransfers')
            ->willReturn([new PriceProductTransfer()]);

        $reader = $this->createReaderWithFlagEnabled(
            $storageClient,
            $lookupReader,
            $priceProductMerchantRelationshipKeyGenerator,
            $priceProductMapper,
            true,
        );

        // Act
        $priceProductTransfers = $reader->findPriceMerchantRelationshipConcrete(7, 5);

        // Assert
        $this->assertCount(1, $priceProductTransfers);
    }

    public function testFindPriceMerchantRelationshipConcreteDoesNotFallBackWhenFallbackDisabledAndNoMerchantRelationshipPrices(): void
    {
        // Arrange
        $lookupReader = $this->createStub(MerchantRelationBusinessUnitLookupReaderInterface::class);
        $lookupReader->method('findMerchantRelationshipIdsByCompanyBusinessUnitId')->willReturn([]);

        $storageClient = $this->createMock(PriceProductMerchantRelationshipStorageToStorageClientInterface::class);
        $storageClient->expects($this->never())->method('get');

        $reader = $this->createReaderWithFlagEnabled($storageClient, $lookupReader);

        // Act
        $priceProductTransfers = $reader->findPriceMerchantRelationshipConcrete(7, 5);

        // Assert
        $this->assertSame([], $priceProductTransfers);
    }

    protected function createReaderWithFlagEnabled(
        PriceProductMerchantRelationshipStorageToStorageClientInterface $storageClient,
        MerchantRelationBusinessUnitLookupReaderInterface $lookupReader,
        ?PriceProductMerchantRelationshipKeyGeneratorInterface $priceProductMerchantRelationshipKeyGenerator = null,
        ?PriceProductMapperInterface $priceProductMapper = null,
        bool $isFallbackEnabled = false
    ): PriceProductMerchantRelationshipConcreteReader {
        $config = $this->createStub(PriceProductMerchantRelationshipStorageConfig::class);
        $config->method('isMerchantRelationPriceKeyEnabled')->willReturn(true);
        $config->method('isMerchantRelationPriceKeyFallbackEnabled')->willReturn($isFallbackEnabled);

        return new PriceProductMerchantRelationshipConcreteReader(
            $storageClient,
            $priceProductMerchantRelationshipKeyGenerator ?? $this->createStub(PriceProductMerchantRelationshipKeyGeneratorInterface::class),
            $priceProductMapper ?? $this->createStub(PriceProductMapperInterface::class),
            $config,
            $lookupReader,
            new MerchantRelationshipPriceStorageReader($storageClient),
        );
    }
}
