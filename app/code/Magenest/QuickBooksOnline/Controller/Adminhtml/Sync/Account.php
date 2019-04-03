<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Controller\Adminhtml\Sync;

use Magenest\QuickBooksOnline\Model\Synchronization\Account as SyncAccount;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Account
 * @package Magenest\QuickBooksOnline\Controller\Adminhtml\Sync
 */
class Account extends \Magento\Backend\App\Action
{
    /**
     * @var SyncAccount
     */
    protected $syncAccount;

    /**
     * Account constructor.
     * @param Context $context
     * @param SyncAccount $syncAccount
     */
    public function __construct(
        Context $context,
        SyncAccount $syncAccount
    ) {
        $this->syncAccount = $syncAccount;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        try {
            $this->syncAccount->syncAllAccount();

            $this->messageManager->addSuccessMessage(
                __('Accounts are updated successfully')
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something happen during syncing process. Detail: ' . $e->getMessage())
            );
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        return $resultRedirect;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_QuickBooksOnline::config_qbonline');
    }
}
