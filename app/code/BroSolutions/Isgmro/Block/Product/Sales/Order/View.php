<?php
namespace BroSolutions\Isgmro\Block\Product\Sales\Order;
use Magento\Customer\Model\Context;

class View extends \Magento\Sales\Block\Order\View
{
    public function getBackUrl()
    {
        if ($this->httpContext->getValue(Context::CONTEXT_AUTH)) {
            return $this->getUrl('sales/order/history');
        }
        return $this->getUrl('*/*/form');
    }
}
