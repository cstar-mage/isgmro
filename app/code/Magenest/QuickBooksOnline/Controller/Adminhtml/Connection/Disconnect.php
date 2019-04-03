<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Controller\Adminhtml\Connection;

use Magenest\QuickBooksOnline\Controller\Adminhtml\AbstractConnection;
use Magenest\QuickBooksOnline\Model\Config;
use Magento\Backend\App\Action\Context;
use Magento\Config\Model\Config as ConfigModel;
use Magento\Framework\View\Result\PageFactory;
use Magenest\QuickBooksOnline\Model\Authenticate;
use Magenest\QuickBooksOnline\Model\Client;
use Magenest\QuickBooksOnline\Model\OauthFactory;

/**
 * Class Disconnect
 * @package Magenest\QuickBooksOnline\Controller\Adminhtml\Connection
 */
class Disconnect extends AbstractConnection
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var ConfigModel
     */
    protected $config;

    /**
     * @var OauthFactory
     */
    protected $oauth;

    /**
     * Disconnect constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Authenticate $authenticate
     * @param Client $client
     * @param ConfigModel $config
     * @param OauthFactory $oauthFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Authenticate $authenticate,
        Client $client,
        ConfigModel $config,
        OauthFactory $oauthFactory
    ) {
        parent::__construct($context, $resultPageFactory, $authenticate);
        $this->client = $client;
        $this->config = $config;
        $this->oauth = $oauthFactory;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        try {
//            $this->client->disconnect();
            $company = (int)$this->config->getConfigDataValue(Config::XML_PATH_QBONLINE_COMPANY_ID);
            $model = $this->oauth->create()->load($company, 'qb_realm');
            $model->delete();
            $this->config->setDataByPath(Config::XML_PATH_QBONLINE_IS_CONNECTED, 0);
            $this->config->setDataByPath(Config::XML_PATH_QBONLINE_COMPANY_ID, null);
            $this->config->save();
            $this->refreshCache();
            $this->messageManager->addSuccessMessage(__('You\'re disconnected'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        $referUrl = $this->_redirect->getRefererUrl();
        $redirectPage = $this->resultRedirectFactory->create();

        return $redirectPage->setUrl($referUrl);
    }

    /**
     *
     */
    protected function refreshCache()
    {
        $_cacheTypeList = $this->_objectManager->create(\Magento\Framework\App\Cache\TypeListInterface::class);
        $_cacheFrontendPool = $this->_objectManager->create(\Magento\Framework\App\Cache\Frontend\Pool::class);
        $types = ['config','full_page'];
        foreach ($types as $type) {
            $_cacheTypeList->cleanType($type);
        }
        foreach ($_cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }
}
