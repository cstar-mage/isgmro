<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Model\Synchronization;

use Magenest\QuickBooksOnline\Model\Synchronization;
use Magento\Config\Model\Config as ConfigModel;
use Magenest\QuickBooksOnline\Model\TaxFactory;
use Magenest\QuickBooksOnline\Model\Client;
use Magenest\QuickBooksOnline\Model\Log;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class TaxCode
 * @package Magenest\QuickBooksOnline\Model\Synchronization
 */
class TaxCode extends Synchronization
{
    /**
     * @var TaxFactory
     */
    protected $taxFactory;

    /**
     * TaxCode constructor.
     * @param Client $client
     * @param Log $log
     * @param TaxFactory $taxFactory
     */
    public function __construct(
        Client $client,
        Log $log,
        TaxFactory $taxFactory
    ) {
        parent::__construct($client, $log);
        $this->taxFactory = $taxFactory;
        $this->type = 'taxcode';
    }

    /**
     * @param $title
     * @param $code
     * @throws LocalizedException
     */
    public function sync($id, $code, $rate)
    {
        $check = $this->getTaxAgencyId();
        if (isset($check['TaxAgency']['0']['Id']) && $check['TaxAgency']['0']['Id'] >0) {
            $params = [
                'TaxCode' => $code,
                'TaxRateDetails' => [
                    'TaxRateName' => $code.'_'.$id,
                    'RateValue' => $rate,
                    'TaxAgencyId' => $check['TaxAgency']['0']['Id'],
                    'TaxApplicableOn' => 'Sales',
                ]
            ];

            $check = $this->checkTaxCode($code);

            if (!empty($check)) {
                if (isset($check['TaxCode'][0]['Id'])) {
                    $model = $this->taxFactory->create()->load($code, 'tax_code');
                    $model->setQboId($check['TaxCode'][0]['Id'])->save();
                    if (isset($check['TaxCode'][0]['SalesTaxRateList']['TaxRateDetail'][0]['TaxRateRef']['value'])) {
                        $model->setTaxRateValue($check['TaxCode'][0]['SalesTaxRateList']['TaxRateDetail'][0]['TaxRateRef']['value'])->save();
                    }
                }
            } else {
                try {
                    $response = $this->sendRequest(\Zend_Http_Client::POST, 'taxservice/taxcode', $params);

                    if (isset($response['TaxCodeId'])) {
                        $model = $this->taxFactory->create()->load($code, 'tax_code');
                        $model->setQboId($response['TaxCodeId'])->save();
                        if (isset($response['TaxRateDetails'])) {
                            $model->setTaxRateValue($response['TaxRateDetails'][0]['TaxRateId'])->save();
                        }
                    } else {
                        throw new LocalizedException(
                            __('We can\'t sync the Tax Code with name like "%1"', $code)
                        );
                    }
                } catch (LocalizedException $e) {
                    $this->addLog($code, null, $e->getMessage());
                }
            }
        } else {
            $this->addLog($code, null, 'Not have tax agency');
        }
    }

    /**
     * Check PaymentMethod
     *
     * @param  $name
     * @return array
     */
    public function checkTaxCode($code)
    {
        $query = "select * from TaxCode WHERE Name='{$code}'";

        return $this->queryTax($query);
    }

    /**
     * Check PaymentMethod
     *
     * @param  $name
     * @return array
     */
    public function getTaxAgencyId()
    {
        $query = "select * from TaxAgency";

        return $this->queryTax($query);
    }
}
