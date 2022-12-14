<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PriceProductMerchantRelationshipStorage\Communication\Plugin\Event\Subscriber;

use Spryker\Shared\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageConfig;
use Spryker\Zed\Event\Dependency\EventCollectionInterface;
use Spryker\Zed\Event\Dependency\Plugin\EventSubscriberInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\MerchantRelationship\Dependency\MerchantRelationshipEvents;
use Spryker\Zed\PriceProductMerchantRelationship\Dependency\PriceProductMerchantRelationshipEvents;
use Spryker\Zed\PriceProductMerchantRelationshipStorage\Communication\Plugin\Event\Listener\MerchantRelationshipListener;
use Spryker\Zed\PriceProductMerchantRelationshipStorage\Communication\Plugin\Event\Listener\PriceProductMerchantRelationshipAbstractDeleteListener;
use Spryker\Zed\PriceProductMerchantRelationshipStorage\Communication\Plugin\Event\Listener\PriceProductMerchantRelationshipAbstractListener;
use Spryker\Zed\PriceProductMerchantRelationshipStorage\Communication\Plugin\Event\Listener\PriceProductMerchantRelationshipConcreteDeleteListener;
use Spryker\Zed\PriceProductMerchantRelationshipStorage\Communication\Plugin\Event\Listener\PriceProductMerchantRelationshipConcreteListener;

/**
 * @method \Spryker\Zed\PriceProductMerchantRelationshipStorage\Business\PriceProductMerchantRelationshipStorageFacadeInterface getFacade()
 * @method \Spryker\Zed\PriceProductMerchantRelationshipStorage\Communication\PriceProductMerchantRelationshipStorageCommunicationFactory getFactory()
 * @method \Spryker\Zed\PriceProductMerchantRelationshipStorage\PriceProductMerchantRelationshipStorageConfig getConfig()
 */
