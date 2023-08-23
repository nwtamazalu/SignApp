<?php
namespace NWT\Signapp\Observer;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use NWT\Signapp\Helper\Data as Helper;
use NWT\Signapp\Model\Application;
use NWT\Signapp\Model\Signature;
use Psr\Log\LoggerInterface;
use Magento\Customer\Model\Session;
use TCPDF_FONTS;
use TCPDI;
use Magento\Store\Model\StoreManagerInterface;

class SignComplete implements ObserverInterface
{
    /**
     * @var ManagerInterface
     */
    private ManagerInterface $messageManager;

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

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var DirectoryList $directoryList
     */
    protected DirectoryList $directoryList;

    /**
     * @var StoreManagerInterface $storeManager
     */
    protected StoreManagerInterface $storeManager;

    private $textSize = 16;

    /**
     * @param ManagerInterface $messageManager
     * @param Helper $helper
     * @param LoggerInterface $logger
     * @param Session $session
     * @param Filesystem $filesystem
     * @param DirectoryList $directoryList
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ManagerInterface $messageManager,
        Helper $helper,
        LoggerInterface $logger,
        Session $session,
        Filesystem $filesystem,
        DirectoryList $directoryList,
        StoreManagerInterface $storeManager
    ) {
        $this->messageManager = $messageManager;
        $this->helper         = $helper;
        $this->logger         = $logger;
        $this->session        = $session;
        $this->filesystem     = $filesystem;
        $this->directoryList  = $directoryList;
        $this->storeManager   = $storeManager;
    }

    /**
     * @param Observer $observer
     * @return Observer
     * @throws LocalizedException
     */
    public function execute(Observer $observer): Observer
    {
        $appId = $observer->getEvent()->getAppId();
        $app = $this->helper->getApplicationById($appId);
        $request = json_decode($app->getRequest());
        $signatures = $app->getSignatures();
        $emailTemplate = $this->helper->getConfig('nwt_signapp/general/email_template_success');
        $completeTemplate = $this->helper->getConfig('nwt_signapp/general/email_template_complete');

        if ($this->helper->getConfig('nwt_signapp/general/email_success_enabled')) {
            $to = $this->session->getCustomer()->getEmail();
            $this->logger->info("SignApp: (Signature) Sending notification email To: $to / App ID: $appId");
            $this->helper->sendEmail($to, ['variables' => ['app' => $app]], $emailTemplate);

            $this->logger->info("SignApp: (Signature) Sending additional email To: " . $app->getInitiatorCustomer()->getEmail() . " / App ID: $appId");
            $this->helper->sendEmail($app->getInitiatorCustomer()->getEmail(), ['variables' => ['app' => $app]], $emailTemplate);
        }

        if (count($signatures) == count($request) + 1) {
            if ($this->helper->getConfig('nwt_signapp/general/email_success_admin_enabled') == 1) {
                $this->helper->sendEmail($this->helper->getConfig(
                    'nwt_signall/general/email_admin_success_address'),
                    ['variables' => ['app' => $app]],
                    $this->helper->getConfig('nwt_signapp/general/email_success_admin_template')
                );
            }

            $pdf = false;
            if ($this->helper->getConfig('nwt_signapp/pdf/pdf_enabled') == 1) {
                $pdf = $this->generatePdf($app, $signatures);
            }

            if ($this->helper->getConfig('nwt_signapp/general/email_complete_email') == 1) {
                $this->helper->sendEmail($app->getInitiatorCustomer()->getEmail(), ['variables' => ['app' => $app]], $completeTemplate);
                foreach ($request as $r) {
                    $this->helper->sendEmail($r[1], ['variables' => ['app' => $app, 'pdf' => $pdf]], $completeTemplate);
                }
            }
        }

        return $observer;
    }

