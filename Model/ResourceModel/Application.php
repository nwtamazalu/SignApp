<?php
namespace NWT\Signapp\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class Application extends AbstractDb
{
    /**
     * Application constructor.
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('sign_applications', 'app_id');
    }

    /**
     * @param AbstractModel $object
     * @return $this
     */
    protected function _beforeSave(AbstractModel $object)
    {
        return $this;
    }

    /**
     * @param AbstractModel $object
     * @return Application
     */
    protected function _beforeDelete(AbstractModel $object)
    {
        return parent::_beforeDelete($object); // TODO: Change the autogenerated stub
    }
}