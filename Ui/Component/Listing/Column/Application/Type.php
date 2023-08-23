<?php
/** @noinspection DuplicatedCode */
namespace NWT\Signapp\Ui\Component\Listing\Column\Application;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use NWT\Signapp\Api\TypeRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;

class Type extends Column
{
    /**
     * @var TypeRepositoryInterface $typeFactory
     */
    protected TypeRepositoryInterface $typeRepository;

    /**
     * @var FilterBuilder $filterBuilder
     */
    protected FilterBuilder $filterBuilder;

    /**
     * @var SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     */
    protected SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param TypeRepositoryInterface $typeRepository
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        TypeRepositoryInterface $typeRepository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        array $components = [],
        array $data = []
    ) {
        $this->typeRepository               = $typeRepository;
        $this->filterBuilder                = $filterBuilder;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     * @throws LocalizedException
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['app_id'])) {
                    $item['type'] = \NWT\Signapp\Model\Type::TYPE_LABELS[$item['type']];
                }
            }
        }
        return $dataSource;
    }
}
