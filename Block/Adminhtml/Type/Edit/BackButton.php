<?php
namespace NWT\Signapp\Block\Adminhtml\Type\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\UrlInterface;
use NWT\Signapp\Model\TypeFactory;
use Magento\Cms\Block\Adminhtml\Page\Edit\BackButton as CmsBackButton;
use Magento\Cms\Api\PageRepositoryInterface;

class BackButton extends CmsBackButton
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var TypeFactory
     */
    protected TypeFactory $typeFactory;

    /**
     * @var PageRepositoryInterface $pageRepository
     */
    protected $pageRepository;

    /**
     * @param TypeFactory $typeFactory
     * @param PageRepositoryInterface $pageRepository
     * @param Context $context
     */
    public function __construct(
        TypeFactory $typeFactory,
        PageRepositoryInterface $pageRepository,
        Context  $context
    ) {
        $this->typeFactory = $typeFactory;
        $this->context = $context;
        parent::__construct($context, $pageRepository);
    }

    /**
     * @return false
     */
    public function getId()
    {

        /**
         * Get Url param  value
         */
        if($this->context->getRequest()->getParam('type_id')){
            $studentModel =$this->typeFactory->create();
            $studentModel->load($this->context->getRequest()->getParam('type_id'));

            return $studentModel->getId();
        }
        return false;
    }

    /**
     * @return UrlInterface
     */
    public function getUrlBuilder()
    {
        return $this->context->getUrlBuilder();
    }

    /**
     * @return array
     */
    public function getButtonData(): array {
        return [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $this->getUrlBuilder()->getUrl('nwt_signapp/index/type')),
            'class' => 'back',
            'sort_order' => 10
        ];
    }

}
