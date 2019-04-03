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

namespace Magedelight\Firstdata\Model\Api;

class Soap extends \Magedelight\Firstdata\Model\Api\AbstractInterface
{
    protected $_cardType = ['VI' => 'Visa', 'MC' => 'MasterCard', 'AE' => 'American Express', 'DI' => 'Discover', 'JCB' => 'JCB'];
    /**
     * @return object
     */
    public function createCustomerProfile()
    {
        $request = $this->createCustomerPaymentProfileRequest();
        $response = $this->_postRequest($request);

        return $response;
    }

    /**
     * @return array
     */
    protected function createCustomerPaymentProfileRequest()
    {
        $data = $this->_prepareData();
        $inputData = $this->getInputData();
        $regionId = $inputData->getRegionId();
        $regionCode = ($regionId) ? $this->regionFactory->create()->load($regionId)->getCode() : $inputData->getState();
        $data['trans_type'] = \Magedelight\Firstdata\Model\Config::PREAUTHORIZE;
       // $data['trans_type'] = 01;
        $creditcard = array(
            'cardnumber' => $inputData->getCcNumber(),
            'cardexpmonth' => $inputData->getCcExpMonth(),
            'ccname' => $inputData->getFirstname().' '.$inputData->getLastname(),
            'cardexpyear' => substr($inputData->getCcExpYear(), -2),
        );
        if ($this->_cvvEnabled) {
            $creditcard['cvmindicator'] = 'provided';
            $creditcard['cvmvalue'] = $inputData->getCcCid();
            $creditcard['cvv2indicator'] = 1;
            $creditcard['cvv2value'] = $inputData->getCcCid();
        }

        $billing = array();

        $billing['name'] = $inputData->getFirstname().' '.$inputData->getLastname();
        $billing['company'] = $inputData->getCompany();
        $billing['address'] = $inputData->getStreet(1);
        $billing['city'] = $inputData->getCity();
        $billing['state'] = $regionCode;
        $billing['zip'] = $inputData->getPostcode();
        $billing['country'] = $inputData->getCountryId();
        $billing['email'] = $inputData->getEmail();
        $billing['phone'] = $inputData->getTelephone();
        $merchantinfo = array();
        $merchantinfo['gatewayId'] = $data['gatewayId'];
        $merchantinfo['gatewayPass'] = $data['gatewayPass'];
        $paymentdetails = array();
        $paymentdetails['chargetotal'] = 0;
        $data = array_merge($data, $creditcard, $billing, $merchantinfo, $paymentdetails);

        return $data;
    }

    /**
     * @return object
     */
    public function updateCustomerProfile()
    {
        $request = $this->updateCustomerProfileRequest();
        $response = $this->_postRequest($request);

        return $response;
    }

    /**
     * @return array
     */
    public function updateCustomerProfileRequest()
    {
        $data = $this->_prepareData();
        $inputData = $this->getInputData();
        $regionId = $inputData->getRegionId();
        $regionCode = ($regionId) ? $this->regionFactory->create()->load($regionId)->getCode() : $inputData->getState();
        $data['trans_type'] = \Magedelight\Firstdata\Model\Config::PREAUTHORIZE;
        $cardUpdateCheck = $inputData->getcc_action();
        if ($cardUpdateCheck != 'existing') {
            $creditcard = array(
                'cardnumber' => $inputData->getCcNumber(),
                'cardexpmonth' => $inputData->getCcExpMonth(),
                'ccname' => $inputData->getFirstname().' '.$inputData->getLastname(),
                'cardexpyear' => substr($inputData->getCcExpYear(), -2),
            );
        } else {
            $creditcard = array(
                'TransarmorToken' => $inputData->getCustomerTransarmorId(),
                'cardexpmonth' => $inputData->getCardexpmonth(),
                'cardexpyear' => $inputData->getCardexpyear(),
                'ccname' => $inputData->getFirstname().' '.$inputData->getLastname(),
                'cardtype' => $this->_cardType[$inputData->getCardtype()],
            );
        }
        if ($this->_cvvEnabled) {
            $creditcard['cvmindicator'] = 'provided';
            $creditcard['cvmvalue'] = $inputData->getCcCid();
            $creditcard['cvv2indicator'] = 1;
            $creditcard['cvv2value'] = $inputData->getCcCid();
        }

        $billing = array();
        $billing['name'] = $inputData->getFirstname().' '.$inputData->getLastname();
        $billing['company'] = $inputData->getCompany();
        $billing['address'] = $inputData->getStreet(1);
        $billing['city'] = $inputData->getCity();
        $billing['state'] = $regionCode;
        $billing['zip'] = $inputData->getPostcode();
        $billing['country'] = $inputData->getCountryId();
        $billing['email'] = $inputData->getEmail();
        $billing['phone'] = $inputData->getTelephone();
        $merchantinfo = array();
        $merchantinfo['gatewayId'] = $data['gatewayId'];
        $merchantinfo['gatewayPass'] = $data['gatewayPass'];
        $paymentdetails = array();
        $paymentdetails['chargetotal'] = 0;
        $data = array_merge($data, $creditcard, $billing, $merchantinfo, $paymentdetails);

        return $data;
    }

