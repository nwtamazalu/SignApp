<?php
namespace NWT\Signapp\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SortOrder;
use NWT\Signapp\Api\ApplicationRepositoryInterface;
use NWT\Signapp\Api\Data\ApplicationInterface;
use NWT\Signapp\Model\ApplicationFactory;
use NWT\Signapp\Model\ResourceModel\Application as ObjectResourceModel;
use NWT\Signapp\Model\ResourceModel\Application\CollectionFactory;

class ApplicationRepository implements ApplicationRepositoryInterface
{
    /**
     * @var ApplicationFactory $objectFactory
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
     * ApplicationRepository constructor.
     *
     * @param ApplicationFactory $objectFactory
     * @param ObjectResourceModel $objectResourceModel
     * @param CollectionFactory $collectionFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        ApplicationFactory                 $objectFactory,
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
    public function save($application)
    {
        try {
            $this->objectResourceModel->save($application);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }
        return $application;
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
    public function delete($application)
    {
        try {
            $this->objectResourceModel->delete($application);
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
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $collection = $this->collectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
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
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $objects = [];
        foreach ($collection as $objectModel) {
            $objects[] = $objectModel;
        }
        $searchResults->setItems($objects);
        return $searchResults;
    }
}
