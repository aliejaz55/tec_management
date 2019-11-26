<?php
/**
 * 11/14/2019 | 10:08 AM
 * @category    b2c
 * @author      Ejaz Alam
 * @email       ejaz.alam@evampsaanga.com
 */

namespace Tec\Management\Controller\Adminhtml\Segment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    protected $resultPageFactory;


    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }


    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->getConfig()->getTitle()->prepend(__('CSV Segment'));
        return $resultPage;
    }
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Tec_Management::csvsegmentlgrid')
            ->addBreadcrumb(__('Tec'), __('Management'));

        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Tec_Management::csvsegmentlgrid');
    }
}
