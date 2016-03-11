<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Newsletter\Persistence;

interface NewsletterQueryContainerInterface
{

    /**
     * @api
     *
     * @param string $email
     * @param string $newsletterTypeName
     *
     * @return \Orm\Zed\Newsletter\Persistence\Base\SpyNewsletterSubscriptionQuery
     */
    public function querySubscriptionByEmailAndNewsletterTypeName($email, $newsletterTypeName);

    /**
     * @api
     *
     * @param string $subscriberKey
     * @param string $newsletterTypeName
     *
     * @return \Orm\Zed\Newsletter\Persistence\Base\SpyNewsletterSubscriptionQuery
     */
    public function querySubscriptionBySubscriberKeyAndNewsletterTypeName($subscriberKey, $newsletterTypeName);

    /**
     * @api
     *
     * @param int $idCustomer
     * @param string $newsletterTypeName
     *
     * @return \Orm\Zed\Newsletter\Persistence\Base\SpyNewsletterSubscriptionQuery
     */
    public function querySubscriptionByIdCustomerAndNewsletterTypeName($idCustomer, $newsletterTypeName);

    /**
     * @api
     *
     * @return \Orm\Zed\Newsletter\Persistence\SpyNewsletterSubscriberQuery
     */
    public function querySubscriber();

    /**
     * @api
     *
     * @return \Orm\Zed\Newsletter\Persistence\Base\SpyNewsletterSubscriptionQuery
     */
    public function querySubscription();

    /**
     * @api
     *
     * @return \Orm\Zed\Newsletter\Persistence\SpyNewsletterTypeQuery
     */
    public function queryNewsletterType();

}