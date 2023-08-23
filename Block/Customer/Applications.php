<?php
namespace NWT\Signapp\Block\Customer;

use Magento\Customer\Model\Customer;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use NWT\Signapp\Model\ApplicationRepository;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\FilterBuilder;

/**
 * Class Applications
 *
 * @package NWT\Signapp\Block\Customer
 */
class Applications extends Template
{
    /**
     * @var Customer $currentCustomer
     */
    public Customer $currentCustomer;

    /**
     * @var Session $customerSession
     */
    protected Session $customerSession;

    /**
     * @var $applications
     */
    protected $applications;

    /**
     * @var SearchCriteriaInterface $searchCriteria
     */
    protected SearchCriteriaInterface $searchCriteria;

    /**
     * @var ApplicationRepository $applicationRepository
     */
    protected ApplicationRepository $applicationRepository;

    /**
     * @var FilterGroup $filterGroup
     */
    protected FilterGroup $filterGroup;

    /**
     * @var FilterBuilder $filterBuilder
     */
    protected FilterBuilder $filterBuilder;

    /**
     * @var SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     */
    protected SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory;

    /**
     * @param Template\Context $context
     * @param Session $session
     * @param ApplicationRepository $applicationRepository
     * @param SearchCriteriaInterface $searchCriteria
     * @param FilterGroup $filterGroup
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Session $session,
        ApplicationRepository $applicationRepository,
        SearchCriteriaInterface $searchCriteria,
        FilterGroup $filterGroup,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        array $data = []
    ) {
        $this->customerSession              = $session;
        $this->applicationRepository        = $applicationRepository;
        $this->searchCriteria               = $searchCriteria;
        $this->filterGroup                  = $filterGroup;
        $this->filterBuilder                = $filterBuilder;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;

        $this->currentCustomer              = $this->customerSession->getCustomer();

        parent::__construct($context, $data);
    }

    /**
     * @return Template
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function _prepareLayout()
    {
        if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbs->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            )->addCrumb(
                'my account',
                [
                    'label' => __('My account'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl() . 'customer/account']
            )->addCrumb(
                'my dogs',
                ['label' => __('My Signature Applications')]
            );
        }

        return parent::_prepareLayout();
    }

    /**
     * @return ExtensibleDataInterface[]
     * @throws LocalizedException
     */
    public function getApplications()
    {
        $customerId = $this->customerSession->getCustomerId();
        $this->filterGroup->setFilters([
            $this->filterBuilder
                ->setField('initiator')
                ->setConditionType('eq')
                ->setValue($customerId)
                ->create()
        ]);

        $this->searchCriteria->setFilterGroups([$this->filterGroup]);
        $list = $this->applicationRepository->getList($this->searchCriteria);
        $items = $list->getItems();

        return $items;
    }

    /**
     * @return ExtensibleDataInterface[]
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getRequests()
    {
        $customerEmail = $this->customerSession->getCustomer()->getEmail();
        $customerSsn   = $this->customerSession->getCustomer()->getData('bankid_personalnumber');

        $search = $this->searchCriteriaBuilderFactory->create();
        $search->addFilter('request', '%'.$customerSsn.'%', 'like');
        $search->addFilter('request', '%'.$customerEmail.'%', 'like');
        $search->addFilter('store_id', $this->_storeManager->getStore()->getId(), 'eq');
        $list = $this->applicationRepository->getList($search->create());
        $items = $list->getItems();

        $parsedItems = [];
        foreach ($items as $item) {
            // If request data is empty, skip it
            if (strlen($item->getData('request')) == 0) continue;
            $req = json_decode($item->getData('request'));
            // If request data is not valid json or empty, skip it
            if (!is_array($req) || !count($req)) continue;

            // Signed by initiator only
            if (!$item->hasInitialSignature()) continue;

            foreach ($req as $r) {
                if ($r[0] == $customerSsn && $r[1] == $customerEmail) {
                    $parsedItems[] = $item;
                }
            }
        }

        return $parsedItems;
    }
}
