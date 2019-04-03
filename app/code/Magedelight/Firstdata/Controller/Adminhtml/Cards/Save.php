<?php

namespace Magedelight\Firstdata\Controller\Adminhtml\Cards;

class Save extends \Magedelight\Firstdata\Controller\Adminhtml\Firstdata
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
        if (!$params) {
            $message = '<div id="messages"><div class="messages"><div class="message message-error error"><div data-ui-id="messages-message-error">'.__('Please try again.').'</div></div></div></div>';
            $result = ['error' => true, 'message' => $message];
            $resultJson = $this->resultJsonFactory->create();
            $resultJson->setData($result);

            return $resultJson;
        }
        #DebugBreak();

        $customer = $this->getCustomer($customerId);
        try {
            $requestObject = $this->_requestObject;

            $requestObject->addData(array(
                    'customer_id' => $customer->getId(),
                    'email' => $customer->getEmail(),
                ));
            $requestObject->addData($params['paymentParam']);

            $response = $this->_soapModel
                ->setInputData($requestObject)
                ->createCustomerProfile();

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
                            $transArmorToken = $response['TransarmorToken'];
                            $this->_cardFactory->create()
                            ->setData($params['paymentParam'])
                            ->setCustomerId($customer->getId())
                            ->setFirstdataTransarmorId($transArmorToken)
                            ->setCcLast4(substr($params['paymentParam']['cc_number'], -4, 4))
                            ->setCreatedAt(date('Y-m-d H:i:s'))
                            ->setUpdatedAt(date('Y-m-d H:i:s'))
                            ->save();
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
        } catch (\Exception $e) {
            $message = '<div id="messages"><div class="messages"><div class="message message-error error"><div data-ui-id="messages-message-error">'.$e->getMessage().'</div></div></div></div>';
            $result = ['error' => true, 'message' => $message];
        }
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($result);

        return $resultJson;
    }
}
