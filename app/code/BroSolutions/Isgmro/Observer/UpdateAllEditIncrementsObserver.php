<?php
namespace  BroSolutions\Isgmro\Observer;
use Magento\Framework\Event\ObserverInterface;

class UpdateAllEditIncrementsObserver implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $order Order */
        $order = $observer->getEvent()->getData('order');
        $requestBody = file_get_contents('php://input');
        if($this->isJson($requestBody)){
            $requestBody = json_decode($requestBody, true);
            if(isset($requestBody['comments'])){
                $order->setData('comment_code', $requestBody['comments']);
            }
        }
        return $this;
    }

    function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}