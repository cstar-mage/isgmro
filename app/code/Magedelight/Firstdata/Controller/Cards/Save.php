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

namespace Magedelight\Firstdata\Controller\Cards;

use Magento\Framework\App\RequestInterface;

class Save extends \Magedelight\Firstdata\Controller\Firstdata
{
    public function getCustomer()
    {
        return $this->_customerSession->getCustomer();
    }

    protected function _getSession()
    {
        return $this->_customerSession;
    }

    public function dispatch(RequestInterface $request)
    {
        if (!$this->_getSession()->authenticate()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }

        return parent::dispatch($request);
    }
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $params = $this->getRequest()->getPostValue();
        if (!$params) {
            return $resultRedirect->setPath('*/*/listing');
        }

        $params['md_firstdata']['address_info']['country_id'] = $params['country_id'];
        $customer = $this->getCustomer();
        try {
            $requestObject = $this->_requestObject;

            $requestObject->addData(array(
                'customer_id' => $customer->getId(),
                'email' => $customer->getEmail(),
            ));
            $requestObject->addData($params['md_firstdata']['address_info']);
            $requestObject->addData($params['md_firstdata']['payment_info']);

            $response = $this->_soapModel
            ->setInputData($requestObject)
            ->createCustomerProfile();

            if (is_array($response) && count($response) > 0) {
                if (array_key_exists('Bank_Message', $response)) {
                    if ($response['Bank_Message'] != 'Approved') {
                        $this->messageManager->addError(__('Gateway error : {'.(string) $response['EXact_Message'].'}'));
                    } elseif ($response['Transaction_Error']) {
                        $this->messageManager->addError(__('Returned Error Message: '.$result['Transaction_Error']));
                    } else {
                        if (isset($response['TransarmorToken'])) {
                            $transArmorToken = $response['TransarmorToken'];
                            $this->_cardFactory->create()
                        ->setData($params['md_firstdata']['address_info'])
                        ->setCustomerId($customer->getId())
                        ->setFirstdataTransarmorId($transArmorToken)
                        ->setccType($params['md_firstdata']['payment_info']['cc_type'])
                        ->setcc_exp_month($params['md_firstdata']['payment_info']['cc_exp_month'])
                        ->setcc_exp_year($params['md_firstdata']['payment_info']['cc_exp_year'])
                        ->setCcLast4(substr($params['md_firstdata']['payment_info']['cc_number'], -4, 4))
                        ->setCreatedAt(date('Y-m-d H:i:s'))
                        ->setUpdatedAt(date('Y-m-d H:i:s'))
                        ->save()
                        ;
                            $this->messageManager->addSuccess(__('Credit card saved successfully.'));
                        }
                    }
                } else {
                    $this->messageManager->addError(__('No approval found'));
                }
            } else {
                $this->messageManager->addError(__('No response found'));
            }

            return $resultRedirect->setPath('*/*/listing');
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __($e->getMessage()));

            return $resultRedirect->setPath('*/*/listing');
        }

        return $resultRedirect->setPath('*/*/listing');
    }
}
