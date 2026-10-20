<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\PriceProductMerchantRelationshipStorage;

use Spryker\Shared\Kernel\AbstractSharedConfig;

class PriceProductMerchantRelationshipStorageConfig extends AbstractSharedConfig
{
    /**
     * Price Dimension Merchant Relationship
     *
     * @uses \Spryker\Shared\PriceProductMerchantRelationship\PriceProductMerchantRelationshipConfig::PRICE_DIMENSION_MERCHANT_RELATIONSHIP
     *
     * @var string
     */
    protected const PRICE_DIMENSION_MERCHANT_RELATIONSHIP = 'PRICE_DIMENSION_MERCHANT_RELATIONSHIP';

    /**
     * @api
     *
     * @uses \Spryker\Shared\PriceProduct\PriceProductConfig::PRICE_MODES
     *
     * @var array<string>
     */
    public const PRICE_MODES = [
        'NET_MODE',
        'GROSS_MODE',
    ];

    /**
     * @api
     *
     * @uses \Spryker\Shared\Price\PriceConfig::PRICE_MODE_GROSS
     *
     * @var string
     */
    public const PRICE_GROSS_MODE = 'GROSS_MODE';

    /**
     * Specification:
     * - This events will be used for spy_merchant entity changes.
     *
     * @uses \Spryker\Zed\Merchant\Dependency\MerchantEvents::ENTITY_SPY_MERCHANT_UPDATE
     *
     * @api
     *
     * @var string
     */
    public const ENTITY_SPY_MERCHANT_UPDATE = 'Entity.spy_merchant.update';

    /**
     * Specification:
     * - This event will be used for spy_price_product_merchant_relationship publishing.
     *
     * @api
     *
     * @var string
     */
    public const PRICE_PRODUCT_ABSTRACT_MERCHANT_RELATIONSHIP_PUBLISH = 'PriceProductMerchantRelationship.price_product_abstract_merchant_relationship.publish';

    /**
     * Specification:
     * - This event will be used for spy_price_product_merchant_relationship un-publishing.
     *
     * @api
     *
     * @var string
     */
    public const PRICE_PRODUCT_ABSTRACT_MERCHANT_RELATIONSHIP_UNPUBLISH = 'PriceProductMerchantRelationship.price_product_abstract_merchant_relationship.unpublish';

    /**
     * Specification:
     * - This event will be used for spy_price_product_merchant_relationship publishing.
     *
     * @api
     *
     * @var string
     */
    public const PRICE_PRODUCT_CONCRETE_MERCHANT_RELATIONSHIP_PUBLISH = 'PriceProductMerchantRelationship.price_product_concrete_merchant_relationship.publish';

    /**
     * Specification:
     * - This event will be used for spy_price_product_merchant_relationship un-publishing.
     *
     * @api
     *
     * @var string
     */
    public const PRICE_PRODUCT_CONCRETE_MERCHANT_RELATIONSHIP_UNPUBLISH = 'PriceProductMerchantRelationship.price_product_concrete_merchant_relationship.unpublish';

    /**
     * Specification:
     * - Queue name used for price product merchant relationship publish events.
     *
     * @api
     *
     * @var string
     */
    public const string PUBLISH_PRICE_PRODUCT_MERCHANT_RELATIONSHIP_QUEUE = 'publish.price_product_merchant_relationship';

    public const string MERCHANT_RELATION_BUSINESS_UNIT_PUBLISH = 'PriceProductMerchantRelationshipStorage.merchant_relation_business_unit.publish';

    public const int ID_COMPANY_BUSINESS_UNIT_NONE = 0;

    /**
     * Specification:
     * - Queue name used for price product concrete merchant relationship publish events.
     *
     * @api
     *
     * @var string
     */
    public const string PUBLISH_PRICE_PRODUCT_CONCRETE_MERCHANT_RELATIONSHIP_QUEUE = 'publish.price_product_concrete_merchant_relationship';

    /**
     * Specification:
     * - Queue name used for price product abstract merchant relationship publish events.
     *
     * @api
     *
     * @var string
     */
    public const string PUBLISH_PRICE_PRODUCT_ABSTRACT_MERCHANT_RELATIONSHIP_QUEUE = 'publish.price_product_abstract_merchant_relationship';

    /**
     * @api
     *
     * @return string
     */
    public function getPriceDimensionMerchantRelationship(): string
    {
        return static::PRICE_DIMENSION_MERCHANT_RELATIONSHIP;
    }

    /**
     * Specification:
     * - Returns `true` when merchant-relationship prices are stored per merchant relationship, `false` when they are stored per company business unit.
     * - `false`: one storage row and one storage key per store, product and company business unit.
     *   Example: `price_key` `DE:205:1`, storage key `price_product_abstract_merchant_relationship:de:205:1`.
     * - `true`: one storage row and one storage key per store, product and merchant relationship.
     *   Example: `price_key` `DE:205:mr3`, storage key `price_product_abstract_merchant_relationship:de:205:mr3`.
     *   One additional lookup key per company business unit (`mrbu:<idCompanyBusinessUnit>`) lists the merchant relationship IDs of the business unit.
     *   The Client reads the lookup key, then reads the price keys of all merchant relationships with one `getMulti()` call and merges the prices.
     * - Set `true` when merchant relationships have many company business units. The number of storage rows and keys drops by the number of business units per merchant relationship.
     * - Keep `false` when each merchant relationship has one company business unit. There is no reduction, and each price read needs one additional lookup key read.
     * - After a change of the value, publish and synchronize the merchant-relationship prices and the `mrbu` resource, then run `price-product-merchant-relationship-storage:cleanup` to remove the rows of the other key shape.
     * - Zed and Client read this value. Override this method on project level in the Shared config.
     * - Changes the content of the transfers passed to `PriceProductMerchantRelationshipStorageFilterPluginInterface`: when `true`, `idCompanyBusinessUnit` is `null` and `idMerchantRelationship` is set. Check the project filter plugins before you change the value.
     *
     * @api
     *
     * @return bool
     */
    public function isMerchantRelationPriceKeyEnabled(): bool
    {
        return false;
    }
}
