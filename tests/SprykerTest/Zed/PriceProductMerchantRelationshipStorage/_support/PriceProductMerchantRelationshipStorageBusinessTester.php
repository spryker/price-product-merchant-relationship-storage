<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Zed\PriceProductMerchantRelationshipStorage;

use Codeception\Actor;
use Spryker\Zed\PriceProductMerchantRelationshipStorage\Business\PriceProductMerchantRelationshipStorageBusinessFactory;
use Spryker\Zed\PriceProductMerchantRelationshipStorage\Business\PriceProductMerchantRelationshipStorageFacade;
use Spryker\Zed\PriceProductMerchantRelationshipStorage\Persistence\PriceProductMerchantRelationshipStorageEntityManager;
use Spryker\Zed\PriceProductMerchantRelationshipStorage\Persistence\PriceProductMerchantRelationshipStoragePersistenceFactory;
use Spryker\Zed\PriceProductMerchantRelationshipStorage\Persistence\PriceProductMerchantRelationshipStorageRepository;

/**
 * Inherited Methods
 *
 * @method void wantTo($text)
 * @method void wantToTest($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause($vars = [])
 *
 * @SuppressWarnings(\SprykerTest\Zed\PriceProductMerchantRelationshipStorage\PHPMD)
 */
class PriceProductMerchantRelationshipStorageBusinessTester extends Actor
{
    use _generated\PriceProductMerchantRelationshipStorageBusinessTesterActions;

    public function createFacadeWithMerchantRelationPriceKeyFlag(bool $isMerchantRelationPriceKeyEnabled): PriceProductMerchantRelationshipStorageFacade
    {
        $priceProductMerchantRelationshipStorageFacade = new PriceProductMerchantRelationshipStorageFacade();
        $priceProductMerchantRelationshipStorageFacade->setFactory(
            $this->createBusinessFactoryWithMerchantRelationPriceKeyFlag($isMerchantRelationPriceKeyEnabled),
        );

        return $priceProductMerchantRelationshipStorageFacade;
    }

    public function createBusinessFactoryWithMerchantRelationPriceKeyFlag(
        bool $isMerchantRelationPriceKeyEnabled
    ): PriceProductMerchantRelationshipStorageBusinessFactory {
        $persistenceFactory = $this->createPersistenceFactoryWithMerchantRelationPriceKeyFlag($isMerchantRelationPriceKeyEnabled);

        /** @var \Spryker\Zed\PriceProductMerchantRelationshipStorage\Business\PriceProductMerchantRelationshipStorageBusinessFactory $businessFactory */
        $businessFactory = $this->getFactory();
        $businessFactory->setRepository((new PriceProductMerchantRelationshipStorageRepository())->setFactory($persistenceFactory));
        $businessFactory->setEntityManager((new PriceProductMerchantRelationshipStorageEntityManager())->setFactory($persistenceFactory));

        return $businessFactory;
    }

    public function createRepositoryWithMerchantRelationPriceKeyFlag(bool $isMerchantRelationPriceKeyEnabled): PriceProductMerchantRelationshipStorageRepository
    {
        $priceProductMerchantRelationshipStorageRepository = new PriceProductMerchantRelationshipStorageRepository();
        $priceProductMerchantRelationshipStorageRepository->setFactory(
            $this->createPersistenceFactoryWithMerchantRelationPriceKeyFlag($isMerchantRelationPriceKeyEnabled),
        );

        return $priceProductMerchantRelationshipStorageRepository;
    }

    protected function createPersistenceFactoryWithMerchantRelationPriceKeyFlag(
        bool $isMerchantRelationPriceKeyEnabled
    ): PriceProductMerchantRelationshipStoragePersistenceFactory {
        $this->mockConfigMethod('isMerchantRelationPriceKeyEnabled', $isMerchantRelationPriceKeyEnabled);

        $persistenceFactory = new PriceProductMerchantRelationshipStoragePersistenceFactory();
        $persistenceFactory->setConfig($this->getModuleConfig());

        return $persistenceFactory;
    }
}
