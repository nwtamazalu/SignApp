<?php
namespace NWT\Signapp\Controller\Sign;

use Magento\Customer\Model\Session;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use NWT\Signapp\Api\SignatureRepositoryInterface;
use NWT\Signapp\Model\ApplicationRepository;
use NWT\Signapp\Model\ResourceModel\Application;
use NWT\Signapp\Model\ResourceModel\Signature;

class Cancel implements ActionInterface
{
    /**
     * @var Context                          $context
     */
    private Context                          $context;

    /**
     * @var Session                          $customerSession
     */
    protected Session                        $customerSession;

    /**
     * @var EventManager                     $eventManager
     */
    protected EventManager                   $eventManager;

    /**
     * @var SignatureRepositoryInterface     $signatureRepository
     */
    protected SignatureRepositoryInterface   $signatureRepository;

    /**
     * @var ApplicationRepository            $applicationRepository
     */
    protected ApplicationRepository          $applicationRepository;

    /**
     * @var FilterGroup                      $filterGroup
     */
    protected FilterGroup                    $filterGroup;

    /**
     * @var FilterBuilder                    $filterBuilder
     */
    protected FilterBuilder                  $filterBuilder;

    /**
     * @var SearchCriteriaBuilderFactory     $searchCriteriaBuilderFactory
     */
    protected SearchCriteriaBuilderFactory   $searchCriteriaBuilderFactory;

    /**
     * @var Signature                        $signatureModel
     */
    protected Signature                      $signatureModel;

    /**
     * @var Application                      $applicationModel
     */
    protected Application                    $applicationModel;

    /**
     * @var RedirectFactory                  $resultRedirectFactory
     */
    protected RedirectFactory                $resultRedirectFactory;

    /**
     * @var ManagerInterface                 $messageManager
     */
    protected ManagerInterface               $messageManager;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param EventManager $eventManager
     * @param SignatureRepositoryInterface $signatureRepository
     * @param ApplicationRepository $applicationRepository
     * @param FilterGroup $filterGroup
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param Signature $signatureModel
     * @param Application $applicationModel
     * @param RedirectFactory $resultRedirectFactory
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Context                              $context,
        Session                              $customerSession,
        EventManager                         $eventManager,
        SignatureRepositoryInterface         $signatureRepository,
        ApplicationRepository                $applicationRepository,
        FilterGroup                          $filterGroup,
        FilterBuilder                        $filterBuilder,
        SearchCriteriaBuilderFactory         $searchCriteriaBuilderFactory,
        Signature                            $signatureModel,
        Application                          $applicationModel,
        RedirectFactory                      $resultRedirectFactory,
        ManagerInterface                     $messageManager
    ) {
        $this->context                       = $context;
        $this->customerSession               = $customerSession;
        $this->eventManager                  = $eventManager;
        $this->signatureRepository           = $signatureRepository;
        $this->applicationRepository         = $applicationRepository;
        $this->filterGroup                   = $filterGroup;
        $this->filterBuilder                 = $filterBuilder;
        $this->searchCriteriaBuilderFactory  = $searchCriteriaBuilderFactory;
        $this->signatureModel                = $signatureModel;
        $this->applicationModel              = $applicationModel;
        $this->resultRedirectFactory         = $resultRedirectFactory;
        $this->messageManager                = $messageManager;
    }

    /**
     * @return Redirect
     * @throws LocalizedException
     */
    public function execute(): Redirect
    {
        $id = $this->context->getRequest()->getParam('id');
        $app = $this->applicationRepository->getById($id);
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sign/applications/index');

        // Barrier: Application is locked and user is initiator
        if ($app->isLocked() && $this->customerSession->getCustomerId() == $app->getInitiator()) {
            $this->messageManager->addErrorMessage('Application is locked.');
            return $resultRedirect;
        }

        // Barrier: >= 2 signatures present
        if (count($app->getSignatures()) >= 2) {
            $this->messageManager->addErrorMessage('Could not remove application.');
            return $resultRedirect;
        }

        // Barrier: current customer is not part of request
        $requestCustomers = $app->getRequestCustomers();
        $accessGranted = false;
        foreach ($requestCustomers as $customer) {
            if ($this->customerSession->getCustomerId() == $customer->getId()) $accessGranted = true;
        }
        if (!$accessGranted) {
            $this->messageManager->addErrorMessage('Access denied.');
            return $resultRedirect;
        }

        try {
            $search = $this->searchCriteriaBuilderFactory->create();
            $this->filterGroup->setFilters([
                $this->filterBuilder
                    ->setField('app_id')
                    ->setConditionType('eq')
                    ->setValue($id)
                    ->create()
            ]);
            $search->setFilterGroups([$this->filterGroup]);
            $signatures = $this->signatureRepository->getList($search->create());

            foreach ($signatures->getItems() as $signature) {
                $this->signatureModel->delete($signature);
            }
            $this->applicationModel->delete($app);
            $this->eventManager->dispatch('nwt_signapp_sign_cancel', ['app' => $app, 'customer' => $this->customerSession->getCustomer(), 'request' => $app->getRequest(), 'initiator' => $app->getInitiatorCustomer()]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage('Could not remove application: ' . $e->getMessage());
        }

        return $resultRedirect;
    }
}
