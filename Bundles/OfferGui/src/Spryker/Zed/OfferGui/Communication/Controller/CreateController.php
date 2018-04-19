<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\OfferGui\Communication\Controller;

use ArrayObject;
use Generated\Shared\Transfer\CartChangeTransfer;
use Generated\Shared\Transfer\OfferResponseTransfer;
use Generated\Shared\Transfer\OfferTransfer;
use Spryker\Service\UtilText\Model\Url\Url;
use Spryker\Zed\Kernel\Communication\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \Spryker\Zed\OfferGui\Communication\OfferGuiCommunicationFactory getFactory()
 */
class CreateController extends AbstractController
{
    public const PARAM_ID_OFFER = 'id-offer';

    protected const MESSAGE_OFFER_CREATE_SUCCESS = 'Offer was created successfully.';

    public const PARAM_KEY_INITIAL_OFFER = 'key-offer';
    public const PARAM_SUBMIT_PERSIST = 'submit-persist';
    public const PARAM_SUBMIT_CUSTOMER_CREATE = 'submit-customer-create';
    public const PARAM_SUBMIT_RELOAD = 'submit-reload';
    public const PARAM_CUSTOMER_REFERENCE = 'customerReference';
    public const REDIRECT_URL_OFFER_VIEW = '/offer-gui/view/details';
    public const PARAM_KEY_REDIRECT_URL = 'redirectUrl';

    protected const SESSION_KEY_OFFER_DATA = 'key-offer-data';

