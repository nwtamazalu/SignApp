<?php
namespace NWT\Signapp\Observer;

use Amasty\Customform\Api\Data\AnswerInterface;
use Amasty\Customform\Api\Data\FormInterface;
use Amasty\Customform\Model\Mail\Notification;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use NWT\Signapp\Helper\Data as Helper;
use NWT\Signapp\Model\Type;
use Psr\Log\LoggerInterface;
use Magento\Customer\Model\Session;
use Magento\Store\Model\StoreManagerInterface;

class AmastyCustomFormSubmitted implements ObserverInterface
{
    /**
     * @var ManagerInterface
     */
    protected ManagerInterface $messageManager;

    /**
     * @var Notification
     */
    protected Notification $notification;

    /**
     * @var Helper $helper
     */
    protected Helper $helper;

    /**
     * @var LoggerInterface $logger
     */
    protected LoggerInterface $logger;

    /**
     * @var Session $session
     */
    protected Session $session;

    /**
     * @var StoreManagerInterface $storeManager
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var ResponseFactory $responseFactory
     */
    protected ResponseFactory $responseFactory;

    /**
     * @var UrlInterface $url
     */
    protected UrlInterface $url;

    /**
     * @param ManagerInterface $messageManager
     * @param Notification $notification
     * @param Helper $helper
     * @param LoggerInterface $logger
     * @param Session $session
     * @param StoreManagerInterface $storeManager
     * @param ResponseFactory $responseFactory
     * @param UrlInterface $url
     */
    public function __construct(
        ManagerInterface $messageManager,
        Notification $notification,
        Helper $helper,
        LoggerInterface $logger,
        Session $session,
        StoreManagerInterface $storeManager,
        ResponseFactory $responseFactory,
        UrlInterface $url,
    ) {
        $this->messageManager  = $messageManager;
        $this->notification    = $notification;
        $this->helper          = $helper;
        $this->logger          = $logger;
        $this->session         = $session;
        $this->storeManager    = $storeManager;
        $this->responseFactory = $responseFactory;
        $this->url             = $url;
    }

    /**
     * @param Observer $observer
     * @return void|Observer
     * @throws AlreadyExistsException|NoSuchEntityException
     * @noinspection DuplicatedCode
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        $answer = $event->getAnswer();
        $form = $event->getForm();
        $emailTemplate = $this->helper->getConfig('nwt_signapp/general/email_template');

        if (!$form instanceof FormInterface || !$answer instanceof AnswerInterface) {
            return $observer;
        }

        if (!$this->helper->isApplication($form->getFormId(), Type::FORM)) {
            return $observer;
        }

        $customer = $this->session->getCustomer();
        if (!$customer || !$customer->getId()) return $observer;

        $this->logger->info('SignApp: Processing form ' . $form->getFormId());

        $data = json_decode($answer->getResponseJson(), true);

        if (!isset($data['signapp-ssn']['value']) || !isset($data['signapp-email']['value'])) return $observer;

        $ssn = $data['signapp-ssn']['value'];
        $email = $data['signapp-email']['value'];

        // Do something with the data
        $app = $this->helper->createSignatureApplication($ssn, $email, $customer, 2, $this->storeManager->getStore()->getId(), ['Service' => $form->getTitle(), 'data' => $data]);
        if ($this->helper->getConfig('nwt_signapp/general/email_enabled')) {
            $this->helper->sendEmail($customer->getEmail(), ['variables' => ['app' => $app, 'data' => $data]], $emailTemplate);
            $this->logger->info("SignApp: (Form Placed) Email sent! SSN: $ssn / Email: $email / Service: " . $form->getTitle());
        }

        $this->logger->info('SignApp: Finished processing form answer ' . $answer->getAnswerId());

        $redirectionUrl = $this->url->getUrl('sign/applications/index');
        $this->responseFactory->create()->setRedirect($redirectionUrl)->sendResponse();

        return $observer;
    }
}
