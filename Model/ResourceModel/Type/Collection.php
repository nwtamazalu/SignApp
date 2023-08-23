<?php
namespace NWT\Signapp\Model\ResourceModel\Type;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'type_id';
    protected $_eventPrefix = 'sign_application_types_collection';
    protected $_eventObject = 'type_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('NWT\Signapp\Model\Type', 'NWT\Signapp\Model\ResourceModel\Type');
    }
}
