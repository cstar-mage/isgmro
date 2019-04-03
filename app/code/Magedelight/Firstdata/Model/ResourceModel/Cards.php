<?php

namespace Magedelight\Firstdata\Model\ResourceModel;

class Cards extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function _construct()
    {
        $this->_init('md_firstdata', 'card_id');
    }
}
