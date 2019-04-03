<?php
namespace Sebwite\SmartSearch\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Search\Model\AutocompleteInterface;
use Magento\Framework\Controller\ResultFactory;

class Suggest extends \Magento\Search\Controller\Ajax\Suggest
{
    private $autocomplete;

    public function __construct(
        Context $context,
        AutocompleteInterface $autocomplete
    ) {
        $this->autocomplete = $autocomplete;
        parent::__construct($context, $autocomplete);
    }


    public function execute()
    {
        if (!$this->getRequest()->getParam('q', false)) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_url->getBaseUrl());
            return $resultRedirect;
        }
        $queryText = $this->getRequest()->getParam('q');
        $viewAllUrl = $this->_url->getUrl('catalogsearch/result').'?q='.$queryText;
        $autocompleteData = $this->autocomplete->getItems();
        $responseData = [];
        foreach ($autocompleteData as $key => $resultItem) {
            if($key !== 'total_count'){
                $responseData[] = $resultItem->toArray();
            } else {
                $responseData['total_count'] = $resultItem;
            }
        }
        $responseData['view_all_url'] = $viewAllUrl;
        /*$totalCount = $this->autocomplete->getTotalCount();
        $responseData['total_count'] = $totalCount;*/
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseData);
        return $resultJson;
    }
}