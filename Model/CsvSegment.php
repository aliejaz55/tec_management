<?php
/**
 * 11/15/2019 | 2:16 PM
 * @category    b2c
 * @author      Ejaz Alam
 * @email       ejaz.alam@evampsaanga.com
 */

namespace Tec\Management\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\App\ResourceConnection;

class CsvSegment extends AbstractModel implements IdentityInterface
{
    /**
     * Code of "Integrity constraint violation: 1062 Duplicate entry" error
     */
    const ERROR_CODE_DUPLICATE_ENTRY = 23000;
    /**
     * @var ResourceModel\AbstractResource
     */
    private $resource;
    private $connection;
    /**
     * @var Context
     */
    private $context;

    public function __construct(
        ResourceConnection $_resource,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->context = $context;
        $this->_resource = $_resource;
    }

    public function getIdentities()
    {
        return [
            'scv_segments_'.$this->getId()
        ];
    }

    protected function _construct()
    {
        $this->_init('Tec\Management\Model\ResourceModel\CsvSegment');
    }
    public function bulkInsert($tableName,$data){
        try {
            $tableName =$this->_resource->getTableName($tableName);
            return $this->_resource->getConnection()->insertMultiple($tableName, $data);
        } catch (\Exception $e) {
            if ($e->getCode() === self::ERROR_CODE_DUPLICATE_ENTRY
                && preg_match('#SQLSTATE\[23000\]: [^:]+: 1062[^\d]#', $e->getMessage())
            ) {
                throw new AlreadyExistsException(
                    __('URL key for specified store already exists.')
                );
            }
            throw $e;
        }
    }
}