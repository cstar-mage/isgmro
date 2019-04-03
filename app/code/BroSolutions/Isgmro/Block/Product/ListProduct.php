<?php

namespace BroSolutions\Isgmro\Block\Product;

class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{
    protected function _getProductCollection()
    {
        if ($this->_productCollection === null) {
            $layer = $this->getLayer();
            if ($this->getShowRootCategory()) {
                $this->setCategoryId($this->_storeManager->getStore()->getRootCategoryId());
            }

            // if this is a product view page
            if ($this->_coreRegistry->registry('product')) {
                // get collection of categories this product is associated with
                $categories = $this->_coreRegistry->registry('product')
                    ->getCategoryCollection()->setPage(1, 1)
                    ->load();
                // if the product is associated with any category
                if ($categories->count()) {
                    // show products from this category
                    $this->setCategoryId(current($categories->getIterator()));
                }
            }

            $origCategory = null;
            if ($this->getCategoryId()) {
                try {
                    $category = $this->categoryRepository->get($this->getCategoryId());
                } catch (NoSuchEntityException $e) {
                    $category = null;
                }

                if ($category) {
                    $origCategory = $layer->getCurrentCategory();
                    $layer->setCurrentCategory($category);
                }
            }
            $this->_productCollection = $layer->getProductCollection();

            $this->prepareSortableFieldsByCategory($layer->getCurrentCategory());

            if ($origCategory) {
                $layer->setCurrentCategory($origCategory);
            }
        }
        $sortParamsExists = $this->_request->getParam('product_list_order', false);
        $productCollectionSelect = $this->_productCollection->getSelect();
        $orderParams = $productCollectionSelect->getPart(\Magento\Framework\DB\Select::ORDER);
        if(!$sortParamsExists || ($sortParamsExists && $sortParamsExists == 'relevance')){
            if(in_array('e.entity_id', $orderParams) === false){
                $productCollectionSelect->reset(\Magento\Framework\DB\Select::ORDER);
            }
            $this->_productCollection->getSelect()->order('score DESC');
        }
        return $this->_productCollection;
    }
}