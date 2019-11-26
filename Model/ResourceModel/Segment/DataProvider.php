<?php


namespace Tec\Management\Model\ResourceModel\Segment;

use \Magento\Store\Model\StoreManagerInterface;
use Tec\Management\Model\ResourceModel\Segment\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Tec\Management\Model\ResourceModel\CsvSegment\CollectionFactory as UsersCollectionFactory;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magento\Cms\Model\ResourceModel\Block\Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */

    public $_storeManager;
    protected $loadedData;
    /**
     * @var SegmentFactory
     */
    private $usersCollectionFactory;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    private $imageUploader;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $blockCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $segmentsCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        DataPersistorInterface $dataPersistor,
        UsersCollectionFactory $usersCollectionFactory,
        StoreManagerInterface $storeManager,
        array $meta = [],
        array $data = []
    )
    {
        $this->collection = $segmentsCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->_storeManager = $storeManager;
        $this->storeManager = $storeManager;
        $this->usersCollectionFactory = $usersCollectionFactory;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $baseUrl = $baseurl = $this->storeManager->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . "segment_upload/";
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $block) {
            $block->load($block->getId());
            $formData = $this->loadedData[$block->getId()] = $block->getData();
            if ($formData['segment_upload']) {
                $uploadedFile = [];
                $uploadedFile[0]['file'] = $formData['segment_upload'];
                $uploadedFile[0]['url'] = $baseUrl . $formData['segment_upload'];
                $formData['segment_upload'] = $uploadedFile;
            }
        }
        $data = $this->dataPersistor->get('tec_managemnet');
        if (!empty($data)) {
            $block = $this->collection->getNewEmptyItem();
            $block->setData($data);
            $this->loadedData[$block->getId()] = $block->getData();
            $this->dataPersistor->clear('tec_managemnet');
        } else {
            if ($items) {
                if ($block->getData('segment_upload') != null) {
                    $segmentUpload[$block->getId()] = $formData;
                    return $segmentUpload;
                }
            }
        }
        return $this->loadedData;
    }
}
