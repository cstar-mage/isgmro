<?php
namespace Magenest\QuickBooksOnline\Ui\Create\Customer;

use Magento\Framework\App\RequestInterface;
use Magenest\QuickBooksOnline\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Class Collection
 * @package Magenest\QuickBooksOnline\Ui\Create\Customer
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
        CollectionFactory $customerFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $customerFactory->create();
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
