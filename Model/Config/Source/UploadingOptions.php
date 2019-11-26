<?php


namespace Tec\Management\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Framework\Data\OptionSourceInterface;

class UploadingOptions extends AbstractSource implements SourceInterface, OptionSourceInterface
{

    public static function getOptionArray()
    {
        return [
            '' =>  __('Select Upload Type'),
            1  => __('Entity IDs'),
            2  => __('Msisdns'),
            3  => __('CNIC')
        ];
    }

    public function getAllOptions()
    {
        $result = [];

        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }

    public function getOptionText($optionId)
    {
        $options = self::getOptionArray();

        return isset($options[$optionId]) ? $options[$optionId] : null;
    }
}
