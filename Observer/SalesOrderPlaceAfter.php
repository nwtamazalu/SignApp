<?php
/** @noinspection DuplicatedCode */
namespace NWT\Signapp\Observer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use NWT\Signapp\Model\Type;
use Psr\Log\LoggerInterface;
use NWT\Signapp\Helper\Config as ConfigHelper;
use Magento\Catalog\Model\Product\Option\Repository as OptionRepository;
use NWT\Signapp\Helper\Data as Helper;

class SalesOrderPlaceAfter implements ObserverInterface
{
    /**
     * @var LoggerInterface $logger
     */
    protected LoggerInterface $logger;

    /**
     * @var ConfigHelper $config
     */
    protected ConfigHelper $config;

    /**
     * @var OptionRepository $optionRepository
     */
    protected OptionRepository $optionRepository;

    /**
     * @var Helper $helper
     */
    protected Helper $helper;

    /**
     * @var ResponseFactory $responseFactory
     */
    protected ResponseFactory $responseFactory;

    /**
     * @var UrlInterface $url
     */
    protected UrlInterface $url;

    /**
     * @var CustomerRepositoryInterface $customerRepo
     */
    protected CustomerRepositoryInterface $customerRepo;

    /**
     * SalesOrderPlaceAfterObserver constructor.
     * @param LoggerInterface $logger
     * @param ConfigHelper $config
     * @param OptionRepository $optionRepository
     * @param Helper $helper
     * @param ResponseFactory $responseFactory
     * @param UrlInterface $url
     * @param CustomerRepositoryInterface $customerRepo
     */
    public function __construct(
        LoggerInterface $logger,
        ConfigHelper $config,
        OptionRepository $optionRepository,
        Helper $helper,
        ResponseFactory $responseFactory,
        UrlInterface $url,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->logger            = $logger;
        $this->config            = $config;
        $this->optionRepository  = $optionRepository;
        $this->helper            = $helper;
        $this->responseFactory   = $responseFactory;
        $this->url               = $url;
        $this->customerRepo      = $customerRepository;
    }

    /**
     * @param Observer $observer
     * @return Observer
     * @throws AlreadyExistsException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function execute(Observer $observer): Observer
    {
        if (!$this->config->isEnabled()) {
            return $observer;
        }

        // Order
        $order = $observer->getEvent()->getOrder();
        if (
            $order->getState() != 'complete'
            || $order->getStatus() != 'complete'
        ) {
            return $observer;
        }

        // Current customer
        if (!$order->getCustomerId()) return $observer;
        $customer = $this->customerRepo->getById($order->getCustomerId());

        $this->logger->info('SignApp: Processing order ' . $order->getIncrementId());

        $emailTemplate = $this->helper->getConfig('nwt_signapp/general/email_template');

        // Order item iteration
        $orderItems = $order->getItems();
        foreach ($orderItems as $item) {
            $data = [];
            $ssn = $email = false;
            $product = $item->getProduct();
            $this->logger->info('SignApp: Processing product ' . $product->getSku() . ' / ' . $product->getId());
            if ($this->helper->isApplication($product->getId(), Type::PRODUCT)) {
                $this->logger->info('SignApp: Product ' . $product->getSku() . ' is_application_product OK');
                $options = $item->getProductOptions();
                if (!isset($options['options'])) continue;
                foreach ($options['options'] as $o) {
                    $optionModel = $this->optionRepository->get($item->getSku(), $o['option_id']);
                    switch (strtoupper($optionModel->getTitle())) {
                        case 'SSN':
                            $ssn = $o['value'];
                            break;
                        case 'EMAIL':
                            $email = $o['value'];
                            break;
                        default:
                            $data[$o['option_id']] = [
                                'value' => $o['value'],
                                'label' => $optionModel->getTitle()
                            ];
                    }
                }
            }
            if (!$email || !$ssn) continue;
            // Do something with the data
            $app = $this->helper->createSignatureApplication($ssn, $email, $customer, 1, $order->getStoreId(), ['Service' => $product->getName(), 'data' => $data]);
            if ($this->helper->getConfig('nwt_signapp/general/email_enabled')) {
                $this->helper->sendEmail($customer->getEmail(), ['variables' => ['app' => $app, 'data' => $data]], $emailTemplate);
                $this->logger->info("SignApp: (Order Placed) Email sent! SSN: $ssn / Email: $email / Service: " . $product->getName());
            }
        }

        $this->logger->info('SignApp: Finished processing order ' . $order->getIncrementId());

        $redirectionUrl = $this->url->getUrl('sign/applications/index');
        $this->responseFactory->create()->setRedirect($redirectionUrl)->sendResponse();

        return $observer;
    }
}
