<?php
namespace BroSolutions\ShippingAdditional\Model\AdminOrder;
class Create extends \Magento\Sales\Model\AdminOrder\Create
{
    public function createOrder($shippingCarrierLabel = false, $accountNumber = false)
    {
        $this->_prepareCustomer();
        $this->_validate();
        $quote = $this->getQuote();
        $this->_prepareQuoteItems();


        $orderData = [];
        if ($this->getSession()->getOrder()->getId()) {
            $oldOrder = $this->getSession()->getOrder();
            $originalId = $oldOrder->getOriginalIncrementId();
            if (!$originalId) {
                $originalId = $oldOrder->getIncrementId();
            }
            $orderData = [
                'original_increment_id' => $originalId,
                'relation_parent_id' => $oldOrder->getId(),
                'relation_parent_real_id' => $oldOrder->getIncrementId(),
                'edit_increment' => $oldOrder->getEditIncrement() + 1,
                'increment_id' => $originalId . '-' . ($oldOrder->getEditIncrement() + 1)
            ];
            $quote->setReservedOrderId($orderData['increment_id']);
        }

        if($shippingCarrierLabel){
            $quote->setShippingCarrier($shippingCarrierLabel);
        }
        if($accountNumber){
            $quote->setAccountNumber($accountNumber);
        }
        $order = $this->quoteManagement->submit($quote, $orderData);

        if ($this->getSession()->getOrder()->getId()) {
            $oldOrder = $this->getSession()->getOrder();
            $oldOrder->setRelationChildId($order->getId());
            $oldOrder->setRelationChildRealId($order->getIncrementId());
            $this->orderManagement->cancel($oldOrder->getEntityId());
            $order->save();
        }
        if ($this->getSendConfirmation()) {
            $this->emailSender->send($order);
        }

        $this->_eventManager->dispatch('checkout_submit_all_after', ['order' => $order, 'quote' => $quote]);

        return $order;
    }
}