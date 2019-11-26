<?php


namespace Tec\Management\Model\Config\Source;


use Magento\CustomerSegment\Model\ResourceModel\Segment\CollectionFactory; // Magento Segments
use Tec\Management\Model\ResourceModel\Segment\CollectionFactory as CustomSegments; // Custom Segments


use Magento\Framework\Option\ArrayInterface;

class AllSegments implements ArrayInterface
{
    /**
     * @var CollectionFactory
     */
    private $identifiersCollectionFactory;
    /**
     * @var CustomSegments
     */
    private $customSegments;

    public function __construct(
        CollectionFactory $identifiersCollectionFactory,
        CustomSegments $customSegments
    ) {
        $this->identifiersCollectionFactory = $identifiersCollectionFactory;
        $this->customSegments = $customSegments;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $data = $this->identifiersCollectionFactory->create();
        $csvSegments = $this->customSegments->create();
        $customCvsSegments = $csvSegments->getData();
        $values = $data->getData();
        $magentoSegments = array();
        $csvUploadedSegments = array();

        if ($data || $customCvsSegments) {
            if (count($values) > 0) {
                foreach ($values as $selectValues) {
                    if (!isset($selectValues['segment_id']) || empty($selectValues['segment_id'])) {
                        $selectValues['segment_id'] = " ";
                    }
                    $magentoSegments[] = [
                        'value' => '1_'.$selectValues['segment_id'],
                        'label' => __($selectValues['name'])
                    ];
                }
            }
//            echo "<pre>";
//            print_r($magentoSegments);
//            exit;

                if (count($customCvsSegments) > 0) {
                    foreach ($customCvsSegments as $customCvsSegment) {
                        $csvUploadedSegments[] = [
                            'value' => '2_'.$customCvsSegment['id'],
                            'label' => __($customCvsSegment['segment_title'])

                        ];
                    }
                }
                return [
                    [
                        'label' => __('Custom Segment'),
                        'value' => $csvUploadedSegments,
                    ],
                    [
                        'label' => __('Magento Segment'),
                        'value' => $magentoSegments,
                    ],
                ];
            } else {
                return [
                    [
                        'label' => __('Select Segment'),
                        'value' => '',
                    ],
                ];
            }
        }

    public function getAllOptions()
    {
        return $this->toOptionArray();
    }
}