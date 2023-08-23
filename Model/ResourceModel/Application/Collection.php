<?php
namespace NWT\Signapp\Model\ResourceModel\Application;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'app_id';
    protected $_eventPrefix = 'sign_applications_collection';
    protected $_eventObject = 'application_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('NWT\Signapp\Model\Application', 'NWT\Signapp\Model\ResourceModel\Application');
    }
}
