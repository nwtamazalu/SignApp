<?php
namespace NWT\Signapp\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class TextFont implements OptionSourceInterface
{
    /**
     * @return array[]
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'courier', 'label' => 'Courier'],
            ['value' => 'courierb', 'label' => 'Courier Bold'],
            ['value' => 'courierbi', 'label' => 'Courier Bold Italic'],
            ['value' => 'courieri', 'label' => 'Courier Italic'],
            ['value' => 'helvetica', 'label' => 'Helvetica'],
            ['value' => 'helveticab', 'label' => 'Helvetica Bold'],
            ['value' => 'helveticabi', 'label' => 'Helvetica Bold Italic'],
            ['value' => 'helveticai', 'label' => 'Helvetica Italic'],
            ['value' => 'symbol', 'label' => 'Symbol'],
            ['value' => 'times', 'label' => 'Times New Roman'],
            ['value' => 'timesb', 'label' => 'Times New Roman Bold'],
            ['value' => 'timesbi', 'label' => 'Times New Roman Bold Italic'],
            ['value' => 'timesi', 'label' => 'Times New Roman Italic'],
            ['value' => 'zapfdingbats', 'label' => 'Zapf Dingbats'],
        ];
    }
}
