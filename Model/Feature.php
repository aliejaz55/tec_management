<?php
/**
 * 11/14/2019 | 9:42 AM
 * @category    b2c
 * @author      Ejaz Alam
 * @email       ejaz.alam@evampsaanga.com
 */

namespace Tec\Management\Model;
use Magento\Framework\Model\AbstractModel;

class Feature extends AbstractModel
{
    protected function _construct()
    {
        $this->_init('Tec\Management\Model\ResourceModel\Feature');
    }
}