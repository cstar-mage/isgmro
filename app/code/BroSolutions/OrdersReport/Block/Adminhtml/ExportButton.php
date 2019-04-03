<?php
namespace BroSolutions\OrdersReport\Block\Adminhtml;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class ExportButton  extends GenericButton implements ButtonProviderInterface
{
    public function getButtonData()
    {
        return [
            'label' => __('Export'),
            'class' => 'save primary',
            'sort_order' => 90,
            'on_click' => sprintf("location.href = '%s';", $this->getUrl('brosolutionsordersreport/reportform/index')),

        ];
    }
}