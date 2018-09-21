<?php 

namespace Custom\Generatepdf\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper{
	public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
       	\Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploader,        
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Custom\Generatepdf\Model\Mail\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
    ) {
        parent::__construct($context);
        $this->_request = $request;
        $this->directoryList = $directoryList;
        $this->fileFactory           = $fileFactory;
        $this->imageFactory = $imageFactory;
        $this->uploader = $uploader;
        $this->filesystem = $filesystem;
        $this->file = $file;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->storeManager = $storeManager;
        $this->_directory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
    }

    public function createPdf($data,$file){
    	//print_r($data);exit;
    	$name = $data["name"];
        $image = $file["name"];
        $email = $data["email"];
        $target = $this->_directory->getAbsolutePath('generated/');
        /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
        $uploader = $this->uploader->create(
            ['fileId' => 'file']
        );
        $uploader->setAllowedExtensions(
            ['jpg', 'jpeg', 'gif', 'png','doc','pdf','xls','docx','zip','mp4','avi','mp3','txt','xlsx','vob','csv','gif']
        );
        $uploader->setAllowRenameFiles(true);
        $uploader->save($target);
        $this->file->changePermissions($target.'/'.$image,0777);
        $imageFile = $target.$image;        
        $pdf = new \Zend_Pdf();
        $pdf->pages[] = $pdf->newPage(\Zend_Pdf_Page::SIZE_A4);
        $page = $pdf->pages[0]; // this will get reference to the first page.
        $style = new \Zend_Pdf_Style();
        $style->setLineColor(new \Zend_Pdf_Color_Rgb(0,0,0));
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font,15);
        $page->setStyle($style);
        $width = $page->getWidth();
        $hight = $page->getHeight();
        $x = 30;
        $pageTopalign = 850; //default PDF page height
        $this->y = 850 - 100; //print table row from page top – 100px
        //Draw table header row’s
        $style->setFont($font,16);
        $page->setStyle($style);
        $page->drawRectangle(30, $this->y + 10, $page->getWidth()-30, $this->y +70, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
        $style->setFont($font,15);
        $page->setStyle($style);
        $page->drawText(__("Cutomer Details"), $x + 5, $this->y+50, 'UTF-8');
        $style->setFont($font,11);
        $page->setStyle($style);
        $page->drawText(__("Name : %1", "$name"), $x + 5, $this->y+33, 'UTF-8');
        $style->setFont($font,12);
        $page->setStyle($style);
        $page->drawText(__("email : %1", "$email"), $x + 100, $this->y+ 33, 'UTF-8');
        $style->setFont($font,12);
        $page->setStyle($style);
        $page->drawText(__("Image"), $x + 60, $this->y-10, 'UTF-8');
        $style->setFont($font,10);
        $page->setStyle($style);
        $add = 9;
        $this->insertImage($imageFile, $x + 70, (int)($this->y-60),$x + 160, (int)($this->y-12), $page);
        $page->drawRectangle(30, $this->y -62, $page->getWidth()-30, $this->y + 10, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
        $page->drawRectangle(30, $this->y -62, $page->getWidth()-30, $this->y - 100, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
        $style->setFont($font,15);
        $page->setStyle($style);
        $page = end($pdf->pages);
        $fileName = $name."_doc.pdf";
        $this->fileFactory->create(
           $fileName,
           $pdf->render(),
           \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR, // this pdf will be saved in var directory with the name example.pdf
           'application/pdf'
        );
    	return $this;
    }

    public function insertImage($imageFile, $x1, $y1, $x2, $y2, \Zend_Pdf_Page &$page)
    {
        try{
            $pdfImage = \Zend_Pdf_Image::imageWithPath($imageFile);
            //Draw image to PDF
            $page->drawImage($pdfImage, $x1, $y1, $x2, $y2);
        }
        catch (\Exception $e) {
            return false;
        }
    }

	public function sendEmail(
		$emailtemplate,
		$senderInfo,
		$templateVars,
		$receiverEmail,
		$receiverName = '',
		$ccEmails = [],
		$bccEmails = []
	) {
		$this->inlineTranslation->suspend();
		try {
			$templateOptions = array(
				'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
				'store' => $this->_storeManager->getStore()->getId()
			);
			$transport = $this->_transportBuilder->setTemplateIdentifier($emailtemplate)
			->setTemplateOptions($templateOptions)
			->setTemplateVars($templateVars)
			->setFrom($senderInfo)
			->addTo($receiverEmail, $receiverName)
			->addCc($ccEmails)
			->addBcc($bccEmails)
			->addAttachment(file_get_contents($pdfFile))
			->getTransport();

			$transport->sendMessage();
		} finally {
			$this->inlineTranslation->resume();
		}
	}

}