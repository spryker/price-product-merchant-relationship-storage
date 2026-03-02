<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\PriceProductMerchantRelationshipStorage\Dependency\Service;

use Generated\Shared\Transfer\PriceProductTransfer;

interface PriceProductMerchantRelationshipStorageToPriceProductServiceInterface
{
    public function buildPriceProductGroupKey(PriceProductTransfer $priceProductTransfer): string;
}
