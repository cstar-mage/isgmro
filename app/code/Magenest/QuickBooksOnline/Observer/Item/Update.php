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
namespace Magenest\QuickBooksOnline\Observer\Item;

use Magenest\QuickBooksOnline\Observer\AbstractObserver;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magenest\QuickBooksOnline\Model\Synchronization\Item;
use Magenest\QuickBooksOnline\Model\Config;
use Magenest\QuickBooksOnline\Model\QueueFactory;

/**
 * Class Update
 */
class Update extends AbstractObserver implements ObserverInterface
{
    
    /**
     * @var Item
     */
    protected $_item;

    /**
     * Update constructor.
     *
     * @param ManagerInterface $messageManager
     * @param Config $config
     * @param QueueFactory $queueFactory
     * @param Item $item
     */
    public function __construct(
        ManagerInterface $messageManager,
        Config $config,
        QueueFactory $queueFactory,
        Item $item
    ) {
        parent::__construct($messageManager, $config, $queueFactory);
        $this->_item = $item;
        $this->type = 'item';
    }

    /**
     * Admin save a Product
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->isConnected() && $this->isConnected() == 1) {
            try {
                /** @var \Magento\Catalog\Model\Product $product */
                $product = $observer->getEvent()->getProduct();
                $id = $product->getId();
                if ($id && $this->isEnabled()) {
                    if ($this->isImmediatelyMode()) {
                        $qboId = $this->_item->sync($id, true);
                        $this->messageManager->addSuccessMessage(
                            __('Updated success this product(Id: %1) in QuickBooksOnline.', $qboId)
                        );
                    } else {
                        $this->addToQueue($id);
                    }
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
    }
}
