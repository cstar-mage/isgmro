<?php
namespace BroSolutions\OrdersReport\Helper;

use Magento\Framework\Exception\NoSuchEntityException;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_orderItemsCollection;
    protected $_fileFactory;
    protected $_directoryList;
    protected $_csvWriter;
    protected $_headerColumns;
    protected $_productModel;
    protected $_orderModel;
    protected $_countryFactory;
    protected $_connection;
    protected $_productReposithory;
    public function __construct(\Magento\Framework\App\Helper\Context $context, \Magento\Sales\Model\ResourceModel\Order\Item\Collection $orderItemsCollection,
                                \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
                                \Magento\Framework\File\Csv $csvFile, \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
                                \Magento\Catalog\Model\Product $productModel, \Magento\Sales\Model\Order $orderModel,
                                \Magento\Directory\Model\CountryFactory $countryFactory,
                                \Magento\Framework\App\ResourceConnection $resourceConnection,
                                \Magento\Catalog\Model\ProductRepository $productRepository)
    {
        $this->_orderItemsCollection = $orderItemsCollection;
        $this->_fileFactory = $fileFactory;
        $this->_csvWriter = $csvFile;
        $this->_directoryList = $directoryList;
        $this->_productModel = $productModel;
        $this->_orderModel = $orderModel;
        $this->_countryFactory = $countryFactory;
        $this->_connection = $resourceConnection;
        $this->_context = $context;
        $this->_productReposithory = $productRepository;
        $this->_headerColumns = array('Order ID', 'Date Added', 'Customer Name', 'Customer Email', 'Customer Group', 'SKU', 'Model', 'Product Name',
            'Category', 'List Price', 'List Total', 'Price', 'Quantity', 'Total excl. Tax', 'Tax', 'Total incl. Tax', 'Qty. Refunded',
            'Refunded', 'Reward Points', 'Sub-Total', 'Shipping', 'Rewards', 'Reward Points (earned)', 'Reward Points (used)', 'Coupons',
            'Coupon Name [Code]', 'Taxes', 'Store Credits', 'Order Total', 'Refunds', 'Shipping Method',
            'Payment Method', 'Purchase order number', 'Status', 'Billing First Name', 'Billing Last Name', 'Billing Company', 'Billing Address',
            'Billing City', 'Billing Zone', 'Billing Zone Code', 'Billing Postcode', 'Billing Country', 'Telephone',
            'Shipping First Name', 'Shipping Last Name', 'Shipping Company', 'Shipping Address', 'Shipping City',
            'Shipping Zone Code', 'Shipping Postcode', 'Shipping Country', 'Order Weight', 'Order Comment');
        $this->urlBuilder = $context->getUrlBuilder();

        parent::__construct($context);
    }

    public function getOrdersCollectionCsv($email, $dateFrom, $dateTo)
    {
        $dateFromFormatted = \DateTime::createFromFormat('m/d/Y', $dateFrom);
        $dateToFormatted = \DateTime::createFromFormat('m/d/Y', $dateTo);

        $this->_orderItemsCollection->getSelect()->joinLeft(
                array('so' => 'sales_order'),
                'main_table.order_id = so.entity_id',
                array('order_created_at' => 'so.created_at', 'order_firstname' => 'so.customer_firstname', 'order_lastname' => 'so.customer_lastname',
                    'order_email' => 'so.customer_email', 'order_currency' => 'so.base_currency_code',
                    'order_base_subtotal' => 'so.base_subtotal', 'order_base_tax_amount' => 'so.base_tax_amount',
                    'order_grand_total' => 'so.grand_total', 'order_subtotal' => 'so.subtotal',
                    'order_shipping_amount' => 'so.shipping_amount', 'order_base_discount_amount' => 'so.base_discount_amount',
                    'order_coupon_code' => 'so.coupon_code', 'order_base_total_refunded' => 'so.base_total_refunded',
                    'order_shipping_description' => 'so.shipping_description', 'order_status' => 'so.status', 'order_customer_id' => 'so.customer_id',
                    'order_increment_id' => 'so.increment_id', 'order_id' => 'so.entity_id')
            )->joinLeft(
            array('si' => 'sales_invoice'),
                'si.entity_id = so.entity_id',
                array('invoice_id' => 'si.entity_id')
            )->where('so.customer_email = \''.$email.'\'')
            ->joinLeft(
            array('cg' => 'customer_group'),
            'so.customer_group_id = cg.customer_group_id',
            array('order_customer_group' => 'cg.customer_group_code')
        )->joinLeft(
                array('st' => 'store'),
                'so.store_id = st.store_id',
                array('store_name' => 'st.name')
            )
            ->joinLeft(
                array('ba' => 'sales_order_address'),
                'so.entity_id = ba.parent_id AND ba.address_type = \'billing\'',
                array('order_billing_firstname' => 'ba.firstname', 'order_billing_lastname' => 'ba.lastname',
                    'order_billing_street' => 'ba.street',
                    'order_billing_company' => 'ba.company', 'order_billing_postcode' => 'ba.postcode', 'order_billing_street' => 'ba.street',
                    'order_billing_city' => 'ba.city', 'order_billing_region_id' => 'ba.region_id', 'order_billing_region' => 'ba.region',
                    'billing_order_telephone' => 'ba.telephone', 'billing_order_country' => 'ba.country_id', 'order_weight' => 'so.weight', 'order_comment' => 'so.comment_code')
            )->joinLeft(
                array('sa' => 'sales_order_address'),
                'so.entity_id = sa.parent_id AND sa.address_type = \'shipping\'',
                array('order_shipping_firstname' => 'sa.firstname', 'order_shipping_lastname' => 'sa.lastname',
                    'order_shipping_company' => 'sa.company', 'order_shipping_postcode' => 'sa.postcode', 'order_shipping_street' => 'sa.street',
                    'order_shipping_city' => 'sa.city', 'order_shipping_region_id' => 'sa.region_id', 'order_shipping_region' => 'sa.region',
                    'shipping_order_telephone' => 'sa.telephone', 'shipping_order_country' => 'sa.country_id')
            )->joinLeft(
                array('dcr' => 'directory_country_region'),
                'ba.region_id = dcr.region_id',
                array('billing_order_region_id' => 'dcr.region_id', 'billing_order_region_code' => 'dcr.code', 'billing_order_region_name' => 'dcr.default_name')
            )->joinLeft(
                array('dcrs' => 'directory_country_region'),
                'sa.region_id = dcrs.region_id',
                array('shipping_order_region_id' => 'dcrs.region_id', 'shipping_order_region_code' => 'dcrs.code', 'shipping_order_region_name' => 'dcrs.default_name')
            )->joinLeft(
                array('sop' => 'sales_order_payment'),
                'main_table.order_id = sop.parent_id',
                array('po_number' => 'sop.po_number')
            );
        $this->_orderItemsCollection->addFieldToFilter('so.created_at', ['gteq' => $dateFromFormatted->format('Y-m-d') . ' 00:00:00'])
            ->addFieldToFilter('so.created_at', ['lteq' => $dateToFormatted->format('Y-m-d') . ' 23:59:59']);
        $this->_orderItemsCollection->getSelect()->group('main_table.item_id');
            //print_r($this->_orderItemsCollection->getSelect()->__toString()); die;
        return $this->getCsvFile();

    }


    public function getCsvFile()
    {
          $data = [
            $this->_headerColumns,
        ];
        $previousOrderIncrementId = false;
        foreach($this->_orderItemsCollection as $item){
            //var_dump($item->getData()); die;
            $itemSku = $item->getSku();
            $rowData = array();
            //order id
            $orderIncrementId = $item->getOrderIncrementId();
            $rowData[] = $orderIncrementId;
            //created at
            $rowData[] = $item->getCreatedAt();
            //customer firstname
            $rowData[] = $item->getOrderFirstname(). ' '. $item->getOrderLastname();
            //customer email
            $rowData[] = $item->getOrderEmail();
            //customer group
            $rowData[] = $item->getOrderCustomerGroup();
            try {
                $product = $this->_productReposithory->get($itemSku);
            } catch (NoSuchEntityException $e){
                $product = false;
            }
            //$product = $this->_productModel->load($item->getProductId());
            //var_dump($product->getData()); die;
            if($product){
                //sku
                $rowData[] = $product->getSku();
                //model
                $rowData[] = $product->getModel();
                //product name
                $rowData[] = $product->getName();
                //product categories
                $rowData[] = $this->getProductCategories($product);
            } else {
                $rowData[] = $item->getSku();
                //model
                $rowData[] = '';
                //product name
                $rowData[] = $item->getName();
                //product categories
                $rowData[] = '';
            }
            //list price
            $itemPrice = $item->getPrice();
            $qtyOrdered = $item->getQtyOrdered();
            $productPrice = 0;
            if($product){
                $productPrice = $product->getPrice();
                $rowData[] = round($productPrice, 2);
            } else {
                $rowData[] = '';
            }
            //list total
            $totalExclTax = $itemPrice * $qtyOrdered;
            $listTotal = $productPrice * $qtyOrdered;
            $rowData[] = round($listTotal, 2);
            //price
            $rowData[] = round($itemPrice, 2);

            //quantity
            $rowData[] = $qtyOrdered;
            //total excl tax
            $rowData[] = round($totalExclTax, 2);
            //tax
            $rowData[] = round($item->getOrderBaseTaxAmount(), 2);
            //total incl tax
            $rowData[] = round($item->getOrderGrandTotal(), 2);
            //qty refunded
            $rowData[] = $item->getQtyRefunded();
            //refunded
            $rowData[] = $item->getAmountRefunded();
            //reward points
            $rowData[] = '';
            //subtotal
            $rowData[] = round($item->getOrderSubtotal(), 2);
            //shipping amount
            $rowData[] = $item->getOrderShippingAmount();
            //rewards
            $rowData[] = '';
            //reward points earned
            $rowData[] = '';
            //reward points used
            $rowData[] = '';
            //coupons
            $rowData[] = $item->getOrderBaseDiscountAmount();
            //coupon code
            $rowData[] = $item->getOrderCouponCode();
            //taxes
            $rowData[] = $item->getOrderBaseTaxAmount();
            //store credits
            $rowData[] = '';
            //order total
            $rowData[] = round($item->getOrderGrandTotal(), 2);
            //refunds
            $rowData[] = $item->getOrderBaseTotalRefunded();
            //shipping method
            $rowData[] = $item->getOrderShippingDescription();
            //payment method
            $rowData[] = $this->getPaymentMethodTitle($item->getOrderId());

            //Purchase order number
            $rowData[] = $item->getPoNumber();
            //status
            $rowData[] = $item->getOrderStatus();
            //order billing firstname
            $rowData[] = $item->getOrderBillingFirstname();
            //order billing lastname
            $rowData[] = $item->getOrderBillingLastname();
            //billing company
            $rowData[] = $item->getOrderBillingCompany();
            //billing address 1 = billing address 2
            $addressStr = $item->getOrderBillingStreet();
            $rowData[] = $addressStr;
            //billing address 1 = billing address 2
            // billing city
            $rowData[] = $item->getOrderBillingCity();
            //region name
            $rowData[] = $item->getBillingOrderRegionName();
            // billing zone (region)
            $rowData[] = $item->getBillingOrderRegionCode();
            //billing postcode
            $rowData[] = $item->getOrderBillingPostcode();
            $country = $this->_countryFactory->create()->loadByCode($item->getBillingOrderCountry());

            if($country){
                $countryName = $country->getName();
                if(is_string($countryName)){
                    $rowData[] = $countryName;
                }
            } else {
                $rowData[] = '';
            }
            //billing country code$this->_countryFactory


            $rowData[] = $item->getBillingOrderTelephone();

            //order shipping firstname
            $rowData[] = $item->getOrderShippingFirstname();
            //order shipping lastname
            $rowData[] = $item->getOrderShippingLastname();
            //shipping company
            $rowData[] = $item->getOrderShippingCompany();
            //shipping address 1 = shipping address 2
            $addressStr = $item->getOrderShippingStreet();
            $rowData[] = $addressStr;
            // shipping city
            $rowData[] = $item->getOrderShippingCity();
            //region name
            $rowData[] = $item->getShippingOrderRegionName();
            //shipping postcode
            $rowData[] = $item->getOrderShippingPostcode();

            //shipping country name
            $rowData[] = $item->getShippingOrderCountry();

            //order weight
            $rowData[] = $item->getOrderWeight();
            //order_comment
            $defaultOrderComments = $this->getOrderComments($item->getOrderId());
            if($previousOrderIncrementId != $orderIncrementId){
                $rowData[] = $defaultOrderComments;
            } else {
                $rowData[] = '';
            }
            $data[] = $rowData;
            $previousOrderIncrementId = $orderIncrementId;

        }
        $fileDirectory = \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR;

        $fileName = $this->getFileName();
        $filePath =  $this->_directoryList->getPath($fileDirectory) . "/" . $fileName;

        $this->_csvWriter
            ->setEnclosure('"')
            ->setDelimiter(',')
            ->saveData($filePath ,$data);
        return $fileName;
        
        /*$resultRaw = $this->resultRawFactory->create();
        return $resultRaw;*/
    }

    public function getProductCategories($product)
    {
        $categoriesArr = array();
        $categories = $product->getCategoryCollection()->addAttributeToSelect('name');
        if($categories->count()){
            foreach($categories as $category){
                $categoriesArr[] = $category->getName();
            }
        }
        return implode(', ', $categoriesArr);
    }

    public function getFileName()
    {
        $fileName = 'sales_report_';
        $now = new \DateTime();
        $fileName .= $now->format('Y-m-d_His');
        return $fileName.'.csv';
    }

    public function getOrder($orderId)
    {
        $order = $this->_orderModel->load($orderId);
        return $order;
    }

    public function getPaymentMethodTitle($orderId)
    {
        $paymentTitle = '';
        $order = $this->getOrder($orderId);
        if($order && $order->getId()){
            $payment = $order->getPayment();
            $method = $payment->getMethodInstance();
            $paymentTitle = $method->getTitle();
        }
        return $paymentTitle;
    }

    public function getUrl($route = '', $params = [])
    {
        return $this->urlBuilder->getUrl($route, $params);
    }

    public function getOrderComments($orderId)
    {
        $orderComments = '';
        $connection = $this->_connection->getConnection();
        $tableName = $this->_connection->getTableName('sales_order_status_history');
        $query = 'SELECT comment FROM '.$tableName.' WHERE parent_id = '.$orderId.' AND entity_name = \'order\'';
        $result = $connection->fetchAssoc($query);
        if(!empty($result)){
            $comments = array_keys($result);
            $orderComments = implode(', ', $comments);
        }
        return $orderComments;
    }
}
