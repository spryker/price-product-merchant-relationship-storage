<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\PriceProductMerchantRelationshipStorage\Storage;

interface PriceProductMerchantRelationshipKeyGeneratorInterface
{
    public function generateKey(string $resourceName, int $idProduct, int $idCompanyBusinessUnit): string;

    public function generateKeyByMerchantRelationship(string $resourceName, int $idProduct, int $idMerchantRelationship): string;

    public function generateKeyByCompanyBusinessUnit(string $resourceName, int $idCompanyBusinessUnit): string;
}