    /**
     * @return object
     */
    public function prepareCaptureResponse(\Magento\Payment\Model\InfoInterface $payment, $amount, $transarmor = false)
    {
        $this->_request = $this->prepareCaptureRequest($payment, $amount, $transarmor);
        $response = $this->_postRequest($this->_request);

        return $response;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param type                                 $amount
     * @param type                                 $transarmor
     *
     * @return type
     */
    public function prepareCaptureRequest(\Magento\Payment\Model\InfoInterface $payment, $amount, $transarmor)
    {
        $data = $this->_prepareData();
        $billingAddress = $payment->getOrder()->getBillingAddress();
        $shippingAddress = $payment->getOrder()->getShippingAddress();
        $data['trans_type'] = \Magedelight\Firstdata\Model\Config::PURCHASE;

        if ($transarmor == false) {
            $post = $this->_httpRequest->getParam('payment');
            $ccNumber = $payment->getCcNumber();
            $expMonth = $payment->getCcExpMonth();
            $expYear = $payment->getCcExpYear();
            $creditcard = array(
                'cardnumber' => empty($ccNumber) ? $post['cc_number'] : $ccNumber,
                'cardexpmonth' => empty($expMonth) ? $post['cc_exp_month'] : $expMonth,
                'ccname' => $billingAddress->getFirstname().' '.$billingAddress->getLastname(),
                'cardexpyear' => substr(empty($expYear) ? $post['cc_exp_year'] : $expYear, -2),
            );
        } else {
            $creditcard = array(
                'TransarmorToken' => $payment->getMdfirstdataTransarmorId(),
                'cardexpmonth' => $payment->getCcExpMonth(),
                'cardexpyear' => $payment->getCcExpYear(),
                'ccname' => $billingAddress->getFirstname().' '.$billingAddress->getLastname(),
                'cardtype' => $this->_cardType[$payment->getCcType()],
            );
        }

        if ($this->_cvvEnabled) {
            $creditcard['cvmindicator'] = 'provided';
            $creditcard['cvmvalue'] = $payment->getCcCid();
            $creditcard['cvv2indicator'] = 1;
            $creditcard['cvv2value'] = $payment->getCcCid();
        }

        $shipping = array();
        $billing = array();
        $order = $payment->getOrder();
        if (!empty($order)) {
            $billing['name'] = $billingAddress->getFirstname().' '.$billingAddress->getLastname();
            $billing['company'] = $billingAddress->getCompany();
            $billing['address'] = $billingAddress->getStreet(1);
            $billing['city'] = $billingAddress->getCity();
            $billing['state'] = $billingAddress->getRegion();
            $billing['zip'] = $billingAddress->getPostcode();
            $billing['country'] = $billingAddress->getCountry();
            $billing['email'] = $order->getCustomerEmail();
            $billing['phone'] = $billingAddress->getTelephone();
            $billing['fax'] = $billingAddress->getFax();

            if (!empty($shipping)) {
                $shipping['sname'] = $shippingAddress->getFirstname().' '.$shippingAddress->getLastname();
                $shipping['saddress1'] = $shippingAddress->getStreet(1);
                $shipping['scity'] = $shippingAddress->getCity();
                $shipping['sstate'] = $shippingAddress->getRegion();
                $shipping['szip'] = $shippingAddress->getPostcode();
                $shipping['scountry'] = $shippingAddress->getCountry();
            }
        }

        $merchantinfo = array();
        $merchantinfo['gatewayId'] = $data['gatewayId'];
        $merchantinfo['gatewayPass'] = $data['gatewayPass'];
        $paymentdetails = array();
        $paymentdetails['chargetotal'] = $amount;

        $data = array_merge($data, $creditcard, $billing, $shipping, $merchantinfo, $paymentdetails);

        return $data;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param type                                 $amount
     * @param type                                 $transarmor
     *
     * @return type
     */
    public function prepareAuthorizeCaptureResponse(\Magento\Payment\Model\InfoInterface $payment, $amount, $transarmor = false)
    {
        $this->_request = $this->prepareAuthorizeCaptureRequest($payment, $amount, $transarmor);
        $response = $this->_postRequest($this->_request);

        return $response;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param type                                 $amount
     * @param type                                 $transarmor
     *
     * @return type
     */
    public function prepareAuthorizeCaptureRequest(\Magento\Payment\Model\InfoInterface $payment, $amount, $transarmor)
    {
        $payment->setAmount($amount);
        $data = $this->_prepareData();
        $data['trans_type'] = \Magedelight\Firstdata\Model\Config::PREAUTHORIZECOMPLETION;
        $data['transaction_tag'] = $payment->getTransactionTag();
        $data['authorization_num'] = $payment->getParentTransactionId();
        $data['chargetotal'] = $amount;
//        echo "<pre>";
//        print_r($data);
//        die();
        return $data;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param type                                 $amount
     * @param type                                 $transarmor
     *
     * @return type
     */
    public function prepareAuthorizeResponse(\Magento\Payment\Model\InfoInterface $payment, $amount, $transarmor = false)
    {
        $this->_request = $this->prepareAuthorizeRequest($payment, $amount, $transarmor);
        $response = $this->_postRequest($this->_request);

        return $response;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param type                                 $amount
     * @param type                                 $transarmor
     *
     * @return type
     */
    public function prepareAuthorizeRequest(\Magento\Payment\Model\InfoInterface $payment, $amount, $transarmor)
    {
        $data = $this->_prepareData();
        $billingAddress = $payment->getOrder()->getBillingAddress();
        $shippingAddress = $payment->getOrder()->getShippingAddress();
        $data['trans_type'] = \Magedelight\Firstdata\Model\Config::PREAUTHORIZE;

        if ($transarmor == false) {
            $post = $this->_httpRequest->getParam('payment');
            $ccNumber = $payment->getCcNumber();
            $expMonth = $payment->getCcExpMonth();
            $expYear = $payment->getCcExpYear();
            $creditcard = array(
                'cardnumber' => empty($ccNumber) ? $post['cc_number'] : $ccNumber,
                'cardexpmonth' => empty($expMonth) ? $post['cc_exp_month'] : $expMonth,
                'ccname' => $billingAddress->getFirstname().' '.$billingAddress->getLastname(),
                'cardexpyear' => substr(empty($expYear) ? $post['cc_exp_year'] : $expYear, -2),
            );
        } else {
            $creditcard = array(
                'TransarmorToken' => $payment->getMdfirstdataTransarmorId(),
                'cardexpmonth' => $payment->getCcExpMonth(),
                'cardexpyear' => $payment->getCcExpYear(),
                'ccname' => $billingAddress->getFirstname().' '.$billingAddress->getLastname(),
                'cardtype' => $this->_cardType[$payment->getCcType()],
            );
        }

        if ($this->_cvvEnabled) {
            $creditcard['cvmindicator'] = 'provided';
            $creditcard['cvmvalue'] = $payment->getCcCid();
            $creditcard['cvv2indicator'] = 1;
            $creditcard['cvv2value'] = $payment->getCcCid();
        }

        $shipping = array();
        $billing = array();
        $order = $payment->getOrder();
        if (!empty($order)) {
            $billing['name'] = $billingAddress->getFirstname().' '.$billingAddress->getLastname();
            $billing['company'] = $billingAddress->getCompany();
            $billing['address'] = $billingAddress->getStreet(1);
            $billing['city'] = $billingAddress->getCity();
            $billing['state'] = $billingAddress->getRegion();
            $billing['zip'] = $billingAddress->getPostcode();
            $billing['country'] = $billingAddress->getCountry();
            $billing['email'] = $order->getCustomerEmail();
            $billing['phone'] = $billingAddress->getTelephone();
            $billing['fax'] = $billingAddress->getFax();

            if (!empty($shipping)) {
                $shipping['sname'] = $shippingAddress->getFirstname().' '.$shippingAddress->getLastname();
                $shipping['saddress1'] = $shippingAddress->getStreet(1);
                $shipping['scity'] = $shippingAddress->getCity();
                $shipping['sstate'] = $shippingAddress->getRegion();
                $shipping['szip'] = $shippingAddress->getPostcode();
                $shipping['scountry'] = $shippingAddress->getCountry();
            }
        }

        $merchantinfo = array();
        $merchantinfo['gatewayId'] = $data['gatewayId'];
        $merchantinfo['gatewayPass'] = $data['gatewayPass'];
        $paymentdetails = array();
        $paymentdetails['chargetotal'] = $amount;

        $data = array_merge($data, $creditcard, $billing, $shipping, $merchantinfo, $paymentdetails);

        return $data;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param type                                 $card
     *
     * @return type
     */
    public function prepareVoidResponse(\Magento\Payment\Model\InfoInterface $payment, $card)
    {
        $billingAddress = $payment->getOrder()->getBillingAddress();
        $data = $this->_prepareData();
        $data['trans_type'] = \Magedelight\Firstdata\Model\Config::VOID;
        $data['oid'] = $payment->getParentTransactionId();
        $data['transaction_tag'] = $payment->getTransactionTag();
        $data['authorization_num'] = $payment->getParentTransactionId();
        $instance = $payment->getMethodInstance()->getInfoInstance();
        $paymentdetails = array();

        $data['ccname'] = $billingAddress->getFirstname().' '.$billingAddress->getLastname();
        $paymentdetails['cardexpmonth'] = $instance->getCcExpMonth();
        $paymentdetails['cardexpyear'] = substr($instance->getCcExpYear(), -2);
        $paymentdetails['TransarmorToken'] = $instance->getFirstdataToken();
        $paymentdetails['cardtype'] = $this->_cardType[$instance->getCcType()];
        $paymentdetails['cardnumber'] = $instance->getCcNumber();
        $order = $payment->getOrder();
        $data['chargetotal'] = $instance->getBaseAmountAuthorized();
        $data = array_merge($data, $paymentdetails);
        $response = $this->_postRequest($data);

        return $response;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param type                                 $amount
     * @param type                                 $realCaptureTransactionId
     *
     * @return type
     */
    public function prepareRefundResponse(\Magento\Payment\Model\InfoInterface $payment, $amount, $realCaptureTransactionId)
    {
        $this->_request = $this->prepareRefundRequest($payment, $amount, $realCaptureTransactionId);
        $response = $this->_postRequest($this->_request);

        return $response;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param type                                 $amount
     * @param type                                 $realCaptureTransactionId
     *
     * @return type
     */
    protected function prepareRefundRequest(\Magento\Payment\Model\InfoInterface $payment, $amount, $realCaptureTransactionId)
    {
        $this->_request = new \stdClass();
        $billingAddress = $payment->getOrder()->getBillingAddress();
        $data = $this->_prepareData();
        $data['trans_type'] = \Magedelight\Firstdata\Model\Config::REFUND;
        $data['oid'] = $payment->getRefundTransactionId();
        $data['transaction_tag'] = $payment->getTransactionTag();
        $data['authorization_num'] = $payment->getCcTransId();

        $instance = $payment->getMethodInstance()->getInfoInstance();
        $paymentdetails = array();
        $paymentdetails['chargetotal'] = $amount;
        $paymentdetails['cardexpmonth'] = $instance->getCcExpMonth();
        $paymentdetails['cardexpyear'] = substr($instance->getCcExpYear(), -2);
        $paymentdetails['TransarmorToken'] = $instance->getFirstdataToken();
        $paymentdetails['cardtype'] = $this->_cardType[$instance->getCcType()];
        $paymentdetails['cardnumber'] = $instance->getCcNumber();
        $data['ccname'] = $billingAddress->getFirstname().' '.$billingAddress->getLastname();

        $data = array_merge($data, $paymentdetails);

        return $data;
    }
    /**
     * @return array
     */
    protected function _prepareData()
    {
        $data = array(
            'keyId' => $this->_keyid,
            'hmacKey' => $this->_hmac,
            'wsdlUrl' => $this->_gatewayUrl,
            'gatewayId' => $this->_gatewayId,
            'gatewayPass' => $this->_gatewayPass,
        );
        if (empty($data['keyId']) || empty($data['hmacKey']) || empty($data['wsdlUrl']) || empty($data['gatewayId']) || empty($data['gatewayPass'])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Gateway Parameters Missing.'));
        }

        return $data;
    }
    /**
     * chnage from: process hash table or xml string table using cURL
     * change to : process data using SoapClientHMAC for latest version of API
     * change on 19-feb-15.
     *
     * @param array $data
     *
     * @return string $xml
     */
    protected function _postRequest($data)
    {
        $debugData = array('request' => $data);
        $trxnProperties = '';
        $trxnProperties = $this->_buildRequest($data);
        try {
            //   $client = Mage::getModel('linkpoint/soapclienthmac', array("url" => $data["wsdlUrl"]));
            $client = new \Magedelight\Firstdata\Model\Api\Soapclienthmac($this->_configModel);
            $response = $client->SendAndCommit($trxnProperties);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__('First Data authorization failed'));
        }

        if (!$response) {
            throw new \Magento\Framework\Exception\LocalizedException(ucwords("error in $response"));
        }
        if (@$client->fault) {
            throw new \Magento\Framework\Exception\LocalizedException("FAULT:  Code: {$client->faultcode} <BR /> String: {$client->faultstring} </B>");
        }

        $result = $this->_readResponse($response);

        if ($this->_configModel->getIsDebugEnabled() == 1) {
            $this->prepareSoapForDebug($data, $response);
        }

        return $result;
    }
    /**
     * @param type $request
     * @param type $response
     */
    public function prepareSoapForDebug($request, $response)
    {
        $requstArray = json_decode(json_encode($request), true);
        $responseArray = json_decode(json_encode($response), true);
        $firstdataRequest = new \SimpleXMLElement('<?xml version="1.0"?><firstdata_request_info></firstdata_request_info>');
        $this->array_to_xml($requstArray, $firstdataRequest);

        $firstdataRequestXMLFile = $firstdataRequest->asXML();

        $dom = new \DOMDocument();
        $dom->loadXML($firstdataRequestXMLFile);
        $dom->formatOutput = true;
        $RequestXML = '';
        $ResponseXML = '';
        $RequestXML .= "Request:\n\n";
        $RequestXML .= $dom->saveXML();
        /*print request log */
        $logger = $this->_zendlogger;
        $logger->addWriter($this->_soaplog);
        $logger->info("$RequestXML");

        $firstdataResponse = new \SimpleXMLElement('<?xml version="1.0"?><firstdata_response_info></firstdata_response_info>');
        $this->array_to_xml($responseArray, $firstdataResponse);
        $firstdataResponseXMLFile = $firstdataResponse->asXML();
        $dom = new \DOMDocument();
        $dom->loadXML($firstdataResponseXMLFile);
        $dom->formatOutput = true;
        $ResponseXML .= "Response:\n\n";
        $ResponseXML .= $dom->saveXML();
        /*print response log*/
        $logger = $this->_zendlogger;
        $logger->addWriter($this->_soaplog);
        $logger->info("$RequestXML");
    }
    /**
     * @param type $array
     * @param type $xml_user_info
     */
    public function array_to_xml($array, &$xml_user_info)
    {
        unset($array['address']);
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (!is_numeric($key)) {
                    $subnode = $xml_user_info->addChild("$key");
                    $this->array_to_xml($value, $subnode);
                } else {
                    $subnode = $xml_user_info->addChild("item$key");
                    $this->array_to_xml($value, $subnode);
                }
            } else {
                if ($key == 'accountNumber') {      // magedelight 672015 for security reasion we cant log sensitive inbfromation so we have put  XXXX
                    $value = substr($value, -4, 4);
                    $value = 'XXXX-'.$value;
                }
                if ($key == 'expirationMonth') {
                    $value = 'XX';
                }
                if ($key == 'expirationYear') {
                    $value = 'XXXX';
                }
                if ($key == 'cvNumber') {
                    $value = 'XX';
                }
                if ($key == 'cardType') {
                    $value = 'XX';
                }
                if ($key == 'getewayId') {
                    $value = 'XXXX';
                }
                $xml_user_info->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }
    /**
     * converts a hash of name-value pairs
     * to the correct array for new API
     * change on 19-feb-15.
     *
     * @param array $pdata
     *
     * @return string $xml
     */
    protected function _buildRequest($req)
    {
        $request = array(
            'User_Name' => '',
            'Secure_AuthResult' => '',
            'Ecommerce_Flag' => '',
            'XID' => isset($req['oid']) ? $req['oid'] : false,
            'ExactID' => $req['gatewayId'],
            'CAVV' => '',
            'Password' => $req['gatewayPass'],
            'CAVV_Algorithm' => '',
            'Transaction_Type' => $req['trans_type'],
            'Reference_No' => '',
            'Customer_Ref' => '',
            'Reference_3' => '',
            'Client_IP' => isset($req['ip']) ? $req['ip'] : false,
            'Client_Email' => isset($req['email']) ? $req['email'] : '',
            'Language' => 'en',
            'Expiry_Date' => isset($req['cardexpmonth']) ? sprintf('%02d', $req['cardexpmonth']).$req['cardexpyear'] : '',
            'CardHoldersName' => isset($req['ccname']) ? $req['ccname'] : '',
            'Track1' => '',
            'Track2' => '',
            'Authorization_Num' => isset($req['authorization_num']) ? $req['authorization_num'] : false,
            'Transaction_Tag' => isset($req['transaction_tag']) ? $req['transaction_tag'] : false,
            'DollarAmount' => $req['chargetotal'],
            'VerificationStr1' => '',
            'VerificationStr2' => isset($req['cvv2value']) ? $req['cvv2value'] : '',
            'CVD_Presence_Ind' => isset($req['cvv2indicator']) ? $req['cvv2indicator'] : '',
            'Secure_AuthRequired' => '',
            'Currency' => '',
            'PartialRedemption' => '',
            'ZipCode' => isset($req['zip']) ? $req['zip'] : '',
            'Tax1Amount' => '',
            'Tax1Number' => '',
            'Tax2Amount' => '',
            'Tax2Number' => '',
            'SurchargeAmount' => '',
            'PAN' => '',
        );

        if (isset($req['TransarmorToken'])) {
            $request['TransarmorToken'] = $req['TransarmorToken'];
            $request['CardType'] = $req['cardtype'];
        } else {
            $request['Card_Number'] = isset($req['cardnumber']) ? $req['cardnumber'] : '';
        }

        return $request;
    }
    /**
     * converts the LSGS response xml string
     * to a hash of name-value pairs.
     *
     * @param string $xml
     *
     * @return array $retarr
     */
    protected function _readResponse($trxnResult)
    {
        foreach ($trxnResult as $key => $value) {
            $value = nl2br($value);
            $retarr[$key] = $value;
        }

        return $retarr;
    }
}
