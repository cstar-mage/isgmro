<?php
namespace BroSolutions\Isgmro\Observer;
use Magento\Framework\Event\ObserverInterface;
class Productsaveafter implements ObserverInterface
{
    protected $request;
    protected $messageManager;
    public function __construct(\Magento\Framework\App\Request\Http $request, \Magento\Framework\Message\ManagerInterface $messageManager)
    {
        $this->request = $request;
        $this->messageManager = $messageManager;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $observer->getData();
        $product = $observer->getProduct();
        if($product){
            $storeId = $this->request->getParam('store', false);
            if(!$storeId){
                $this->messageManager->addWarning('This data does not appear in the store - use default store view.');
            }
        }
        return $this;
    }
}