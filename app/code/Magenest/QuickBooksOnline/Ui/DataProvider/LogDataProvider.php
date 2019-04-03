<?php
namespace Magenest\QuickBooksOnline\Ui\DataProvider;

use Magento\Framework\App\RequestInterface;
use Magenest\QuickBooksOnline\Model\ResourceModel\Log\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Class LogDataProvider
 *
 * @package Magenest\QuickBooksOnline\Ui\DataProvider
 */
class LogDataProvider extends AbstractDataProvider
{
    
    /**
     * LogDataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param RequestInterface $request
     * @param CollectionFactory $collectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        RequestInterface $request,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $items = [];
        /** @var \Magento\Framework\Model\AbstractModel $attribute */
        foreach ($this->getCollection() as $attribute) {
            $items[] = $attribute->toArray();
        }

        return [
            'totalRecords' => $this->collection->getSize(),
            'items' => $items
        ];
    }
}
