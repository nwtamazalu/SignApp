<?php
namespace NWT\Signapp\Controller\Adminhtml\Type;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use NWT\Signapp\Api\TypeRepositoryInterface;

class Delete extends Action
{
    /**
     * @var TypeRepositoryInterface $typeRepository
     */
    protected TypeRepositoryInterface $typeRepository;

    /**
     * @param TypeRepositoryInterface $typeRepository
     * @param Context $context
     */
    public function __construct(
        TypeRepositoryInterface $typeRepository,
        Context $context
    ) {
        $this->typeRepository    = $typeRepository;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        try {
            $this->typeRepository->deleteById($id);
        } catch (NoSuchEntityException|LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('nwt_signapp/index/type');
        return $resultRedirect;
    }


}
