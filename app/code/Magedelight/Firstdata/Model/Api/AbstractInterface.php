<?php

namespace Magedelight\Firstdata\Model\Api;

class AbstractInterface extends \Magento\Framework\DataObject
{
    protected $_configModel;
    protected $_storeManager;
    protected $regionFactory;
    protected $countryFactory;

    protected $_inputData = [];

    protected $_responseData = [];

    protected $_gatewayId;
    protected $_gatewayPass;
    protected $_keyid;
    protected $_hmac;

    protected $_apiGatewayUrl;
    protected $_cvvEnabled;
    protected $_soaperror;
    protected $_zendlogger;
    protected $_soaplog;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_httpRequest;

    public function __construct(\Magedelight\Firstdata\Model\Config $configModel,
            \Magento\Directory\Model\RegionFactory $regionFactory,
            \Magento\Directory\Model\CountryFactory $countryFactory,
            \Magento\Store\Model\StoreManager $storeManager,
            \Magento\Framework\App\RequestInterface $httpRequest,
            array $data = [])
    {
        $this->_configModel = $configModel;
        $this->_storeManager = $storeManager;

        $this->_gatewayId = $configModel->getGatewayId();
        $this->_gatewayPass = $configModel->getGatewayPass();
        $this->_keyid = $configModel->getKeyId();
        $this->_hmac = $configModel->getHmacKey();

        $this->_cvvEnabled = $configModel->isCardVerificationEnabled();
        $this->_gatewayUrl = $configModel->getGatewayUrl();
        $this->regionFactory = $regionFactory;
        $this->countryFactory = $countryFactory;
        $this->_httpRequest = $httpRequest;
        $this->_soaperror = new \Zend\Log\Writer\Stream(BP.'/var/log/Magedelight_Firstdata_SOAPError.log');
        $this->_soaplog = new \Zend\Log\Writer\Stream(BP.'/var/log/md_firstdata.log');
        $this->_zendlogger = new \Zend\Log\Logger();
        parent::__construct($data);
    }

    /**
     * @param type $input
     *
     * @return \Magedelight\Authorizecim\Model\Api\AbstractInterface
     */
    public function setInputData($input = null)
    {
        $this->_inputData = $input;

        return $this;
    }
    /**
     * @return inputdata
     */
    public function getInputData()
    {
        return $this->_inputData;
    }

    /**
     * @param type $response
     *
     * @return \Magedelight\Authorizecim\Model\Api\AbstractInterface
     */
    public function setResponseData($response = [])
    {
        $this->_responseData = $response;

        return $this;
    }
    /**
     * @return responsedata
     */
    public function getResponseData()
    {
        return $this->_responseData;
    }
    /**
     * @return config model
     */
    public function getConfigModel()
    {
        return $this->_configModel;
    }
}
