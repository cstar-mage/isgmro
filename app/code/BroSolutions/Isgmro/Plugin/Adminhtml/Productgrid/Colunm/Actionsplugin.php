<?php
namespace BroSolutions\Isgmro\Plugin\Adminhtml\Productgrid\Colunm;
class Actionsplugin
{
    protected $urlBuilder;

    const DEFAULT_FRONTEND_STORE_ID_FOR_ADMIN = 1;

    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
    }

    public function afterPrepareDataSource(\Magento\Catalog\Ui\Component\Listing\Columns\ProductActions $subject, $result)
    {
        if (isset($result['data']['items'])) {
            foreach ($result['data']['items'] as &$item) {
                $item[$subject->getData('name')]['edit'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'catalog/product/edit',
                        ['id' => $item['entity_id'], 'store' => self::DEFAULT_FRONTEND_STORE_ID_FOR_ADMIN]
                    ),
                    'label' => __('Edit'),
                    'hidden' => false,
                ];
            }
        }

        return $result;
    }
}