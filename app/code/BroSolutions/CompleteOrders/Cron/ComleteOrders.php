<?php
namespace BroSolutions\CompleteOrders\Cron;
use \Psr\Log\LoggerInterface;
class ComleteOrders
{
    protected $_logger;
    protected $_resourceConnection;

    public function __construct(LoggerInterface $logger, \Magento\Framework\App\ResourceConnection $resourceConnection)
    {
        $this->_logger = $logger;
        $this->_resourceConnection = $resourceConnection;
    }


    public function execute()
    {
        $connection = $this->_resourceConnection->getConnection();
        $ordersTableName = $this->_resourceConnection->getTableName('sales_order');
        $ordersGridTableName = $this->_resourceConnection->getTableName('sales_order_grid');
        $sqlOrders = "UPDATE " . $ordersTableName . " SET status='complete' where status IN ('pending', 'processing')";
        $connection->query($sqlOrders);
        $sqlOrdersGrid = "UPDATE " . $ordersGridTableName . " SET status='complete' where status IN ('pending', 'processing')";
        $connection->query($sqlOrdersGrid);
    }
}