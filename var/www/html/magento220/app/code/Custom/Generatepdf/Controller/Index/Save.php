<?php 
namespace Custom\Generatepdf\Controller\Index;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class Save extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Custom\Generatepdf\Helper\Data $helperData,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->resultRawFactory      = $resultRawFactory;
        $this->helperData = $helperData;
        parent::__construct($context);
    }
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $file = $this->getRequest()->getFiles('file');
        $helperData = $this->helperData->createPdf($data,$file);
        return $this->_redirect($this->_redirect->getRefererUrl());
    }
}