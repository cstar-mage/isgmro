<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Model\Config\Source;

/**
 * Class Country
 * @package Magenest\QuickBooksOnline\Model\Config\Source
 */
class Country implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'OTHER' => 'Other',
            'UK' => 'United Kingdom',
        ];
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];
        foreach ($this->toArray() as $k => $v) {
            $result[] = ['value' => $k, 'label' => $v];
        }

        return $result;
    }
}
