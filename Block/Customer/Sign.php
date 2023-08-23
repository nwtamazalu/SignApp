<?php
/** @noinspection DuplicatedCode */
namespace NWT\Signapp\Block\Customer;

use Magento\Framework\App\Response\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template\Context;
use NWT\Signapp\Api\Data\ApplicationInterface;
use NWT\Signapp\Model\Application;
use NWT\Signapp\Model\ApplicationRepository;

/**
 * Class Applications
 *
 * @package NWT\Signapp\Block\Customer
 */
class Sign extends Template
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
     * @param Context $context
     * @param ApplicationRepository $applicationRepository
     * @param ManagerInterface $messageManager
     * @param Http $http
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        ApplicationRepository $applicationRepository,
        ManagerInterface $messageManager,
        Http $http,
        array $data = []
    ) {
        $this->context               = $context;
        $this->applicationRepository = $applicationRepository;
        $this->messageManager        = $messageManager;
        $this->http                  = $http;
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
                ['label' => __('New Signature')]
            );
        }

        return parent::_prepareLayout();
    }

    /**
     * @return Application|ApplicationInterface
     */
    public function getSignatureRequest()
    {
        $id = $this->context->getRequest()->getParam('app_id');
        try {
            $application = $this->applicationRepository->getById($id);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage(__('Error: ') . $e->getMessage());
            $this->http->setRedirect($this->getUrl('sign/applications'));
            exit;
        }
        return $application;
    }
}
