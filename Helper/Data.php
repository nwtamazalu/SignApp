<?php
namespace NWT\Signapp\Helper;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use NWT\Signapp\Model\Application;
use NWT\Signapp\Model\ApplicationFactory;
use NWT\Signapp\Model\ResourceModel\Application as ApplicationResource;
use NWT\Signapp\Mail\Template\TransportBuilder;
use Psr\Log\LoggerInterface;
use NWT\Signapp\Model\TypeRepository;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroupFactory;
use Magento\Framework\Api\FilterBuilder;

class Data
{
    /**
     * @var CustomerRepositoryInterface $customerRepositoryInterface
     */
    protected CustomerRepositoryInterface $customerRepositoryInterface;

    /**
     * @var ApplicationFactory $applicationFactory
     */
    protected ApplicationFactory $applicationFactory;

    /**
     * @var ApplicationResource $applicationResource
     */
    protected ApplicationResource $applicationResource;

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @var TransportBuilder $transportBuilder
     */
    protected TransportBuilder $transportBuilder;

    /**
     * @var LoggerInterface $logger
     */
    protected LoggerInterface $logger;

    /**
     * @var TypeRepository $typeRepository
     */
    protected TypeRepository $typeRepository;

    /**
     * @var SearchCriteriaInterface $searchCriteria
     */
    protected SearchCriteriaInterface $searchCriteria;

    /**
     * @var FilterGroupFactory $filterGroupFactory
     */
    protected FilterGroupFactory $filterGroupFactory;

    /**
     * @var FilterBuilder $filterBuilder
     */
    protected FilterBuilder $filterBuilder;

    /**
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param ApplicationFactory $applicationFactory
     * @param ApplicationResource $applicationResource
     * @param ScopeConfigInterface $scopeConfig
     * @param TransportBuilder $transportBuilder
     * @param LoggerInterface $logger
     * @param TypeRepository $typeRepository
     * @param SearchCriteriaInterface $searchCriteria
     * @param FilterGroupFactory $filterGroupFactory
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepositoryInterface,
        ApplicationFactory $applicationFactory,
        ApplicationResource $applicationResource,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        LoggerInterface $logger,
        TypeRepository $typeRepository,
        SearchCriteriaInterface $searchCriteria,
        FilterGroupFactory $filterGroupFactory,
        FilterBuilder $filterBuilder
    ) {
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->applicationFactory          = $applicationFactory;
        $this->applicationResource         = $applicationResource;
        $this->scopeConfig                 = $scopeConfig;
        $this->transportBuilder            = $transportBuilder;
        $this->logger                      = $logger;
        $this->typeRepository              = $typeRepository;
        $this->searchCriteria              = $searchCriteria;
        $this->filterGroupFactory          = $filterGroupFactory;
        $this->filterBuilder               = $filterBuilder;
    }

    /**
     * @param string $ssn
     * @return bool|CustomerInterface
     */
    public function getCustomerBySsn(string $ssn)
    {
        $customer = false;
        try {
            $filter1 = $this->filterBuilder
                ->setField('bankid_personalnumber')
                ->setConditionType('eq')
                ->setValue($ssn)
                ->create();
            $filterGroup1 = $this->filterGroupFactory->create()->setFilters([$filter1]);
            $search = $this->searchCriteria->setfilterGroups([$filterGroup1]);
            $customer = $this->customerRepositoryInterface->getList($search);
            if ($customer->getTotalCount()) {
                $customer = current($customer->getItems());
            } else return false;
        } catch (LocalizedException|NoSuchEntityException $e) {
            return false;
        }

        return $customer;
    }

    /**
     * @param string $ssn
     * @param string $email
     * @param $customer
     * @param int $type
     * @param int $storeId
     * @param array $data
     * @return ApplicationResource
     * @throws AlreadyExistsException
     */
    public function createSignatureApplication(string $ssn, string $email, $customer, int $type, int $storeId, array $data)
    {
        $app = $this->applicationFactory->create();
        $app->setData('initiator', $customer->getId());
        $app->setData('type', $type);
        $app->setData('request', json_encode([[$ssn, $email]]));
        $app->setData('extra_data', json_encode($data));
        $app->setData('store_id', $storeId);
        return $this->applicationResource->save($app);
    }

    /**
     * @param $key
     * @param string $scope
     * @param int $scopeId
     * @return mixed
     */
    public function getConfig($key, string $scope = ScopeInterface::SCOPE_STORE, int $scopeId = 0): mixed
    {
        return $this->scopeConfig->getValue($key, $scope, $scopeId);
    }

    /**
     * @param $to
     * @param $data
     * @param $template
     * @param bool $enableBcc
     * @return void
     */
    public function sendEmail($to, $data, $template, mixed $enableBcc = false): void
    {
        /** @noinspection DuplicatedCode */
        try {
            $email = $this->scopeConfig->getValue('trans_email/ident_support/email', ScopeInterface::SCOPE_STORE);
            $name  = $this->scopeConfig->getValue('trans_email/ident_support/name', ScopeInterface::SCOPE_STORE);

            $this->transportBuilder->setTemplateIdentifier($template);
            $this->transportBuilder->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => 0]);
            $this->transportBuilder->setTemplateVars(['data' => $data['variables']]);
            $this->transportBuilder->setFromByScope(['email' => $email, 'name' => $name]);
            $this->transportBuilder->addTo($to);

            // Not implemented, may be useful
            $bcc  = $this->scopeConfig->getValue('nwt_signapp/general/bcc', ScopeInterface::SCOPE_STORE);
            if ($enableBcc && strlen($bcc)) {
                $this->transportBuilder->addBcc($bcc);
            }

            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();

            $this->logger->info(__('SignApp: Email has been sent to') . ' ' . $to);
        } catch (MailException $me) {
            $this->logger->error(__('SignApp: MailException: %1', $me->getMessage()));
        } catch (\Exception $e) {
            $this->logger->error(__('SignApp: Exception: %1', $e->getMessage()));
        }
    }

    /**
     * @param $appId
     * @return Application
     */
    public function getApplicationById($appId): Application
    {
        $app = $this->applicationFactory->create();

        try {
            $this->applicationResource->load($app, $appId);
        } catch (\Exception $e) {
            $this->logger->error('SignApp: ' . $e->getMessage());
        }

        return $app;
    }

    /**
     * @param $id
     * @param $type
     * @return bool
     */
    public function isApplication($id, $type)
    {
        $result = false;
        try {
            $filter1 = $this->filterBuilder
                ->setField('sign_request_type')
                ->setConditionType('eq')
                ->setValue($type)
                ->create();
            $filter2 = $this->filterBuilder
                ->setField('sign_request_type_id')
                ->setConditionType('eq')
                ->setValue($id)
                ->create();
            $filterGroup1 = $this->filterGroupFactory->create()->setFilters([$filter1]);
            $filterGroup2 = $this->filterGroupFactory->create()->setFilters([$filter2]);

            $search = $this->searchCriteria->setfilterGroups([$filterGroup1, $filterGroup2]);
            $result = $this->typeRepository->getList($search);
        } catch (\Exception $e) {
            return false;
        }

        return ($result->getTotalCount() > 0);
    }
}
