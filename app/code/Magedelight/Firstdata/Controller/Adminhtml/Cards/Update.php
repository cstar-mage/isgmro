<?php

namespace Magedelight\Firstdata\Controller\Adminhtml\Cards;

class Update extends \Magedelight\Firstdata\Controller\Adminhtml\Firstdata
{
    public function getCustomer($customerId)
    {
        return $this->_customerFactory->create()->load($customerId);
    }
    public function execute()
    {
        $append = '';
        $params = $this->getRequest()->getParams();
        $customerId = $params['id'];
        if ($this->_directoryHelper->isRegionRequired($params['paymentParam']['country_id'])) {
            $params['paymentParam']['state'] = '';
        } else {
            $params['paymentParam']['region_id'] = 0;
        }
        if (!$params) {
            $message = '<div id="messages"><div class="messages"><div class="message message-error error"><div data-ui-id="messages-message-error">'.__('Please try again.').'</div></div></div></div>';
            $result = ['error' => true, 'message' => $message];
            $resultJson = $this->resultJsonFactory->create();
            $resultJson->setData($result);

            return $resultJson;
        }

        $customer = $this->getCustomer($customerId);

        try {
            $updateCardId = $params['paymentParam']['card_id'];

            if (!empty($updateCardId)) {
                $cardModel = $this->_cardFactory->create()->load($updateCardId);
                if ($cardModel->getId()) {
                    $transarmorId = $cardModel->getData('firstdata_transarmor_id');
                    $requestObject = $this->_requestObject;
                    $requestObject->addData(array(
                                'customer_id' => $customer->getId(),
                                'customer_transarmor_id' => $transarmorId,
                                'cardexpmonth' => $cardModel->getData('cc_exp_month'),
                                'cardexpyear' => substr($cardModel->getData('cc_exp_year'), -2),
                                'cardtype' => $cardModel->getData('cc_type'),
                            ));
                    $requestObject->addData($params['paymentParam']);
                    $response = $this->_soapModel
                            ->setInputData($requestObject)
                            ->updateCustomerProfile();

                    if (is_array($response) && count($response) > 0) {
                        if (array_key_exists('Bank_Message', $response)) {
                            if ($response['Bank_Message'] != 'Approved') {
                                $message = __('Gateway error : {'.(string) $response['EXact_Message'].'}');
                                $result = ['error' => true, 'message' => $message];
                            } elseif ($response['Transaction_Error']) {
                                $message = __('Returned Error Message: '.$result['Transaction_Error']);
                                $result = ['error' => true, 'message' => $message];
                            } else {
                                if (isset($response['TransarmorToken'])) {
                                    $oldCardData = $cardModel->getData();
                                    unset($oldCardData['card_id']);
                                    $transArmorToken = $response['TransarmorToken'];

                                    $model = $this->_cardFactory->create();
                                    $model->load($updateCardId);
                                    $model
                                    ->setData($oldCardData);

                                    $model->setData($params['paymentParam']);

                                    if ($params['paymentParam']['cc_action'] == 'existing') {
                                        $model->setccType($oldCardData['cc_type'])
                                        ->setcc_exp_month($oldCardData['cc_exp_month'])
                                        ->setcc_exp_year($oldCardData['cc_exp_year']);
                                        if (isset($oldCardData['cc_last4'])):
                                            $model->setcc_last4($oldCardData['cc_last4']);
                                        endif;
                                    } else {
                                        $model->setccType($params['paymentParam']['cc_type'])
                                        ->setcc_exp_month($params['paymentParam']['cc_exp_month'])
                                        ->setcc_exp_year($params['paymentParam']['cc_exp_year'])
                                        ->setcc_last4(substr($params['paymentParam']['cc_number'], -4, 4));
                                    }
                                    $model->setFirstdataTransarmorId($transArmorToken)
                                    ->setCustomerId($customer->getId())
                                    ->setUpdatedAt(date('Y-m-d H:i:s'))
                                    ->setCardId($updateCardId);
                                    $model->save();

                                    $message = '<div id="messages"><div class="messages"><div class="message message-success success"><div data-ui-id="messages-message-success">Credit card saved successfully.</div></div></div></div>';
                                    $firstdataBlock = $this->_view->getLayout()->createBlock(
                                    'Magedelight\Firstdata\Block\Adminhtml\CardTab'
                                    );
                                    $firstdataBlock->setChild('firstdataAddCards', $this->_view->getLayout()->createBlock(
                                        'Magedelight\Firstdata\Block\Adminhtml\CardForm'
                                    ));
                                    $firstdataBlock->setCustomerId($customerId);
                                    $append .= $firstdataBlock->toHtml();
                                    $result = ['error' => false, 'message' => $message, 'carddata' => $append];
                                }
                            }
                        } else {
                            $message = __('No approval found');
                            $result = ['error' => true, 'message' => $message];
                        }
                    } else {
                        $message = __('No response found');
                        $result = ['error' => true, 'message' => $message];
                    }
                }
            } else {
                $message = __('Card not found');
                $result = ['error' => true, 'message' => $message];
            }
        } catch (\Exception $e) {
            $message = '<div id="messages"><div class="messages"><div class="message message-error error"><div data-ui-id="messages-message-error">'.$e->getMessage().'</div></div></div></div>';
            $result = ['error' => true, 'message' => $message];
        }
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($result);

        return $resultJson;
    }
    protected function _isAllowed()
    {
        return true;
    }
}
