<?php
namespace NWT\Signapp\Block\Customer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\View;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\StringUtils;

/**
 * Class Applications
 *
 * @package NWT\Signapp\Block\Customer
 */
class Checkssn extends View
{

    /**
     * @var EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var PriceCurrencyInterface
     * @deprecated 102.0.0
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var Product
     */
    protected $_productHelper;

    /**
     * @var ConfigInterface
     */
    protected $productTypeConfig;

    /**
     * @var FormatInterface
     */
    protected $_localeFormat;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param Context $context
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param EncoderInterface $jsonEncoder
     * @param StringUtils $string
     * @param Product $productHelper
     * @param ConfigInterface $productTypeConfig
     * @param FormatInterface $localeFormat
     * @param Session $customerSession
     * @param ProductRepositoryInterface|PriceCurrencyInterface $productRepository
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     * @codingStandardsIgnoreStart
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context                                 $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        EncoderInterface                        $jsonEncoder,
        StringUtils                             $string,
        Product                                 $productHelper,
        ConfigInterface                         $productTypeConfig,
        FormatInterface                         $localeFormat,
        Session                                 $customerSession,
        ProductRepositoryInterface              $productRepository,
        PriceCurrencyInterface                  $priceCurrency,
        array                                   $data = []
    ) {
        $this->_productHelper = $productHelper;
        $this->urlEncoder = $urlEncoder;
        $this->_jsonEncoder = $jsonEncoder;
        $this->productTypeConfig = $productTypeConfig;
        $this->string = $string;
        $this->_localeFormat = $localeFormat;
        $this->customerSession = $customerSession;
        $this->productRepository = $productRepository;
        $this->priceCurrency = $priceCurrency;
        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data
        );
    }

}
