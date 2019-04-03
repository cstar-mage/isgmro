<?php
namespace BroSolutions\ShippingAdditional\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;

class ShippingAdditional extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'shippingadditional';
    protected $_configProvider;
    protected $_checkoutSession;
    protected $_state;
    protected $_adminSession;
    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \BroSolutions\ShippingAdditional\Model\ShippingAdditionalBlockConfigProvider $configProvider,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\State $state,
        \Magento\Backend\Model\Session\Quote $adminSession,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_configProvider = $configProvider;
        $this->_checkoutSession = $checkoutSession;
        $this->_state = $state;
        $this->_adminSession = $adminSession;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return ['shippingadditional' => $this->getConfigData('name')];
    }

    /**
     * @param RateRequest $request
     * @return bool|Result
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $accountNumber = '';
        $shippingCarrier = '';
        $quote = $this->_checkoutSession->getQuote();
        $postcode = $quote->getShippingAddress()->getPostcode();

        $isAdminArea = $this->_getIsAdminArea();
        if($isAdminArea){
//return false;
            if(!$postcode){
                $postcode = $this->_adminSession->getQuote()->getShippingAddress()->getPostcode();
            }
        }
        if(empty($postcode)){
            return false;
        }

        if(isset($data['addressInformation']) && isset($data['addressInformation']['custom_attributes'])){
            foreach($data['addressInformation']['custom_attributes'] as $attribute){
                if(isset($attribute['attribute_code']) && $attribute['attribute_code'] == 'account_number'){
                    $accountNumber = $attribute['value'];
                }
                if(isset($attribute['attribute_code']) && $attribute['attribute_code'] == 'ship_carrier_ship_method'){
                    $shippingCarrier = $attribute['value'];
                }
                if($shippingCarrier && $accountNumber){
                    $quote->setShippingCarrier($shippingCarrier);
                    $quote->setAccountNumber($accountNumber);
                }
            }
        }
        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->_rateResultFactory->create();

        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->_rateMethodFactory->create();

        $method->setCarrier('shippingadditional');
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod('shippingadditional');
        $method->setMethodTitle($this->getConfigData('name'));


        $method->setPrice('0.00');
        $method->setCost('0.00');

        $result->append($method);

        return $result;
    }

    protected function _getIsAdminArea()
    {
        return 'adminhtml' === $this->_state->getAreaCode();
    }
}