<?php
/**
 * 11/13/2019 | 5:31 PM
 * @category    b2c
 * @author      Ejaz Alam
 * @email       ejaz.alam@evampsaanga.com
 */

namespace Tec\Management\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        /**
         * create table 'tec_features_details'
         */
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('features_details')){
            $table = $installer->getConnection()->newTable($installer->getTable('features_details'))
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ],
                    'ID'
                )
                ->addColumn(
                    'feature_name',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => true,
                    ],
                    'Feature Name'
                )
                ->addColumn(
                    'identifier',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => false,
                    ],
                    'Unique Identifier'
                )
                ->addColumn(
                    'feature_name_ur',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => true,
                    ],
                    'Feature Name Urdu'
                )
                ->addColumn(
                    'is_active',
                    Table::TYPE_INTEGER,
                    10,
                    [
                        'nullable' => false,
                    ],
                    'status'
                )
                ->addColumn(
                    'segment_type',
                    Table::TYPE_INTEGER,
                    10,
                    [
                        'nullable' => false,
                    ],
                    'status'
                )
                ->addColumn(
                    'is_paid',
                    Table::TYPE_INTEGER,
                    10,
                    [
                        'nullable' => false,
                    ],
                    'Paid activity'
                );
            $installer->getConnection()->createTable($table);

            $installer->getConnection()->addIndex(
                $installer->getTable('features_details'),
                $setup->getIdxName(
                    $installer->getTable('features_details'),
                    ['feature_name', 'feature_name_ur'],
                    AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['feature_name', 'feature_name_ur'],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            );
        }

        /**
         * create table 'tec_features_segments'
         */
        if (!$installer->tableExists('features_segments')){
            $table = $installer->getConnection()->newTable(
                $installer->getTable('features_segments'))
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ],
                    'ID'
                )
                ->addColumn(
                    'feature_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true,
                    ],
                    'feature ID'
                )
                ->addColumn(
                    'segment_ids',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => true,
                    ],
                    'Segment IDs'
                )
                ->addForeignKey(
                    $setup->getFkName(
                        'features_segments',
                        'feature_id',
                        'features_details',
                        'id'
                    ),
                    'feature_id',
                    $setup->getTable('features_details'),
                    'id',
                    Table::ACTION_CASCADE
                )
                ->setComment(
                    'Segement Details'
                ) ;
            $installer->getConnection()->createTable($table);
        }


        /**
         * CSV Segments Table
         */
        if (!$installer->tableExists('csv_segments')){
            $table = $installer->getConnection()->newTable($installer->getTable('csv_segments'))
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ],
                    'ID'
                )
                ->addColumn(
                    'segment_title',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => true,
                    ],
                    'Segment Name'
                )
                ->addColumn(
                    'segment_upload',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => true,
                    ],
                    'CSV Segment'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_INTEGER,
                    10,
                    [
                        'nullable' => false,
                    ],
                    'status'
                );
            $installer->getConnection()->createTable($table);

            $installer->getConnection()->addIndex(
                $installer->getTable('csv_segments'),
                $setup->getIdxName(
                    $installer->getTable('csv_segments'),
                    ['segment_title'],
                    AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['segment_title'],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            );
        }
        /**
         * create table 'csv_segments_users'
         */
        if (!$installer->tableExists('csv_segments_users')){
            $table = $installer->getConnection()->newTable(
                $installer->getTable('csv_segments_users'))
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ],
                    'ID'
                )
                ->addColumn(
                    'csv_segment_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true,
                    ],
                    'Segment Id'
                )
                ->addColumn(
                    'customer_id',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => true,
                    ],
                    'Customer ID'
                )
                ->addColumn(
                    'creation_time',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    [
                        'nullable' => false,
                        'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT
                    ],
                    'Creation Time'
                )
                ->addColumn(
                    'update_time',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    [
                        'nullable' => false,
                        'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE
                    ],
                    'Modification Time'
                )
                ->addForeignKey(
                    $setup->getFkName(
                        'csv_segments',
                        'id',
                        'csv_segments_users',
                        'csv_segment_id'
                    ),
                    'csv_segment_id',
                    $setup->getTable('csv_segments'),
                    'id',
                    Table::ACTION_CASCADE
                )
                ->setComment(
                    'CSV Segement Details'
                ) ;
            $installer->getConnection()->createTable($table);
        }
        $setup->endSetup();

    }
}