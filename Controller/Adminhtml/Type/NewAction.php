<?php
namespace NWT\Signapp\Controller\Adminhtml\Type;

use NWT\Signapp\Controller\Adminhtml\Index\Type;

class NewAction extends Type
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
