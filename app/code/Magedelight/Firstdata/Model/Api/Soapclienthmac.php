<?php

namespace Magedelight\Firstdata\Model\Api;

class Soapclienthmac extends \SoapClient
{
    public function __construct(
        $configModel
    ) {
        $this->_configModel = $configModel;
        $options = array();
        $propertiesWsdl = $this->getConfigModel()->getGatewayUrl();
        global $context;
        $context = stream_context_create();
        $options['stream_context'] = $context;

        return parent::SoapClient($propertiesWsdl, $options);
    }

    public function __doRequest($request, $location, $action, $version, $one_way = null)
    {
        global $context;
        $hmacKey = $this->getConfigModel()->getHmacKey();
        $keyId = $this->getConfigModel()->getKeyId();
        $hashtime = date('c');
        $hashstr = "POST\ntext/xml; charset=utf-8\n".sha1($request)."\n".$hashtime."\n".parse_url($location, PHP_URL_PATH);
        $authstr = base64_encode(hash_hmac('sha1', $hashstr, $hmacKey, true));
        if (version_compare(PHP_VERSION, '5.3.11') == -1) {
            ini_set('user_agent', 'PHP-SOAP/'.PHP_VERSION."\r\nAuthorization: GGE4_API ".$keyId.':'.$authstr."\r\nx-gge4-date: ".$hashtime."\r\nx-gge4-content-sha1: ".sha1($request));
        } else {
            stream_context_set_option($context, array('http' => array('header' => 'authorization: GGE4_API '.$keyId.':'.$authstr."\r\nx-gge4-date: ".$hashtime."\r\nx-gge4-content-sha1: ".sha1($request))));
        }

        return parent::__doRequest($request, $location, $action, $version, $one_way);
    }
    public function getConfigModel()
    {
        return $this->_configModel;
    }
}
