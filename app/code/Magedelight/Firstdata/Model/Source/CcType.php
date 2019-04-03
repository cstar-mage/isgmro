<?php

namespace Magedelight\Firstdata\Model\Source;

class CcType extends \Magento\Payment\Model\Source\Cctype
{
    public function getAllowedTypes()
    {
        return ['VI', 'MC', 'AE', 'DI', 'JCB'];
    }
}
