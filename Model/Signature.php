<?php
namespace NWT\Signapp\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class Signature extends AbstractExtensibleModel implements IdentityInterface
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'nwt_signapp_signatures';

    /**
     * @var string
     */
    protected $_cacheTag = 'nwt_signapp_signatures';

    /**
     * @var string
     */
    protected $_eventPrefix = 'nwt_signapp_signatures';

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('NWT\Signapp\Model\ResourceModel\Signature');
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get default values
     *
     * @return array
     */
    public function getDefaultValues(): array
    {
        return [];
    }
}
