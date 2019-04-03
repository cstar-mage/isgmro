<?php

namespace Magedelight\Firstdata\Controller\Adminhtml\Cards;

class Edit extends \Magedelight\Firstdata\Controller\Adminhtml\Firstdata
{
    public function execute()
    {
        $editId = $this->getRequest()->getParam('customercardid', null);
        if (!is_null($editId)) {
            $cardmodel = $this->_cardFactory->create();
            $cardModel = $cardmodel->load($editId);
            if ($cardModel->getId()) {
                $card = $cardModel->getData();
            }
        }
        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('md.firstdata.ajax.form')->setCard($this->_jsonEncoder->encode($card));

        return $resultLayout;
    }
}
