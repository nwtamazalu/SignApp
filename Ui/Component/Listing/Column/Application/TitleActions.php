<?php
namespace NWT\Signapp\Ui\Component\Listing\Column\Application;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class TitleActions extends Column
{
    /**
     * Url path  to edit
     *
     * @var string
     */
    const URL_PATH_EDIT = 'nwt_signapp/application/edit';

    /**
     * Url path  to delete
     *
     * @var string
     */
    const URL_PATH_DELETE = 'nwt_signapp/application/delete';

    /**
     * URL builder
     *
     * @var UrlInterface
     */
    protected UrlInterface $_urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->_urlBuilder = $urlBuilder;
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
                    $item[$this->getData('name')] = [
//                        'edit' => [
//                            'href' => $this->_urlBuilder->getUrl(
//                                static::URL_PATH_EDIT,
//                                [
//                                    'id' => $item['app_id']
//                                ]
//                            ),
//                            'label' => __('Edit')
//                        ],
                        'delete' => [
                            'href' => $this->_urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                [
                                    'id' => $item['app_id'],
                                    '_secure' => true
                                ]
                            ),
                            'label' => __('Delete'),
                            'confirm' => [
                                'title' => __('Delete ' . $item['app_id']),
                                'message' => __('Are you sure you want to delete this record?')
                            ]
                        ]
                    ];
                }
            }
        }
        return $dataSource;
    }
}
