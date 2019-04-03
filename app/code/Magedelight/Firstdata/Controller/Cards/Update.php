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

class Update extends \Magedelight\Firstdata\Controller\Firstdata
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
//        echo "<pre>";
//        print_r($params);
//        die();
        $customer = $this->getCustomer();
        $updateCardId = $params['md_firstdata']['card_id'];

        if (!empty($updateCardId)) {
            try {
                $cardModel = $this->_cardFactory->create()->load($updateCardId);
                $requestObject = $this->_requestObject;
                $transarmorId = $cardModel->getData('firstdata_transarmor_id');
                $requestObject->addData(array(
                    'customer_id' => $customer->getId(),
                    'customer_transarmor_id' => $transarmorId,
                    'cardexpmonth' => $cardModel->getData('cc_exp_month'),
                    'cardexpyear' => substr($cardModel->getData('cc_exp_year'), -2),
                    'cardtype' => $cardModel->getData('cc_type'),
                ));
                $requestObject->addData($params['md_firstdata']['address_info']);
                $requestObject->addData($params['md_firstdata']['payment_info']);
//                echo "<pre>";
//                print_r($requestObject);
//                die();
                $response = $this->_soapModel
                ->setInputData($requestObject)
                ->updateCustomerProfile();

                if (is_array($response) && count($response) > 0) {
                    if (array_key_exists('Bank_Message', $response)) {
                        if ($response['Bank_Message'] != 'Approved') {
                            $this->messageManager->addError(__('Gateway error : {'.(string) $response['EXact_Message'].'}'));
                        } elseif ($response['Transaction_Error']) {
                            $this->messageManager->addError(__('Returned Error Message: '.$result['Transaction_Error']));
                        } else {
                            if (isset($response['TransarmorToken'])) {
                                $oldCardData = $cardModel->getData();
                                unset($oldCardData['card_id']);
                                $transArmorToken = $response['TransarmorToken'];

                                $model = $this->_cardFactory->create();
                                $model->load($updateCardId);
                                $model
                            ->setData($oldCardData);

                                $model->setData($params['md_firstdata']['address_info']);
                                $cardUpdateCheck = $params['md_firstdata']['payment_info'];
                                if ($cardUpdateCheck['cc_action'] == 'existing') {
                                    $model->setccType($oldCardData['cc_type'])
                                ->setcc_exp_month($oldCardData['cc_exp_month'])
                                ->setcc_exp_year($oldCardData['cc_exp_year']);
                                    if (isset($oldCardData['cc_last4'])):
                                    $model->setcc_last4($oldCardData['cc_last4']);
                                    endif;
                                } else {
                                    $model->setccType($params['md_firstdata']['payment_info']['cc_type'])
                                ->setcc_exp_month($params['md_firstdata']['payment_info']['cc_exp_month'])
                                ->setcc_exp_year($params['md_firstdata']['payment_info']['cc_exp_year'])
                                ->setcc_last4(substr($params['md_firstdata']['payment_info']['cc_number'], -4, 4));
                                }
                                $model->setFirstdataTransarmorId($transArmorToken)
                            ->setCustomerId($customer->getId())
                            ->setUpdatedAt(date('Y-m-d H:i:s'))
                            ->setCardId($updateCardId);
                                $model->save();
                                $this->messageManager->addSuccess(__('Credit card updated successfully.'));
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
        }

        return $resultRedirect->setPath('*/*/listing');
    }
}
