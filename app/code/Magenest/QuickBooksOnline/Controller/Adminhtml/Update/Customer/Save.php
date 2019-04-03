<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Controller\Adminhtml\Update\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Save
 * @package Magenest\QuickBooksOnline\Controller\Adminhtml\Update\Customer
 */
class Save extends \Magento\Framework\App\Action\Action
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
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerModel;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $address;

    /**
     * Save constructor.
     * @param Context $context
     * @param ResultJsonFactory $resultJsonFactory
     * @param LoggerInterface $loggerInterface
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Customer\Model\CustomerFactory $customerModel
     * @param \Magento\Customer\Model\AddressFactory $address
     */
    public function __construct(
        Context $context,
        ResultJsonFactory $resultJsonFactory,
        LoggerInterface $loggerInterface,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Customer\Model\CustomerFactory $customerModel,
        \Magento\Customer\Model\AddressFactory $address
    ) {
        $this->customerModel = $customerModel;
        $this->address = $address;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $loggerInterface;
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
        $this->logger->debug(print_r($params, true));
        $resultRedirect = $this->resultRedirectFactory->create();
        if (isset($params['customer']) && !empty($params['customer'])) {
            $data = $params['customer'];
            try {
                foreach ($data as $_data) {
                    $customerModel = $this->customerModel->create()->getCollection()
                        ->addFieldToFilter('email', $_data['email'])->getFirstItem();
                    if (!empty($customerModel->getData()) && !empty($_data['first_name']) && !empty($_data['last_name'])) {
                        $arrayDefault = [];
                        $arrayDefault['firstname'] = $_data['first_name'];
                        $arrayDefault['lastname'] = $_data['last_name'];
                        $customerModel->addData($arrayDefault)->save();

                        $billing  = $customerModel->getDefaultBilling();
                        $shipping = $customerModel->getDefaultShipping();
                        $billingId = 0;
                        if ($billing) {
                            $billingId = $billing;
                            $this->saveAddress($_data, 'billing', $billingId, $customerModel);
                        }
                        $shippingId = 0;
                        if ($shipping) {
                            $shippingId = $shipping;
                            $this->saveAddress($_data, 'shipping', $shippingId, $customerModel);
                        }
                    }
                }
                $this->messageManager->addSuccessMessage('All Customer(s) have updated');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e, __('Something went wrong while saving the customer.'));
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($data);
                return  $resultRedirect->setPath('qbonline/update_customer/index', ['_current' => true]);
            }
        }

        return  $resultRedirect->setPath('qbonline/update_customer/index', ['_current' => true]);
    }

    /**
     * @param $_data
     * @param $type
     * @param $id
     * @param $customerModel
     */
    public function saveAddress($_data, $type, $id, $customerModel)
    {
        $array = [];
        if (!empty($_data[$type]['street']) && isset($_data[$type]['street'])) {
            $array['street'] = $_data[$type]['street'];
        }
        if (!empty($_data[$type]['city']) && isset($_data[$type]['city'])) {
            $array['city'] = $_data[$type]['city'];
        }
        if (!empty($_data[$type]['country']) && isset($_data[$type]['country'])) {
            $array['country_id'] = $_data[$type]['country'];
        }
        if (!empty($_data[$type]['region']) && isset($_data[$type]['region'])) {
            $array['region'] = $_data[$type]['region'];
        }
        if (!empty($_data[$type]['postcode']) && isset($_data[$type]['postcode'])) {
            $array['postcode'] = $_data[$type]['postcode'];
        }
        if (!empty($_data['phone']) && isset($_data[$type]['phone'])) {
            $array['telephone'] = $_data['phone'];
        }

        $addressModel = $this->address->create();
        if ($id > 0) {
            $addressModel->load($id);
        } else {
            if (!empty($_data['first_name'])) {
                $array['firstname'] =  $_data['first_name'];
            }
            if (!empty($_data['last_name'])) {
                $array['lastname'] =  $_data['last_name'];
            }

            $array['parent_id'] = $customerModel->getId();
            $array['is_active'] = 1 ;
        }
        $addressModel->addData($array)->save();

        $text = 'default_'.$type;
        $customerModel->setData($text, $addressModel->getId());
    }
}
