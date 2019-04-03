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

namespace Magedelight\Firstdata\Block\Adminhtml;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

class CardTab extends \Magento\Backend\Block\Template implements TabInterface
{
    /**
     * @var Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magedelight\Firstdata\Model\CardsFactory
     */
    protected $_cardFactory;

    /**
     * @var Magedelight\Firstdata\Helper\Data
     */
    protected $firstdataHelper;

    /**
     * @var Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var Magento\Framework\Json\Encoder
     */
    protected $jsonEncoder;

    protected $_template = 'tab/savedCards.phtml';
    protected $customerid;

    /**
     * @param \Magento\Backend\Block\Template\Context   $context
     * @param \Magento\Framework\Registry               $registry
     * @param \Magedelight\Firstdata\Model\CardsFactory $cardFactory
     * @param \Magedelight\Firstdata\Helper\Data        $firstdataHelper
     * @param \Magento\Customer\Model\CustomerFactory   $customerFactory
     * @param \Magento\Framework\Json\Encoder           $jsonEncoder
     * @param array                                     $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magedelight\Firstdata\Model\CardsFactory $cardFactory,
        \Magedelight\Firstdata\Helper\Data $firstdataHelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Json\Encoder $jsonEncoder,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_cardFactory = $cardFactory;
        $this->firstdataHelper = $firstdataHelper;
        $this->customerFactory = $customerFactory;
        $this->jsonEncoder = $jsonEncoder;
        parent::__construct($context, $data);
    }
    /**
     * @return tab title
     */
    public function getTabLabel()
    {
        return __('Saved Firstdata Cards');
    }

    /**
     * @return tab class
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * @return return tab url
     */
    public function getTabUrl()
    {
        return '';
    }

    /**
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        if ($this->getCustomerId()) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        if ($this->getCustomerId()) {
            return false;
        }

        return true;
    }

    /**
     * @return tab title
     */
    public function getTabTitle()
    {
        return __('Saved Firstdata Cards');
    }
    /**
     * @return customer id
     */
    public function getCustomerId()
    {
        $customerId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        if (empty($customerId)) {
            $customerId = $this->customerid;
        }

        return $customerId;
    }
    /**
     * @param type $customerid
     */
    public function setCustomerId($customerid)
    {
        $this->customerid = $customerid;
    }

    /**
     * @return type
     */
    public function getCards()
    {
        $customerId = $this->getCustomerId();

        $result = array();
        if (!empty($customerId)) {
            $result = $this->_cardFactory->create()->getCollection()
                ->addFieldToFilter('customer_id', $customerId)
                ->getData();
        }

        return $result;
    }
    public function getFormatedAddress($card)
    {
        return $this->firstdataHelper->getFormatedAddress($card);
    }

    public function getCardsInJson()
    {
        return $this->jsonEncoder->encode($this->getCards());
    }

    public function getDeleteActionUrl()
    {
        return $this->getUrl('md_firstdata/cards/delete', ['id' => $this->getCustomerId()]);
    }

    public function getEditCardAction()
    {
        return $this->getUrl('md_firstdata/cards/edit', ['id' => $this->getCustomerId()]);
    }

    public function getAddAction()
    {
        return $this->getUrl('md_firstdata/cards/add', ['id' => $this->getCustomerId()]);
    }
}
