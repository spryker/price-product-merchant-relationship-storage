<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\PriceProductMerchantRelationshipStorage\Storage;

use Generated\Shared\Transfer\PriceProductStorageTransfer;
use Spryker\Client\PriceProductMerchantRelationshipStorage\Dependency\Client\PriceProductMerchantRelationshipStorageToStorageClientInterface;
use Spryker\Client\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageConfig;
use Spryker\Shared\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageConstants;

class PriceProductMerchantRelationshipConcreteReader implements PriceProductMerchantRelationshipConcreteReaderInterface
{
    /**
     * @var \Spryker\Client\PriceProductMerchantRelationshipStorage\Dependency\Client\PriceProductMerchantRelationshipStorageToStorageClientInterface
     */
    protected $storageClient;

    /**
     * @var \Spryker\Client\PriceProductMerchantRelationshipStorage\Storage\PriceProductMerchantRelationshipKeyGeneratorInterface
     */
    protected $priceStorageKeyGenerator;

    /**
     * @var \Spryker\Client\PriceProductMerchantRelationshipStorage\Storage\PriceProductMapperInterface
     */
    protected $priceProductMapper;

    public function __construct(
        PriceProductMerchantRelationshipStorageToStorageClientInterface $storageClient,
        PriceProductMerchantRelationshipKeyGeneratorInterface $priceStorageKeyGenerator,
        PriceProductMapperInterface $priceProductMapper,
        protected PriceProductMerchantRelationshipStorageConfig $priceProductMerchantRelationshipStorageConfig,
        protected MerchantRelationBusinessUnitLookupReaderInterface $merchantRelationBusinessUnitLookupReader,
        protected MerchantRelationshipPriceStorageReaderInterface $merchantRelationshipPriceStorageReader
    ) {
        $this->storageClient = $storageClient;
        $this->priceStorageKeyGenerator = $priceStorageKeyGenerator;
        $this->priceProductMapper = $priceProductMapper;
    }

    /**
     * @param int $idProductAbstract
     * @param int $idCompanyBusinessUnit
     *
     * @return array<\Generated\Shared\Transfer\PriceProductTransfer>
     */
    public function findPriceMerchantRelationshipConcrete(int $idProductAbstract, int $idCompanyBusinessUnit): array
    {
        if ($this->priceProductMerchantRelationshipStorageConfig->isMerchantRelationPriceKeyEnabled()) {
            $priceProductTransfers = $this->findPriceMerchantRelationshipConcreteByMerchantRelationship($idProductAbstract, $idCompanyBusinessUnit);

            if ($priceProductTransfers || !$this->priceProductMerchantRelationshipStorageConfig->isMerchantRelationPriceKeyFallbackEnabled()) {
                return $priceProductTransfers;
            }
        }

        $key = $this->priceStorageKeyGenerator->generateKey(
            PriceProductMerchantRelationshipStorageConstants::PRICE_PRODUCT_CONCRETE_MERCHANT_RELATIONSHIP_RESOURCE_NAME,
            $idProductAbstract,
            $idCompanyBusinessUnit,
        );

        return $this->findPriceProductStorageTransfer($key);
    }

    /**
     * @param int $idProductConcrete
     * @param int $idCompanyBusinessUnit
     *
     * @return array<\Generated\Shared\Transfer\PriceProductTransfer>
     */
    protected function findPriceMerchantRelationshipConcreteByMerchantRelationship(int $idProductConcrete, int $idCompanyBusinessUnit): array
    {
        $idMerchantRelationships = $this->merchantRelationBusinessUnitLookupReader->findMerchantRelationshipIdsByCompanyBusinessUnitId($idCompanyBusinessUnit);

        if (!$idMerchantRelationships) {
            return [];
        }

        $keys = [];
        foreach ($idMerchantRelationships as $idMerchantRelationship) {
            $keys[] = $this->priceStorageKeyGenerator->generateKeyByMerchantRelationship(
                PriceProductMerchantRelationshipStorageConstants::PRICE_PRODUCT_CONCRETE_MERCHANT_RELATIONSHIP_RESOURCE_NAME,
                $idProductConcrete,
                $idMerchantRelationship,
            );
        }

        $prices = $this->merchantRelationshipPriceStorageReader->findMergedPricesByKeys($keys);

        if (!$prices) {
            return [];
        }

        $priceProductStorageTransfer = (new PriceProductStorageTransfer())->fromArray(['prices' => $prices], true);

        return $this->priceProductMapper->mapPriceProductStorageTransferToPriceProductTransfers($priceProductStorageTransfer);
    }

    /**
     * @param string $key
     *
     * @return array<\Generated\Shared\Transfer\PriceProductTransfer>
     */
    protected function findPriceProductStorageTransfer(string $key): array
    {
        $priceData = $this->storageClient->get($key);

        if (!$priceData) {
            return [];
        }

        $priceProductStorageTransfer = new PriceProductStorageTransfer();
        $priceProductStorageTransfer->fromArray($priceData, true);

        return $this->priceProductMapper->mapPriceProductStorageTransferToPriceProductTransfers($priceProductStorageTransfer);
    }
}
