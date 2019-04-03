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

class Listing extends \Magedelight\Firstdata\Block\Customer\Firstdata
{
    public function getCustomer()
    {
        return $this->customerSession;
    }

    public function getCustomerCards()
    {
        $customerId = $this->getCustomer()->getId();
       # $customerId = "2";
        $result = array();
        if (!empty($customerId)) {
            $result = $this->_cardCollectionFactory->create()
            ->addFieldToFilter('customer_id', $customerId)
            ->getData();
        }

        return $result;
    }

    public function getAddressHtml($_card)
    {
        $typeObject = new \Magento\Framework\DataObject();
        $typeObject->addData(array(
            'code' => 'html',
            'title' => 'HTML',
            'default_format' => $this->getconfig->getDefaultFormat(),
        ));
        $this->addressRender->setType($typeObject);
        $data = array();
        $countryName = '';
        if (!empty($_card['country_id'])) {
            $countryName = $this->_countryFactory->create()->loadByCode($_card['country_id'])->getName();
        }
        $data['firstname'] = $_card['firstname'];
        $data['lastname'] = $_card['lastname'];
        $data['street'] = $_card['street'];
        $data['city'] = $_card['city'];
        $data['country_id'] = $_card['country_id'];
        $data['country'] = $countryName;
        $data['region_id'] = $_card['region_id'];
        $data['postcode'] = $_card['postcode'];
        $data['telephone'] = $_card['telephone'];
        $format = $this->addressRender->getFormatArray($data);

        return $this->_filterManager->template($format, ['variables' => $data]);
    }
    public function getBackUrl()
    {
        return $this->urlBuilder->getUrl('customer/account');
    }
    /**
     * @return string
     */
    public function getAddCardUrl()
    {
        return $this->_urlBuilder->getUrl('md_firstdata/cards/add');
    }

    public function getPostUrl()
    {
        return $this->_urlBuilder->getUrl('md_firstdata/cards/edit');
    }

    public function getDeleteAction()
    {
        return $this->_urlBuilder->getUrl('md_firstdata/cards/delete');
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
}
