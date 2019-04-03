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

/**
 * Class DataProvider.
 */
class ConfigProvider extends \Magento\Payment\Model\CcGenericConfigProvider
{
    protected $methodCodes = [
        Payment::CODE,
    ];

    /**
     * @var Magedelight\Firstdata\Model\Config
     */
    protected $config;

    /**
     * @var Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var Magento\Payment\Model\Config
     */
    protected $_paymentConfig;

    /**
     * @var Magento\Backend\Model\Session\Quote
     */
    protected $sessionquote;

    /**
     * @var Magedelight\Firstdata\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var Magento\Framework\Encryption\Encryptor
     */
    protected $encryptor;

    /**
     * @var Magedelight\Firstdata\Model\CardsFactory
     */
    protected $cardfactory;

    protected $cards;

    /**
     * @param \Magento\Payment\Model\CcConfig           $ccConfig
     * @param \Magento\Payment\Helper\Data              $paymentHelper
     * @param \Magedelight\Firstdata\Model\Config       $config
     * @param \Magento\Checkout\Model\Session           $checkoutSession
     * @param \Magento\Customer\Model\Session           $customerSession
     * @param \Magento\Payment\Model\Config             $paymentConfig
     * @param \Magento\Backend\Model\Session\Quote      $sessionquote
     * @param \Magedelight\Firstdata\Helper\Data        $dataHelper
     * @param \Magento\Framework\Encryption\Encryptor   $encryptor
     * @param \Magedelight\Firstdata\Model\CardsFactory $cardFactory
     * @param array                                     $methodCodes
     */
    public function __construct(
            \Magento\Payment\Model\CcConfig $ccConfig,
            \Magento\Payment\Helper\Data $paymentHelper,
            \Magedelight\Firstdata\Model\Config $config,
            \Magento\Checkout\Model\Session $checkoutSession,
            \Magento\Customer\Model\Session $customerSession,
            \Magento\Payment\Model\Config $paymentConfig,
            \Magento\Backend\Model\Session\Quote $sessionquote,
            \Magedelight\Firstdata\Helper\Data $dataHelper,
            \Magento\Framework\Encryption\Encryptor $encryptor,
           \Magedelight\Firstdata\Model\CardsFactory $cardFactory,
            array $methodCodes = []
    ) {
        $this->config = $config;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->_paymentConfig = $paymentConfig;
        $this->dataHelper = $dataHelper;
        $this->encryptor = $encryptor;
        $this->cardfactory = $cardFactory;
        $this->sessionquote = $sessionquote;
        parent::__construct($ccConfig, $paymentHelper, $methodCodes);
    }

    /**
     * Returns applicable stored cards.
     *
     * @return array
     */
    public function getStoredCards()
    {
        $result = array();
        $cardData = [];
        if ($this->dataHelper->checkAdmin()) {
            $customerId = $this->sessionquote->getQuote()->getCustomerId();
        } else {
            $customer = $this->customerSession->getCustomer();
            $customerId = $customer->getId();
        }
        if (!empty($customerId)) {
            $cardModel = $this->cardfactory->create();
            $cardData = $cardModel->getCollection()
                ->addFieldToFilter('customer_id', $customerId)
                ->getData();
        }

        foreach ($cardData as $key => $_card) {
            $cardReplaced = 'XXXX-'.$_card['cc_last_4'];
            $result[$this->encryptor->encrypt($_card['firstdata_transarmor_id'])] = sprintf('%s, %s %s', $cardReplaced, $_card['firstname'], $_card['lastname']);
        }
        $result['new'] = 'Use other card';

        return $result;
    }

    protected function getCcAvailableCcTypes()
    {
        return $this->dataHelper->getCcAvailableCardTypes();
    }

    public function canSaveCard()
    {
        if (!$this->config->getSaveCardOptional()) {
            return true;
        }

        return false;
    }
    public function getCcMonths()
    {
        return $this->_paymentConfig->getMonths();
    }

    public function show3dSecure()
    {
        return false;
    }

    public function getConfig()
    {
        if (!$this->config->getIsActive()) {
            return [];
        }
        $config = parent::getConfig();

        $config = array_merge_recursive($config, [
            'payment' => [
                \Magedelight\Firstdata\Model\Payment::CODE => [
                   'canSaveCard' => $this->canSaveCard(),
                   'storedCards' => $this->getStoredCards(),
                    'ccMonths' => $this->getCcMonths(),
                    'ccYears' => $this->getCcYears(),
                    'hasVerification' => $this->config->isCardVerificationEnabled(),
                    'creditCardExpMonth' => (int) $this->dataHelper->getTodayMonth(),
                    'creditCardExpYear' => (int) $this->dataHelper->getTodayYear(),
                    'availableCardTypes' => $this->getCcAvailableCcTypes(),
                ],
            ],
        ]);

        return $config;
    }
}
