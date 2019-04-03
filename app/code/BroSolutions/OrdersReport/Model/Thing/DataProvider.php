<?php

namespace BroSolutions\OrdersReport\Model\Thing;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Reporting;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Reporting $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
        $searchCriteriaBuilder,
        $request,
        $filterBuilder,
        $meta = [],
        $data = []);
    }

    public function getData()
    {
        return array('config' => array('urls' => array('save' => 'brosolutionsordersreport/reportform/index')));
    }
}
