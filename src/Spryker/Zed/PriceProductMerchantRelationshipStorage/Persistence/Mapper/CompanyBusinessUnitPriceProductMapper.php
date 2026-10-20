<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PriceProductMerchantRelationshipStorage\Persistence\Mapper;

use Generated\Shared\Transfer\PriceProductMerchantRelationshipStorageTransfer;
use Generated\Shared\Transfer\PriceProductMerchantRelationshipValueTransfer;
use Spryker\Zed\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageConfig;

class CompanyBusinessUnitPriceProductMapper implements CompanyBusinessUnitPriceProductMapperInterface
{
    protected const string PRICE_KEY_SEPARATOR = ':';

    protected const string MERCHANT_RELATIONSHIP_KEY_MARKER = 'mr';

    public function __construct(protected PriceProductMerchantRelationshipStorageConfig $priceProductMerchantRelationshipStorageConfig)
    {
    }

    /**
     * @param array<array<string, mixed>> $priceProductMerchantRelationshipTransfers
     *
     * @return array<\Generated\Shared\Transfer\PriceProductMerchantRelationshipStorageTransfer>
     */
    public function mapPriceProductMerchantRelationshipArrayToTransfers(array $priceProductMerchantRelationshipTransfers): array
    {
        $pricesByKey = [];
        foreach ($priceProductMerchantRelationshipTransfers as $priceProductMerchantRelationshipData) {
            $uniquePriceIndex = $this->createUniquePriceIndex($priceProductMerchantRelationshipData);
            if (!isset($pricesByKey[$uniquePriceIndex])) {
                $pricesByKey[$uniquePriceIndex] = $this->createPriceProductMerchantRelationshipStorageTransfer(
                    $priceProductMerchantRelationshipData,
                    $uniquePriceIndex,
                );
            }

            $this->addUngroupedPrice(
                $pricesByKey[$uniquePriceIndex],
                $priceProductMerchantRelationshipData,
            );
        }

        return $pricesByKey;
    }

    /**
     * @param array<string, mixed> $priceProductMerchantRelationshipData
     */
    protected function createPriceProductMerchantRelationshipStorageTransfer(
        array $priceProductMerchantRelationshipData,
        string $uniquePriceIndex
    ): PriceProductMerchantRelationshipStorageTransfer {
        $priceProductMerchantRelationshipStorageTransfer = (new PriceProductMerchantRelationshipStorageTransfer())
            ->fromArray($priceProductMerchantRelationshipData, true)
            ->setPriceKey($uniquePriceIndex);

        if (!$this->priceProductMerchantRelationshipStorageConfig->isMerchantRelationPriceKeyEnabled()) {
            return $priceProductMerchantRelationshipStorageTransfer->setIdMerchantRelationship(null);
        }

        return $priceProductMerchantRelationshipStorageTransfer;
    }

    /**
     * @param array<string, mixed> $priceProductMerchantRelationshipData
     */
    protected function createUniquePriceIndex(array $priceProductMerchantRelationshipData): string
    {
        if ($this->priceProductMerchantRelationshipStorageConfig->isMerchantRelationPriceKeyEnabled()) {
            return implode(static::PRICE_KEY_SEPARATOR, [
                $priceProductMerchantRelationshipData[PriceProductMerchantRelationshipStorageTransfer::STORE_NAME],
                $priceProductMerchantRelationshipData[PriceProductMerchantRelationshipStorageTransfer::ID_PRODUCT],
                static::MERCHANT_RELATIONSHIP_KEY_MARKER . $priceProductMerchantRelationshipData[PriceProductMerchantRelationshipStorageTransfer::ID_MERCHANT_RELATIONSHIP],
            ]);
        }

        return implode(static::PRICE_KEY_SEPARATOR, [
            $priceProductMerchantRelationshipData[PriceProductMerchantRelationshipStorageTransfer::STORE_NAME],
            $priceProductMerchantRelationshipData[PriceProductMerchantRelationshipStorageTransfer::ID_PRODUCT],
            $priceProductMerchantRelationshipData[PriceProductMerchantRelationshipStorageTransfer::ID_COMPANY_BUSINESS_UNIT],
        ]);
    }

    /**
     * @param array<string, mixed> $priceProductMerchantRelationshipData
     */
    protected function addUngroupedPrice(
        PriceProductMerchantRelationshipStorageTransfer $merchantRelationshipStorageTransfer,
        array $priceProductMerchantRelationshipData
    ): void {
        $merchantRelationshipStorageTransfer->addUngroupedPrice(
            (new PriceProductMerchantRelationshipValueTransfer())
                ->fromArray($priceProductMerchantRelationshipData, true),
        );
    }
}
