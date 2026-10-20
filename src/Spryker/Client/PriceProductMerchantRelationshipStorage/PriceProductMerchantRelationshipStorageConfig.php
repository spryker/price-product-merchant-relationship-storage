<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\PriceProductMerchantRelationshipStorage;

use Spryker\Client\Kernel\AbstractBundleConfig;

/**
 * @method \Spryker\Shared\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageConfig getSharedConfig()
 */
class PriceProductMerchantRelationshipStorageConfig extends AbstractBundleConfig
{
    /**
     * @api
     *
     * @return string
     */
    public function getPriceDimensionMerchantRelationship()
    {
        return $this->getSharedConfig()->getPriceDimensionMerchantRelationship();
    }

    /**
     * Specification:
     * - Returns `true` when merchant-relationship prices are stored per merchant relationship, `false` when they are stored per company business unit.
     * - See {@link \Spryker\Shared\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageConfig::isMerchantRelationPriceKeyEnabled()}.
     *
     * @api
     *
     * @return bool
     */
    public function isMerchantRelationPriceKeyEnabled(): bool
    {
        return $this->getSharedConfig()->isMerchantRelationPriceKeyEnabled();
    }

    /**
     * Specification:
     * - Applies only when {@link isMerchantRelationPriceKeyEnabled()} returns `true`.
     * - Returns `true` when the Client reads the prices per company business unit if the merchant-relationship read returns no prices.
     * - Use `true` during the rollout: the storage can contain only the old key shape until publish and synchronize are complete.
     * - The fallback applies only when the merchant-relationship read returns no prices at all for the product and the company business unit.
     * - When only some merchant relationships of a company business unit are published, the Client returns only the published prices.
     * - Until the cleanup, the fallback can return prices that are no longer current, for example after all merchant-relationship prices of a product are deleted.
     * - Set `false` right after publish, synchronize and `price-product-merchant-relationship-storage:cleanup` are complete. Each price read without merchant-relationship prices then needs no additional key read.
     *
     * @api
     *
     * @return bool
     */
    public function isMerchantRelationPriceKeyFallbackEnabled(): bool
    {
        return false;
    }
}
