<?php
namespace BroSolutions\ShippingAdditional\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
class ShippingAdditionalBlockConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $_shippingMethodConfig = false;
    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    protected function _prepareShippingMethodConfig()
    {
        if(!$this->_shippingMethodConfig){
            $this->_shippingMethodConfig = array('dropdown_config' =>
                array(
                    'usps' => 'USPS', 
                    'ups' => 'UPS', 
                    'ups_freight' => 'UPS Freight', 
                    'fedex' => 'FedEx', 
                    'fedex_freight' => 'FedEx Freight', 
                    'dhl' => 'DHL',
                    'vendor_delivery' => 'Vendor Delivery', 
                    'other' => 'Other (include carrier with account number)'
                )
            );
        }
        return $this->_shippingMethodConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $dropdownConfig = $this->_prepareShippingMethodConfig();
        $config = array();
        $config = array_merge($config, $dropdownConfig);
        return $config;
    }



    public function getStoreId()
    {
        return $this->storeManager->getStore()->getStoreId();
    }


    public function getShippingCarrierLabel($val)
    {
        $this->_prepareShippingMethodConfig();
        $label = (isset($this->_shippingMethodConfig['dropdown_config'][$val])) ? $this->_shippingMethodConfig['dropdown_config'][$val] : '';
        return $label;
    }
}
