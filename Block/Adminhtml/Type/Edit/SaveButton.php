<?php
namespace NWT\Signapp\Block\Adminhtml\Type\Edit;

/**
 * Class SaveButton
 * @package NWT\Blog\Block\Adminhtml\Item\Edit
 */
class SaveButton extends \Magento\Cms\Block\Adminhtml\Page\Edit\SaveButton
{
    /**
     * @return array
     */
    public function getButtonData(): array
    {
        return [
            'label' => __('Save'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => 'nwt_signapp_type_edit_form.nwt_signapp_type_edit_form',
                                'actionName' => 'save',
                                'params' => [
                                    false
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'sort_order' => 90,
        ];
    }
}
