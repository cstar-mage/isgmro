<?php

namespace BroSolutions\ShippingAdditional\Observer;

class SalesEventQuoteSubmitBeforeObserver implements \Magento\Framework\Event\ObserverInterface
{
    protected $_dataProvider;
    protected $_state;

    public function __construct(\BroSolutions\ShippingAdditional\Model\ShippingAdditionalBlockConfigProvider $dataProvider, \Magento\Framework\App\State $state)
    {
        $this->_dataProvider = $dataProvider;
        $this->_state = $state;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $source = $observer->getEvent()->getQuote();
        $target = $observer->getEvent()->getOrder();
        $selectedShippingMethod = $target->getShippingMethod();
        if($selectedShippingMethod == 'shippingadditional_shippingadditional'){
            $shippingCarrier = $source->getShippingCarrier();
            $accountNumber = $source->getAccountNumber();
            if($accountNumber && !empty($accountNumber)){
                $target->setAccountNumber($accountNumber);
            }
            $shippingCarrierLabel = $this->_dataProvider->getShippingCarrierLabel($shippingCarrier);
            if($shippingCarrier && !empty($shippingCarrier)){
                if('adminhtml' == $this->_state->getAreaCode()){
                    $target->setShippingCarrier($shippingCarrier);
                } else {
                    $target->setShippingCarrier($shippingCarrierLabel);
                }
            }
        }
        return $this;
    }

}