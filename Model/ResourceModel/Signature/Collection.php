<?php
namespace NWT\Signapp\Model\ResourceModel\Signature;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'sign_id';
    protected $_eventPrefix = 'sign_signatures_collection';
    protected $_eventObject = 'signature_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('NWT\Signapp\Model\Signature', 'NWT\Signapp\Model\ResourceModel\Signature');
    }
}