    protected const ERROR_MESSAGE_ITEMS_NOT_AVAILABLE = 'Please fill offer with available items';

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function indexAction(Request $request)
    {
        $isSubmitPersist = $request->request->get(static::PARAM_SUBMIT_PERSIST);

        $offerTransfer = $this->getOfferTransfer($request);
        //When we create customer, this method restores offer data from session.
        $this->processCustomerRedirect($request, $offerTransfer);

        $form = $this->getFactory()->getOfferForm($offerTransfer, $request);
        $form->handleRequest($request);

        if ($request->request->has(static::PARAM_SUBMIT_CUSTOMER_CREATE)) {
            $this->getFactory()
                ->createCreateRequestHandler()
                ->addItems($offerTransfer);

            $redirectBackUrl = $this->storeFormDataIntoSession($form->getData());

            $redirectUrl = Url::generate(
                '/customer/add',
                [static::PARAM_KEY_REDIRECT_URL => urlencode($redirectBackUrl)]
            )->build();

            return $this->redirectResponse($redirectUrl);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Generated\Shared\Transfer\OfferTransfer $offerTransfer */
            $offerTransfer = $form->getData();
            $quoteTransfer = $offerTransfer->getQuote();

            //remove vouchers
            $voucherDiscounts = $quoteTransfer->getVoucherDiscounts();
            foreach ($quoteTransfer->getVoucherDiscounts() as $key => $discountTransfer) {
                if (!$discountTransfer->getVoucherCode()) {
                    $voucherDiscounts->offsetUnset($key);
                }
            }
            $quoteTransfer->setVoucherDiscounts($voucherDiscounts);

            //add items
            $items = clone $quoteTransfer->getItems();
            $quoteTransfer->setItems(new ArrayObject());

            foreach ($items as $itemTransfer) {
                if ($itemTransfer->getQuantity() <= 0) {
                    continue;
                }

                $cartChangeTransfer = new CartChangeTransfer();
                $cartChangeTransfer->setQuote($quoteTransfer);
                $cartChangeTransfer->addItem($itemTransfer);

                $quoteTransfer = $this->getFactory()
                    ->getCartFacade()
                    ->add($cartChangeTransfer);
            }

            //add incoming items
            $incomingItems = new ArrayObject();
            foreach ($quoteTransfer->getIncomingItems() as $itemTransfer) {
                if ($itemTransfer->getSku()) {
                    $incomingItems->append($itemTransfer);
                }
            }

            foreach ($incomingItems as $itemTransfer) {
                $cartChangeTransfer = new CartChangeTransfer();
                $cartChangeTransfer->setQuote($quoteTransfer);
                $cartChangeTransfer->addItem($itemTransfer);

                $quoteTransfer = $this->getFactory()
                    ->getCartFacade()
                    ->add($cartChangeTransfer);
            }

            //refresh form after calculations
            $form = $this->getFactory()->getOfferForm($offerTransfer, $request);

            if ($isSubmitPersist) {
                //save offer and a quote
                $this->getFactory()
                    ->getOfferFacade()
                    ->createOffer($offerTransfer);
            }

            $this->addSuccessMessage(static::MESSAGE_OFFER_CREATE_SUCCESS);

            $offerResponseTransfer = $this->getFactory()
                ->getOfferFacade()
                ->createOffer($offerTransfer);

            if ($offerResponseTransfer->getIsSuccessful()) {
                return $this->getSuccessfulRedirect($offerResponseTransfer);
            }
        }

        return $this->viewResponse([
            'offer' => $offerTransfer,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param \Generated\Shared\Transfer\OfferTransfer $offerTransfer
     *
     * @return string
     */
    protected function storeFormDataIntoSession(OfferTransfer $offerTransfer): string
    {
        $offerJsonData = $this->getFactory()->getUtilEncoding()->encodeJson($offerTransfer->toArray());
        $offerKey = $this->generateOfferKey($offerJsonData);

        $this->getFactory()
            ->getSessionClient()
            ->set($offerKey, $offerJsonData);

        $redirectUrl = Url::generate(
            '/offer-gui/create',
            [static::PARAM_KEY_INITIAL_OFFER => $offerKey]
        )->build();

        return $redirectUrl;
    }

    /**
     * @param string $offerKey
     *
     * @return array|null
     */
    protected function retrieveFormDataFromSession(string $offerKey): ?array
    {
        $jsonData = $this->getFactory()
            ->getSessionClient()
            ->get($offerKey);

        return $this->getFactory()
            ->getUtilEncoding()
            ->decodeJson($jsonData, true);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Generated\Shared\Transfer\OfferTransfer
     */
    protected function getOfferTransfer(Request $request)
    {
        $keyOffer = $request->get(static::PARAM_KEY_INITIAL_OFFER);

        $offerJson = $this->getFactory()
            ->getSessionClient()
            ->get($keyOffer);

        $offerTransfer = new OfferTransfer();

        if ($offerJson !== null) {
            $offerTransfer->fromArray(
                $this->getFactory()
                    ->getUtilEncoding()
                    ->decodeJson($offerJson, true)
            );
        }

        return $offerTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\OfferResponseTransfer $offerResponseTransfer
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function getSuccessfulRedirect(OfferResponseTransfer $offerResponseTransfer)
    {
        $this->getFactory()->getMessengerFacade()->getStoredMessages();

        $redirectUrl = Url::generate(
            static::REDIRECT_URL_OFFER_VIEW,
            [EditController::PARAM_ID_OFFER => $offerResponseTransfer->getOffer()->getIdOffer()]
        )->build();

        return $this->redirectResponse($redirectUrl);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Generated\Shared\Transfer\OfferTransfer $offerTransfer
     *
     * @return \Generated\Shared\Transfer\OfferTransfer
     */
    protected function processCustomerRedirect(Request $request, OfferTransfer $offerTransfer): OfferTransfer
    {
        if (!$request->query->has(static::PARAM_CUSTOMER_REFERENCE) || !$request->query->has(static::PARAM_KEY_INITIAL_OFFER)) {
            return $offerTransfer;
        }
        $offerKey = $request->query->get(static::PARAM_KEY_INITIAL_OFFER);

        $data = $this->retrieveFormDataFromSession($offerKey);

        if (!$data) {
            return $offerTransfer;
        }
        return (new OfferTransfer())->fromArray(
            $data
        );
    }

    /**
     * @param string $offerJsonData
     *
     * @return string
     */
    protected function generateOfferKey(string $offerJsonData): string
    {
        return md5($offerJsonData);
    }
}
