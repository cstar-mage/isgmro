<?php
namespace BroSolutions\Isgmro\Observer;
use Magento\Framework\Event\ObserverInterface;

class AddCommentToStatusHistory implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getData('order');
        $requestBody = file_get_contents('php://input');
        if($this->_isJson($requestBody)){
            $requestBody = json_decode($requestBody, true);
            if(isset($requestBody['comments'])){
                if($order){
                    $order->addStatusHistoryComment($requestBody['comments']);
                    $order->save();
                }
            }

        }

        return $this;
    }

    protected function _isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}