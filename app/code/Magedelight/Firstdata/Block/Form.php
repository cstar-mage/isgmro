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

namespace Magedelight\Firstdata\Block;

class Form extends \Magento\Payment\Block\Form\Cc
{
    protected $_paymentConfig;
    protected $_firstdataPaymentConfig;
    protected $checkoutsession;
    protected $_template = 'Magedelight_Firstdata::form.phtml';
    protected $confiprovider;
    protected $config;
    protected $items = [];
    public function __construct(\Magento\Framework\View\Element\Template\Context $context,
            \Magedelight\Firstdata\Model\ConfigProvider $configprovider,
            \Magedelight\Firstdata\Model\Config $config,
            \Magento\Checkout\Model\Session $checkoutsession,
            \Magento\Payment\Model\Config $paymentConfig, \Magedelight\Firstdata\Model\Config $firstdataConfig, array $data = [])
    {
        parent::__construct($context, $paymentConfig, $data);
        $this->_paymentConfig = $paymentConfig;
        $this->confiprovider = $configprovider;
        $this->config = $config;
        $this->checkoutsession = $checkoutsession;
        $this->_firstdataPaymentConfig = $firstdataConfig;
    }

    public function getCcAvailableTypes()
    {
        $types = $this->_paymentConfig->getCcTypes();
        if ($method = $this->getMethod()) {
            $availableTypes = $this->_firstdataPaymentConfig->getCcTypes();
            if ($availableTypes) {
                $availableTypes = explode(',', $availableTypes);
                foreach ($types as $code => $name) {
                    if (!in_array($code, $availableTypes)) {
                        unset($types[$code]);
                    }
                }
            }
        }

        return $types;
    }
    public function getQuoteItems()
    {
        $quote = $this->checkoutsession->getQuote();
        if ($quote && $quote->getId()) {
            $this->items = $quote->getAllItems();
        }

        return $this->items;
    }
    public function getCcMonths()
    {
        $months = $this->getData('cc_months');
        if ($months === null) {
            $months[0] = __('Month');
            $months = array_merge($months, $this->_paymentConfig->getMonths());
            $this->setData('cc_months', $months);
        }

        return $months;
    }
    public function getSaveCardOptional()
    {
        return $this->config->getSaveCardOptional();
    }
    public function getCcYears()
    {
        $years = $this->getData('cc_years');
        if ($years === null) {
            $years = $this->_paymentConfig->getYears();
            $years = [0 => __('Year')] + $years;
            $this->setData('cc_years', $years);
        }

        return $years;
    }

    public function hasVerification()
    {
        if ($this->getMethod()) {
            $configData = $this->_firstdataPaymentConfig->isCardVerificationEnabled();
            if ($configData === null) {
                return true;
            }

            return $configData;
        }

        return true;
    }

    public function hasSsCardType()
    {
        $availableTypes = explode(',', $this->_firstdataPaymentConfig->getCcTypes());
        $ssPresenations = array_intersect(['SS', 'SM', 'SO'], $availableTypes);
        if ($availableTypes && count($ssPresenations) > 0) {
            return true;
        }

        return false;
    }

    public function getSsStartYears()
    {
        $years = [];
        $first = date('Y');

        for ($index = 5; $index >= 0; --$index) {
            $year = $first - $index;
            $years[$year] = $year;
        }
        $years = [0 => __('Year')] + $years;

        return $years;
    }
    /**
     * Render block HTML.
     *
     * @return string
     */
    protected function _toHtml()
    {
        $this->_eventManager->dispatch('payment_form_block_to_html_before', ['block' => $this]);

        return parent::_toHtml();
    }
    public function getCustomerSavedCards()
    {
        return $this->confiprovider->getStoredCards();
    }
}
