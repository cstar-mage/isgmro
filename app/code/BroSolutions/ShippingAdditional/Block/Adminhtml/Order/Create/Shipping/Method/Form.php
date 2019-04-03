<?php
namespace BroSolutions\ShippingAdditional\Block\Adminhtml\Order\Create\Shipping\Method;
class Form extends \Magento\Sales\Block\Adminhtml\Order\Create\Shipping\Method\Form
{
    protected $_configProvider;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Tax\Helper\Data $taxData,
        \BroSolutions\ShippingAdditional\Model\ShippingAdditionalBlockConfigProvider $configProvider,
        array $data = []
    ) {
        $this->_configProvider = $configProvider;
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $taxData, $data);
    }

    public function getConfigProvider()
    {
        return $this->_configProvider;
    }
}