class PriceProductMerchantRelationshipStorageEventSubscriber extends AbstractPlugin implements EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Spryker\Zed\Event\Dependency\EventCollectionInterface $eventCollection
     *
     * @return \Spryker\Zed\Event\Dependency\EventCollectionInterface
     */
    public function getSubscribedEvents(EventCollectionInterface $eventCollection): EventCollectionInterface
    {
        $this
            ->addMerchantRelationshipCreateListener($eventCollection)
            ->addMerchantRelationshipUpdateListener($eventCollection)
            ->addMerchantRelationshipDeleteListener($eventCollection)
            ->addConcretePriceProductMerchantRelationshipCreateListener($eventCollection)
            ->addConcretePriceProductMerchantRelationshipUpdateListener($eventCollection)
            ->addConcretePriceProductMerchantRelationshipDeleteListener($eventCollection)
            ->addAbstractPriceProductMerchantRelationshipCreateListener($eventCollection)
            ->addAbstractPriceProductMerchantRelationshipUpdateListener($eventCollection)
            ->addAbstractPriceProductMerchantRelationshipDeleteListener($eventCollection)
            ->addConcretePriceProductMerchantRelationshipPublishListener($eventCollection)
            ->addAbstractPriceProductMerchantRelationshipPublishListener($eventCollection)
            ->addConcretePriceProductMerchantRelationshipUnpublishListener($eventCollection)
            ->addAbstractPriceProductMerchantRelationshipUnpublishListener($eventCollection);

        return $eventCollection;
    }

    /**
     * @param \Spryker\Zed\Event\Dependency\EventCollectionInterface $eventCollection
     *
     * @return $this
     */
    protected function addMerchantRelationshipCreateListener(EventCollectionInterface $eventCollection)
    {
        $eventCollection->addListenerQueued(
            MerchantRelationshipEvents::ENTITY_SPY_MERCHANT_RELATIONSHIP_TO_COMPANY_BUSINESS_UNIT_CREATE,
            new MerchantRelationshipListener(),
            0,
            null,
            $this->getConfig()->getMerchantRelationEventQueueName(),
        );

        return $this;
    }

    /**
     * @param \Spryker\Zed\Event\Dependency\EventCollectionInterface $eventCollection
     *
     * @return $this
     */
    protected function addMerchantRelationshipUpdateListener(EventCollectionInterface $eventCollection)
    {
        $eventCollection->addListenerQueued(
            MerchantRelationshipEvents::ENTITY_SPY_MERCHANT_RELATIONSHIP_TO_COMPANY_BUSINESS_UNIT_UPDATE,
            new MerchantRelationshipListener(),
            0,
            null,
            $this->getConfig()->getMerchantRelationEventQueueName(),
        );

        return $this;
    }

    /**
     * @param \Spryker\Zed\Event\Dependency\EventCollectionInterface $eventCollection
     *
     * @return $this
     */
    protected function addMerchantRelationshipDeleteListener(EventCollectionInterface $eventCollection)
    {
        $eventCollection->addListenerQueued(
            MerchantRelationshipEvents::ENTITY_SPY_MERCHANT_RELATIONSHIP_TO_COMPANY_BUSINESS_UNIT_DELETE,
            new MerchantRelationshipListener(),
            0,
            null,
            $this->getConfig()->getMerchantRelationEventQueueName(),
        );

        return $this;
    }

    /**
     * @param \Spryker\Zed\Event\Dependency\EventCollectionInterface $eventCollection
     *
     * @return $this
     */
    protected function addConcretePriceProductMerchantRelationshipCreateListener(EventCollectionInterface $eventCollection)
    {
        $eventCollection->addListenerQueued(
            PriceProductMerchantRelationshipEvents::ENTITY_SPY_PRICE_PRODUCT_MERCHANT_RELATIONSHIP_CREATE,
            new PriceProductMerchantRelationshipConcreteListener(),
            0,
            null,
            $this->getConfig()->getPriceProductConcreteMerchantRelationEventQueueName(),
        );

        return $this;
    }

    /**
     * @param \Spryker\Zed\Event\Dependency\EventCollectionInterface $eventCollection
     *
     * @return $this
     */
    protected function addConcretePriceProductMerchantRelationshipUpdateListener(EventCollectionInterface $eventCollection)
    {
        $eventCollection->addListenerQueued(
            PriceProductMerchantRelationshipEvents::ENTITY_SPY_PRICE_PRODUCT_MERCHANT_RELATIONSHIP_UPDATE,
            new PriceProductMerchantRelationshipConcreteListener(),
            0,
            null,
            $this->getConfig()->getPriceProductConcreteMerchantRelationEventQueueName(),
        );

        return $this;
    }

    /**
     * @param \Spryker\Zed\Event\Dependency\EventCollectionInterface $eventCollection
     *
     * @return $this
     */
    protected function addConcretePriceProductMerchantRelationshipDeleteListener(EventCollectionInterface $eventCollection)
    {
        $eventCollection->addListenerQueued(
            PriceProductMerchantRelationshipEvents::ENTITY_SPY_PRICE_PRODUCT_MERCHANT_RELATIONSHIP_DELETE,
            new PriceProductMerchantRelationshipConcreteDeleteListener(),
            0,
            null,
            $this->getConfig()->getPriceProductConcreteMerchantRelationEventQueueName(),
        );

        return $this;
    }

    /**
     * @param \Spryker\Zed\Event\Dependency\EventCollectionInterface $eventCollection
     *
     * @return $this
     */
    protected function addAbstractPriceProductMerchantRelationshipCreateListener(EventCollectionInterface $eventCollection)
    {
        $eventCollection->addListenerQueued(
            PriceProductMerchantRelationshipEvents::ENTITY_SPY_PRICE_PRODUCT_MERCHANT_RELATIONSHIP_CREATE,
            new PriceProductMerchantRelationshipAbstractListener(),
            0,
            null,
            $this->getConfig()->getPriceProductAbstractMerchantRelationEventQueueName(),
        );

        return $this;
    }

    /**
     * @param \Spryker\Zed\Event\Dependency\EventCollectionInterface $eventCollection
     *
     * @return $this
     */
    protected function addAbstractPriceProductMerchantRelationshipUpdateListener(EventCollectionInterface $eventCollection)
    {
        $eventCollection->addListenerQueued(
            PriceProductMerchantRelationshipEvents::ENTITY_SPY_PRICE_PRODUCT_MERCHANT_RELATIONSHIP_UPDATE,
            new PriceProductMerchantRelationshipAbstractListener(),
            0,
            null,
            $this->getConfig()->getPriceProductAbstractMerchantRelationEventQueueName(),
        );

        return $this;
    }

    /**
     * @param \Spryker\Zed\Event\Dependency\EventCollectionInterface $eventCollection
     *
     * @return $this
     */
    protected function addAbstractPriceProductMerchantRelationshipDeleteListener(EventCollectionInterface $eventCollection)
    {
        $eventCollection->addListenerQueued(
            PriceProductMerchantRelationshipEvents::ENTITY_SPY_PRICE_PRODUCT_MERCHANT_RELATIONSHIP_DELETE,
            new PriceProductMerchantRelationshipAbstractDeleteListener(),
            0,
            null,
            $this->getConfig()->getPriceProductAbstractMerchantRelationEventQueueName(),
        );

        return $this;
    }

    /**
     * @param \Spryker\Zed\Event\Dependency\EventCollectionInterface $eventCollection
     *
     * @return $this
     */
    protected function addConcretePriceProductMerchantRelationshipPublishListener(EventCollectionInterface $eventCollection)
    {
        $eventCollection->addListenerQueued(
            PriceProductMerchantRelationshipStorageConfig::PRICE_PRODUCT_CONCRETE_MERCHANT_RELATIONSHIP_PUBLISH,
            new PriceProductMerchantRelationshipConcreteListener(),
            0,
            null,
            $this->getConfig()->getPriceProductConcreteMerchantRelationEventQueueName(),
        );

        return $this;
    }

    /**
     * @param \Spryker\Zed\Event\Dependency\EventCollectionInterface $eventCollection
     *
     * @return $this
     */
    protected function addAbstractPriceProductMerchantRelationshipPublishListener(EventCollectionInterface $eventCollection)
    {
        $eventCollection->addListenerQueued(
            PriceProductMerchantRelationshipStorageConfig::PRICE_PRODUCT_ABSTRACT_MERCHANT_RELATIONSHIP_PUBLISH,
            new PriceProductMerchantRelationshipAbstractListener(),
            0,
            null,
            $this->getConfig()->getPriceProductAbstractMerchantRelationEventQueueName(),
        );

        return $this;
    }

    /**
     * @param \Spryker\Zed\Event\Dependency\EventCollectionInterface $eventCollection
     *
     * @return $this
     */
    protected function addConcretePriceProductMerchantRelationshipUnpublishListener(EventCollectionInterface $eventCollection)
    {
        $eventCollection->addListenerQueued(
            PriceProductMerchantRelationshipStorageConfig::PRICE_PRODUCT_CONCRETE_MERCHANT_RELATIONSHIP_UNPUBLISH,
            new PriceProductMerchantRelationshipConcreteDeleteListener(),
            0,
            null,
            $this->getConfig()->getPriceProductConcreteMerchantRelationEventQueueName(),
        );

        return $this;
    }

    /**
     * @param \Spryker\Zed\Event\Dependency\EventCollectionInterface $eventCollection
     *
     * @return $this
     */
    protected function addAbstractPriceProductMerchantRelationshipUnpublishListener(EventCollectionInterface $eventCollection)
    {
        $eventCollection->addListenerQueued(
            PriceProductMerchantRelationshipStorageConfig::PRICE_PRODUCT_ABSTRACT_MERCHANT_RELATIONSHIP_UNPUBLISH,
            new PriceProductMerchantRelationshipAbstractDeleteListener(),
            0,
            null,
            $this->getConfig()->getPriceProductAbstractMerchantRelationEventQueueName(),
        );

        return $this;
    }
}
