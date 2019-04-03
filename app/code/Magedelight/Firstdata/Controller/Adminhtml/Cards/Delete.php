<?php

namespace Magedelight\Firstdata\Controller\Adminhtml\Cards;

class Delete extends \Magedelight\Firstdata\Controller\Adminhtml\Firstdata
{
    protected function _getSession()
    {
        return $this->_customerSession;
    }

    public function execute()
    {
        $customerId = $this->getRequest()->getParam('id');
        $append = '';
        $deleteCardId = $this->getRequest()->getParam('customercardid');
        if (!empty($deleteCardId)) {
            $cardModel = $this->_cardFactory->create()->load($deleteCardId);
            $transarmorId = $cardModel->getData('firstdata_transarmor_id');

            if ($transarmorId && $customerId) {
                try {
                    $cardModel->delete();
                    $append .= '<div id="messages"><div class="messages"><div class="message message-success success"><div data-ui-id="messages-message-success">'.__('Card deleted successfully.').'</div></div></div></div>';
                    $result = ['error' => false, 'message' => $append];
                } catch (\Exception $e) {
                    $append .= '<div id="messages"><div class="messages"><div class="message message-error error"><div data-ui-id="messages-message-error">'.$e->getMessage().'</div></div></div></div>';
                    $result = ['error' => true, 'message' => $append];
                }
            } else {
                $append .= '<div id="messages"><div class="messages"><div class="message message-error error"><div data-ui-id="messages-message-error">'.__('Card does not exists').'</div></div></div></div>';
                $result = ['error' => true, 'message' => $append];
            }
        } else {
            $append .= '<div id="messages"><div class="messages"><div class="message message-error error"><div data-ui-id="messages-message-error">'.__('Unable to find card to delete.').'</div></div></div></div>';
            $result = ['error' => true, 'message' => $append];
        }
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($result);

        return $resultJson;
    }
}
