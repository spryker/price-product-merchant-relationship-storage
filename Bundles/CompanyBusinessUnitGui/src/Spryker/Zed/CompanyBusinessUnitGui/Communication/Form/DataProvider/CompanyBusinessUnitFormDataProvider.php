<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CompanyBusinessUnitGui\Communication\Form\DataProvider;

use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Spryker\Zed\CompanyBusinessUnitGui\Communication\Form\CompanyBusinessUnitForm;
use Spryker\Zed\CompanyBusinessUnitGui\Dependency\Facade\CompanyBusinessUnitGuiToCompanyBusinessUnitFacadeInterface;
use Spryker\Zed\CompanyBusinessUnitGui\Dependency\Facade\CompanyBusinessUnitGuiToCompanyFacadeInterface;

class CompanyBusinessUnitFormDataProvider
{
    /**
     * @var \Spryker\Zed\CompanyBusinessUnitGui\Dependency\Facade\CompanyBusinessUnitGuiToCompanyBusinessUnitFacadeInterface
     */
    protected $companyBusinessUnitFacade;

    /**
     * @var \Spryker\Zed\CompanyBusinessUnitGui\Dependency\Facade\CompanyBusinessUnitGuiToCompanyFacadeInterface
     */
    protected $companyFacade;

    /**
     * @param \Spryker\Zed\CompanyBusinessUnitGui\Dependency\Facade\CompanyBusinessUnitGuiToCompanyBusinessUnitFacadeInterface $companyBusinessUnitFacade
     * @param \Spryker\Zed\CompanyBusinessUnitGui\Dependency\Facade\CompanyBusinessUnitGuiToCompanyFacadeInterface $companyFacade
     */
    public function __construct(
        CompanyBusinessUnitGuiToCompanyBusinessUnitFacadeInterface $companyBusinessUnitFacade,
        CompanyBusinessUnitGuiToCompanyFacadeInterface $companyFacade
    ) {
        $this->companyBusinessUnitFacade = $companyBusinessUnitFacade;
        $this->companyFacade = $companyFacade;
    }

    /**
     * @param int|null $idCompanyBusinessUnit
     *
     * @return \Generated\Shared\Transfer\CompanyBusinessUnitTransfer
     */
    public function getData(?int $idCompanyBusinessUnit = null): CompanyBusinessUnitTransfer
    {
        $companyBusinessUnitTransfer = $this->createCompanyBusinessUnitTransfer();
        if (!$idCompanyBusinessUnit) {
            return $companyBusinessUnitTransfer;
        }

        return $this->companyBusinessUnitFacade->getCompanyBusinessUnitById(
            $companyBusinessUnitTransfer->setIdCompanyBusinessUnit($idCompanyBusinessUnit)
        );
    }

    /**
     * @param int|null $idCompanyBusinessUnit
     *
     * @return array
     */
    public function getOptions(?int $idCompanyBusinessUnit = null): array
    {
        return [
            'data_class' => CompanyBusinessUnitTransfer::class,
            CompanyBusinessUnitForm::OPTION_COMPANY_CHOICES => $this->prepareCompanyChoices(),
        ];
    }

    /**
     * @return \Generated\Shared\Transfer\CompanyBusinessUnitTransfer
     */
    protected function createCompanyBusinessUnitTransfer(): CompanyBusinessUnitTransfer
    {
        return new CompanyBusinessUnitTransfer();
    }

    /**
     * @return array
     */
    protected function prepareCompanyChoices(): array
    {
        $result = [];

        foreach ($this->companyFacade->getCompanies()->getCompanies() as $company) {
            $result[$company->getIdCompany()] = $company->getName();
        }

        return $result;
    }
}