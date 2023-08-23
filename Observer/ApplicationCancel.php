<?php
namespace NWT\Signapp\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use NWT\Signapp\Helper\Data as Helper;
use Psr\Log\LoggerInterface;
use Magento\Customer\Model\Session;

class ApplicationCancel implements ObserverInterface
{
    /**
     * @var Helper $helper
     */
    private Helper $helper;

    /**
     * @var Session $session
     */
    private Session $session;

    /**
     * @var LoggerInterface $logger
     */
    protected LoggerInterface $logger;

    public function __construct(
        Helper $helper,
        LoggerInterface $logger,
        Session $session
    ) {
        $this->helper         = $helper;
        $this->logger         = $logger;
        $this->session        = $session;
    }

    /**
     * @param Observer $observer
     * @return Observer
     */
    public function execute(Observer $observer): Observer
    {
        $app = $observer->getEvent()->getApp();
        $request = $observer->getEvent()->getRequest();
        $initiator = $observer->getEvent()->getInitiator();
        $adminEmail = $this->helper->getConfig('nwt_signapp/general/email_cancel_admin');
        $adminEmailEnabled = $this->helper->getConfig('nwt_signapp/general/email_cancel_admin_email');
        $emailEnabled = $this->helper->getConfig('nwt_signapp/general/email_cancel_enabled');

        if ($emailEnabled) {
            // Initiator
            $this->helper->sendEmail($initiator->getEmail(), ['variables' => ['app' => $app]], 'nwt_signapp_general_email_template_cancel');

            // Request
            $request = json_decode($request);
            foreach ($request as $r) {
                // Who signed? Send to them
                if ($this->helper->getConfig('nwt_signapp/general/email_cancel_enabled') && isset($r[1])) {
                    $this->helper->sendEmail($r[1], ['variables' => ['app' => $app]], 'nwt_signapp_general_email_template_cancel');
                    $this->logger->info("SignApp: (Signature) Email sent! To: " . $r[1] . " / App ID: " . $app->getId());
                }
            }
        }

        if ($adminEmailEnabled && strlen($adminEmail)) {
            $this->helper->sendEmail($initiator->getEmail(), ['variables' => ['app' => $app]], 'nwt_signapp_general_email_template_cancel_admin');
        }

        $this->logger->info('SignApp: Canceled app ID ' . $app->getId());

        return $observer;
    }
}
