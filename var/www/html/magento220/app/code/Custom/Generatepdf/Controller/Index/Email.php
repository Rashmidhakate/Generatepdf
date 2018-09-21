<?php
namespace Custom\Generatepdf\Controller\Index;

use Magento\Framework\App\Filesystem\DirectoryList;

class Email extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\App\Request\Http $request,
		\Custom\Generatepdf\Model\Mail\TransportBuilder $transportBuilder,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
		\Magento\Framework\App\Filesystem\DirectoryList $directoryList,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{
		$this->_request = $request;
		$this->_transportBuilder = $transportBuilder;
		$this->_storeManager = $storeManager;
		$this->inlineTranslation = $inlineTranslation;
		$this->directoryList = $directoryList;
		$this->_pageFactory = $pageFactory;
		return parent::__construct($context);
	}

	public function execute()
	{
		$directoryPath = $this->directoryList->getPath('var');
		$pdfFile = $directoryPath.'/'."riddhi_doc.pdf";
		//echo $pdfFile;exit;
		$resultPage = $this->_pageFactory->create();
	 	//$store = $this->_storeManager->getStore()->getId();
		$templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->_storeManager->getStore()->getId());
		$templateVars = array(
		    'store' => $this->_storeManager->getStore(),
		    'customer_name' => 'John Doe',
		    'message'   => 'Hello World!!.'
		);
		$from = array('email' => "test@webkul.com", 'name' => 'Name of Sender');
		$this->inlineTranslation->suspend();
		$to = array('ratna@commercepundit.com');
        $transport = $this->_transportBuilder->setTemplateIdentifier('custom_pdf_template')
            ->setTemplateOptions($templateOptions)
            ->setTemplateVars($templateVars)
            ->setFrom($from)
            // you can config general email address in Store -> Configuration -> General -> Store Email Addresses
            ->addTo($to)
            ->addAttachment(file_get_contents($pdfFile))
            ->getTransport();
        $transport->sendMessage();
        $this->inlineTranslation->resume();
        echo "done";
		return $resultPage;
	}
}

