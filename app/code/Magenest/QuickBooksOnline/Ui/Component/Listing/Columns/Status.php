<?php
namespace Magenest\QuickBooksOnline\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;
use Magenest\QuickBooksOnline\Model\Queue\Status as QueueStatus;

/**
 * Class Status
 * @package Magenest\QuickBooksOnline\Ui\Component\Listing\Columns
 */
class Status extends Column
{

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if ($item['status'] && $item['status'] == QueueStatus::STATUS_ENQUEUE) {
                    $class = 'notice';
                    $label = 'Success';
                } elseif ($item['status'] && $item['status'] == QueueStatus::STATUS_DEQUEUE) {
                    $class = 'critical';
                    $label = 'Failed';
                } else {
                    $class = 'critical';
                    $label = 'Failed';
                }
                $item['status'] = '<span class="grid-severity-'
                    . $class .'">'. $label .'</span>';
            }
        }
        return $dataSource;
    }
}
