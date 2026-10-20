<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\PriceProductMerchantRelationshipStorage\Storage;

interface MerchantRelationBusinessUnitLookupReaderInterface
{
    /**
     * @return array<int>
     */
    public function findMerchantRelationshipIdsByCompanyBusinessUnitId(int $idCompanyBusinessUnit): array;
}
