<?php
namespace BroSolutions\ShippingAdditional\Controller\Adminhtml\Order\Create;
class Save extends \Magento\Sales\Controller\Adminhtml\Order\Create\Save
{
    protected $_dataProvider;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \BroSolutions\ShippingAdditional\Model\ShippingAdditionalBlockConfigProvider $dataProvider
    ) {
        $this->_dataProvider = $dataProvider;
        parent::__construct($context, $productHelper, $escaper, $resultPageFactory, $resultForwardFactory);

    }
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            // check if the creation of a new customer is allowed
            if (!$this->_authorization->isAllowed('Magento_Customer::manage')
                && !$this->_getSession()->getCustomerId()
                && !$this->_getSession()->getQuote()->getCustomerIsGuest()
            ) {
                return $this->resultForwardFactory->create()->forward('denied');
            }
            $this->_getOrderCreateModel()->getQuote()->setCustomerId($this->_getSession()->getCustomerId());
            $this->_processActionData('save');
            $paymentData = $this->getRequest()->getPost('payment');
            if ($paymentData) {
                $paymentData['checks'] = [
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_INTERNAL,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_COUNTRY,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_CURRENCY,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_ZERO_TOTAL,
                ];
                $this->_getOrderCreateModel()->setPaymentData($paymentData);
                $this->_getOrderCreateModel()->getQuote()->getPayment()->addData($paymentData);
            }
            $shippingCarrier = $this->getRequest()->getParam('ship_carrier_ship_method', false);
            $shippingCarrierLabel = false;
            if($shippingCarrier){
                $shippingCarrierLabel = $this->_dataProvider->getShippingCarrierLabel($shippingCarrier);
            }
            $accountNumber = $this->getRequest()->getParam('account_number', false);
            $order = $this->_getOrderCreateModel()
                ->setIsValidate(true)
                ->importPostData($this->getRequest()->getPost('order'));
            $orderShippingMethod = $order->getShippingMethod();
            if($orderShippingMethod == 'shippingadditional_shippingadditional'){
                $order->createOrder($shippingCarrierLabel, $accountNumber);
            } else {
                $order->createOrder(false, false);
            }
            $this->_getSession()->clearStorage();
            $this->messageManager->addSuccess(__('You created the order.'));
            $resultRedirect->setPath('sales/order/index');
        } catch (PaymentException $e) {
            $this->_getOrderCreateModel()->saveQuote();
            $message = $e->getMessage();
            if (!empty($message)) {
                $this->messageManager->addError($message);
            }
            $resultRedirect->setPath('sales/*/');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            // customer can be created before place order flow is completed and should be stored in current session
            $this->_getSession()->setCustomerId($this->_getSession()->getQuote()->getCustomerId());
            $message = $e->getMessage();
            if (!empty($message)) {
                $this->messageManager->addError($message);
            }
            $resultRedirect->setPath('sales/*/');
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Order saving error: %1', $e->getMessage()));
            $resultRedirect->setPath('sales/*/');
        }
        return $resultRedirect;
    }

    protected function _getOrderCreateModel()
    {
        return $this->_objectManager->get(\BroSolutions\ShippingAdditional\Model\AdminOrder\Create::class);
    }
}
