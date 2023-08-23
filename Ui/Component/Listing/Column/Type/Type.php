<?php
/** @noinspection DuplicatedCode */
namespace NWT\Signapp\Ui\Component\Listing\Column\Type;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Type extends Column
{
    /**
     * Request Types
     */
    const REQUEST_TYPES = [1 => 'Product', 2 => 'Form'];

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['type_id'])) {
                    $item['sign_request_type'] = self::REQUEST_TYPES[$item['sign_request_type']];
                }
            }
        }
        return $dataSource;
    }
}
