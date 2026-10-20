<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PriceProductMerchantRelationshipStorage;

use Spryker\Zed\Kernel\AbstractBundleConfig;

/**
 * @method \Spryker\Shared\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageConfig getSharedConfig()
 */
class PriceProductMerchantRelationshipStorageConfig extends AbstractBundleConfig
{
    /**
     * @api
     *
     * @var string
     */
    public const PRICE_PRODUCT_MERCHANT_RELATIONSHIP_SYNC_STORAGE_QUEUE = 'sync.storage.price';

    /**
     * @api
     *
     * @uses \Orm\Zed\MerchantRelationship\Persistence\Map\SpyMerchantRelationshipToCompanyBusinessUnitTableMap::COL_FK_COMPANY_BUSINESS_UNIT
     *
     * @var string
     */
    public const COL_FK_COMPANY_BUSINESS_UNIT = 'spy_merchant_relationship_to_company_business_unit.fk_company_business_unit';

    /**
     * @api
     *
     * @uses \Orm\Zed\PriceProductMerchantRelationship\Persistence\Map\SpyPriceProductMerchantRelationshipTableMap::COL_FK_MERCHANT_RELATIONSHIP
     *
     * @var string
     */
    public const COL_FK_MERCHANT_RELATIONSHIP = 'spy_price_product_merchant_relationship.fk_merchant_relationship';

    /**
     * @api
     *
     * @uses \Orm\Zed\PriceProductMerchantRelationship\Persistence\Map\SpyPriceProductMerchantRelationshipTableMap::COL_FK_PRICE_PRODUCT_STORE
     *
     * @var string
     */
    public const COL_FK_PRICE_PRODUCT_STORE = 'spy_price_product_merchant_relationship.fk_price_product_store';

    /**
     * @api
     *
     * @uses \Orm\Zed\PriceProductMerchantRelationship\Persistence\Map\SpyPriceProductMerchantRelationshipTableMap::COL_FK_PRODUCT
     *
     * @var string
     */
    public const COL_FK_PRODUCT = 'spy_price_product_merchant_relationship.fk_product';

    /**
     * @api
     *
     * @uses \Orm\Zed\PriceProductMerchantRelationship\Persistence\Map\SpyPriceProductMerchantRelationshipTableMap::COL_FK_PRODUCT_ABSTRACT
     *
     * @var string
     */
    public const COL_FK_PRODUCT_ABSTRACT = 'spy_price_product_merchant_relationship.fk_product_abstract';

    /**
     * @api
     *
     * @uses \Spryker\Shared\PriceProduct\PriceProductConfig::PRICE_DATA
     *
     * @var string
     */
    public const PRICE_DATA = 'priceData';

    /**
     * @api
     *
     * @uses \Spryker\Shared\Price\PriceConfig::PRICE_MODE_GROSS
     *
     * @var string
     */
    public const PRICE_MODE_GROSS = 'GROSS_MODE';

    /**
     * @api
     *
     * @uses \Spryker\Shared\Price\PriceConfig::PRICE_MODE_NET
     *
     * @var string
     */
    public const PRICE_MODE_NET = 'NET_MODE';

    /**
     * @var int<1, max>
     */
    protected const int COMPANY_BUSINESS_UNIT_CHUNK_SIZE = 100;

    /**
     * @var int<1, max>
     */
    protected const int PRODUCT_CHUNK_SIZE = 200;

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
     * - Returns the number of company business unit IDs resolved per reconciliation query when publishing merchant relationship prices.
     * - Lower values reduce peak memory and query size at the cost of more round trips.
     *
     * @api
     *
     * @return int<1, max>
     */
    public function getCompanyBusinessUnitChunkSize(): int
    {
        return static::COMPANY_BUSINESS_UNIT_CHUNK_SIZE;
    }

    /**
     * Specification:
     * - Returns the number of product IDs published per batch when publishing merchant relationship prices.
     * - Lower values reduce peak memory and query size at the cost of more round trips.
     *
     * @api
     *
     * @return int<1, max>
     */
    public function getProductChunkSize(): int
    {
        return static::PRODUCT_CHUNK_SIZE;
    }

    /**
     * @api
     *
     * @return string|null
     */
    public function getPriceProductConcreteMerchantRelationSynchronizationPoolName(): ?string
    {
        return null;
    }

    /**
     * @api
     *
     * @return string|null
     */
    public function getPriceProductAbstractMerchantRelationSynchronizationPoolName(): ?string
    {
        return null;
    }

    /**
     * @api
     *
     * @return string|null
     */
    public function getMerchantRelationBusinessUnitSynchronizationPoolName(): ?string
    {
        return null;
    }

    /**
     * @api
     *
     * @deprecated Use {@link \Spryker\Zed\SynchronizationBehavior\SynchronizationBehaviorConfig::isSynchronizationEnabled()} instead.
     *
     * @return bool
     */
    public function isSendingToQueue(): bool
    {
        return true;
    }

    /**
     * @api
     *
     * @return string|null
     */
    public function getMerchantRelationEventQueueName(): ?string
    {
        return null;
    }

    /**
     * @api
     *
     * @return string|null
     */
    public function getPriceProductConcreteMerchantRelationEventQueueName(): ?string
    {
        return null;
    }

    /**
     * @api
     *
     * @return string|null
     */
    public function getPriceProductAbstractMerchantRelationEventQueueName(): ?string
    {
        return null;
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
}
