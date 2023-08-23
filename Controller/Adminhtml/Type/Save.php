<?php
/** @noinspection DuplicatedCode */
namespace NWT\Signapp\Controller\Adminhtml\Type;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Result\PageFactory;
use NWT\Signapp\Model\TypeFactory;
use NWT\Signapp\Model\ResourceModel\Type;
use Psr\Log\LoggerInterface;

class Save extends \NWT\Signapp\Controller\Adminhtml\Index\Type
{
    /**
     * @var TypeFactory
     */
    private TypeFactory $typeFactory;

    /**
     * @var Type
     */
    private Type $typeResource;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * Save constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param TypeFactory $typeFactory
     * @param Type $typeResource
     * @param LoggerInterface $logger
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        TypeFactory $typeFactory,
        Type $typeResource,
        LoggerInterface $logger,
        SerializerInterface $serializer
    ) {
        $this->typeFactory = $typeFactory;
        $this->typeResource = $typeResource;
        $this->logger = $logger;
        $this->serializer = $serializer;
        parent::__construct($context, $resultPageFactory);
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        /**
         * @var Redirect $resultRedirect
         */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        $id = $data['type_id'] ?? null;

        if ($data) {
            $model = $this->typeFactory->create();
            if (!empty($id)) {
                $model->load($id);
            }
            unset($data['type_id']);

            foreach ($data as $key => $val) {
                $model->setData($key, $val);
            }

            try {
                $this->typeResource->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the item.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);

                $id = $model->getId();

                return $resultRedirect->setPath('nwt_signapp/type/edit', ['id' => $id]);
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData($data);

                return $resultRedirect->setPath('nwt_signapp/index/type');
            }
        }

        // Something weird happened, just go back to the listing
        return $resultRedirect->setPath('nwt_signapp/type/edit', ['id' => $id]);
    }
}
