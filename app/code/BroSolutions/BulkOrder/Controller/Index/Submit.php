<?php
namespace BroSolutions\BulkOrder\Controller\Index;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Webapi\Exception;

class Submit extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
    protected $_cart;
    protected $_productRepository;
    protected $_messageManager;
    protected $_productModel;
    protected $_stockInterface;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory, Cart $cart, \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Model\Product $productModel, \Magento\CatalogInventory\Api\StockRegistryInterface $stockInterface)
    {
        $this->_pageFactory = $pageFactory;
        $this->_cart = $cart;
        $this->_productRepository = $productRepository;
        $this->_messageManager = $context->getMessageManager();
        $this->_productModel = $productModel;
        $this->_stockInterface = $stockInterface;
        return parent::__construct($context);
    }

    public function execute()
    {

        $productWasAdded = false;
        $requestParams = $this->getRequest()->getParams();
        $errorMessage = '';
        if(isset($requestParams['sku']) && isset($requestParams['qty'])){
            foreach($requestParams['sku'] as $key => $sku){
                if(!empty($sku) && isset($requestParams['qty'][$key]) && $requestParams['qty'][$key] > 0){
                    $qty = $requestParams['qty'][$key];
                    $product = $this->_getProductBySku($sku);
                    if($product){
                        if($this->_checkProductStatus($product)){
                            if($this->_checkProductStock($product, $qty)){
                                try {
                                    $this->_cart->addProduct($product, array('product' => $product->getId(), 'qty' => $qty));
                                } catch (Exception $e){
                                    $errorMessage .= __($e->getMessage());
                                }
                                $productWasAdded = true;
                            } else {
                                $errorMessage .= __(sprintf('We don\'t have as many "%s" as you requested. ', $sku));
                            }
                        } else {
                            $errorMessage .= __(sprintf('Product "%s" that you are trying to add is not available. ', $sku));
                        }
                    } else {
                        $errorMessage .= __(sprintf('Product with SKU %s was not found. ', $sku));
                    }
                }
            }
        }
        try {
            $this->_cart->save();
        } catch (Exception $e){

        }
        if(!empty($errorMessage)){
            $this->_messageManager->addError($errorMessage);
        }
        if($productWasAdded){
            $this->_redirect('checkout/cart/index');
        } else {
            $this->_redirect('bulkorder/index/index');
        }
        return;
    }

    protected function _getProductBySku($sku)
    {
        $productId = $this->_productModel->getIdBySku($sku);
        if($productId){
            $product = $this->_productRepository->get($sku);
            if($product->getId() && $product->getTypeId() == 'simple'){
                return $product;
            }
        }
        return false;
    }

    public function _checkProductStock($product, $qty)
    {
        $productStatus = $product->getStatus();
        if($productStatus == 1){
            if($product){
                $stockItem = $this->_stockInterface->getStockItem($product->getId());
                $isInStock = $stockItem->getIsInStock();
                if($isInStock == 1){
                    $stockQty = $stockItem->getQty();
                    if($qty <= $stockQty){
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function _checkProductStatus($product)
    {
        $productStatus = $product->getStatus();
        if($productStatus == 1){
            return true;
        }
        return false;
    }
}
