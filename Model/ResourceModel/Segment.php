<?php
/**
 * 11/14/2019 | 12:40 PM
 * @category    b2c
 * @author      Ejaz Alam
 * @email       ejaz.alam@evampsaanga.com
 */

namespace Tec\Management\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class Segment extends AbstractDb
{
    public function __construct(
        Context $context
    )
    {
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init('csv_segments', 'id');
    }
}