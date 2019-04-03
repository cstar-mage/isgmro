<?php

namespace Magedelight\Firstdata\Controller\Adminhtml\Deletecards;

use Magedelight\Firstdata\Model\CardsFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;

class Index extends \Magento\Backend\App\Action
{
    
    public $cards;
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        CardsFactory $cards
    ){
        parent::__construct($context);
        $this->cards = $cards;        
    }

    public function execute()
    {
        try {
            $cards = $this->cards->create()
                    ->getCollection();
            
            if(!empty($cards->getData())){
                foreach ($cards as $card){                   
                    $card->delete();
                }
                $this->messageManager->addSuccessMessage(__('Your Cards has been deleted.'));
            }
            else{
                $this->messageManager->addErrorMessage(__('Customers having Cards not found.')); 
            }
            
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());            
            return $resultRedirect;
            
        } catch (\Exception $e){
            $this->messageManager->addErrorMessage(__("Cards not found, Try again"));
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
        return ;
    }
 
    protected function _isAllowed()
    {
        return true;
    }
}
