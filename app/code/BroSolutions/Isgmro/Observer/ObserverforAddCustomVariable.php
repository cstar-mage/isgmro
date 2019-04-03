<?php
namespace BroSolutions\Isgmro\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;

class ObserverforAddCustomVariable implements ObserverInterface
{

    public function __construct(
    ) {
    }

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Framework\App\Action\Action $controller */
        $transport = $observer->getTransport();
        $commentCode = false;
        $shippingCarrier = false;
        $accountNumber = false;
        if($transport){
            $order = $transport->getOrder();
            if($order){
                $commentCode = $order->getData('comment_code');
                $shippingCarrier = $order->getData('shipping_carrier');
                $accountNumber = $order->getData('account_number');
            }
        }
        $transport['comment_code'] = $commentCode;
        $transport['shipping_carrier'] = $shippingCarrier;
        $transport['account_number'] = $accountNumber;

        return $this;
    }
}