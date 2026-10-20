<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Client\PriceProductMerchantRelationshipStorage\Storage;

use PHPUnit\Framework\TestCase;
use Spryker\Client\PriceProductMerchantRelationshipStorage\Dependency\Client\PriceProductMerchantRelationshipStorageToStorageClientInterface;
use Spryker\Client\PriceProductMerchantRelationshipStorage\Storage\MerchantRelationshipPriceStorageReader;
use Spryker\Client\Storage\Redis\Service;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group PriceProductMerchantRelationshipStorage
 * @group Storage
 * @group MerchantRelationshipPriceStorageReaderTest
 * Add your own group annotations below this line
 */
class MerchantRelationshipPriceStorageReaderTest extends TestCase
{
    protected const string FIRST_KEY = 'price_product_abstract_merchant_relationship:de:7:mr2';

    protected const string SECOND_KEY = 'price_product_abstract_merchant_relationship:de:7:mr9';

    public function testFindMergedPricesByKeysKeepsPricesOfEveryMerchantRelationshipForSameCurrencyAndPriceType(): void
    {
        // Arrange
        $reader = $this->createReader([
            static::FIRST_KEY => ['prices' => [2 => ['EUR' => ['GROSS_MODE' => ['DEFAULT' => 100], 'NET_MODE' => ['DEFAULT' => 90]]]]],
            static::SECOND_KEY => ['prices' => [9 => ['EUR' => ['GROSS_MODE' => ['DEFAULT' => 80], 'NET_MODE' => ['DEFAULT' => 70]]]]],
        ]);

        // Act
        $prices = $reader->findMergedPricesByKeys([static::FIRST_KEY, static::SECOND_KEY]);

        // Assert
        $this->assertSame([
            2 => ['EUR' => ['GROSS_MODE' => ['DEFAULT' => 100], 'NET_MODE' => ['DEFAULT' => 90]]],
            9 => ['EUR' => ['GROSS_MODE' => ['DEFAULT' => 80], 'NET_MODE' => ['DEFAULT' => 70]]],
        ], $prices);
    }

    public function testFindMergedPricesByKeysKeepsMerchantRelationshipIdsAsKeys(): void
    {
        // Arrange
        $reader = $this->createReader([
            static::FIRST_KEY => ['prices' => [2 => ['EUR' => ['GROSS_MODE' => ['DEFAULT' => 100]]]]],
            static::SECOND_KEY => ['prices' => [9 => ['CHF' => ['GROSS_MODE' => ['ORIGINAL' => 120]]]]],
        ]);

        // Act
        $prices = $reader->findMergedPricesByKeys([static::FIRST_KEY, static::SECOND_KEY]);

        // Assert
        $this->assertSame([2, 9], array_keys($prices));
    }

    public function testFindMergedPricesByKeysLetsLaterKeyWinForSameMerchantRelationshipLeaf(): void
    {
        // Arrange
        $reader = $this->createReader([
            static::FIRST_KEY => ['prices' => [2 => ['EUR' => ['GROSS_MODE' => ['DEFAULT' => 100, 'ORIGINAL' => 150]]]]],
            static::SECOND_KEY => ['prices' => [2 => ['EUR' => ['GROSS_MODE' => ['DEFAULT' => 110]]]]],
        ]);

        // Act
        $prices = $reader->findMergedPricesByKeys([static::FIRST_KEY, static::SECOND_KEY]);

        // Assert
        $this->assertSame([2 => ['EUR' => ['GROSS_MODE' => ['DEFAULT' => 110, 'ORIGINAL' => 150]]]], $prices);
    }

    public function testFindMergedPricesByKeysSkipsMissingAndInvalidValues(): void
    {
        // Arrange
        $storageClient = $this->createStub(PriceProductMerchantRelationshipStorageToStorageClientInterface::class);
        $storageClient->method('getMulti')->willReturn([
            Service::KV_PREFIX . static::FIRST_KEY => 1,
            Service::KV_PREFIX . static::SECOND_KEY => json_encode(['noPrices' => []]),
        ]);

        // Act
        $prices = (new MerchantRelationshipPriceStorageReader($storageClient))->findMergedPricesByKeys([static::FIRST_KEY, static::SECOND_KEY]);

        // Assert
        $this->assertSame([], $prices);
    }

    /**
     * @param array<string, array<string, mixed>> $valuesByKey
     */
    protected function createReader(array $valuesByKey): MerchantRelationshipPriceStorageReader
    {
        $keyValues = [];
        foreach ($valuesByKey as $key => $value) {
            $keyValues[Service::KV_PREFIX . $key] = json_encode($value);
        }

        $storageClient = $this->createStub(PriceProductMerchantRelationshipStorageToStorageClientInterface::class);
        $storageClient->method('getMulti')->willReturn($keyValues);

        return new MerchantRelationshipPriceStorageReader($storageClient);
    }
}
