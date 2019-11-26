<?php
/**
 * 11/14/2019 | 12:39 PM
 * @category    b2c
 * @author      Ejaz Alam
 * @email       ejaz.alam@evampsaanga.com
 */

namespace Tec\Management\Model;

use Magento\Framework\Model\AbstractModel;

class Segment extends AbstractModel
{
    protected function _construct()
    {
        $this->_init('Tec\Management\Model\ResourceModel\Segment');
    }
}