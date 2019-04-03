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

namespace Magedelight\Firstdata\Controller\Cards;

use Magento\Framework\App\RequestInterface;

class Delete extends \Magedelight\Firstdata\Controller\Firstdata
{
    protected function _getSession()
    {
        return $this->_customerSession;
    }

    public function dispatch(RequestInterface $request)
    {
        if (!$this->_getSession()->authenticate()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }

        return parent::dispatch($request);
    }
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $deleteCardId = $this->getRequest()->getPostValue('card_id');
        if (!empty($deleteCardId)) {
            $cardModel = $this->_cardFactory->create()->load($deleteCardId);
            $transarmorId = $cardModel->getData('firstdata_transarmor_id');
            $customerId = $this->_customerSession->getCustomerId();
            if ($transarmorId && $customerId) {
                try {
                    $cardModel->delete();
                    $this->messageManager->addSuccess(__('Card deleted successfully.'));
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());

                    return $resultRedirect->setPath('*/*/listing');
                }
            } else {
                $this->messageManager->addError('Card does not exists.');

                return $resultRedirect->setPath('*/*/listing');
            }

            return $resultRedirect->setPath('*/*/listing');
        } else {
            $this->messageManager->addError('Unable to find card to delete.');

            return $resultRedirect->setPath('*/*/listing');
        }
    }
}
