<?php
/**
 * Magedelight
 * Copyright (C) 2016 Magedelight <info@magedelight.com>.
 *
 * NOTICE OF LICENSE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://opensource.org/licenses/gpl-3.0.html.
 *
 * @category Magedelight
 *
 * @copyright Copyright (c) 2016 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Firstdata\Model;

use Magento\Quote\Api\Data\CartInterface;

class Payment extends \Magento\Payment\Model\Method\Cc
{
    const CODE = 'md_firstdata';
    const RESPONSE_CODE_SUCCESS = 100;
    const CC_CARDTYPE_SS = 'SS';

    const REQUEST_TYPE_AUTH_CAPTURE = 'AUTH_CAPTURE';
    const REQUEST_TYPE_AUTH_ONLY = 'AUTH_ONLY';
    const REQUEST_TYPE_CAPTURE_ONLY = 'CAPTURE_ONLY';
    const REQUEST_TYPE_CREDIT = 'CREDIT';
    const REQUEST_TYPE_VOID = 'VOID';
    const REQUEST_TYPE_PRIOR_AUTH_CAPTURE = 'PRIOR_AUTH_CAPTURE';

    /**
     * Bit masks to specify different payment method checks.
     *
     * @see Mage_Payment_Model_Method_Abstract::isApplicableToQuote
     */
    const CHECK_USE_FOR_COUNTRY = 1;
    const CHECK_USE_FOR_CURRENCY = 2;
    const CHECK_USE_CHECKOUT = 4;
    const CHECK_USE_FOR_MULTISHIPPING = 8;
    const CHECK_USE_INTERNAL = 16;
    const CHECK_ORDER_TOTAL_MIN_MAX = 32;
    const CHECK_RECURRING_PROFILES = 64;
    const CHECK_ZERO_TOTAL = 128;

    const RESPONSE_CODE_APPROVED = 1;
    const RESPONSE_CODE_DECLINED = 2;
    const RESPONSE_CODE_ERROR = 3;
    const RESPONSE_CODE_HELD = 4;

    const RESPONSE_REASON_CODE_APPROVED = 1;
    const RESPONSE_REASON_CODE_NOT_FOUND = 16;
    const RESPONSE_REASON_CODE_PARTIAL_APPROVE = 295;
    const RESPONSE_REASON_CODE_PENDING_REVIEW_AUTHORIZED = 252;
    const RESPONSE_REASON_CODE_PENDING_REVIEW = 253;
    const RESPONSE_REASON_CODE_PENDING_REVIEW_DECLINED = 254;

    const PARTIAL_AUTH_CARDS_LIMIT = 5;
    const PARTIAL_AUTH_LAST_SUCCESS = 'last_success';
    const PARTIAL_AUTH_LAST_DECLINED = 'last_declined';
    const PARTIAL_AUTH_ALL_CANCELED = 'all_canceled';
    const PARTIAL_AUTH_CARDS_LIMIT_EXCEEDED = 'card_limit_exceeded';
    const PARTIAL_AUTH_DATA_CHANGED = 'data_changed';
    const TRANSACTION_STATUS_EXPIRED = 'expired';

    /**
     * @var string
     */
    protected $_formBlockType = 'Magedelight\Firstdata\Block\Form';

    /**
     * @var string
     */
    protected $_infoBlockType = 'Magedelight\Firstdata\Block\Info';

    /**
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * @var bolean
     */
    protected $_isGateway = true;

    /**
     * @var bolean
     */
    protected $_canAuthorize = true;

    /**
     * @var bolean
     */
    protected $_canCapture = true;

    /**
     * @var bolean
     */
    protected $_canRefund = true;

    /**
     * @var bolean
     */
    protected $_canVoid = true;

    /**
     * @var bolean
     */
    protected $_canUseInternal = true;

    /**
     * @var bolean
     */
    protected $_canUseCheckout = true;

    /**
     * @var bolean
     */
    protected $_canUseForMultishipping = true;

    /**
     * @var bolean
     */
    protected $_canSaveCc = false;

    /**
     * @var bolean
     */
    protected $_canReviewPayment = false;

    /**
     * @var bolean
     */
    protected $_canManageRecurringProfiles = false;

    /**
     * @var bolean
     */
    protected $_canFetchTransactionInfo = true;

    /**
     * @var bolean
     */
    protected $_canCapturePartial = true;

    /**
     * @var bolean
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * @var int
     */
    protected $_store = 0;

    /**
     * @var object
     */
    protected $_customer = null;

    /**
     * @var bolean
     */
    protected $_backend = false;

    /**
     * @var object
     */
    protected $_invoice = null;

    /**
     * @var object
     */
    protected $_creditmemo = null;

    /**
     * @var object
     */
    protected $_cardsStorage = null;

    /**
     * @var string
     */
    protected $_isTransactionFraud = 'is_transaction_fraud';

    /**
     * @var string
     */
    protected $_realTransactionIdKey = 'real_transaction_id';

    /**
     * @var string
     */
    protected $_isGatewayActionsLockedKey = 'is_gateway_actions_locked';

    /**
     * @var string
     */
    protected $_partialAuthorizationLastActionStateSessionKey = 'magedelight_firstdata_last_action_state';

    /**
     * @var string
     */
    protected $_partialAuthorizationChecksumSessionKey = 'magedelight_firstdata_checksum';

    /**
     * @var array
     */
    protected $_allowCurrencyCode = array();

    /**
     * @var array
     */
    protected $_postData = array();

    /**
     * Store manager.
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Object manager.
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Checkout session.
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * Payment config model.
     *
     * @var \Magento\Payment\Model\Config
     */
    protected $_paymentConfig;

    /**
     * @var Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * Customer session.
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var Magedelight\Firstdata\Model\Api\Soap
     */
    protected $soapmodel;

    /**
     * @var Magedelight\Firstdata\Model\CardsFactory
     */
    protected $_cardfactory;

    /**
     * @var Magedelight\Firstdata\Model\Payment\Cards
     */
    protected $cardpayment;

    /**
     * @var Magedelight\Firstdata\Model\Config
     */
    protected $firstdataConfig;

    /**
     * @var Magedelight\Firstdata\Helper\Data
     */
    protected $firstdataHelper;

     /**
      * @param \Magento\Framework\Model\Context $context
      * @param \Magento\Framework\Registry $registry
      * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
      * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
      * @param \Magento\Payment\Helper\Data $paymentData
      * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
      * @param \Magento\Payment\Model\Method\Logger $logger
      * @param \Magento\Framework\Module\ModuleListInterface $moduleList
      * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
      * @param \Magento\Store\Model\StoreManagerInterface $storeManager
      * @param \Magento\Framework\ObjectManagerInterface $objectManager
      * @param \Magento\Checkout\Model\Session $checkoutSession
      * @param \Magento\Sales\Model\OrderFactory $orderFactory
      * @param \Magento\Payment\Model\Config $paymentconfig
      * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
      * @param \Magento\Customer\Model\Session $customerSession
      * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
      * @param \Magedelight\Firstdata\Model\Api\Soap $soapmodel
      * @param \Magedelight\Firstdata\Model\CardsFactory $cardFactory
      * @param \Magedelight\Firstdata\Model\Payment\Cards $cardpayment
      * @param \Magedelight\Firstdata\Model\Config $firstdataConfig
      * @param \Magedelight\Firstdata\Helper\Data $firstdataHelper
      * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
      * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
      * @param array $data
      */
     public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
         \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Payment\Model\Config $paymentconfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magedelight\Firstdata\Model\Api\Soap $soapmodel,
        \Magedelight\Firstdata\Model\CardsFactory $cardFactory,
        \Magedelight\Firstdata\Model\Payment\Cards $cardpayment,
        \Magedelight\Firstdata\Model\Config $firstdataConfig,
        \Magedelight\Firstdata\Helper\Data $firstdataHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
           $context,
           $registry,
           $extensionFactory,
           $customAttributeFactory,
           $paymentData,
           $scopeConfig,
           $logger,
           $moduleList,
           $localeDate,
           $resource,
           $resourceCollection,
           $data
        );
         $this->_storeManager = $storeManager;
         $this->_objectManager = $objectManager;
         $this->_checkoutSession = $checkoutSession;
         $this->orderFactory = $orderFactory;
         $this->_paymentConfig = $paymentconfig;
         $this->_date = $date;
         $this->_customerSession = $customerSession;
         $this->encryptor = $encryptor;
         $this->soapmodel = $soapmodel;
         $this->_cardfactory = $cardFactory;
         $this->cardpayment = $cardpayment;
         $this->firstdataConfig = $firstdataConfig;
         $this->firstdataHelper = $firstdataHelper;

         $this->_backend = ($this->_storeManager->getStore()->getId() == 0) ? true : false;
         if ($this->_backend && $this->_registry->registry('current_order')) {
             $this->setStore($this->_registry->registry('current_order')->getStoreId());
         } elseif ($this->_backend && $this->_registry->registry('current_invoice')) {
             $this->setStore($this->_registry->registry('current_invoice')->getStoreId());
         } elseif ($this->_backend && $this->_registry->registry('current_creditmemo')) {
             $this->setStore($this->_registry->registry('current_creditmemo')->getStoreId());
         } elseif ($this->_backend && $this->_registry->registry('current_customer') != false) {
             $this->setStore($this->_registry->registry('current_customer')->getStoreId());
         } elseif ($this->_backend && $this->_objectManager->get('Magento\Backend\Model\Session\Quote')->getStoreId() > 0) {
             $this->setStore($this->_objectManager->get('Magento\Backend\Model\Session\Quote')->getStoreId());
         } else {
             $this->setStore($this->_storeManager->getStore()->getId());
         }
     }

     /**
      * @param type $id
      *
      * @return \Magedelight\Firstdata\Model\Payment
      */
     public function setStore($id)
     {
         $this->_storeId = $id;

         return $this;
     }

    /**
     * @param type $customer
     *
     * @return \Magedelight\Firstdata\Model\Payment
     */
    public function setCustomer($customer)
    {
        $this->_customer = $customer;
        if ($customer->getStoreId() > 0) {
            $this->setStore($customer->getStoreId());
        }

        return $this;
    }
    /**
     * @return customer model
     */
    public function getCustomer()
    {
        if (isset($this->_customer)) {
            $customer = $this->_customer;
        } elseif ($this->_backend) {
            $customer = $this->_objectManager->create()->load($this->_objectManager->get('Magento\Backend\Model\Session\Quote')->getCustomerId());
        } else {
            $customer = $this->_customerSession->getCustomer();
        }

        $this->setCustomer($customer);

        return $customer;
    }

    /**
     * @param type $transarmorId
     *
     * @return type
     */
    public function getTransarmorCardInfo($transarmorId = null)
    {
        $card = null;
        if (!is_null($transarmorId)) {
            $cardModel = $this->_cardfactory->create();
            $card = $cardModel->getCollection()
                ->addFieldToFilter('firstdata_transarmor_id', $transarmorId)
                ->getData()
                ;
        }

        return $card;
    }

    /**
     * @param type $transarmorId
     * @param type $payment
     * @param type $customerid
     *
     * @return type
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveCustomerProfileData($transarmorId, $payment, $customerid = null)
    {
        if (empty($customerid)) {
            $post = $this->_postData;
            $customer = $this->getCustomer();
            $customerid = $customer->getId();
            $ccType = $post['cc_type'];
            $ccExpMonth = $post['expiration'];
            $ccExpYear = $post['expiration_yr'];
            $ccLast4 = substr($post['cc_number'], -4, 4);
        } else {
            $ccType = $payment->getCcType();
            $ccExpMonth = $payment->getCcExpMonth();
            $ccExpYear = $payment->getCcExpYear();
            $ccLast4 = $payment->getCcLast4();
        }

        if (!empty($transarmorId) && $customerid) {
            $billing = $payment->getOrder()->getBillingAddress();
            $post = $this->_postData;
            try {
                $model = $this->_cardfactory->create();
                $model->setFirstname($billing->getFirstname())
                        ->setLastname($billing->getLastname())
                        ->setPostcode($billing->getPostcode())
                        ->setCountryId($billing->getCountryId())
                        ->setRegionId($billing->getRegionId())
                        ->setState($billing->getRegion())
                        ->setCity($billing->getCity())
                        ->setCompany($billing->getCompany())
                        ->setStreet($billing->getStreet()[0])
                        ->setTelephone($billing->getTelephone())
                        ->setCustomerId($customerid)
                        ->setFirstdataTransarmorId($transarmorId)
                        ->setccType($ccType)
                        ->setcc_exp_month($ccExpMonth)
                        ->setcc_exp_year($ccExpYear)
                        ->setcc_last4($ccLast4)
                        ->setCreatedAt($this->_date->gmtDate())
                        ->setUpdatedAt($this->_date->gmtDate())
                        ->save();

                return;
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase(__('Unable to save customer profile due to: %1', $e->getMessage())));
            }
        }
    }

    /**
     * @param \Magento\Framework\DataObject $data
     *
     * @return \Magedelight\Firstdata\Model\Payment
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);

        $post = $data->getData()['additional_data'];
        if (empty($this->_postData)) {
            $this->_postData = $post;
        }
        $this->_registry->register('postdata', $this->_postData);

        if (isset($post['transarmor_id']) && $post['transarmor_id'] != 'new') {
            $transarmorIdCheck = $this->encryptor->decrypt($post['transarmor_id']);
            $creditCard = $this->getTransarmorCardInfo($transarmorIdCheck);
            if ($creditCard != '' && !empty($creditCard)) {
                $this->getInfoInstance()->setCcLast4($creditCard[0]['cc_last_4'])
                    ->setCcType($creditCard[0]['cc_type'])
                    ->setCcExpMonth($creditCard[0]['cc_exp_month'])
                    ->setCcExpYear(substr($creditCard[0]['cc_exp_year'], -2))
                    ->setAdditionalInformation('md_firstdata_transarmor_id', $post['transarmor_id'], 'md_save_card', $post['save_card'], 'cc_type', $creditCard[0]['cc_type']);
                if (isset($post['cc_cid'])) {
                    $this->getInfoInstance()->setCcCid($post['cc_cid']);
                }
            }

            unset($this->_postData['cc_type']);
            unset($this->_postData['cc_number']);
            unset($this->_postData['expiration']);
            unset($this->_postData['expiration_yr']);
            $this->_registry->unregister('postdata');
            $this->_registry->register('postdata', $this->_postData);
        } else {
            $this->getInfoInstance()->setCcType($post['cc_type'])
                ->setCcLast4(substr($post['cc_number'], -4))
                ->setCcNumber($post['cc_number'])
                ->setCcExpMonth($post['expiration'])
                ->setCcExpYear($post['expiration_yr']);
            if (isset($post['save_card'])) {
                $saveCard = $post['save_card'];
            } else {
                $saveCard = false;
            }
            $this->getInfoInstance()->setAdditionalInformation('md_save_card', $saveCard);
            if (isset($post['cc_cid'])) {
                $this->getInfoInstance()->setCcCid($post['cc_cid']);
            }
            $this->_checkoutSession->setSaveCardFlag($saveCard);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function validate()
    {
        if (empty($this->_postData)) {
            $this->_postData = $this->_registry->registry('postdata');
        }
        $post = $this->_postData;

        $transarmorId = (isset($post['transarmor_id'])) ? $post['transarmor_id'] : 'new';
        if ($transarmorId == 'new' || !empty($post['cc_number'])) {
            try {
                return parent::validate();
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        } else {
            return true;
        }
    }

    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $exceptionMessage = false;
        if ($amount <= 0) {
            throw new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase(__('Invalid amount for authorization.')));
        }
        $this->_initCardsStorage($payment);
        if (empty($this->_postData)) {
            $this->_postData = $this->_registry->registry('postdata');
        }
        $post = $this->_postData;
        try {
            $isMultiShipping = $this->_checkoutSession->getQuote()->getData('is_multi_shipping');
             //   $transArmorIdCheck=$isMultiShipping=="1"?$payment->getData('additional_information','magedelight_firstdata_transarmor_id'):$post['transarmor_id'];
                $transArmorIdCheck = $payment->getData('additional_information', 'md_firstdata_transarmor_id');
            if ((!empty($transArmorIdCheck) && empty($post['cc_number'])) || ($isMultiShipping == '1' && !empty($transArmorIdCheck))) { // magedelight order using transarmor id

                    $transArmorIdCheck = $this->encryptor->decrypt($transArmorIdCheck); // magedelight here we decrept transarmor id
                    $payment->setMdfirstdataTransarmorId($transArmorIdCheck);
                $payment->setAdditionalInformation('md_firstdata_transarmor_id', $transArmorIdCheck);
                if ($payment->getCcType() == '') {
                    $carddata = $this->getTransarmorCardInfo($transArmorIdCheck);
                    $payment->setCcType($carddata[0]['cc_type']);
                }
                $response = $this->soapmodel
                    ->prepareAuthorizeResponse($payment, $amount, true);
            } else {
                $response = $this->soapmodel
                    ->prepareAuthorizeResponse($payment, $amount, false);
            }
            if (is_array($response) && count($response) > 0) {
                if (array_key_exists('Bank_Message', $response)) {
                    if ($response['Bank_Message'] != 'Approved') {
                        $payment->setStatus(self::STATUS_ERROR);
                        $card = $this->_registerCard($response, $payment, self::STATUS_ERROR);
                        throw new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase('Gateway error : {'.(string) $result['EXact_Message'].'}'));
                    } elseif ($response['Transaction_Error']) {
                        $card = $this->_registerCard($response, $payment, self::STATUS_ERROR);
                        throw new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase('Returned Error Message: '.$result['Transaction_Error']));
                    } else {
                        if (!empty($transArmorIdCheck) && empty($post['cc_number'])) {
                            $card = $this->getTransarmorCardInfo($transArmorIdCheck);
                            $payment->setCcLast4($card[0]['cc_last_4']);
                            $payment->setCcType($card[0]['cc_type']);
                            $payment->setCcExpMonth($card[0]['cc_exp_month']);
                            $payment->setCcExpYear(substr($card[0]['cc_exp_year'], -2));
                            $payment->setAdditionalInformation('md_firstdata_transarmor_id', $transArmorIdCheck);
                            $payment->setMdfirstdataTransarmorId($transArmorIdCheck);
                        } else {
                            $payment->setCcLast4(substr($post['cc_number'], -4, 4));
                            $payment->setCcExpMonth($post['expiration']);
                            $payment->setCcExpYear(substr($post['expiration_yr'], -2));
                            $payment->setCcType($post['cc_type']);
                        }
                        $saveCard = $payment->getData('additional_information', 'md_save_card');

                        if (($saveCard == 'true' && isset($post['cc_number'])) && $post['cc_number'] != '' && ($this->_customerSession->getCustomerId() || ($this->firstdataHelper->checkAdmin() && $this->_objectManager->get('Magento\Backend\Model\Session\Quote')->getQuote()->getCustomerId()))) {
                            // magedelight saved card only for register customer

                                if (isset($response['TransarmorToken'])) {
                                    $customerid = $this->firstdataHelper->checkAdmin() ? $this->_objectManager->get('Magento\Backend\Model\Session\Quote')->getQuote()->getCustomerId() : $this->_customerSession->getCustomer()->getId();
                                    $this->saveCustomerProfileData($response['TransarmorToken'], $payment, $customerid);
                                }
                        }

                        $csToRequestMap = self::REQUEST_TYPE_AUTH_ONLY;
                        $payment->setAnetTransType($csToRequestMap);
                        $payment->setAmount($amount);
                        $newTransactionType = \Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH;
                        $card = $this->_registerCard($response, $payment, self::STATUS_SUCCESS);

                        $this->_addTransaction(
                                $payment,
                                $response['Authorization_Num'],
                                $newTransactionType,
                                array('is_transaction_closed' => 0),
                                array($this->_realTransactionIdKey => ['Authorization_Num']),
                                $this->firstdataHelper->getTransactionMessage(
                                    $payment, $csToRequestMap, $response['Authorization_Num'], $card, $amount
                                )
                            );
                        $card->setLastTransId($response['Authorization_Num']);

                        $payment->setAdditionalInformation('payment_type', $this->getConfigData('payment_action'));
                        $payment->setLastTransId($response['Authorization_Num'])
                                       ->setCcTransId($response['Authorization_Num'])
                                       ->setTransactionId($response['Authorization_Num'])
                                       ->setMdFirstdataRequestid($response['Authorization_Num'])
                                       ->setTransactionTag((string) $response['Transaction_Tag'])
                                        ->setFirstdataToken($response['TransarmorToken'])
                                        ->setIsTransactionClosed(0)
                                        ->setStatus(self::STATUS_APPROVED);
                    }
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase(__('No approval found')));
                }
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase(__('No response found')));
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase(__('Firstdata Gateway request error: %1', $e->getMessage())));
        }
        if ($exceptionMessage !== false) {
            throw new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase($exceptionMessage));
        }
        $payment->setSkipTransactionCreation(true);

        return $this;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param type                                 $amount
     *
     * @return \Magedelight\Firstdata\Model\Payment
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if ($amount <= 0) {
            throw new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase(__('Invalid amount for capture.')));
        }
        $this->_initCardsStorage($payment);

        if (empty($this->_postData)) {
            $this->_postData = $this->_registry->registry('postdata');
        }
        $post = $this->_postData;

        try {
            if ($this->_isPreauthorizeCapture($payment)) {
                $this->_preauthorizeCapture($payment, $amount);
            } else {
                $isMultiShipping = $this->_checkoutSession->getQuote()->getData('is_multi_shipping');
                $transArmorIdCheck = $payment->getData('additional_information', 'md_firstdata_transarmor_id');
                if ((!empty($transArmorIdCheck) && empty($post['cc_number'])) || ($isMultiShipping == '1' && !empty($transArmorIdCheck))) { // magedelight order using transarmor id
                        $transArmorIdCheck = $this->encryptor->decrypt($transArmorIdCheck); // magedelight here we decrept transarmor id
                        $payment->setMdfirstdataTransarmorId($transArmorIdCheck);
                    if ($payment->getCcType() == '') {
                        $carddata = $this->getTransarmorCardInfo($transArmorIdCheck);
                        $payment->setCcType($carddata[0]['cc_type']);
                    }

                    $payment->setAdditionalInformation('md_firstdata_transarmor_id', $transArmorIdCheck);
                    $response = $this->soapmodel
                        ->prepareCaptureResponse($payment, $amount, true);
                } else {
                    $response = $this->soapmodel
                        ->prepareCaptureResponse($payment, $amount, false);
                }

                if (is_array($response) && count($response) > 0) {
                    if (array_key_exists('Bank_Message', $response)) {
                        if ($response['Bank_Message'] != 'Approved') {
                            $payment->setStatus(self::STATUS_ERROR);
                            $card = $this->_registerCard($response, $payment, self::STATUS_ERROR);
                            throw new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase('Gateway error : {'.(string) $response['EXact_Message'].'}'));
                        } elseif ($response['Transaction_Error']) {
                            $card = $this->_registerCard($response, $payment, self::STATUS_ERROR);
                            throw new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase('Returned Error Message: '.$result['Transaction_Error']));
                        } else {
                            if (!empty($transArmorIdCheck) && empty($post['cc_number'])) {
                                $card = $this->getTransarmorCardInfo($transArmorIdCheck);
                                $payment->setCcLast4($card[0]['cc_last_4']);
                                $payment->setCcType($card[0]['cc_type']);
                                $payment->setCcExpMonth($card[0]['cc_exp_month']);
                                $payment->setCcExpYear(substr($card[0]['cc_exp_year'], -2));
                                $payment->setAdditionalInformation('magedelight_firstdata_transarmor_id', $transArmorIdCheck);
                                $payment->setMdfirstdataTransarmorId($transArmorIdCheck);
                            } else {
                                $payment->setCcLast4(substr($post['cc_number'], -4, 4));
                                $payment->setCcExpMonth($post['expiration']);
                                $payment->setCcExpYear(substr($post['expiration_yr'], -2));
                                $payment->setCcType($post['cc_type']);
                            }
                            $saveCard = $payment->getData('additional_information', 'md_save_card');

                            if (($saveCard == 'true' && isset($post['cc_number'])) && $post['cc_number'] != '' && ($this->_customerSession->getCustomerId() || ($this->firstdataHelper->checkAdmin() && $this->_objectManager->get('Magento\Backend\Model\Session\Quote')->getQuote()->getCustomerId()))) {
                                // magedelight saved card only for register customer

                                if (isset($response['TransarmorToken'])) {
                                    $customerid = $this->firstdataHelper->checkAdmin() ? $this->_objectManager->get('Magento\Backend\Model\Session\Quote')->getQuote()->getCustomerId() : $this->_customerSession->getCustomer()->getId();
                                    $this->saveCustomerProfileData($response['TransarmorToken'], $payment, $customerid);
                                }
                            }

                            $card = $this->_registerCard($response, $payment, self::STATUS_SUCCESS);
                            $csToRequestMap = self::REQUEST_TYPE_AUTH_CAPTURE;
                            $newTransactionType = \Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE;

                            $this->_addTransaction(
                                $payment,
                                $response['Authorization_Num'],
                                $newTransactionType,
                                array('is_transaction_closed' => 0),
                                array($this->_realTransactionIdKey => ['Authorization_Num']),
                                $this->firstdataHelper->getTransactionMessage(
                                    $payment, $csToRequestMap, $response['Authorization_Num'], $card, $amount
                                )
                            );
                            $card->setLastTransId($response['Authorization_Num']);
                            $card->setCapturedAmount($card->getProcessedAmount());
                            $captureTransactionId = $response['Authorization_Num'];
                            $card->setLastCapturedTransactionId($captureTransactionId);
                            $this->getCardsStorage()->updateCard($card);

                            $payment->setLastTransId($response['Authorization_Num'])
                                ->setLastFirstdataToken($response['TransarmorToken'])
                                ->setTransactionTag((string) $response['Transaction_Tag'])
                                ->setCcTransId($response['Authorization_Num'])
                                ->setTransactionId($response['Authorization_Num'])
                                ->setIsTransactionClosed(0)
                                ->setFirstdataToken($response['TransarmorToken']);
                        }
                    } else {
                        throw new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase(__('No approval found')));
                    }
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase(__('No response found')));
                }
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase(__('Gateway request error: %1', $e->getMessage())));
        }
        $payment->setSkipTransactionCreation(true);

        return $this;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param type                                 $amount
     *
     * @return \Magedelight\Firstdata\Model\Payment
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $cardsStorage = $this->getCardsStorage($payment);
        if ($this->_formatAmount(
                $cardsStorage->getCapturedAmount() - $cardsStorage->getRefundedAmount()
                ) < $amount
            ) {
            throw new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase(__('Invalid amount for refund.')));
        }
        $messages = array();
        $isSuccessful = false;
        $isFiled = false;
            // Grab the invoice in case partial invoicing
            $creditmemo = $this->_registry->registry('current_creditmemo');
        if (!is_null($creditmemo)) {
            $this->_invoice = $creditmemo->getInvoice();
        }
        foreach ($cardsStorage->getCards() as $card) {
            $lastTransactionId = $payment->getData('cc_trans_id');
            $cardTransactionId = $card->getTransactionId();

            if ($lastTransactionId == $cardTransactionId) {
                if ($amount > 0) {
                    $cardAmountForRefund = $this->_formatAmount($card->getCapturedAmount() - $card->getRefundedAmount());
                    if ($cardAmountForRefund <= 0) {
                        continue;
                    }
                    if ($cardAmountForRefund > $amount) {
                        $cardAmountForRefund = $amount;
                    }
                    try {
                        $newTransaction = $this->_refundCardTransaction($payment, $cardAmountForRefund, $card);
                        if ($newTransaction != null) {
                            $messages[] = $newTransaction->getMessage();
                            $isSuccessful = true;
                        }
                    } catch (\Exception $e) {
                        $messages[] = $e->getMessage();
                        $isFiled = true;
                        continue;
                    }
                    $card->setRefundedAmount($this->_formatAmount($card->getRefundedAmount() + $cardAmountForRefund));
                    $cardsStorage->updateCard($card);
                    $amount = $this->_formatAmount($amount - $cardAmountForRefund);
                } else {
                    $payment->setSkipTransactionCreation(true);

                    return $this;
                }
            }
        }

        if ($isFiled) {
            $this->_processFailureMultitransactionAction($payment, $messages, $isSuccessful);
        }

        $payment->setSkipTransactionCreation(true);

        return $this;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     *
     * @return \Magedelight\Firstdata\Model\Payment
     */
    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
        $cardsStorage = $this->getCardsStorage($payment);
        $messages = array();
        $isSuccessful = false;
        $isFiled = false;
        foreach ($cardsStorage->getCards() as $card) {
            $lastTransactionId = $payment->getData('cc_trans_id');
            $cardTransactionId = $card->getTransactionId();
            if ($lastTransactionId == $cardTransactionId) {
                try {
                    $newTransaction = $this->_voidCardTransaction($payment, $card);
                    if ($newTransaction != null) {
                        $messages[] = $newTransaction->getMessage();
                        $isSuccessful = true;
                    }
                } catch (\Exception $e) {
                    $messages[] = $e->getMessage();
                    $isFiled = true;
                    continue;
                }
                $cardsStorage->updateCard($card);
            }
        }
        if ($isFiled) {
            $this->_processFailureMultitransactionAction($payment, $messages, $isSuccessful);
        }

        $payment->setSkipTransactionCreation(true);

        return $this;
    }

    /**
     * @param type $payment
     * @param type $card
     *
     * @return type
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _voidCardTransaction($payment, $card)
    {
        $authTransactionId = $card->getLastTransId();
        if ($payment->getCcTransId()) {
            $realAuthTransactionId = $payment->getTransactionId();
            $payment->setAnetTransType(self::REQUEST_TYPE_VOID);
            $payment->setTransId($realAuthTransactionId);
            $response = $this->soapmodel
                ->prepareVoidResponse($payment, $card);

            if (is_array($response) && count($response) > 0) {
                if (array_key_exists('Bank_Message', $response)) {
                    if ($response['Bank_Message'] != 'Approved') {
                        throw new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase('Gateway error : {'.(string) $response['EXact_Message'].'}'));
                    } else {
                        // $payment->setStatus(self::STATUS_SUCCESS);
                        $voidTransactionId = $response['Authorization_Num'].'-void';
                        $card->setLastTransId($voidTransactionId);
                        $payment->setTransactionId($response['Authorization_Num'])
                            ->setFirstdataToken($response['TransarmorToken'])
                            ->setIsTransactionClosed(1);

                        $this->_addTransaction(
                                $payment,
                                $voidTransactionId,
                                \Magento\Sales\Model\Order\Payment\Transaction::TYPE_VOID,
                                array(
                                    'is_transaction_closed' => 1,
                                    'should_close_parent_transaction' => 1,
                                    'parent_transaction_id' => $authTransactionId,
                                ),
                                array($this->_realTransactionIdKey => $response['Authorization_Num']),
                                $this->firstdataHelper->getTransactionMessage(
                                    $payment, self::REQUEST_TYPE_VOID, $response['Authorization_Num'], $card
                                )
                            );

                        return $this;
                    }
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase(__('No approval found')));
                }
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase(__('No response found')));
            }
        } else {
            return;
        }
    }
    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     *
     * @return type
     */
    public function cancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        return $this->void($payment);
    }

   /**
    * Payment method available? Yes.
    */
   public function getConfigModel()
   {
       return $this->firstdataConfig;
   }

   /**
    * @param CartInterface $quote
    *
    * @return type
    */
   public function isAvailable(CartInterface $quote = null)
   {
       $checkResult = new \StdClass();
       $isActive = $this->getConfigModel()->getIsActive();
       $checkResult->isAvailable = $isActive;
       $checkResult->isDeniedInConfig = !$isActive;
       if ($checkResult->isAvailable && $quote) {
           $checkResult->isAvailable = $this->isApplicableToQuote($quote, self::CHECK_RECURRING_PROFILES);
       }

       return parent::isAvailable($quote);
   }

    /**
     * @param \Magento\Sales\Model\Order\Payment $payment
     *
     * @return bool
     */
    protected function _isPreauthorizeCapture(\Magento\Sales\Model\Order\Payment $payment)
    {
        if ($this->getCardsStorage()->getCardsCount() <= 0) {
            return false;
        }
        foreach ($this->getCardsStorage()->getCards() as $card) {
            $lastTransactionId = $payment->getData('cc_trans_id');
            $cardTransactionId = $card->getTransactionId();
            if ($lastTransactionId == $cardTransactionId) {
                if ($payment->getCcTransId()) {
                    return true;
                }

                return false;
            }
        }
    }

    /**
     * @param type $payment
     *
     * @return type
     */
    public function getCardsStorage($payment = null)
    {
        if (is_null($payment)) {
            $payment = $this->getInfoInstance();
        }
        if (is_null($this->_cardsStorage)) {
            $this->_initCardsStorage($payment);
        }

        return $this->_cardsStorage;
    }

    /**
     * @param type $payment
     */
    protected function _initCardsStorage($payment)
    {
        $this->_cardsStorage = $this->cardpayment->setPayment($payment);
    }

    /**
     * @param type                               $response
     * @param \Magento\Sales\Model\Order\Payment $payment
     *
     * @return type
     */
    protected function _registerCard($response, \Magento\Sales\Model\Order\Payment $payment, $status)
    {
        $cardsStorage = $this->getCardsStorage($payment);
        $card = $cardsStorage->registerCard();
        $transarmorId = $payment->getData('additional_information', 'md_firstdata_transarmor_id');
        if ($transarmorId != '') {
            $customerCard = $this->getTransarmorCardInfo($transarmorId);
            $card->setCcType($customerCard[0]['cc_type'])
                ->setCcLast4($customerCard[0]['cc_last_4'])
                ->setCcExpMonth($customerCard[0]['cc_exp_month'])
                ->setCcOwner($customerCard[0]['firstname'])
                ->setCcExpYear($customerCard[0]['cc_exp_year']);
        } else {
            if (empty($this->_postData)) {
                $this->_postData = $this->_registry->registry('postdata');
            }
            $post = $this->_postData;

            $card->setCcType($post['cc_type'])
                ->setCcLast4(substr($post['cc_number'], -4, 4))
                ->setCcExpMonth($post['expiration'])
                ->setCcExpYear($post['expiration_yr']);
        }

        $card
            ->setLastTransId($response['Authorization_Num'])
            ->setTransactionId($response['Authorization_Num']);

        if ($status == self::STATUS_SUCCESS) {
            $card
                    ->setMerchantReferenceCode($response['Retrieval_Ref_No'])
                    ->setRequestedAmount($response['DollarAmount'])
                    ->setProcessedAmount($response['DollarAmount'])
                    ->setTransactionTag((string) $response['Transaction_Tag']);
            if (isset($response['AVS'])) {
                $card->setAvsResultCode($response['AVS']);
            }
        }

        $cardsStorage->updateCard($card);

        return $card;
    }

    /**
     * @param type $payment
     * @param type $requestedAmount
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _preauthorizeCapture($payment, $requestedAmount)
    {
        $cardsStorage = $this->getCardsStorage($payment);
        if ($this->_formatAmount(
                $cardsStorage->getProcessedAmount() - $cardsStorage->getCapturedAmount()
                ) < $requestedAmount
            ) {
            throw new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase(__('Invalid amount for capture.')));
        }
        $messages = array();
        $isSuccessful = false;
        $isFiled = false;
        foreach ($cardsStorage->getCards() as $card) {
            $lastTransactionId = $payment->getData('cc_trans_id');
            $cardTransactionId = $card->getTransactionId();
            if ($lastTransactionId == $cardTransactionId) {
                if ($requestedAmount > 0) {
                    $prevCaptureAmount = $card->getCapturedAmount();
                    $cardAmountForCapture = $card->getProcessedAmount();
                    if ($cardAmountForCapture > $requestedAmount) {
                        $cardAmountForCapture = $requestedAmount;
                    }
                    try {
                        $newTransaction = $this->_preauthorizeCaptureCardTransaction(
                                $payment, $cardAmountForCapture, $card
                            );
                        if ($newTransaction != null) {
                            $messages[] = $newTransaction->getMessage();
                            $isSuccessful = true;
                        }
                    } catch (\Exception $e) {
                        $messages[] = $e->getMessage();
                        $isFiled = true;
                        continue;
                    }
                    $newCapturedAmount = $prevCaptureAmount + $cardAmountForCapture;
                    $card->setCapturedAmount($newCapturedAmount);
                    $cardsStorage->updateCard($card);
                    $requestedAmount = $this->_formatAmount($requestedAmount - $cardAmountForCapture);
                    if ($isSuccessful) {
                        $balance = $card->getProcessedAmount() - $card->getCapturedAmount();
                        if ($balance > 0) {
                            $payment->setAnetTransType(self::REQUEST_TYPE_AUTH_ONLY);
                            $payment->setAmount($balance);
                        }
                    }
                }
            }
        }
        if ($isFiled) {
            $this->_processFailureMultitransactionAction($payment, $messages, $isSuccessful);
        }
    }

    /**
     * @param type $payment
     * @param type $amount
     * @param type $card
     *
     * @return type
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _preauthorizeCaptureCardTransaction($payment, $amount, $card)
    {
        $authTransactionId = $card->getLastTransId();

        if ($payment->getCcTransId()) {
            $newTransactionType = \Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE;
            $payment->setAnetTransType(self::REQUEST_TYPE_PRIOR_AUTH_CAPTURE);

            $payment->setAmount($amount);
            $response = $this->soapmodel
                ->prepareAuthorizeCaptureResponse($payment, $amount, false);

            if (is_array($response) && count($response) > 0) {
                if (array_key_exists('Bank_Message', $response)) {
                    if ($response['Bank_Message'] != 'Approved') {
                        $payment->setStatus(self::STATUS_ERROR);
                        throw new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase('Gateway error : {'.(string) $response['EXact_Message'].'}'));
                    } elseif ($response['Transaction_Error']) {
                        throw new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase($response['Transaction_Error']));
                    } else {
                        $captureTransactionId = $response['Authorization_Num'].'-capture';
                        $card->setLastCapturedTransactionId($captureTransactionId);
                        $this->_addTransaction(
                                    $payment,
                                    $captureTransactionId,
                                    $newTransactionType,
                                    array(
                                        'is_transaction_closed' => 0,
                                        'parent_transaction_id' => $authTransactionId,
                                    ),
                                    array($this->_realTransactionIdKey => $response['Authorization_Num']),
                                    $this->firstdataHelper->getTransactionMessage(
                                        $payment, self::REQUEST_TYPE_PRIOR_AUTH_CAPTURE, $response['Authorization_Num'], $card, $amount
                                    )
                                );
                    }
                } else {
                    Mage::throwException('No approval found');
                    throw new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase(__('No approval found')));
                }
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase(__('No response found')));
            }
        } else {
            return;
        }
    }

    /**
     * @param type $amount
     * @param type $asFloat
     *
     * @return type
     */
    protected function _formatAmount($amount, $asFloat = false)
    {
        $amount = sprintf('%.2F', $amount); // "f" depends on locale, "F" doesn't
            return $asFloat ? (float) $amount : $amount;
    }

    /**
     * @param type $payment
     *
     * @return type
     */
    protected function _isGatewayActionsLocked($payment)
    {
        return $payment->getAdditionalInformation($this->_isGatewayActionsLockedKey);
    }

    /**
     * @param \Magento\Framework\DataObject $object
     * @param type                          $checkSumDataKeys
     *
     * @return type
     */
    protected function _generateChecksum(\Magento\Framework\DataObject $object, $checkSumDataKeys = array())
    {
        $data = array();
        foreach ($checkSumDataKeys as $dataKey) {
            $data[] = $dataKey;
            $data[] = $object->getData($dataKey);
        }

        return md5(implode($data, '_'));
    }

    /**
     * @param type $payment
     * @param type $messages
     * @param type $isSuccessfulTransactions
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _processFailureMultitransactionAction($payment, $messages, $isSuccessfulTransactions)
    {
        if ($isSuccessfulTransactions) {
            $messages[] = __('Gateway actions are locked because the gateway cannot complete one or more of the transactions. Please log in to your Firstdata account to manually resolve the issue(s).');
            $currentOrderId = $payment->getOrder()->getId();
            $copyOrder = $this->orderFactory->create()->load($currentOrderId);
            $copyOrder->getPayment()->setAdditionalInformation($this->_isGatewayActionsLockedKey, 1);
            foreach ($messages as $message) {
                $copyOrder->addStatusHistoryComment($message);
            }
            $copyOrder->save();
        }
        throw new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase(implode(' | ', $messages)));
    }

    /**
     * @param type $payment
     * @param type $amount
     * @param type $card
     *
     * @return type
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _refundCardTransaction($payment, $amount, $card)
    {
        $credit_memo = $this->_registry->registry('current_creditmemo');
        $captureTransactionId = $credit_memo->getInvoice()->getTransactionId();
           // $captureTransaction = $payment->getTransaction($captureTransactionId);
            if ($payment->getCcTransId()) {
                $payment->setAnetTransType(self::REQUEST_TYPE_CREDIT);
                $payment->setXTransId($payment->getTransactionId());
                $payment->setAmount($amount);

                $response = $this->soapmodel
                ->prepareRefundResponse($payment, $amount, $payment->getTransactionId());

                if (is_array($response) && count($response) > 0) {
                    if (array_key_exists('Bank_Message', $response)) {
                        if ($response['Bank_Message'] != 'Approved') {
                            throw new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase(__('Gateway error : {'.(string) $response['EXact_Message'].'}')));
                        } else {
                            $refundTransactionId = $response['Authorization_Num'].'-refund';
                            $shouldCloseCaptureTransaction = 0;

                            if ($this->_formatAmount($card->getCapturedAmount() - $card->getRefundedAmount()) == $amount) {
                                $card->setLastTransId($refundTransactionId);
                                $shouldCloseCaptureTransaction = 1;
                            }
                            $this->_addTransaction(
                                        $payment,
                                        $refundTransactionId,
                                        \Magento\Sales\Model\Order\Payment\Transaction::TYPE_REFUND,
                                        array(
                                            'is_transaction_closed' => 1,
                                            'should_close_parent_transaction' => $shouldCloseCaptureTransaction,
                                            'parent_transaction_id' => $captureTransactionId,
                                        ),
                                        array($this->_realTransactionIdKey => $response['Authorization_Num']),
                                        $this->firstdataHelper->getTransactionMessage(
                                            $payment, self::REQUEST_TYPE_CREDIT, $response['Authorization_Num'], $card, $amount
                                        )
                                    );
                        }
                    } else {
                        throw new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase(__('No approval found')));
                    }
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase(__('No response found')));
                }
            } else {
                return;
            }
    }

    /**
     * @param type $text
     *
     * @return type
     */
    protected function _wrapGatewayError($text)
    {
        return __('Gateway error:'.$text);
    }

    /**
     * @param type $payment
     *
     * @return \Magedelight\Firstdata\Model\Payment
     */
    private function _clearAssignedData($payment)
    {
        $payment->setCcType(null)
            ->setCcOwner(null)
            ->setCcLast4(null)
            ->setCcNumber(null)
            ->setCcCid(null)
            ->setCcExpMonth(null)
            ->setCcExpYear(null)
            ->setCcSsIssue(null)
            ->setCcSsStartMonth(null)
            ->setCcSsStartYear(null)
            ;

        return $this;
    }

    /**
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @param type                               $transactionId
     * @param type                               $transactionType
     * @param array                              $transactionDetails
     * @param array                              $transactionAdditionalInfo
     * @param type                               $message
     *
     * @return type
     */
    protected function _addTransaction(\Magento\Sales\Model\Order\Payment $payment, $transactionId, $transactionType,
            array $transactionDetails = array(), array $transactionAdditionalInfo = array(), $message = false
        ) {
        $payment->setTransactionId($transactionId);
        $payment->setLastTransId($transactionId);

        foreach ($transactionDetails as $key => $value) {
            $payment->setData($key, $value);
        }
        foreach ($transactionAdditionalInfo as $key => $value) {
            $payment->setTransactionAdditionalInfo($key, $value);
        }
        $transaction = $payment->addTransaction($transactionType, null, false, $message);

        $transaction->setMessage($message);

        return $transaction;
    }

    /**
     * @param type $invoice
     * @param type $payment
     *
     * @return \Magedelight\Firstdata\Model\Payment
     */
    public function processInvoice($invoice, $payment)
    {
        $lastCaptureTransId = '';
        $cardsStorage = $this->getCardsStorage($payment);
        foreach ($cardsStorage->getCards() as $card) {
            $lastTransactionId = $payment->getData('cc_trans_id');
            $cardTransactionId = $card->getTransactionId();
            if ($lastTransactionId == $cardTransactionId) {
                $lastCapId = $card->getData('last_captured_transaction_id');
                if ($lastCapId && !empty($lastCapId) && !is_null($lastCapId)) {
                    $lastCaptureTransId = $lastCapId;
                    break;
                }
            }
        }

        $invoice->setTransactionId($lastCaptureTransId);

        return $this;
    }

    /**
     * @param type $creditmemo
     * @param type $payment
     *
     * @return \Magedelight\Firstdata\Model\Payment
     */
    public function processCreditmemo($creditmemo, $payment)
    {
        $lastRefundedTransId = '';
        $cardsStorage = $this->getCardsStorage($payment);
        foreach ($cardsStorage->getCards() as $card) {
            $lastTransactionId = $payment->getData('cc_trans_id');
            $cardTransactionId = $card->getTransactionId();
            if ($lastTransactionId == $cardTransactionId) {
                $lastCardTransId = $card->getData('last_refunded_transaction_id');
                if ($lastCardTransId && !empty($lastCardTransId) && !is_null($lastCardTransId)) {
                    $lastRefundedTransId = $lastCardTransId;
                    break;
                }
            }
        }
        $creditmemo->setTransactionId($lastRefundedTransId);

        return $this;
    }

    /**
     * @param type $quote
     * @param type $checksBitMask
     *
     * @return bool
     */
    public function isApplicableToQuote($quote, $checksBitMask)
    {
        if ($checksBitMask & self::CHECK_USE_FOR_COUNTRY) {
            if (!$this->canUseForCountry($quote->getBillingAddress()->getCountry())) {
                return false;
            }
        }
        if ($checksBitMask & self::CHECK_USE_FOR_CURRENCY) {
            if (!$this->canUseForCurrency($quote->getStore()->getBaseCurrencyCode())) {
                return false;
            }
        }
        if ($checksBitMask & self::CHECK_USE_CHECKOUT) {
            if (!$this->canUseCheckout()) {
                return false;
            }
        }
        if ($checksBitMask & self::CHECK_USE_FOR_MULTISHIPPING) {
            if (!$this->canUseForMultishipping()) {
                return false;
            }
        }
        if ($checksBitMask & self::CHECK_USE_INTERNAL) {
            if (!$this->canUseInternal()) {
                return false;
            }
        }
        if ($checksBitMask & self::CHECK_ORDER_TOTAL_MIN_MAX) {
            $total = $quote->getBaseGrandTotal();
            $minTotal = $this->getConfigData('min_order_total');
            $maxTotal = $this->getConfigData('max_order_total');
            if (!empty($minTotal) && $total < $minTotal || !empty($maxTotal) && $total > $maxTotal) {
                return false;
            }
        }
            /*
            if ($checksBitMask & self::CHECK_RECURRING_PROFILES) {
                if (!$this->canManageRecurringProfiles() && $quote->hasRecurringItems()) {
                    return false;
                }
            }
            */
            if ($checksBitMask & self::CHECK_ZERO_TOTAL) {
                $total = $quote->getBaseSubtotal() + $quote->getShippingAddress()->getBaseShippingAmount();
                if ($total < 0.0001 && $this->getCode() != 'free') {
                    return false;
                }
            }

        return true;
    }

    /**
     * @param type $ccType
     *
     * @return type
     */
    protected function _formatCcType($ccType)
    {
        $allTypes = $this->_paymentConfig->getCcTypes();
        $allTypes = array_flip($allTypes);

        if (isset($allTypes[$ccType]) && !empty($allTypes[$ccType])) {
            return $allTypes[$ccType];
        }

        return $ccType;
    }

    /**
     * @param type $response
     * @param type $orderPayment
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _processPartialAuthorizationResponse($response, $orderPayment)
    {
        if (!$response->getSplitTenderId()) {
            return false;
        }
        $quotePayment = $orderPayment->getOrder()->getQuote()->getPayment();
        $this->setPartialAuthorizationLastActionState(self::PARTIAL_AUTH_LAST_DECLINED);
        $exceptionMessage = null;
        try {
            switch ($response->getResponseCode()) {
                    case self::RESPONSE_CODE_APPROVED:
                        $this->_registerCard($response, $orderPayment);
                        $this->_clearAssignedData($quotePayment);
                        $this->setPartialAuthorizationLastActionState(self::PARTIAL_AUTH_LAST_SUCCESS);

                        return true;
                    case self::RESPONSE_CODE_HELD:
                        if ($response->getResponseReasonCode() != self::RESPONSE_REASON_CODE_PARTIAL_APPROVE) {
                            return false;
                        }
                        if ($this->getCardsStorage($orderPayment)->getCardsCount() + 1 >= self::PARTIAL_AUTH_CARDS_LIMIT) {
                            $this->cancelPartialAuthorization($orderPayment);
                            $this->_clearAssignedData($quotePayment);
                            $this->setPartialAuthorizationLastActionState(self::PARTIAL_AUTH_CARDS_LIMIT_EXCEEDED);
                            $quotePayment->setAdditionalInformation($orderPayment->getAdditionalInformation());
                            $exceptionMessage = __('You have reached the maximum number of credit card allowed to be used for the payment.');
                            break;
                        }
                        $orderPayment->setAdditionalInformation($this->_splitTenderIdKey, $response->getSplitTenderId());
                        $this->_registerCard($response, $orderPayment);
                        $this->_clearAssignedData($quotePayment);
                        $this->setPartialAuthorizationLastActionState(self::PARTIAL_AUTH_LAST_SUCCESS);
                        $quotePayment->setAdditionalInformation($orderPayment->getAdditionalInformation());
                        $exceptionMessage = null;
                        break;
                    case self::RESPONSE_CODE_DECLINED:
                    case self::RESPONSE_CODE_ERROR:
                        $this->setPartialAuthorizationLastActionState(self::PARTIAL_AUTH_LAST_DECLINED);
                        $quotePayment->setAdditionalInformation($orderPayment->getAdditionalInformation());
                        $exceptionMessage = $this->_wrapGatewayError($response->getResponseReasonText());
                        break;
                    default:
                        $this->setPartialAuthorizationLastActionState(self::PARTIAL_AUTH_LAST_DECLINED);
                        $quotePayment->setAdditionalInformation($orderPayment->getAdditionalInformation());
                        $exceptionMessage = $this->_wrapGatewayError(
                            __('Payment partial authorization error.')
                        );
                }
        } catch (\Exception $e) {
            $exceptionMessage = $e->getMessage();
        }
        throw new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase($exceptionMessage));
    }
}
