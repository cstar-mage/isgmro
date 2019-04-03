<?php

namespace Magedelight\Firstdata\Controller\Cards;

use Magento\Framework\App\RequestInterface;

class Edit extends \Magedelight\Firstdata\Controller\Firstdata
{
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
        $resultPage = $this->resultPageFactory->create();
        $resultRedirect = $this->resultRedirectFactory->create();

        $editId = $this->getRequest()->getPostValue('card_id');
        $customerId = $this->_customerSession->getCustomerId();

        if ($editId && $customerId) {
            $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
            if ($navigationBlock) {
                $navigationBlock->setActive('md_firstdata/cards/listing/');
            }

            return $resultPage;
        } else {
            $this->messageManager->addError('Card information missing.');

            return $resultRedirect->setPath('*/*/listing');
        }

        return $resultPage;
    }
}
