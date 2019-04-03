<?php
namespace BroSolutions\BulkOrder\Block;
class Index extends \Magento\Framework\View\Element\Template
{
    const ROWS_COUNT = 15;
    const COLS_COUNT = 3;

    public function getRowsCount()
    {
        return self::ROWS_COUNT;
    }

    public function getColsCount()
    {
        return self::COLS_COUNT;
    }

    public function getFormAction()
    {
        return $this->getUrl('bulkorder/index/submit');
    }
}