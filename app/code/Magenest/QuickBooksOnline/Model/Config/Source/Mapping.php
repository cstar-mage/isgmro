<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Mapping
 * @package Magenest\QuickBooksOnline\Model\Config\Source
 */
class Mapping implements ArrayInterface
{
    /**@#+
     * constant
     */
    const STATUS_PROCESSING= 1;
    const STATUS_SUCCESS = 2;
    const STATUS_FAIL = 3;


    /**
     * Options array
     *
     * @var array
     */
    protected $_options = [
        self::STATUS_PROCESSING => 'Processing',
        self::STATUS_SUCCESS => 'Success',
        self::STATUS_FAIL => 'Fail',
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
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_SUCCESS => 'Success',
            self::STATUS_FAIL => 'Fail',
        ];
    }
}
