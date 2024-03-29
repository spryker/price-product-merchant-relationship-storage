<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\PriceProductMerchantRelationshipStorage;

use Spryker\Client\Kernel\AbstractDependencyProvider;
use Spryker\Client\Kernel\Container;
use Spryker\Client\PriceProductMerchantRelationshipStorage\Dependency\Client\PriceProductMerchantRelationshipStorageToCustomerClientBridge;
use Spryker\Client\PriceProductMerchantRelationshipStorage\Dependency\Client\PriceProductMerchantRelationshipStorageToStorageClientBridge;
use Spryker\Client\PriceProductMerchantRelationshipStorage\Dependency\Client\PriceProductMerchantRelationshipStorageToStoreClientBridge;
use Spryker\Client\PriceProductMerchantRelationshipStorage\Dependency\Service\PriceProductMerchantRelationshipStorageToPriceProductServiceBridge;
use Spryker\Client\PriceProductMerchantRelationshipStorage\Dependency\Service\PriceProductMerchantRelationshipToSynchronizationServiceBridge;

/**
 * @method \Spryker\Client\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageConfig getConfig()
 */
class PriceProductMerchantRelationshipStorageDependencyProvider extends AbstractDependencyProvider
{
    /**
     * @var string
     */
    public const CLIENT_STORAGE = 'CLIENT_STORAGE';

    /**
     * @var string
     */
    public const CLIENT_STORE = 'CLIENT_STORE';

    /**
     * @var string
     */
    public const CLIENT_PRICE_PRODUCT = 'CLIENT_PRICE_PRODUCT';

    /**
     * @var string
     */
    public const CLIENT_CUSTOMER = 'CLIENT_CUSTOMER';

    /**
     * @var string
     */
    public const SERVICE_SYNCHRONIZATION = 'SERVICE_SYNCHRONIZATION';

    /**
     * @var string
     */
    public const SERVICE_PRICE_PRODUCT = 'SERVICE_PRICE_PRODUCT';

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    public function provideServiceLayerDependencies(Container $container): Container
    {
        $container = $this->addStorageClient($container);
        $container = $this->addStoreClient($container);
        $container = $this->addCustomerClient($container);
        $container = $this->addSynchronizationService($container);
        $container = $this->addPriceProductService($container);

        return $container;
    }

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    protected function addStorageClient(Container $container): Container
    {
        $container->set(static::CLIENT_STORAGE, function (Container $container) {
            return new PriceProductMerchantRelationshipStorageToStorageClientBridge($container->getLocator()->storage()->client());
        });

        return $container;
    }

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    protected function addSynchronizationService(Container $container): Container
    {
        $container->set(static::SERVICE_SYNCHRONIZATION, function (Container $container) {
            return new PriceProductMerchantRelationshipToSynchronizationServiceBridge($container->getLocator()->synchronization()->service());
        });

        return $container;
    }

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    protected function addStoreClient(Container $container): Container
    {
        $container->set(static::CLIENT_STORE, function (Container $container) {
            return new PriceProductMerchantRelationshipStorageToStoreClientBridge($container->getLocator()->store()->client());
        });

        return $container;
    }

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    protected function addCustomerClient(Container $container): Container
    {
        $container->set(static::CLIENT_CUSTOMER, function (Container $container) {
            return new PriceProductMerchantRelationshipStorageToCustomerClientBridge($container->getLocator()->customer()->client());
        });

        return $container;
    }

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    protected function addPriceProductService(Container $container): Container
    {
        $container->set(static::SERVICE_PRICE_PRODUCT, function (Container $container) {
            return new PriceProductMerchantRelationshipStorageToPriceProductServiceBridge($container->getLocator()->priceProduct()->service());
        });

        return $container;
    }
}
