<?php
namespace BroSolutions\Isgmro\Model\Carrier;
use Magento\Quote\Model\Quote\Address\RateRequest;

class Flatrate extends \Magento\OfflineShipping\Model\Carrier\Flatrate
{
    protected $_cart;
    const SHIPPING_PERCENT_RATE = 8;
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\OfflineShipping\Model\Carrier\Flatrate\ItemPriceCalculator $itemPriceCalculator,
        \Magento\Checkout\Model\Cart $cart,
        array $data = []
    ) {
        $this->_cart = $cart;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $rateResultFactory, $rateMethodFactory, $itemPriceCalculator, $data);
    }

    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        $freeBoxes = $this->getFreeBoxesCount($request);
        $this->setFreeBoxes($freeBoxes);

        /** @var Result $result */
        $result = $this->_rateResultFactory->create();

        $shippingPrice = $this->_getPrice();
        //$shippingPrice = $this->getShippingPrice($request, $freeBoxes);


        if ($shippingPrice !== false) {
            $method = $this->createResultMethod($shippingPrice);
            $result->append($method);
        }

        return $result;
    }

    public function _getPrice()
    {
        $quote = $this->_cart->getQuote();
        if($quote){
            $subtotal = $quote->getSubtotal();
            return ($subtotal/100)*self::SHIPPING_PERCENT_RATE;
        }
        return 0.0;
    }

    private function createResultMethod($shippingPrice)
    {
        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->_rateMethodFactory->create();

        $method->setCarrier('flatrate');
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod('flatrate');
        $method->setMethodTitle($this->getConfigData('name'));

        $method->setPrice($shippingPrice);
        $method->setCost($shippingPrice);
        return $method;
    }
}