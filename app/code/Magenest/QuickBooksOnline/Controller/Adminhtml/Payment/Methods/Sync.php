<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Controller\Adminhtml\Payment\Methods;

use Magenest\QuickBooksOnline\Controller\Adminhtml\Payment\AbstractPaymentMethods;

/**
 * Class Sync
 *
 * @package Magenest\QuickBooksOnline\Controller\Adminhtml\Payment\Methods
 */
class Sync extends AbstractPaymentMethods
{
    /**
     * execute the action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $config = $this->_objectManager->create('Magenest\QuickBooksOnline\Model\Config');
        $connect = $config->getConnected();
        $totals = 0;
        if ($connect && $connect == 1) {
            $paymentMethodsList = $this->_scopeConfig->getValue('payment');
            foreach ($paymentMethodsList as $code => $data) {
                if (isset($data['active']) && isset($data['title'])) {
                    $title = $data['title'];
                    if (strlen($title) > 31) {
                        $title = substr($title, 0, 31);
                        $this->messageManager->addNoticeMessage(
                            __(
                                sprintf(
                                    'Payment Methods \'%s\' renamed to \'%s\' when synced to QuickBooks Online',
                                    $data['title'],
                                    $title
                                )
                            )
                        );
                    }
                    try {
                        $this->paymentMethods->sync($title, $code);
                        $totals++;
                    } catch (\Exception $e) {
                        $this->messageManager->addErrorMessage($e->getMessage());
                    }
                }
            }
        } else {
            $this->messageManager->addErrorMessage(__('Not connect to QuickBooks Online'));
        }

        $this->messageManager->addSuccessMessage(
            __(
                sprintf('Totals %s Payment Methods have been sync/update to QuickBooksOnline.', $totals)
            )
        );

        return $this->_redirect('*/*/index');
    }
}
