<?php
namespace NWT\Signapp\Block\Customer;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\DefaultPathInterface;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Html\Link\Current;
use Magento\Framework\View\Element\Template\Context;

class Link extends Current
{
    /**
     * @var CustomerSession $_customerSession
     */
    protected CustomerSession $_customerSession;

    /**
     * @param Context $context
     * @param DefaultPathInterface $defaultPath
     * @param CustomerSession $customerSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        DefaultPathInterface $defaultPath,
        CustomerSession $customerSession,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        parent::__construct($context, $defaultPath, $data);
    }

    /**
     * @return string|null
     */
    protected function _toHtml()
    {
        $html = null;
        if ($this->_customerSession->isLoggedIn()) {
            $html = '<li class="nav item"><a href="/' . $this->getPath() . '">' . $this->escapeHtml((string)new Phrase($this->getLabel())) . '</a></li>';
        }
        return $html;
    }
}
