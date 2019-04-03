<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Model\Synchronization;

use Magenest\QuickBooksOnline\Model\Synchronization;
use Magenest\QuickBooksOnline\Model\Client;
use Magenest\QuickBooksOnline\Model\Log;
use Magento\Config\Model\Config as ConfigModel;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Account
 * @package Magenest\QuickBooksOnline\Model\Sync
 */
class Account extends Synchronization
{
    /**
     * Core Config Model
     *
     * @var ConfigModel
     */
    protected $configModel;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Account constructor.
     *
     * @param Client $client
     * @param Log $log
     * @param ScopeConfigInterface $scopeConfig
     * @param ConfigModel $configModel
     */
    public function __construct(
        Client $client,
        Log $log,
        ScopeConfigInterface $scopeConfig,
        ConfigModel $configModel
    ) {
        parent::__construct($client, $log);
        $this->configModel = $configModel;
        $this->_scopeConfig = $scopeConfig;
        $this->type = 'account';
    }

    /**
     * Create an Account
     *
     * @param string $type
     * @param bool $update
     * @return bool|mixed
     * @throws \Exception
     */
    public function sync($type = 'income', $update = false)
    {
        $id = $this->_scopeConfig->getValue('qbonline/account/' . $type . '_id');

        if ($id && !$update) {
            return $id;
        }

        if ($type == 'asset') {
            $params = [
                'Name'           => 'Asset Account using sync with Magento',
                'SubAccount'     => false,
                'Active'         => true,
                'AccountType'    => 'Other Current Asset',
                'Classification' => 'Asset',
                'AccountSubType' => 'Inventory',
            ];
        } elseif ($type == 'expense') {
            $params = [
                'Name'           => 'Expense Account using sync with Magento',
                'SubAccount'     => false,
                'Active'         => true,
                'AccountType'    => 'Cost of Goods Sold',
                'Classification' => 'Expense',
                'AccountSubType' => 'SuppliesMaterialsCogs',
            ];
        } else {
            $params = [
                'Name'           => 'Income Account using sync with Magento',
                'SubAccount'     => false,
                'Active'         => true,
                'AccountType'    => 'Income',
                'Classification' => 'Revenue',
                'AccountSubType' => 'SalesOfProductIncome',
            ];
        }
        
        $account = $this->checkAccount($params['Name']);
        if (isset($account['Id'])) {
            $this->saveDataByPath('qbonline/account/'.$type . '_id', $account['Id']);
            
            return $account['Id'];
        }
        $response = $this->sendRequest(\Zend_Http_Client::POST, 'account', $params);
        $this->saveDataByPath('qbonline/account/'.$type . '_id', $response['Account']['Id']);
        
        return $response['Account']['Id'];
    }

    /**
     * sync all account
     */
    public function syncAllAccount()
    {
        $isConneted = $this->_scopeConfig->getValue('qbonline/connection/is_connected');
        if (isset($isConneted) && $isConneted == 1) {
            $arrayAccount = ['asset','expense','income'];
            $i =0;
            foreach ($arrayAccount as $key => $value) {
                $this->sync($value, true);
                $i++;
            }
        }
    }

    /**
     * Save to `core_config_data` table
     *
     * @param $path
     * @param $value
     * @throws \Exception
     */
    protected function saveDataByPath($path, $value)
    {
        $this->configModel->setDataByPath($path, $value);
        $this->configModel->save();
    }

    /**
     * Check Account
     *
     * @param  $name
     * @return bool
     */
    public function checkAccount($name)
    {
        $query = "SELECT Id FROM Account WHERE Name='{$name}'";

        return $this->query($query);
    }
}
