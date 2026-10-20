<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Client\PriceProductMerchantRelationshipStorage\Storage;

use PHPUnit\Framework\TestCase;
use Spryker\Client\PriceProductMerchantRelationshipStorage\Dependency\Client\PriceProductMerchantRelationshipStorageToStorageClientInterface;
use Spryker\Client\PriceProductMerchantRelationshipStorage\Storage\MerchantRelationBusinessUnitLookupReader;
use Spryker\Client\PriceProductMerchantRelationshipStorage\Storage\PriceProductMerchantRelationshipKeyGeneratorInterface;
use Spryker\Shared\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageConstants;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group PriceProductMerchantRelationshipStorage
 * @group Storage
 * @group MerchantRelationBusinessUnitLookupReaderTest
 * Add your own group annotations below this line
 */
class MerchantRelationBusinessUnitLookupReaderTest extends TestCase
{
    public function testFindMerchantRelationshipIdsByCompanyBusinessUnitIdReturnsIdsFromStorage(): void
    {
        // Arrange
        $reader = $this->createReader([PriceProductMerchantRelationshipStorageConstants::MERCHANT_RELATION_BUSINESS_UNIT_DATA_KEY_MERCHANT_RELATIONSHIP_IDS => [2, 7]]);

        // Act
        $idMerchantRelationships = $reader->findMerchantRelationshipIdsByCompanyBusinessUnitId(501);

        // Assert
        $this->assertSame([2, 7], $idMerchantRelationships);
    }

    public function testFindMerchantRelationshipIdsByCompanyBusinessUnitIdReturnsEmptyArrayWhenKeyMissing(): void
    {
        // Arrange
        $reader = $this->createReader(null);

        // Act
        $idMerchantRelationships = $reader->findMerchantRelationshipIdsByCompanyBusinessUnitId(502);

        // Assert
        $this->assertSame([], $idMerchantRelationships);
    }

    public function testFindMerchantRelationshipIdsByCompanyBusinessUnitIdReadsStorageOnEveryCall(): void
    {
        // Arrange
        $keyGenerator = $this->createStub(PriceProductMerchantRelationshipKeyGeneratorInterface::class);
        $keyGenerator->method('generateKeyByCompanyBusinessUnit')->willReturn('kv:mrbu:503');

        $storageClient = $this->createMock(PriceProductMerchantRelationshipStorageToStorageClientInterface::class);
        $storageClient->expects($this->exactly(2))->method('get')->willReturnOnConsecutiveCalls(
            null,
            [PriceProductMerchantRelationshipStorageConstants::MERCHANT_RELATION_BUSINESS_UNIT_DATA_KEY_MERCHANT_RELATIONSHIP_IDS => [9]],
        );

        $reader = new MerchantRelationBusinessUnitLookupReader($storageClient, $keyGenerator);

        // Act
        $firstResult = $reader->findMerchantRelationshipIdsByCompanyBusinessUnitId(503);
        $secondResult = $reader->findMerchantRelationshipIdsByCompanyBusinessUnitId(503);

        // Assert
        $this->assertSame([], $firstResult);
        $this->assertSame([9], $secondResult);
    }

    protected function createReader(?array $storedData): MerchantRelationBusinessUnitLookupReader
    {
        $keyGenerator = $this->createStub(PriceProductMerchantRelationshipKeyGeneratorInterface::class);
        $keyGenerator->method('generateKeyByCompanyBusinessUnit')->willReturn('kv:mrbu:x');

        $storageClient = $this->createStub(PriceProductMerchantRelationshipStorageToStorageClientInterface::class);
        $storageClient->method('get')->willReturn($storedData);

        return new MerchantRelationBusinessUnitLookupReader($storageClient, $keyGenerator);
    }
}
