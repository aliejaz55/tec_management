<?php
/**
 * 11/15/2019 | 12:00 PM
 * @category    b2c
 * @author      Ejaz Alam
 * @email       ejaz.alam@evampsaanga.com
 */

namespace Tec\Management\Controller\Adminhtml\Segment;

use Exception;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Backend\App\Action;
use Tec\Management\Model\Segment;
use Tec\Management\Model\CsvSegment;
use Tec\Management\Helper\Data;

class Save extends Action
{
    protected $dataProcessor;
    protected $dataPersistor;
    protected $imageUploader;
    protected $model;
    protected $date;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var CsvSegment
     */
    private $csvSegment;
    /**
     * @var Action\Context
     */
    private $context;
    /**
     * @var CollectionFactory
     */
    private $customerFactory;
    /**
     * @var Data
     */
    private $helper;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param CsvSegment $csvSegment
     * @param PostDataProcessor $dataProcessor
     * @param Segment $model
     * @param StoreManagerInterface $storeManager
     * @param DataPersistorInterface $dataPersistor
     * @param CollectionFactory $customerFactory
     * @param DateTime $date
     */
    public function __construct(
        Action\Context $context,
        CsvSegment $csvSegment,
        PostDataProcessor $dataProcessor,
        Segment $model,
        StoreManagerInterface $storeManager,
        DataPersistorInterface $dataPersistor,
        CollectionFactory $customerFactory,
        Data $data,
        DateTime $date

    ) {
        parent::__construct($context);
        $this->dataProcessor = $dataProcessor;
        $this->dataPersistor = $dataPersistor;
        $this->storeManager = $storeManager;
        $this->model = $model;
        $this->date = $date;
        $this->csvSegment = $csvSegment;
        $this->customerFactory = $customerFactory;
        $this->helper = $data;
    }

