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
namespace Magenest\QuickBooksOnline\Controller\Connection;

use \Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validator\Exception;
use Magenest\QuickBooksOnline\Model\Authenticate;

/**
 * Class Success
 *
 * @package Magenest\QuickBooksOnline\Controller\Adminhtml\Connection
 */
class Success extends Action
{
    /**
     * @var Authenticate
     */
    protected $authenticate;

    /**
     * Success constructor.
     * @param Context $context
     * @param Authenticate $authenticate
     */
    public function __construct(
        Context $context,
        Authenticate $authenticate
    ) {
        parent::__construct($context);
        $this->authenticate = $authenticate;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */

        try {
            $code = $this->getRequest()->getParam('code');
            $state = $this->getRequest()->getParam('state');

            if (strcmp($state, "RandomState") != 0) {
                throw new Exception("The state is not correct from Intuit Server. Consider your app is hacked.");
            }

            $this->authenticate->getAccessToken("authorization_code", $code);

            $this->messageManager->addSuccessMessage(__('You\'re connected!'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->_redirect('qbonline/connection/index');
    }
}
