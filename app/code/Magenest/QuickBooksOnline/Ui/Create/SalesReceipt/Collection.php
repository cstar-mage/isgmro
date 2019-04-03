<?php
namespace Magenest\QuickBooksOnline\Ui\Create\SalesReceipt;

use Magento\Framework\App\RequestInterface;
use Magenest\QuickBooksOnline\Model\ResourceModel\SalesReceipt\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Class Collection
 * @package Magenest\QuickBooksOnline\Ui\Create\SalesReceipt
 */
class Collection extends AbstractDataProvider
{
    protected $collection;

    /**
     * Collection constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param RequestInterface $request
     * @param CollectionFactory $queueFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        RequestInterface $request,
        CollectionFactory $saleFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $saleFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $items = [];
        foreach ($this->getCollection() as $attribute) {
            $items[] = $attribute->toArray();
        }

        return [
            'totalRecords' => $this->collection->getSize(),
            'items' => $items
        ];
    }
}
