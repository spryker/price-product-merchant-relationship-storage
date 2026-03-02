<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\PriceProductMerchantRelationshipStorage;

use Spryker\Client\Kernel\AbstractFactory;
use Spryker\Client\PriceProductMerchantRelationshipStorage\Dependency\Client\PriceProductMerchantRelationshipStorageToCustomerClientInterface;
use Spryker\Client\PriceProductMerchantRelationshipStorage\Dependency\Client\PriceProductMerchantRelationshipStorageToStorageClientInterface;
use Spryker\Client\PriceProductMerchantRelationshipStorage\Dependency\Client\PriceProductMerchantRelationshipStorageToStoreClientInterface;
use Spryker\Client\PriceProductMerchantRelationshipStorage\Dependency\Service\PriceProductMerchantRelationshipStorageToPriceProductServiceInterface;
use Spryker\Client\PriceProductMerchantRelationshipStorage\Dependency\Service\PriceProductMerchantRelationshipToSynchronizationServiceInterface;
use Spryker\Client\PriceProductMerchantRelationshipStorage\MerchantRelationshipFinder\CompanyBusinessUnitFinder;
use Spryker\Client\PriceProductMerchantRelationshipStorage\MerchantRelationshipFinder\CompanyBusinessUnitFinderInterface;
use Spryker\Client\PriceProductMerchantRelationshipStorage\Storage\PriceProductMapper;
use Spryker\Client\PriceProductMerchantRelationshipStorage\Storage\PriceProductMapperInterface;
use Spryker\Client\PriceProductMerchantRelationshipStorage\Storage\PriceProductMerchantRelationshipAbstractReader;
use Spryker\Client\PriceProductMerchantRelationshipStorage\Storage\PriceProductMerchantRelationshipAbstractReaderInterface;
use Spryker\Client\PriceProductMerchantRelationshipStorage\Storage\PriceProductMerchantRelationshipConcreteReader;
use Spryker\Client\PriceProductMerchantRelationshipStorage\Storage\PriceProductMerchantRelationshipConcreteReaderInterface;
use Spryker\Client\PriceProductMerchantRelationshipStorage\Storage\PriceProductMerchantRelationshipKeyGenerator;
use Spryker\Client\PriceProductMerchantRelationshipStorage\Storage\PriceProductMerchantRelationshipKeyGeneratorInterface;

/**
 * @method \Spryker\Client\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageConfig getConfig()
 */
class PriceProductMerchantRelationshipStorageFactory extends AbstractFactory
{
    public function createPriceProductMerchantRelationshipAbstractReader(): PriceProductMerchantRelationshipAbstractReaderInterface
    {
        return new PriceProductMerchantRelationshipAbstractReader(
            $this->getStorageClient(),
            $this->createPriceProductMerchantRelationshipKeyGenerator(),
            $this->createPriceProductMapper(),
        );
    }

    public function createPriceProductMerchantRelationshipConcreteReader(): PriceProductMerchantRelationshipConcreteReaderInterface
    {
        return new PriceProductMerchantRelationshipConcreteReader(
            $this->getStorageClient(),
            $this->createPriceProductMerchantRelationshipKeyGenerator(),
            $this->createPriceProductMapper(),
        );
    }

    public function createCompanyBusinessUnitFinder(): CompanyBusinessUnitFinderInterface
    {
        return new CompanyBusinessUnitFinder($this->getCustomerClient());
    }

    public function createPriceProductMerchantRelationshipKeyGenerator(): PriceProductMerchantRelationshipKeyGeneratorInterface
    {
        return new PriceProductMerchantRelationshipKeyGenerator(
            $this->getSynchronizationService(),
            $this->getStoreClient(),
        );
    }

    public function getCustomerClient(): PriceProductMerchantRelationshipStorageToCustomerClientInterface
    {
        return $this->getProvidedDependency(PriceProductMerchantRelationshipStorageDependencyProvider::CLIENT_CUSTOMER);
    }

    public function getStorageClient(): PriceProductMerchantRelationshipStorageToStorageClientInterface
    {
        return $this->getProvidedDependency(PriceProductMerchantRelationshipStorageDependencyProvider::CLIENT_STORAGE);
    }

    public function getPriceProductService(): PriceProductMerchantRelationshipStorageToPriceProductServiceInterface
    {
        return $this->getProvidedDependency(PriceProductMerchantRelationshipStorageDependencyProvider::SERVICE_PRICE_PRODUCT);
    }

    public function getStoreClient(): PriceProductMerchantRelationshipStorageToStoreClientInterface
    {
        return $this->getProvidedDependency(PriceProductMerchantRelationshipStorageDependencyProvider::CLIENT_STORE);
    }

    public function getSynchronizationService(): PriceProductMerchantRelationshipToSynchronizationServiceInterface
    {
        return $this->getProvidedDependency(PriceProductMerchantRelationshipStorageDependencyProvider::SERVICE_SYNCHRONIZATION);
    }

    public function getPriceProductMerchantRelationshipStorageConfig(): PriceProductMerchantRelationshipStorageConfig
    {
        return $this->getConfig();
    }

    public function createPriceProductMapper(): PriceProductMapperInterface
    {
        return new PriceProductMapper(
            $this->getConfig(),
            $this->getPriceProductService(),
        );
    }
}
