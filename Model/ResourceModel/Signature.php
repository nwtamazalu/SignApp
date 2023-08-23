<?php
namespace NWT\Signapp\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class Signature extends AbstractDb
{
    /**
     * Signature constructor.
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
        $this->_init('sign_signatures', 'sign_id');
    }

    /**
     * @param AbstractModel $object
     * @return $this
     */
    protected function _beforeSave(AbstractModel $object)
    {
        return $this;
    }
}
