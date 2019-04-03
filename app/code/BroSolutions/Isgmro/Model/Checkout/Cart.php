<?php
namespace BroSolutions\Isgmro\Model\Checkout;
class Cart extends \Magento\Checkout\Model\Cart
{
    public function addOrderItem($orderItem, $qtyFlag = null)
    {
        if ($orderItem->getParentItem() === null) {
            $storeId = $this->_storeManager->getStore()->getId();
            try {
                $product = $this->productRepository->getById($orderItem->getProductId(), false, $storeId, true);
            } catch (NoSuchEntityException $e) {
                return $this;
            }
            $info = $orderItem->getProductOptionByCode('info_buyRequest');
            $qty = 0;
            if ($qtyFlag === null) {
                $qty = $orderItem->getQtyOrdered();
            } else {
                $qty = 1;
            }
            if($info === NULL){
                $info = array();
            }
            $info = new \Magento\Framework\DataObject($info);
            $info->setQty($qty);

            $this->addProduct($product, $info);
        }
        return $this;
    }
}