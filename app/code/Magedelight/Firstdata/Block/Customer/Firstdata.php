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

namespace Magedelight\Firstdata\Block\Customer;

class Firstdata extends \Magento\Directory\Block\Data
{
    /**
     * Url Builder.
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * Store manager.
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Scope config.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Payment config model.
     *
     * @var \Magento\Payment\Model\Config
     */
    protected $_paymentConfig;

         /**
          * @var \Magento\Customer\Model\Session
          */
         protected $customerSession;
    /**
     * @var \Magedelight\Firstdata\Model\Config
     */
    protected $getconfig;

    /**
     * @var \Magedelight\Firstdata\Model\ResourceModel\Cards\CollectionFactory
     */
    protected $_cardCollectionFactory;

    /**
     * @var \Magento\Customer\Block\Address\Renderer\DefaultRenderer
     */
    protected $addressRender;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $_countryFactory;

    /**
     * Filter manager.
     *
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $_filterManager;

    /**
     * @var \Magedelight\Firstdata\Model\CardsFactory
     */
    protected $_cardFactory;
   /**
    * @param \Magento\Framework\View\Element\Template\Context $context
    * @param \Magento\Directory\Helper\Data $directoryHelper
    * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
    * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
    * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
    * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
    * @param \Magento\Payment\Model\Config $paymentConfig
    * @param \Magedelight\Firstdata\Model\Config $getconfig
    * @param \Magento\Customer\Model\Session $customerSession
    * @param array $data
    */
   public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magedelight\Firstdata\Model\Config $getconfig,
        \Magento\Customer\Model\Session $customerSession,
        \Magedelight\Firstdata\Model\ResourceModel\Cards\CollectionFactory $cardCollectionFactory,
        \Magento\Customer\Block\Address\Renderer\DefaultRenderer $addressRender,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magedelight\Firstdata\Model\CardsFactory $cardFactory,
        array $data = []
    ) {
       $this->_urlBuilder = $context->getUrlBuilder();
       $this->_storeManager = $context->getStoreManager();
       $this->_scopeConfig = $context->getScopeConfig();
       $this->_paymentConfig = $paymentConfig;
       $this->getconfig = $getconfig;
       $this->customerSession = $customerSession;
       $this->_cardCollectionFactory = $cardCollectionFactory;
       $this->addressRender = $addressRender;
       $this->_countryFactory = $countryFactory;
       $this->_filterManager = $context->getFilterManager();
       $this->_cardFactory = $cardFactory;
       parent::__construct($context, $directoryHelper, $jsonEncoder, $configCacheType, $regionCollectionFactory, $countryCollectionFactory, $data);
   }
}
