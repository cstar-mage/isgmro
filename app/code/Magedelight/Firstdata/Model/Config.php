<?php

namespace Magedelight\Firstdata\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    const FIRSTDATA_ACTIVE = 'payment/md_firstdata/active';
    const FIRSTDATA_TITLE = 'payment/md_firstdata/title';

    const FIRSTDATA_GATEWAY_ID = 'payment/md_firstdata/gateway_id';
    const FIRSTDATA_GATEWAY_PASS = 'payment/md_firstdata/gateway_pass';
    const FIRSTDATA_KEY_ID = 'payment/md_firstdata/key_id';
    const FIRSTDATA_HMAC_KEY = 'payment/md_firstdata/hmac_key';

    const FIRSTDATA_TEST = 'payment/md_firstdata/test';
    const FIRSTDATA_PAYMENT_ACTION = 'payment/md_firstdata/payment_action';
    const FIRSTDATA_DEBUG = 'payment/md_firstdata/debug';
    const FIRSTDATA_CCTYPES = 'payment/md_firstdata/cctypes';
    const FIRSTDATA_CCV = 'payment/md_firstdata/useccv';
    const FIRSTDATA_SOAP_GATEWAY_URL = 'payment/md_firstdata/soap_gateway_url';
    const FIRSTDATA_SOAP_TEST_GATEWAY_URL = 'payment/md_firstdata/test_soap_gateway_url';
    const FIRSTDATA_VALIDATION_TYPE = 'payment/md_firstdata/validation_mode';
    const FIRSTDATA_CARD_SAVE_OPTIONAL = 'payment/md_firstdata/save_optional';
    const FIRSTDATA_NEW_ORDER_STATUS = 'payment/md_firstdata/order_status';

    const FIRSTDATA_VALIDATION_NONE = 'none';
    const FIRSTDATA_VALIDATION_TEST = 'testMode';
    const FIRSTDATA_VALIDATION_LIVE = 'liveMode';

    const PREAUTHORIZE = '01';
    const PURCHASE = '00';
    const PREAUTHORIZECOMPLETION = '32';
    const REFUND = '04';
    const VOID = '13';

    protected $_storeId = null;
    protected $_backend = false;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $quoteSession;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_adminsession;

    /**
     * Scope config.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Encryption\Encryptor
     */
    protected $encryptor;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\Session\Quote $quoteSession,
        \Magento\Backend\Model\Session $adminsession,
        \Magento\Framework\Encryption\Encryptor $encryptor,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->_storeManager = $storeManager;
        $this->_registry = $registry;
        $this->quoteSession = $quoteSession;
        $this->_adminsession = $adminsession;
        $this->_scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
        $this->_backend = false;

        if ($this->_backend && $this->_registry->registry('current_order') != false) {
            $this->setStoreId($this->_registry->registry('current_order')->getStoreId());
            $this->_adminsession->setCustomerStoreId(null);
        } elseif ($this->_backend && $this->_registry->registry('current_invoice') != false) {
            $this->setStoreId($this->_registry->registry('current_invoice')->getStoreId());
            $this->_adminsession->setCustomerStoreId(null);
        } elseif ($this->_backend && $this->_registry->registry('current_creditmemo') != false) {
            $this->setStoreId($this->_registry->registry('current_creditmemo')->getStoreId());
            $this->_adminsession->setCustomerStoreId(null);
        } elseif ($this->_backend && $this->_registry->registry('current_customer') != false) {
            $this->setStoreId($this->_registry->registry('current_customer')->getStoreId());
            $this->_adminsession->setCustomerStoreId($this->_registry->registry('current_customer')->getStoreId());
        } elseif ($this->_backend && $this->_session->getStore()->getId() > 0) {
            $this->setStoreId($this->_session->getStore()->getId());
            $this->_adminsession->setCustomerStoreId(null);
        } else {
            $customerStoreSessionId = $this->_adminsession->getCustomerStoreId();
            if ($this->_backend && $customerStoreSessionId != null) {
                $this->setStoreId($customerStoreSessionId);
            } else {
                $this->setStoreId($this->_storeManager->getStore()->getId());
            }
        }
    }
    public function setStoreId($storeId = 0)
    {
        $this->_storeId = $storeId;

        return $this;
    }

    public function getConfigData($field, $storeId = null)
    {
        return $this->_scopeConfig->getValue($field, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getIsActive()
    {
        return $this->getConfigData(self::FIRSTDATA_ACTIVE, $this->_storeId);
    }

    /**
     * This method will return whether test mode is enabled or not.
     *
     * @return bool
     */
    public function getIsTestMode()
    {
        return $this->getConfigData(self::FIRSTDATA_TEST, $this->_storeId);
    }

    /**
     * This metod will return FIRSTDATA Gateway url depending on test mode enabled or not.
     *
     * @return string
     */
    public function getGatewayUrl()
    {
        $isTestMode = $this->getIsTestMode();
        $gatewayUrl = ($isTestMode) ? $this->getConfigData(self::FIRSTDATA_SOAP_TEST_GATEWAY_URL, $this->_storeId) : $this->getConfigData(self::FIRSTDATA_SOAP_GATEWAY_URL, $this->_storeId);

        return trim($gatewayUrl);
    }

    /**
     * This methos will return Firstdata payment method title set by admin to display on onepage checkout payment step.
     *
     * @return string
     */
    public function getMethodTitle()
    {
        return (string) $this->getConfigData(self::FIRSTDATA_TITLE, $this->_storeId);
    }

    /**
     * This method will return gateway id set by admin in configuration.
     *
     * @return string
     */
    public function getGatewayId()
    {
        return trim($this->encryptor->decrypt($this->getConfigData(self::FIRSTDATA_GATEWAY_ID, $this->_storeId)));
    }

    /**
     * This method will return gateway password set by admin in configuration.
     *
     * @return string
     */
    public function getGatewayPass()
    {
        return trim($this->encryptor->decrypt($this->getConfigData(self::FIRSTDATA_GATEWAY_PASS, $this->_storeId)));
    }
    /**
     * This method will return gateway password set by admin in configuration.
     *
     * @return string
     */
    public function getKeyId()
    {
        return trim($this->encryptor->decrypt($this->getConfigData(self::FIRSTDATA_KEY_ID, $this->_storeId)));
    }
    /**
     * This method will return gateway password set by admin in configuration.
     *
     * @return string
     */
    public function getHmacKey()
    {
        return trim($this->encryptor->decrypt($this->getConfigData(self::FIRSTDATA_HMAC_KEY, $this->_storeId)));
    }
    /**
     * This will returne payment action whether it is authorized or authorize and capture.
     *
     * @return string
     */
    public function getPaymentAction()
    {
        return (string) $this->getConfigData(self::FIRSTDATA_PAYMENT_ACTION, $this->_storeId);
    }
    /**
     * This method will return whether debug is enabled from config.
     *
     * @return bool
     */
    public function getIsDebugEnabled()
    {
        return (boolean) $this->getConfigData(self::FIRSTDATA_DEBUG, $this->_storeId);
    }

    /**
     * This method return whether card verification is enabled or not.
     *
     * @return bool
     */
    public function isCardVerificationEnabled()
    {
        return (boolean) $this->getConfigData(self::FIRSTDATA_CCV, $this->_storeId);
    }

    /**
     * Firstdata validation mode.
     *
     * @return string
     */
    public function getValidationMode()
    {
        return (string) $this->getConfigData(self::FIRSTDATA_VALIDATION_TYPE, $this->_storeId);
    }

    /**
     * Method which will return whether customer must save credit card as profile of not.
     *
     * @return bool
     */
    public function getSaveCardOptional()
    {
        return (boolean) $this->getConfigData(self::FIRSTDATA_CARD_SAVE_OPTIONAL, $this->_storeId);
    }

    /**
     * @return config value
     */
    public function getCcTypes()
    {
        return $this->getConfigData(self::FIRSTDATA_CCTYPES, $this->_storeId);
    }

    /**
     * @return config value
     */
    public function getDefaultFormat()
    {
        return $this->getConfigData('customer/address_templates/html', $this->_storeId);
    }
}
