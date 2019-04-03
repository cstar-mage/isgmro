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

namespace Magedelight\Firstdata\Block\Customer\Cards;

class Edit extends \Magedelight\Firstdata\Block\Customer\Firstdata
{
    /**
     * @return array
     */
    public function getCard()
    {
        $cardId = $this->getRequest()->getPostValue('card_id');
        if (!empty($cardId)) {
            $cardData = $this->_cardFactory->create()->load($cardId);

            return $cardData->getData();
        } else {
            return;
        }
    }
    /**
     * @return url
     */
    public function getBackUrl()
    {
        return $this->_urlBuilder->getUrl('md_firstdata/cards/listing');
    }

    /**
     * @return url
     */
    public function getSaveUrl()
    {
        return $this->_urlBuilder->getUrl('md_firstdata/cards/update');
    }

    /**
     * @return array of avaliable cc type
     */
    public function getCcAvailableTypes()
    {
        $types = $this->_paymentConfig->getCcTypes();
        $availableTypes = explode(',', $this->getconfig->getCcTypes());
        if ($availableTypes) {
            foreach ($types as $code => $name) {
                if (!in_array($code, $availableTypes)) {
                    unset($types[$code]);
                }
            }
        }

        return $types;
    }

    /**
     * @return array of cc months
     */
    public function getCcMonths()
    {
        $months = $this->getData('cc_months');
        if (is_null($months)) {
            $months[0] = __('Month');
            $months = array_merge($months, $this->_paymentConfig->getMonths());
            $this->setData('cc_months', $months);
        }

        return $months;
    }
    /**
     * @return bool
     */
    public function hasVerification()
    {
        return $this->getconfig->isCardVerificationEnabled();
    }

    /**
     * @return availbale cc years
     */
    public function getCcYears()
    {
        $years = $this->getData('cc_years');
        if (!($years)) {
            $years = $this->_paymentConfig->getYears();
            $years = [0 => __('Year')] + $years;
            $this->setData('cc_years', $years);
        }

        return $years;
    }
    /**
     * @param type config path
     *
     * @return configuration value
     */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
