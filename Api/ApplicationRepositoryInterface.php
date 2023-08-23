<?php
namespace NWT\Signapp\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use NWT\Signapp\Api\Data\ApplicationInterface;

interface ApplicationRepositoryInterface
{
    /**
     * Save Log
     * @param AbstractModel $app
     * @return ApplicationInterface
     * @throws LocalizedException
     */
    public function save(
        AbstractModel $app
    );

    /**
     * Retrieve Log
     * @param string $entityId
     * @return ApplicationInterface
     * @throws LocalizedException
     */
    public function getById($entityId);

    /**
     * Retrieve Log matching the specified criteria.
     * @param SearchCriteriaInterface $searchCriteria
     * @throws LocalizedException
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Log
     * @param AbstractModel $app
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(
        AbstractModel $app
    );

    /**
     * Delete Log by ID
     * @param string $entityId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($entityId);
}

