<?php
/** @noinspection DuplicatedCode */
namespace NWT\Signapp\Ui\Component\Listing\Column\Application;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Customer\Api\CustomerRepositoryInterface;

class Initiator extends Column
{
    /**
     * @var CustomerRepositoryInterface $customerRepository
     */
    protected CustomerRepositoryInterface $customerRepository;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CustomerRepositoryInterface $customerRepository,
        array $components = [],
        array $data = []
    ) {
        $this->customerRepository = $customerRepository;
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
                    $customer = $this->customerRepository->getById($item['initiator']);
                    $item['initiator'] = $customer->getName();
                }
            }
        }
        return $dataSource;
    }
}
