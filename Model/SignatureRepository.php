<?php
namespace NWT\Signapp\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SortOrder;
use NWT\Signapp\Api\SignatureRepositoryInterface;
use NWT\Signapp\Api\Data\SignatureInterface;
use NWT\Signapp\Model\SignatureFactory;
use NWT\Signapp\Model\ResourceModel\Signature as ObjectResourceModel;
use NWT\Signapp\Model\ResourceModel\Signature\CollectionFactory;

class SignatureRepository implements SignatureRepositoryInterface
{
    /**
     * @var SignatureFactory $objectFactory
     */
    protected $objectFactory;

    /**
     * @var ObjectResourceModel $objectResourceModel
     */
    protected ObjectResourceModel $objectResourceModel;

    /**
     * @var CollectionFactory $collectionFactory
     */
    protected CollectionFactory $collectionFactory;

    /**
     * @var SearchResultsInterfaceFactory $searchResultsFactory
     */
    protected SearchResultsInterfaceFactory $searchResultsFactory;

    /**
     * SignatureRepository constructor.
     *
     * @param SignatureFactory $objectFactory
     * @param ObjectResourceModel $objectResourceModel
     * @param CollectionFactory $collectionFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        SignatureFactory                 $objectFactory,
        ObjectResourceModel             $objectResourceModel,
        CollectionFactory               $collectionFactory,
        SearchResultsInterfaceFactory   $searchResultsFactory
    ) {
        $this->objectFactory        = $objectFactory;
        $this->objectResourceModel  = $objectResourceModel;
        $this->collectionFactory    = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @inheritDoc
     *
     * @throws CouldNotSaveException
     */
    public function save($signature)
    {
        try {
            $this->objectResourceModel->save($signature);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }
        return $signature;
    }

    /**
     * @inheritDoc
     */
    public function getById($id)
    {
        $object = $this->objectFactory->create();
        $this->objectResourceModel->load($object, $id);
        if (!$object->getId()) {
            throw new NoSuchEntityException(__('Object with id "%1" does not exist.', $id));
        }
        return $object;
    }

    /**
     * @inheritDoc
     */
    public function delete($signature)
    {
        try {
            $this->objectResourceModel->delete($signature);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }

    /**
     * @inheritDoc
     * @noinspection DuplicatedCode
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $collection = $this->collectionFactory->create();
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $fields[] = $filter->getField();
                $conditions[] = [$condition => $filter->getValue()];
            }
            if ($fields) {
                $collection->addFieldToFilter($fields, $conditions);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
        $objects = [];
        foreach ($collection as $objectModel) {
            $objects[] = $objectModel;
        }
        $searchResults->setItems($objects);
        return $searchResults;
    }
}
