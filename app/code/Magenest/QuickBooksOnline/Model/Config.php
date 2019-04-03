<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Config
 *
 * @package Magenest\QuickBooksOnline\Model
 */
class Config
{
    
    /**@#+
     * Constants Path for check
     */
    const XML_PATH_QBONLINE_IS_CONNECTED = 'qbonline/connection/is_connected';
    const XML_PATH_QBONLINE_APP_MODE = 'qbonline/connection/app_mode';
    const XML_PATH_QBONLINE_COMPANY_ID = 'qbonline/connection/company_id';

    /**@#+
     * Production Mode Application
     */
    const XML_PATH_PRODUCTION_APPLICATION_TOKEN = 'qbonline/production_mode/application_token';
    const XML_PATH_PRODUCTION_CONSUMER_KEY = 'qbonline/production_mode/consumer_key';
    const XML_PATH_PRODUCTION_CONSUMER_SECRET = 'qbonline/production_mode/consumer_secret';

    /**@#+
     * Sandbox Mode Application
     */
    const XML_PATH_SANDBOX_APPLICATION_TOKEN = 'qbonline/sandbox_mode/application_token';
    const XML_PATH_SANDBOX_CONSUMER_KEY = 'qbonline/sandbox_mode/consumer_key';
    const XML_PATH_SANDBOX_CONSUMER_SECRET = 'qbonline/sandbox_mode/consumer_secret';


    const XML_PATH_SANDBOX_CLIENT_SECRET = 'qbonline/connection/test_client_secret';
    const XML_PATH_SANDBOX_CLIENT_ID = 'qbonline/connection/test_client_id';
    const XML_PATH_PRODUCTION_CLIENT_SECRET = 'qbonline/connection/live_client_secret';
    const XML_PATH_PRODUCTION_CLIENT_ID = 'qbonline/connection/live_client_id';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param $type
     * @return mixed
     */
    public function isEnableByType($type)
    {
        $path = 'qbonline/' . strtolower($type) . '/enabled';

        return $this->isEnabled($path);
    }

    /**
     * Get Sync Mode Config
     *
     * @param $type
     * @return mixed
     */
    public function getSyncModeByType($type)
    {
        $path = 'qbonline/' . strtolower($type) . '/sync_mode';

        return $this->getConfig($path);
    }

    /**
     * Get Cron Time
     *
     * @param $type
     * @return mixed
     */
    public function getCronTimeByType($type)
    {
        $path = 'qbonline/' . strtolower($type) . '/cron_time';

        return $this->getConfig($path);
    }

    /**
     * Get Prefix
     *
     * @param $type
     * @return mixed
     */
    public function getPrefix($type)
    {
        $path = 'qbonline/prefix_sale/'.strtolower($type);

        return $this->getConfig($path);
    }

    /**
     * @param $path
     * @return mixed
     */
    protected function getConfig($path)
    {
        return $this->scopeConfig->getValue($path);
    }

    /**
     * Check is enable
     *
     * @param $path
     * @return bool
     */
    protected function isEnabled($path)
    {
        return $this->scopeConfig->isSetFlag($path);
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->getConfig('qbonline/tax_shipping/country');
    }

    /**
     * @return mixed
     */
    public function getTaxShipping()
    {
        return $this->getConfig('qbonline/tax_shipping/tax_shipping');
    }

    /**
     * @return mixed
     */
    public function getConnected()
    {
        return $this->getConfig('qbonline/connection/is_connected');
    }

    /**
     * @return mixed
     */
    public function getCompanyId()
    {
        return $this->getConfig(self::XML_PATH_QBONLINE_COMPANY_ID);
    }
}
