<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksOnline extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package  Magenest_QuickBooksOnline
 * @author   Magenest JSC
 */
namespace Magenest\QuickBooksOnline\Model;

use Magenest\QuickBooksOnline\Logger\Logger;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Synchronization
 *
 * @package Magenest\QuickBooksOnline\Model
 */
abstract class Synchronization
{
    
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Log
     */
    protected $log;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_model;

    /**
     * @var array
     */
    protected $parameter;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Synchronization constructor.
     *
     * @param Client $client
     * @param Log $log
     */
    public function __construct(
        Client $client,
        Log $log
    ) {
        $this->logger = ObjectManager::getInstance()->get(Logger::class);
        $this->client = $client;
        $this->log = $log;
    }

    /**
     * Set Model
     *
     * @param \Magento\Framework\DataObject $model
     * @return $this
     */
    public function setModel($model)
    {
        $this->_model = $model;
        return $this;
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getModel()
    {
        return $this->_model;
    }

    /**
     * @return $this
     */
    public function unsetModel()
    {
        unset($this->_model);
        
        return $this;
    }
    
    /**
     * @param array $params
     * @return $this
     */
    public function setParameter($params)
    {
        if ($this->parameter !== null) {
            $this->parameter = array_replace_recursive($this->parameter, $params);
        } else {
            $this->parameter = $params;
        }
        
        return $this;
    }

    /**
     * @return array
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * Query to QuickBooks Online
     *
     * @param $query
     * @return array
     * @throws LocalizedException
     */
    public function query($query)
    {
        try {
            $path = 'query?query='.rawurlencode($query);
            $responses = $this->sendRequest(\Zend_Http_Client::GET, $path);
            foreach ($responses as $response) {
                if (is_array($response)) {
                    foreach ($response as $item) {
                        if (is_array($item) && isset($item[0]['Id'])) {
                            return $item[0];
                        }
                    }
                }
            }
            return [];
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Query to QuickBooks Online
     *
     * @param $query
     * @return array
     * @throws LocalizedException
     */
    public function queryTax($query)
    {
        try {
            $path = 'query?query='.rawurlencode($query);
            $responses = $this->sendRequest(\Zend_Http_Client::GET, $path);
            foreach ($responses as $response) {
                if (is_array($response)) {
                    return $response;
                }
            }

            return [];
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * @param string $method
     * @param $path
     * @param array $params
     * @return mixed|string
     * @throws LocalizedException
     */
    protected function sendRequest($method, $path, $params = [])
    {
        if ($this->client->isExpiredToken()) {
            try {
                $this->client->refreshAccessToken();
            } catch (\Exception $e) {
                throw new LocalizedException(__($e->getMessage()));
            }
        }

        return $this->client->sendRequest($method, $path, $params);
    }
    
    /**
     * @param \Magento\Customer\Model\Address|\Magento\Sales\Model\Order\Address $address
     * @return array
     */
    protected function getAddress($address)
    {
        return [
            'Line1' => $address->getFirstname().' '.$address->getLastname(),
            'Line2' => $address->getStreetLine(1),
            'Line3' => $address->getStreetLine(2),
            'City' => $address->getCity(),
            'Country' => $address->getCountryId(),
            'CountrySubDivisionCode' => $address->getRegion(),
            'PostalCode' => $address->getPostcode()
        ];
    }

    /**
     * Save history sync to database
     *
     * @param $id
     * @param $qboId
     * @param null $msg
     */
    public function addLog($id, $qboId = null, $msg = null)
    {
        $data = [
            'type_id' => $id,
            'qbo_id'  => $qboId,
            'type'    => $this->type,
            'msg'     => $msg
        ];

        $this->log->setData($data);
        $this->log->save();
    }
}
