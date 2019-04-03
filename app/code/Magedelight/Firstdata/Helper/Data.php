<?php
/**
 * Magedelight
 * Copyright (C) 2016 Magedelight <info@magedelight.com>.
 *
 * NOTICE OF LICENSE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://opensource.org/licenses/gpl-3.0.html.
 *
 * @category Magedelight
 *
 * @copyright Copyright (c) 2016 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Firstdata\Helper;

use Magento\Payment\Model\Config as PaymentConfig;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const REQUEST_TYPE_AUTH_ONLY = 'AUTH_ONLY';
    const REQUEST_TYPE_AUTH_CAPTURE = 'AUTH_CAPTURE';
    const REQUEST_TYPE_PRIOR_AUTH_CAPTURE = 'PRIOR_AUTH_CAPTURE';
    const REQUEST_TYPE_CREDIT = 'CREDIT';
    const REQUEST_TYPE_VOID = 'VOID';
     /**
      * @var Magento\Customer\Model\Address\Config 
      */
     protected $_addressConfig;

     /**
      * @var Magento\Directory\Model\Region
      */
     protected $_region;

     /**
      * @var Magento\Framework\Stdlib\DateTime
      */
     protected $dateFormat;

     /**
      * @var Magento\Payment\Model\Config
      */
     protected $paymentConfig;

      /**
       * @var Magento\Framework\Stdlib\DateTime\DateTime 
       */
      protected $dateTime;

      /**
       * @var Magedelight\Firstdata\Model\Config
       */
      protected $firstdataConfig;

    protected $today = null;
    
    protected $_storeManager;
      /**
       * @param \Magento\Framework\App\Helper\Context $context
       * @param \Magento\Customer\Model\Address\Config $addressConfig
       * @param \Magento\Directory\Model\Region $region
       * @param \Magento\Framework\Stdlib\DateTime $dateFormat
       * @param PaymentConfig $paymentConfig
       * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
       * @param \Magedelight\Firstdata\Model\Config $firstdataConfig
       */
      public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Magento\Directory\Model\Region $region,
        \Magento\Framework\Stdlib\DateTime $dateFormat,
         PaymentConfig $paymentConfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magedelight\Firstdata\Model\Config $firstdataConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
          $this->_addressConfig = $addressConfig;
          $this->_region = $region;
          $this->dateFormat = $dateFormat;
          $this->paymentConfig = $paymentConfig;
          $this->dateTime = $dateTime;
          $this->firstdataConfig = $firstdataConfig;
          $this->_storeManager = $storeManager;
          parent::__construct($context);
      }
      
    public function isEnabled()
    {
        $currentUrl = $this->_storeManager->getStore()->getBaseUrl();
        $domain = $this->getDomainName($currentUrl);
        $selectedWebsites = $this->getConfig('magedelight_firstdata/general/select_website');
        $websites = explode(',',$selectedWebsites);
        if(in_array($domain, $websites) && $this->getConfig('payment/md_firstdata/active') && $this->getConfig('magedelight_firstdata/license/data'))
        {
          return true;
        }else{
          return false;
        }
    }

    public function getDomainName($domain){
        $string = '';
        
        $withTrim = str_replace(array("www.","http://","https://"),'',$domain);
        
        /* finding the first position of the slash  */
        $string = $withTrim;
        
        $slashPos = strpos($withTrim,"/",0);
        
        if($slashPos != false){
            $parts = explode("/",$withTrim);
            $string = $parts[0];
        }
        return $string;
    }

    public function getWebsites()
    {
        $websites = $this->_storeManager->getWebsites();
        $websiteUrls = array();
        foreach($websites as $website)
        {
            foreach($website->getStores() as $store){
                $wedsiteId = $website->getId();
                $storeObj = $this->_storeManager->getStore($store);
                $storeId = $storeObj->getId();
                $url = $storeObj->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
                $parsedUrl = parse_url($url);
                $websiteUrls[] = str_replace(array('www.', 'http://', 'https://'), '', $parsedUrl['host']);
            }
        }

        return $websiteUrls;
    }

    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }  
    /**
     * @param type $card
     *
     * @return type
     */
    public function getFormatedAddress($card)
    {
        $address = new \Magento\Framework\DataObject();
        $regionId = $card['region_id'];

        $regionName = ($regionId) ? $this->_region->load($regionId)->getName() : $card['state'];
        $address->addData(array(
            'firstname' => (string) $card['firstname'],
            'lastname' => (string) $card['lastname'],
            'company' => (string) $card['company'],
            'street1' => (string) $card['street'],
            'city' => (string) $card['city'],
            'region' => (string) $regionName,
            'postcode' => (string) $card['postcode'],
            'telephone' => (string) $card['telephone'],
            'country' => (string) $card['country_id'],
        ));

        $renderer = $this->_addressConfig->getFormatByCode('html')->getRenderer();

        return $renderer->renderArray($address->getData());
    }
    public function getCcAvailableCardTypes()
    {
        $types = array_flip(explode(',', $this->firstdataConfig->getCcTypes()));
        $mergedArray = [];

        if (is_array($types)) {
            foreach (array_keys($types) as $type) {
                $types[$type] = $this->getCcTypeNameByCode($type);
            }
        }

        //preserve the same credit card order
        $allTypes = $this->getCcTypes();
        if (is_array($allTypes)) {
            foreach ($allTypes as $ccTypeCode => $ccTypeName) {
                if (array_key_exists($ccTypeCode, $types)) {
                    $mergedArray[$ccTypeCode] = $ccTypeName;
                }
            }
        }

        return $mergedArray;
    }
    public function getCcTypes()
    {
        $ccTypes = $this->paymentConfig->getCcTypes();
        if (is_array($ccTypes)) {
            return $ccTypes;
        } else {
            return false;
        }
    }
    public function getCcTypeNameByCode($code)
    {
        $ccTypes = $this->paymentConfig->getCcTypes();
        if (isset($ccTypes[$code])) {
            return $ccTypes[$code];
        } else {
            return false;
        }
    }
    public function getTodayYear()
    {
        if (!$this->today) {
            $this->today = $this->dateTime->gmtTimestamp();
        }

        return date('Y', $this->today);
    }

    public function getTodayMonth()
    {
        if (!$this->today) {
            $this->today = $this->dateTime->gmtTimestamp();
        }

        return date('m', $this->today);
    }
    public function getTransactionMessage($payment, $requestType, $lastTransactionId, $card, $amount = false,
            $exception = false
        ) {
        return $this->getExtendedTransactionMessage(
                $payment, $requestType, $lastTransactionId, $card, $amount, $exception
            );
    }

    public function getExtendedTransactionMessage($payment, $requestType, $lastTransactionId, $card, $amount = false,
            $exception = false, $additionalMessage = false
        ) {
        $operation = $this->_getOperation($requestType);

        if (!$operation) {
            return false;
        }

        if ($amount) {
            $amount = sprintf('amount %s', $this->_formatPrice($payment, $amount));
        }

        if ($exception) {
            $result = sprintf('failed');
        } else {
            $result = sprintf('successful');
        }

        $card = sprintf('Credit Card: xxxx-%s', $card->getCcLast4());

        $pattern = '%s %s %s - %s.';
        $texts = array($card, $amount, $operation, $result);

        if (!is_null($lastTransactionId)) {
            $pattern .= ' %s.';
            $texts[] = sprintf('Firstdata Transaction ID %s', $lastTransactionId);
        }

        if ($additionalMessage) {
            $pattern .= ' %s.';
            $texts[] = $additionalMessage;
        }
        $pattern .= ' %s';
        $texts[] = $exception;

        return call_user_func_array('sprintf', array_merge(array($pattern), $texts));
    }
    protected function _getOperation($requestType)
    {
        switch ($requestType) {
                case self::REQUEST_TYPE_AUTH_ONLY:
                    return __('authorize');
                case self::REQUEST_TYPE_AUTH_CAPTURE:
                    return __('authorize and capture');
                case self::REQUEST_TYPE_PRIOR_AUTH_CAPTURE:
                    return __('capture');
                case self::REQUEST_TYPE_CREDIT:
                    return __('refund');
                case self::REQUEST_TYPE_VOID:
                    return __('void');
                default:
                    return false;
            }
    }
    protected function _formatPrice($payment, $amount)
    {
        return $payment->getOrder()->getBaseCurrency()->formatTxt($amount);
    }
    public function getAvsLabel($avs)
    {
        if (isset($this->_avsResponses[ $avs ])) {
            return __(sprintf('%s (%s)', $avs, $this->_avsResponses[ $avs ]));
        }

        return $avs;
    }

    public function getCvnLabel($cvn)
    {
        if (isset($this->_cvnResponses[ $cvn ])) {
            return __(sprintf('%s (%s)', $cvn, $this->_cvnResponses[ $cvn ]));
        }

        return $cvn;
    }
    public function checkAdmin()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $app_state = $om->get('Magento\Framework\App\State');
        $area_code = $app_state->getAreaCode();
        if ($app_state->getAreaCode() == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE) {
            return true;
        } else {
            return false;
        }
    }
}
