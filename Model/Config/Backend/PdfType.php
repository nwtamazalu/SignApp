<?php
namespace NWT\Signapp\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\File;

class PdfType extends File
{
    /**
     * @return string[]
     */
    public function getAllowedExtensions() {
        return ['pdf'];
    }
}