    /**
     * Dispatch request
     *
     * @return ResultInterface|ResponseInterface
     * @throws NotFoundException
     * @throws Exception
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        echo "<pre>";
        print_r($data);
        exit;

        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $data = $this->dataProcessor->filter($data);
            if (isset($data['segment_upload'][0]['file']) && isset($data['segment_upload'][0]['tmp_name'])) {
                $data['segment_upload'] = $data['segment_upload'][0]['file'];
                $this->imageUploader = ObjectManager::getInstance()->get(
                    'Tec\Management\SegmentUpload'
                );
                $this->imageUploader->moveFileFromTmp($data['segment_upload']);
            } elseif (isset($data['segment_upload'][0]['file']) && !isset($data['segment_upload'][0]['tmp_name'])) {
                $data['segment_upload'] = $data['segment_upload'][0]['file'];
            } else {
                $data['segment_upload'] = '';
            }
            $id = $data['id'];
            $fileUploadingType = $data['file_type'];
            if (empty($id)) {
                unset($data['id']);
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
                // file upload Functionality
                try{
                    if($fileUploadingType === 1) { // Entity IDs
                        $uploadedFilesWithEntityIds = $this->storeManager->getStore()->getBaseUrl(
                                UrlInterface::URL_TYPE_MEDIA
                            ) . $this->imageUploader->getFilePath($this->imageUploader->getBasePath(), $data['segment_upload']);
                        $entityIds = array();
                        $bulkInsert= array();
                        $row = -1;
                        $r = 0;
                        if (($handle = fopen("$uploadedFilesWithEntityIds", "r")) !== FALSE) {
                            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                                $totalRecordsInCsv = count($data);
                                $row++;
                                for ($c = 0; $c < $totalRecordsInCsv; $c++) {
                                    $eId[$row][$c] = $data[$c];
                                    $entityIds[] = round($eId[$row][$c],0);
                                }
                            }
                            fclose($handle);
                        }
                        $collection = $this->customerFactory->create()
                            ->addAttributeToFilter('entity_id',array("in"=>$entityIds))->getData();
                        if(!$id) {
                            foreach ($collection as $value ) {
                                $bulkInsert[] = [
                                    'customer_id' => $value['entity_id'],
                                    'msisdn' => $value['lastname'],
                                    'csv_segment_id' => $this->model->getId()
                                ];
                            }
                            if($collection > 0 && !empty($collection))
                            {
                                $this->model->save();
                                $this->csvSegment->bulkInsert('csv_segments_users', $bulkInsert);
                                $this->messageManager->addSuccess(__('You saved the Post.'));
                                $this->dataPersistor->clear('features');
                                if ($this->getRequest()->getParam('back')) {
                                    return $resultRedirect->setPath(
                                        '*/*/edit',
                                        ['id' => $this->model->getId(),
                                            '_current' => true]
                                    );
                                }
                            }else{
                                $this->messageManager->addError('Customer ID in csv does not contain any reference in db');
                                $this->model->delete();
                            }
                        }
                    }
                    if ($fileUploadingType == 2){ // MSISDN
                        $uploadedFilesWithMsisdn = $this->storeManager->getStore()->getBaseUrl(
                                UrlInterface::URL_TYPE_MEDIA
                            ) . $this->imageUploader->getFilePath($this->imageUploader->getBasePath(), $data['segment_upload']);
                        $msisdns = array();
                        $bulkInsert = array();
                        $row = -1;
                        $r = 0;
                        if (($handle = fopen("$uploadedFilesWithMsisdn", "r")) !== FALSE) {
                            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                                $totalRecordsInCsv = count($data);
                                $row++;
                                for ($c = 0; $c < $totalRecordsInCsv; $c++) {
                                    $mobileNumber[$row][$c] = $data[$c];
                                    // validate csv msisdn
                                    $isValidMobileNumber = $this->helper->validateMobileNumber($mobileNumber[$row][$c]);
                                    if ($isValidMobileNumber){
                                        // if MSISDN is valid add into array
                                        $msisdns[] = round($mobileNumber[$row][$c],0);
                                    }
                                }
                            }
                            fclose($handle);
                        }
                        $collection = $this->customerFactory->create()
                            ->addAttributeToFilter('lastname',array("in"=>$msisdns))
                            ->getData();
                        if(!$id) {
                            if($collection > 0 && !empty($collection))
                            {
                                $this->model->save();
                                foreach ($collection as $value ) {
                                    $bulkInsert[] = [
                                        'customer_id' => $value['entity_id'],
                                        'msisdn' => $value['lastname'],
                                        'csv_segment_id' => $this->model->getId()
                                    ];
                                }
                                $this->csvSegment->bulkInsert('csv_segments_users', $bulkInsert);
                                $this->messageManager->addSuccess(__('You saved the Post.'));
                                $this->dataPersistor->clear('features');
                                if ($this->getRequest()->getParam('back')) {
                                    return $resultRedirect->setPath(
                                        '*/*/edit',
                                        ['id' => $this->model->getId(),
                                            '_current' => true]
                                    );
                                }
                            }else{
                                $this->messageManager->addError('MSISDN in csv does not contain any reference in db');
                                $this->model->delete();
                            }
                        }
                    }
                    if ($fileUploadingType == 3){ // CNIC
                        $uploadedFilesWithCnic = $this->storeManager->getStore()->getBaseUrl(
                                UrlInterface::URL_TYPE_MEDIA
                            ) . $this->imageUploader
                                ->getFilePath($this->imageUploader->getBasePath(),
                                    $data['segment_upload']); // file complete URL
                        $allCnics  = array();
                        $bulkInsert = array();
                        $row = -1;
                        $r = 0;
                        if (($handle = fopen("$uploadedFilesWithCnic", "r")) !== FALSE) {
                            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                                $totalRecordsInCsv = count($data);
                                $row++;
                                for ($c = 0; $c < $totalRecordsInCsv; $c++) {
                                    $cnic[$row][$c] = $data[$c];
                                    // validate csv msisdn
                                    $isValidCnicNumber = $this->helper->validateCnicNumber($cnic[$row][$c]);
                                    if ($isValidCnicNumber){
                                        // if MSISDN is valid add into array
                                        $allCnics[] = $isValidCnicNumber;
                                    }
                                }
                            }
                            fclose($handle);
                        }
                        $collection = $this->customerFactory->create()
                            ->addAttributeToFilter('cnic',array("in"=>$allCnics))
                            ->getData();
                        if(!$id) {
                            if($collection > 0 && !empty($collection))
                            {
                                $this->model->save();
                                foreach ($collection as $value ) {
                                    $bulkInsert[] = [
                                        'customer_id' => $value['entity_id'],
                                        'msisdn' => $value['lastname'],
                                        'csv_segment_id' => $this->model->getId()
                                    ];
                                }
                                $this->csvSegment->bulkInsert('csv_segments_users', $bulkInsert);
                                $this->messageManager->addSuccess(__('You saved the Post.'));
                                $this->dataPersistor->clear('features');
                                if ($this->getRequest()->getParam('back')) {
                                    return $resultRedirect->setPath(
                                        '*/*/edit',
                                        ['id' => $this->model->getId(),
                                            '_current' => true]
                                    );
                                }
                            }else{
                                $this->messageManager->addError('MSISDN in csv does not contain any reference in db');
                                $this->model->delete();
                            }
                        }
                    }
                } catch (Exception $e){
                    $this->messageManager->addException($e, __($e->getMessage()));
                }
                $this->dataPersistor->set('features', $data);
                return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            }
        }
        return $resultRedirect->setPath('tecfeature/segment/');
    }
}