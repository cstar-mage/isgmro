<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Controller\Adminhtml\Create\Customer;

use Magento\Backend\App\Action;

/**
 * Class Save
 * @package Magenest\QuickBooksOnline\Controller\Adminhtml\Create\Customer
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magenest\QuickBooksOnline\Model\CustomerFactory
     */
    protected $customer;

    /**
     * @var \Magenest\QuickBooksOnline\Model\CustomerAddressFactory
     */
    protected $address;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param \Magenest\QuickBooksOnline\Model\CustomerFactory $customerFactory
     * @param \Magenest\QuickBooksOnline\Model\CustomerAddressFactory $customerAddressFactory
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
        \Magenest\QuickBooksOnline\Model\CustomerFactory $customerFactory,
        \Magenest\QuickBooksOnline\Model\CustomerAddressFactory $customerAddressFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->customer = $customerFactory;
        $this->address = $customerAddressFactory;
        parent::__construct($context);
    }

    /**
     * Save user
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {
            $paramCutomer = $data['customer'];
            $model = $this->customer->create()->load($paramCutomer['customer_id']);
            $model->addData($paramCutomer);
            $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($model->getData());
            try {
                $model->save();
                $address = $this->address->create();
                $paramBilling = $data['billing'];
                if ($paramBilling['enabled'] == 1) {
                    if (isset($paramBilling['entity_id'])) {
                        $address->load($paramBilling['entity_id']);
                    }
                    $address->addData($paramBilling)->save();
                    $model->setDefaultBilling($address->getId())->save();
                } else {
                    if (isset($paramBilling['entity_id'])) {
                        $address->load($paramBilling['entity_id'])->delete();
                        $model->setDefaultBilling(null)->save();
                    }
                }

                $paramShipping = $data['shipping'];
                if ($paramShipping['enabled'] == 1) {
                    if (isset($paramShipping['entity_id'])) {
                        $address->load($paramShipping['entity_id']);
                    }
                    $address->addData($paramShipping)->save();
                    $model->setDefaultShipping($address->getId())->save();
                } else {
                    if (isset($paramShipping['entity_id'])) {
                        $address->load($paramShipping['entity_id'])->delete();
                        $model->setDefaultShipping(null)->save();
                    }
                }

                $this->messageManager->addSuccess(__('The rule has been saved.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError($e, __('Something went wrong while saving the rule.'));
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($data);

                return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            }
        }

        return $resultRedirect->setPath('*/*/');
    }
}
