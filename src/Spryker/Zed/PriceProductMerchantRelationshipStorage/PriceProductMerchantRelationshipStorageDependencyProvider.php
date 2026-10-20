<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PriceProductMerchantRelationshipStorage;

use Orm\Zed\MerchantRelationship\Persistence\SpyMerchantRelationshipToCompanyBusinessUnitQuery;
use Orm\Zed\PriceProductMerchantRelationship\Persistence\SpyPriceProductMerchantRelationshipQuery;
use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;
use Spryker\Zed\PriceProductMerchantRelationshipStorage\Dependency\Facade\PriceProductMerchantRelationshipStorageToEventBehaviorFacadeBridge;
use Spryker\Zed\PriceProductMerchantRelationshipStorage\Dependency\Facade\PriceProductMerchantRelationshipStorageToMerchantRelationshipFacadeBridge;

/**
 * @method \Spryker\Zed\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageConfig getConfig()
 */
class PriceProductMerchantRelationshipStorageDependencyProvider extends AbstractBundleDependencyProvider
{
    public const string PROPEL_QUERY_MERCHANT_RELATIONSHIP_TO_COMPANY_BUSINESS_UNIT = 'PROPEL_QUERY_MERCHANT_RELATIONSHIP_TO_COMPANY_BUSINESS_UNIT';

    public const string FACADE_SYNCHRONIZATION = 'FACADE_SYNCHRONIZATION';

    /**
     * @var string
     */
    public const PROPEL_QUERY_PRICE_PRODUCT_MERCHANT_RELATIONSHIP = 'PROPEL_QUERY_PRICE_PRODUCT_MERCHANT_RELATIONSHIP';

    /**
     * @var string
     */
    public const FACADE_EVENT_BEHAVIOR = 'FACADE_EVENT_BEHAVIOR';

    /**
     * @var string
     */
    public const FACADE_MERCHANT_RELATIONSHIP = 'FACADE_MERCHANT_RELATIONSHIP';

    /**
     * @var string
     */
    public const PLUGINS_PRICE_PRODUCT_MERCHANT_RELATIONSHIP_STORAGE_FILTER = 'PLUGINS_PRICE_PRODUCT_MERCHANT_RELATIONSHIP_STORAGE_FILTER';

    public function provideCommunicationLayerDependencies(Container $container): Container
    {
        $container = $this->addEventBehaviorFacade($container);
        $container = $this->addSynchronizationFacade($container);

        return $container;
    }

    public function provideBusinessLayerDependencies(Container $container): Container
    {
        $container = $this->addEventBehaviorFacade($container);
        $container = $this->addMerchantRelationshipFacade($container);
        $container = $this->addPriceProductMerchantRelationshipStorageFilterPlugins($container);

        return $container;
    }

    public function providePersistenceLayerDependencies(Container $container): Container
    {
        $container = $this->addPropelPriceProductMerchantRelationshipQuery($container);
        $container = $this->addPropelMerchantRelationshipToCompanyBusinessUnitQuery($container);

        return $container;
    }

    protected function addEventBehaviorFacade(Container $container): Container
    {
        $container->set(static::FACADE_EVENT_BEHAVIOR, function (Container $container) {
            return new PriceProductMerchantRelationshipStorageToEventBehaviorFacadeBridge(
                $container->getLocator()->eventBehavior()->facade(),
            );
        });

        return $container;
    }

    protected function addSynchronizationFacade(Container $container): Container
    {
        $container->set(static::FACADE_SYNCHRONIZATION, function (Container $container) {
            return $container->getLocator()->synchronization()->facade();
        });

        return $container;
    }

    protected function addMerchantRelationshipFacade(Container $container): Container
    {
        $container->set(static::FACADE_MERCHANT_RELATIONSHIP, function (Container $container) {
            return new PriceProductMerchantRelationshipStorageToMerchantRelationshipFacadeBridge(
                $container->getLocator()->merchantRelationship()->facade(),
            );
        });

        return $container;
    }

    protected function addPropelPriceProductMerchantRelationshipQuery(Container $container): Container
    {
        $container->set(static::PROPEL_QUERY_PRICE_PRODUCT_MERCHANT_RELATIONSHIP, $container->factory(function () {
            return SpyPriceProductMerchantRelationshipQuery::create();
        }));

        return $container;
    }

    protected function addPropelMerchantRelationshipToCompanyBusinessUnitQuery(Container $container): Container
    {
        $container->set(static::PROPEL_QUERY_MERCHANT_RELATIONSHIP_TO_COMPANY_BUSINESS_UNIT, $container->factory(function () {
            return SpyMerchantRelationshipToCompanyBusinessUnitQuery::create();
        }));

        return $container;
    }

    protected function addPriceProductMerchantRelationshipStorageFilterPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_PRICE_PRODUCT_MERCHANT_RELATIONSHIP_STORAGE_FILTER, function () {
            return $this->getPriceProductMerchantRelationshipStorageFilterPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Zed\PriceProductMerchantRelationshipStorageExtension\Dependency\Plugin\PriceProductMerchantRelationshipStorageFilterPluginInterface>
     */
    protected function getPriceProductMerchantRelationshipStorageFilterPlugins(): array
    {
        return [];
    }
}
