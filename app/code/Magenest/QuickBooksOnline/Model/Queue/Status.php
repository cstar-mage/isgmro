<?php
namespace Magenest\QuickBooksOnline\Model\Queue;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Status
 * @package Magenest\QuickBooksOnline\Model\Queue
 */
class Status implements ArrayInterface
{

    /**@#+
     * constant
     */
    const STATUS_ENQUEUE = 1;
    const STATUS_DEQUEUE = 2;
    const STATUS_FAIL = 3;


    /**
     * Options array
     *
     * @var array
     */
    protected $_options = [
        self::STATUS_ENQUEUE => 'Enqueue',
        self::STATUS_DEQUEUE => 'Dequeue',
    ];

    /**
     * Return options array
     * @return array
     */
    public function toOptionArray()
    {
        $res = [];
        foreach ($this->toArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->_options;
    }

    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public static function getOptionArray()
    {
        return [
            self::STATUS_ENQUEUE => 'Enqueue',
            self::STATUS_DEQUEUE => 'Dequeue',
            self::STATUS_FAIL => 'Fail'
        ];
    }
}
