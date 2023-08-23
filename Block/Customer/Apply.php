<?php
namespace NWT\Signapp\Block\Customer;

use Magento\Framework\App\Response\Http;
use Magento\Framework\App\Response\HttpInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template\Context;
use NWT\Signapp\Api\Data\ApplicationInterface;
use NWT\Signapp\Model\Application;
use NWT\Signapp\Model\ApplicationRepository;
use NWT\Signapp\Helper\Data as Helper;
use Magento\Customer\Model\Session;

/**
 * Class Applications
 *
 * @package NWT\Signapp\Block\Customer
 */
class Apply extends Template
{
    /**
     * @var Template\Context $context
     */
    protected Template\Context $context;

    /**
     * @var ApplicationRepository
     */
    protected ApplicationRepository $applicationRepository;

    /**
     * @var ManagerInterface $messageManager
     */
    protected ManagerInterface $messageManager;

    /**
     * @var Http $http
     */
    protected Http $http;

    /**
     * @var Helper $helper
     */
    public Helper $helper;

    /**
     * @var Session $customerSession
     */
    public Session $customerSession;

    /**
     * @param Context $context
     * @param ApplicationRepository $applicationRepository
     * @param ManagerInterface $messageManager
     * @param Http $http
     * @param Helper $helper
     * @param Session $customerSession
     * @param array $data
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    public function __construct(
        Template\Context $context,
        ApplicationRepository $applicationRepository,
        ManagerInterface $messageManager,
        Http $http,
        Helper $helper,
        Session $customerSession,
        array $data = []
    ) {
        $this->context               = $context;
        $this->applicationRepository = $applicationRepository;
        $this->messageManager        = $messageManager;
        $this->http                  = $http;
        $this->helper                = $helper;
        $this->customerSession       = $customerSession;

        $this->applicationLock();

        parent::__construct($context, $data);
    }

    /**
     * @return Template
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @noinspection DuplicatedCode
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
                ['label' => __('New Signature')]
            );
        }

        return parent::_prepareLayout();
    }

    /**
     * @return Http|HttpInterface|ApplicationInterface|Application
     */
    public function getSignatureRequest()
    {
        $id = $this->context->getRequest()->getParam('id');
        try {
            $application = $this->applicationRepository->getById($id);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage(__('Error: ') . $e->getMessage());
            return $this->http->setRedirect($this->getUrl('sign/applications'));
        }
        return $application;
    }

    /**
     * @return void
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    public function applicationLock()
    {
        $request = $this->getSignatureRequest();
        if (!$request->isLocked() && ($this->customerSession->getCustomerId() != $request->getInitiator())) {
            $request->lock();
            $this->applicationRepository->save($request);
        }
    }
}
