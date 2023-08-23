<?php
namespace NWT\Signapp\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Customer\Model\CustomerFactory;
use Magento\Store\Model\StoreManagerInterface;

class Application extends AbstractExtensibleModel implements IdentityInterface
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'nwt_signapp_applications';

    /**
     * @var string
     */
    protected $_cacheTag = 'nwt_signapp_applications';

    /**
     * @var string
     */
    protected $_eventPrefix = 'nwt_signapp_applications';

    /**
     * @var int $_timeout 24h
     */
    protected $_timeout = 86400;

    /**
     * @var SignatureRepository $signatureRepository
     */
    protected SignatureRepository $signatureRepository;

    /**
     * @var SearchCriteriaInterface $searchCriteria
     */
    protected SearchCriteriaInterface $searchCriteria;

    /**
     * @var FilterGroup $filterGroup
     */
    protected FilterGroup $filterGroup;

    /**
     * @var FilterBuilder $filterBuilder
     */
    protected FilterBuilder $filterBuilder;

    /**
     * @var FilterGroupBuilder $filterGroupBuilder
     */
    protected FilterGroupBuilder $filterGroupBuilder;

    /**
     * @var CustomerRepositoryInterface $customerRepository
     */
    protected CustomerRepositoryInterface $customerRepository;

    /**
     * @var Session $session
     */
    protected Session $session;

    /**
     * @var CustomerFactory $customerFactory
     */
    protected CustomerFactory $customerFactory;

    /**
     * @var StoreManagerInterface $storeManager
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     * @param CustomerFactory $customerFactory
     * @param Session $session
     * @param CustomerRepositoryInterface $customerRepository
     * @param SignatureRepository $signatureRepository
     * @param SearchCriteriaInterface $searchCriteria
     * @param FilterGroup $filterGroup
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        StoreManagerInterface        $storeManager,
        CustomerFactory              $customerFactory,
        Session                      $session,
        CustomerRepositoryInterface  $customerRepository,
        SignatureRepository          $signatureRepository,
        SearchCriteriaInterface      $searchCriteria,
        FilterGroup                  $filterGroup,
        FilterBuilder                $filterBuilder,
        FilterGroupBuilder           $filterGroupBuilder,
        Context                      $context,
        Registry                     $registry,
        ExtensionAttributesFactory   $extensionFactory,
        AttributeValueFactory        $customAttributeFactory,
        AbstractResource             $resource = null,
        AbstractDb                   $resourceCollection = null,
        array                        $data = []
    ) {
        $this->storeManager        = $storeManager;
        $this->customerFactory     = $customerFactory;
        $this->session             = $session;
        $this->customerRepository  = $customerRepository;
        $this->signatureRepository = $signatureRepository;
        $this->searchCriteria      = $searchCriteria;
        $this->filterGroup         = $filterGroup;
        $this->filterBuilder       = $filterBuilder;
        $this->filterGroupBuilder  = $filterGroupBuilder;
        parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $resource, $resourceCollection, $data);
    }

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('NWT\Signapp\Model\ResourceModel\Application');
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get default values
     *
     * @return array
     */
    public function getDefaultValues(): array
    {
        return [];
    }

    /**
     * @return ExtensibleDataInterface[]
     * @throws LocalizedException
     */
    public function getSignatures()
    {
        $this->filterGroup->setFilters([
            $this->filterBuilder
                ->setField('app_id')
                ->setConditionType('eq')
                ->setValue($this->getData('app_id'))
                ->create()
        ]);

        $this->searchCriteria->setFilterGroups([$this->filterGroup]);
        $list = $this->signatureRepository->getList($this->searchCriteria);
        return $list->getItems();
    }

    /**
     * @return int
     * @throws LocalizedException
     */
    public function hasInitialSignature()
    {
        $appIdFilter = $this->filterBuilder
                ->setField('app_id')
                ->setConditionType('eq')
                ->setValue($this->getData('app_id'))
                ->create();

        $initiatorFilter = $this->filterBuilder
                ->setField('customer_id')
                ->setConditionType('eq')
                ->setValue($this->getData('initiator'))
                ->create();

        $appIdFilterGroup = $this->filterGroupBuilder->addFilter($appIdFilter)->create();
        $initiatorFilterGroup = $this->filterGroupBuilder->addFilter($initiatorFilter)->create();

        $this->searchCriteria->setFilterGroups([$appIdFilterGroup, $initiatorFilterGroup]);
        $list = $this->signatureRepository->getList($this->searchCriteria);

        return count($list->getItems());
    }

    /**
     * @return ExtensibleDataInterface[]
     * @throws LocalizedException
     * @noinspection DuplicatedCode
     */
    public function getSignatureForCustomer($customer = false): array
    {
        if (!$customer) {
            $customer = $this->session->getCustomer();
        }

        $appIdFilter = $this->filterBuilder
            ->setField('app_id')
            ->setConditionType('eq')
            ->setValue($this->getData('app_id'))
            ->create();

        $initiatorFilter = $this->filterBuilder
            ->setField('customer_id')
            ->setConditionType('eq')
            ->setValue($customer->getId())
            ->create();

        $appIdFilterGroup = $this->filterGroupBuilder->addFilter($appIdFilter)->create();
        $initiatorFilterGroup = $this->filterGroupBuilder->addFilter($initiatorFilter)->create();

        $this->searchCriteria->setFilterGroups([$appIdFilterGroup, $initiatorFilterGroup]);
        $list = $this->signatureRepository->getList($this->searchCriteria);
        return $list->getItems();
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getRequestCustomers()
    {
        $customers = [];

        foreach (json_decode($this->getData('request')) as $request) {
            $websiteID = $this->storeManager->getStore()->getWebsiteId();
            try {
                $customers[] = $this->customerFactory->create()->setWebsiteId($websiteID)->loadByEmail($request[1]);
            } catch (\Exception $e) {
                $this->_logger->warning('SignApp: Could not find customer with email ' . $request[1] . ' -- ' . $e->getMessage());
                continue;
            }
        }

        return $customers;
    }

    /**
     * @return false|CustomerInterface
     */
    public function getInitiatorCustomer()
    {
        try {
            $customer = $this->customerRepository->getById($this->getData('initiator'));
        } catch (LocalizedException $e) {
            return false;
        }
        return $customer;
    }

    /**
     * @return bool
     */
    public function isLocked()
    {
        $timeout = $this->getData('timeout');
        if (is_null($timeout)) return false;
        return time() < strtotime($timeout);
    }

    /**
     * @return Application
     */
    public function lock()
    {
        $this->setData('timeout', time() + $this->_timeout);
        return $this;
    }
}
