<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\PriceProductMerchantRelationshipStorage\MerchantRelationshipFinder;

use Generated\Shared\Transfer\CustomerTransfer;

class CompanyBusinessUnitFinder implements CompanyBusinessUnitFinderInterface
{
    /**
     * @var \Spryker\Client\PriceProductMerchantRelationshipStorage\Dependency\Client\PriceProductMerchantRelationshipStorageToCustomerClientInterface
     */
    protected $customerClient;

    /**
     * @var \Generated\Shared\Transfer\CustomerTransfer|null
     */
    protected static $customerTransfer;

    /**
     * @var bool
     */
    protected static $customerTransferLoaded = false;

    /**
     * @param \Spryker\Client\PriceProductMerchantRelationshipStorage\Dependency\Client\PriceProductMerchantRelationshipStorageToCustomerClientInterface $customerClient
     */
    public function __construct($customerClient)
    {
        $this->customerClient = $customerClient;
    }

    public function findCurrentCustomerCompanyBusinessUnitId(): ?int
    {
        $customerTransfer = $this->getCustomerTransfer();

        if ($customerTransfer === null) {
            return null;
        }

        $companyTransfer = $customerTransfer->getCompanyUserTransfer();
        if (!$companyTransfer) {
            return null;
        }

        $companyBusinessUnit = $companyTransfer->getCompanyBusinessUnit();
        if (!$companyBusinessUnit) {
            return null;
        }

        return $companyBusinessUnit->getIdCompanyBusinessUnit();
    }

    protected function getCustomerTransfer(): ?CustomerTransfer
    {
        if (static::$customerTransferLoaded === false) {
            static::$customerTransfer = $this->customerClient->getCustomer();
            static::$customerTransferLoaded = true;
        }

        return static::$customerTransfer;
    }
}
