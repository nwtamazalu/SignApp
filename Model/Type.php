<?php
namespace NWT\Signapp\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class Type extends AbstractExtensibleModel implements IdentityInterface
{
    const PRODUCT = 1;
    const FORM = 2;

    const TYPE_LABELS = [1 => 'PRODUCT', 2 => 'FORM'];

    /**
     * Cache tag
     */
    const CACHE_TAG = 'nwt_signapp_application_types';

    /**
     * @var string
     */
    protected $_cacheTag = 'nwt_signapp_application_types';

    /**
     * @var string
     */
    protected $_eventPrefix = 'nwt_signapp_application_types';

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('NWT\Signapp\Model\ResourceModel\Type');
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