    /** @noinspection DuplicatedCode */
    /**
     * @return string[]|bool
     * @throws FileSystemException|NoSuchEntityException
     * @throws LocalizedException
     */
    public function generatePdf(Application $app, array $signatures)
    {
        $fileName = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath() . 'signature_template/'. $this->helper->getConfig('nwt_signapp/pdf/pdf_template');

        //
        // TCPDF
        //
        // Get initial orientation
        $pdf = new TCPDI();
        $pdf->setSourceFile($fileName);
        $idx = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($idx);
        $orientation = ($size['w'] > $size['h'] ? 'L' : 'P');

        // Import template, set orientation
        $pdf = new TCPDI($orientation, 'pt');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->setSourceFile($fileName);
        $idx = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($idx);
        $orientation = ($size['w'] > $size['h'] ? 'L' : 'P');

        // First page
        $pdf->AddPage($orientation);
        $pdf->setPageFormatFromTemplatePage(1, $orientation);
        $pdf->useTemplate($idx, null, null, $size['w'], $size['h'], true);
        $pdf->setFontSubsetting(false);
        $font = $this->helper->getConfig('nwt_signapp/pdf/text_font');
        // $originalSpacing = $pdf->getFontSpacing();
        // $originalStretching = $pdf->getFontStretching();
        if ($pdf->getNumPages() >= 1) {
            $pdf->setTextColor(0,0,0);
            $pdf->SetFont($font);
            $pdf->setFontSize($this->textSize);
        }
        $textWidth = $pdf->getPageWidth() - 50;
        $textHeight = $pdf->getPageHeight() - 100;

        //
        // PAGE 1
        //
        $pdf->setPage(1);

        $text = [];

        $text[] = __("Signature request");
        $text[] = "";
        $text[] = __("Application ID: ") . $app->getId();
        $text[] = __("Initiated by: ")
                . $app->getInitiatorCustomer()->getName()
                . ' (' . $app->getInitiatorCustomer()->getEmail() . ') - '
                . $app->getInitiatorCustomer()->getCustomAttribute('bankid_personalnumber')->getValue();
        $text[] = __("Requested from:");

        foreach ($app->getRequestCustomers() as $customer) {
            $text[] = $customer->getName()
                    . ' (' . $customer->getEmail() . ') - '
                    . $customer->getData('bankid_personalnumber');
        }

        $pdf->MultiCell($textWidth, $textHeight, implode("\n", $text), 0, 'L');

        //
        // PAGE 2
        //
        $pdf->AddPage($orientation);
        $pdf->setPage(2);

        $text = [];

        foreach ($app->getSignatures() as $key => $signature) {
            $text[] = 'Signature #' . $signature->getId() . ' : ' . sha1($signature->getSignature());
            $text[] = "";
        }

        $pdf->MultiCell($textWidth, $textHeight, implode("\n", $text), 0, 'L');

        // Save PDF
        $fileName = sha1($app->getId()) . '.pdf';

        $pdf->Output($this->directoryList->getPath('tmp') . '/' . $fileName, "F");

        if (is_file($this->filesystem->getDirectoryRead(DirectoryList::TMP)->getAbsolutePath() . '/' . $fileName)) {
            rename(
                $this->filesystem->getDirectoryRead(DirectoryList::TMP)->getAbsolutePath()
                . '/'
                . $fileName,
                $this->getDownloadPath() . $fileName
            );

            return [
                'fileName' => $fileName,
                'fullPath' => $this->getDownloadPath() . $fileName,
                'fileUrl' => $this->getDownloadUrl() . $fileName
            ];
        }

        return false;
    }

    /**
     * @return string
     * @throws FileSystemException
     */
    private function getDownloadPath(): string
    {
        $path = $this->filesystem
                ->getDirectoryWrite(DirectoryList::MEDIA)
                ->getAbsolutePath() . 'signature_pdf/';
        if (!is_dir($path)) {
            $u = umask();
            umask(022);
            mkdir($path);
            umask($u);
        }
        return $path;
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    private function getDownloadUrl(): string
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'signature_pdf/';
    }
}
