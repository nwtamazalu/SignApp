<?php
/** @noinspection DuplicatedCode */
namespace NWT\Signapp\Ui\Component\Listing\Column\Application;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Request extends Column
{
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
                if (isset($item['app_id'])) {
                    $json = json_decode($item['request']);
                    $items = [];
                    foreach ($json as $pair) {
                        $items[] = $pair[0] . " (" . $pair[1] . ")";
                    }
                    $item['request'] = implode(", ", $items);
                }
            }
        }
        return $dataSource;
    }
}
