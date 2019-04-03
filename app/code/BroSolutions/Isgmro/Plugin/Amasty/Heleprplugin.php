<?php
namespace BroSolutions\Isgmro\Plugin\Amasty;
class Heleprplugin
{
    public function aroundGetSettingByValue(\Amasty\Shopby\Helper\OptionSetting $subject, $procede, $value, $filterCode, $storeId)
    {
        if($filterCode == \Amasty\Shopby\Helper\FilterSetting::ATTR_PREFIX){
            return false;
        }
        return $procede();
    }
}