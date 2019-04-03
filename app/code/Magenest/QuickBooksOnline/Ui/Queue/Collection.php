<?php
namespace Magenest\QuickBooksOnline\Ui\Queue;

use Magento\Framework\App\RequestInterface;
use Magenest\QuickBooksOnline\Model\ResourceModel\Queue\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Class Collection
 *
 * @package Magenest\QuickBooksOnline\Ui\Queue
 */
class Collection extends AbstractDataProvider
{
    /**
     * @var RequestInterface
     */
    protected $request;
    protected $queueFactory;

    /**
     * Collection constructor.
     *
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
        CollectionFactory $queueFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $queueFactory->create();
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
