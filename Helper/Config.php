<?php
namespace NWT\Signapp\Helper;

use Magento\Contact\Model\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    const XML_PATH_ENABLED = 'nwt_signapp/general/enabled';

    const XML_PATH_EMAIL_TEMPLATE = 'nwt_signapp/general/email_template';

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * AdminFailed constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->getScopeConfigFlag(self::XML_PATH_ENABLED);
    }

    /**
     * @return string
     */
    public function getEmailTemplateId(): string
    {
        return $this->getScopeConfigValue(self::XML_PATH_EMAIL_TEMPLATE);
    }

    /**
     * @return string
     */
    public function getDefaultEmailSender(): string
    {
        return $this->getScopeConfigValue(ConfigInterface::XML_PATH_EMAIL_SENDER);
    }

    /**
     * @param string $xmlPath
     * @param string $scope
     * @return bool
     */
    protected function getScopeConfigFlag(string $xmlPath, string $scope = ScopeInterface::SCOPE_STORE): bool
    {
        return $this->scopeConfig->isSetFlag($xmlPath, $scope);
    }

    /**
     * @param string $xmlPath
     * @param string $scope
     * @return mixed
     */
    protected function getScopeConfigValue(string $xmlPath, string $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue($xmlPath, $scope);
    }
}
