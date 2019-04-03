<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Controller\Adminhtml\Update\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Psr\Log\LoggerInterface;
use Magenest\QuickBooksOnline\Model\Synchronization\Customer;

/**
 * Class Update
 * @package Magenest\QuickBooksOnline\Controller\Adminhtml\Update
 */
class Update extends \Magento\Framework\App\Action\Action
{

    /**
     * @var ResultJsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @var Customer
     */
    protected $customer;

    /**
     * Update constructor.
     * @param Context $context
     * @param ResultJsonFactory $resultJsonFactory
     * @param LoggerInterface $loggerInterface
     * @param Customer $customer
     * @param \Magento\Framework\View\LayoutInterface $layout
     */
    public function __construct(
        Context $context,
        ResultJsonFactory $resultJsonFactory,
        LoggerInterface $loggerInterface,
        Customer $customer,
        \Magento\Framework\View\LayoutInterface $layout
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $loggerInterface;
        $this->customer = $customer;
        $this->_layout = $layout;
        parent::__construct($context);
    }

    /**
     * return json customer
     *
     * @return mixed
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $data = $this->customer->getCustomer($params);
        $resultLayout = '';
        if (isset($data['Customer'])) {
            $feedback = serialize($data['Customer']);
            $resultLayout = $this->_layout->createBlock('Magenest\QuickBooksOnline\Block\Adminhtml\Update\Customer')->setFeedback($feedback)->toHtml();
        }
        $result = json_encode($resultLayout);
        $resultJson = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $sendResult = $resultJson->setData($result);

        return $sendResult;
    }
}
