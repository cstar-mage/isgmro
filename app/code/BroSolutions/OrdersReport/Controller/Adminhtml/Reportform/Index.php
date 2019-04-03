<?php

namespace BroSolutions\OrdersReport\Controller\Adminhtml\Reportform;

class Index extends \Magento\Backend\App\Action
{
    protected $resultPageFactory = false;
    protected $_helper;
    protected $_fileFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \BroSolutions\OrdersReport\Helper\Data $helper,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    )
    {
        parent::__construct($context);
        $this->_helper = $helper;
        $this->_fileFactory = $fileFactory;

        $this->resultPageFactory = $resultPageFactory;
    }
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        if($data && isset($data['email']) && isset($data['from_date']) && isset($data['to_date'])){
            $fileName = $this->_helper->getOrdersCollectionCsv($data['email'], $data['from_date'], $data['to_date']);
            return $this->_fileFactory->create(
                $fileName,
                [
                    'type'  => "filename",
                    'value' => $fileName,
                    'rm'    => true,
                ],
                \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
                'text/csv',
                null
            );
        }
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend((__('Export Orders')));
        
        return $resultPage;
    }
}
