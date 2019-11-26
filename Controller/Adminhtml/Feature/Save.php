<?php
/**
 * 11/14/2019 | 11:34 AM
 * @category    b2c
 * @author      Ejaz Alam
 * @email       ejaz.alam@evampsaanga.com
 */

namespace Tec\Management\Controller\Adminhtml\Feature;
use Exception;
use Magento\Backend\App\Action;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Tec\Management\Model\Feature;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Tec\Management\Model\FeatureSegment;

class Save extends Action
{

    protected $dataProcessor;
    protected $dataPersistor;
    protected $imageUploader;
    protected $model;
    protected $date;
    /**
     * @var FeaturesSegment
     */
    private $segmentModel;
    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param PostDataProcessor $dataProcessor
     * @param Feature $model
     * @param FeatureSegment $segmentModel
     * @param DataPersistorInterface $dataPersistor
     * @param CollectionFactory $customerFactory
     * @param StoreManagerInterface $storeManager
     * @param DateTime $date
     */
    public function __construct(
        Action\Context $context,
        PostDataProcessor $dataProcessor,
        Feature $model,
        FeatureSegment $segmentModel,
        DataPersistorInterface $dataPersistor,
        CollectionFactory $customerFactory,
        StoreManagerInterface $storeManager,
        DateTime $date
    ) {
        $this->dataProcessor = $dataProcessor;
        $this->dataPersistor = $dataPersistor;
        $this->_customerFactory = $customerFactory;
        $this->model = $model;
        $this->_storeManager = $storeManager;
        $this->segmentModel = $segmentModel;
        $this->date = $date;
        parent::__construct($context);
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $data = $this->dataProcessor->filter($data);
            $id = $data['id'];
            if(empty($id)){
                unset($data['id']);
            }
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $this->model->load($id);
            }

            $this->model->setData($data);
            $this->_eventManager->dispatch(
                'features_prepare_save',
                [
                    'features' => $this->model,
                    'request' => $this->getRequest()
                ]
            );

            if (!$this->dataProcessor->validate($data)) {
                return $resultRedirect->setPath('*/*/edit', ['id' => $this->model->getId(), '_current' => true]);
            }
            try {
                $this->model->save();
                if ($data['segment_ids']){
                    // saving data to another table.
                    $SegmentsIdsWithType = $data['segment_ids'];
                    $SegmentsIds = str_replace("_"," ",$SegmentsIdsWithType);
                    foreach ($SegmentsIds as $ids){
                        $type = $ids[0];
                        $this->segmentModel->setData('feature_id',$this->model->getId());
                        $this->segmentModel->setData('segment_ids',substr($ids,1));
                        $this->segmentModel->setData('segment_type',$type);
                        $this->segmentModel->save();
                        $this->segmentModel->unsetData();
                    }
                }
            } catch (Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Post.'));
            }

            $this->dataPersistor->set('features', $data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('tecfeature/feature');
    }

}