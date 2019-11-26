<?php
/**
 * 11/19/2019 | 3:34 PM
 * @category    b2c
 * @author      Ejaz Alam
 * @email       ejaz.alam@evampsaanga.com
 */

namespace Tec\Management\Controller\Adminhtml\Feature;
use Magento\Backend\App\Action;
use Tec\Management\Model\Feature;
use Tec\Management\Model\ResourceModel\Feature\CollectionFactory;

class Delete extends Action
{
    const ADMIN_RESOURCE = 'TEC_FeaturesManagement::featuresDelete';

    protected $collection;
    /**
     * @var \Tec\FeaturesManagement\Model\Features
     */
    private $model;

    public function __construct(
        Action\Context $context,
        Feature $model,
        CollectionFactory $Collection
    ) {
        $this->collection = $Collection;
        $this->model = $model;
        parent::__construct($context);
    }
    public function execute()
    {
//        echo json_encode($this->model->getData());
//        exit;
        $id = $this->getRequest()->getParam('id');
        echo $id;
        exit;
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            $title = "";
            try {
                $this->model->load($id);
                $title = $this->model->getTitle();
                $this->model->delete();
                $this->messageManager->addSuccess(__('Feature '.$title.'has been deleted.'));
                $this->_eventManager->dispatch(
                    'adminhtml_managment_on_delete',
                    ['title' => $title, 'status' => 'success']
                );
                return $resultRedirect->setPath('tecfeature/feature/index');
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_managment_on_delete',
                    ['title' => $title, 'status' => 'fail']
                );
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
        $this->messageManager->addError(__('We can\'t find a post to delete.'));
        return $resultRedirect->setPath('tecfeature/feature/index');
    }
}
