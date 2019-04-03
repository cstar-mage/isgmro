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

namespace Magedelight\Firstdata\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;

abstract class Firstdata extends Action
{
    /**
     * Response.
     *
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Customer session.
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\DataObjec
     */
    protected $_requestObject;

    /**
     * @var \Magedelight\Firstdata\Model\Api\Soap
     */
    protected $_soapModel;

    /**
     * @var \Magedelight\Firstdata\Model\CardsFactory
     */
    protected $_cardFactory;

    /**
     * @var Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory = null;

    /**
     * @var Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var Magento\Directory\Helper\Data
     */
    protected $_directoryHelper;
    /**
     * @param Context                                          $context
     * @param \Magento\Framework\View\Result\PageFactory       $resultPageFactory
     * @param Registry                                         $registry
     * @param \Magento\Customer\Model\Session                  $customerSession
     * @param \Magento\Framework\DataObject                    $requestObject
     * @param \Magedelight\Firstdata\Model\Api\Soap            $soapModel
     * @param \Magedelight\Firstdata\Model\CardsFactory        $cardFactory
     * @param \Magento\Framework\View\Result\LayoutFactory     $resultLayoutFactory
     * @param \Magento\Customer\Model\CustomerFactory          $customerFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     *
     * @return type
     */
    public function __construct(Context $context,
            \Magento\Framework\View\Result\PageFactory $resultPageFactory,
            Registry $registry,
            \Magento\Customer\Model\Session $customerSession,
            \Magento\Framework\DataObject $requestObject,
            \Magedelight\Firstdata\Model\Api\Soap $soapModel,
            \Magedelight\Firstdata\Model\CardsFactory $cardFactory,
            \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
            \Magento\Customer\Model\CustomerFactory $customerFactory,
            \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
            \Magento\Framework\Json\EncoderInterface $jsonEncoder,
            \Magento\Directory\Helper\Data $directoryHelper
        ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->_customerSession = $customerSession;
        $this->_requestObject = $requestObject;
        $this->_soapModel = $soapModel;
        $this->_cardFactory = $cardFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->_customerFactory = $customerFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_directoryHelper = $directoryHelper;

        return parent::__construct($context);
    }
    protected function _isAllowed()
    {
        return true;
    }
}
