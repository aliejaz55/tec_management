<?php


namespace Tec\Management\Model\ResourceModel\Feature;

use Tec\Management\Model\ResourceModel\Feature\CollectionFactory;
use Tec\Management\Model\ResourceModel\FeatureSegment\CollectionFactory as SegmentFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

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
    private $segmentFactory;

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
        CollectionFactory $testimonialsCollectionFactory,
        SegmentFactory $segmentFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $testimonialsCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->_storeManager=$storeManager;
        $this->segmentFactory = $segmentFactory;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)){
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        $collection = $this->segmentFactory->create();

        foreach ($items as $page) {
            $features_ids = $collection->addFieldToFilter('feature_id',$page->getId());
            $featuresArray = $features_ids->getData();
            $segmentIds = array();

            foreach ($featuresArray as $ids){
                $segmentIds[] = $ids['segment_ids'];
            }
                $page->setData('segment_ids',$segmentIds);
            $this->loadedData[$page->getId()] = $page->getData();
        }
        $data = $this->dataPersistor->get('tec_featuresmanagement');
        if (!empty($data)) {
            $page = $this->collection->getNewEmptyItem();
            $page->setData($data);
            $this->loadedData[$page->getId()] = $page->getData();
            $this->dataPersistor->clear('tec_featuresmanagement');
        }

        return $this->loadedData;
    }
}
