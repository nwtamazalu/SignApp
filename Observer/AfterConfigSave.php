<?php
/** @noinspection DuplicatedCode */
namespace NWT\Signapp\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Filesystem;
use NWT\Signapp\Helper\Data as Helper;
use Magento\Framework\App\Filesystem\DirectoryList;

class AfterConfigSave implements ObserverInterface
{
    /**
     * @var Helper $helper
     */
    protected Helper $helper;

    /**
     * @var Filesystem $filesystem
     */
    protected Filesystem $filesystem;

    /**
     * SalesOrderPlaceAfterObserver constructor.
     * @param Helper $helper
     * @param Filesystem $filesystem
     */
    public function __construct(
        Helper $helper,
        Filesystem $filesystem
    ) {
        $this->helper     = $helper;
        $this->filesystem = $filesystem;
    }

    /**
     * Clean up old PDF templates
     *
     * @param Observer $observer
     * @return Observer
     */
    public function execute(Observer $observer): Observer
    {
        $template = $this->helper->getConfig('nwt_signapp/pdf/pdf_template');
        $mediapath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        foreach (glob($mediapath . '/signature_template/default/*.pdf') as $file) {
            if (basename($file) != basename($template)) {
                unlink($file);
            }
        }
        return $observer;
    }
}
