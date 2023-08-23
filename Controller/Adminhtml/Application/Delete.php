<?php
namespace NWT\Signapp\Controller\Adminhtml\Application;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use NWT\Signapp\Api\ApplicationRepositoryInterface;

class Delete extends Action
{
    /**
     * @var ApplicationRepositoryInterface $applicationRepo
     */
    protected ApplicationRepositoryInterface $applicationRepo;

    /**
     * @param ApplicationRepositoryInterface $applicationRepo
     * @param Context $context
     */
    public function __construct(
        ApplicationRepositoryInterface $applicationRepo,
        Context $context
    ) {
        $this->applicationRepo    = $applicationRepo;
        parent::__construct($context);
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        try {
            $this->applicationRepo->deleteById($id);
        } catch (NoSuchEntityException|LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('nwt_signapp/index/index');
        return $resultRedirect;
    }


}
