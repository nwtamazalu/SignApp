<?php
namespace NWT\Signapp\Controller\Checkssn;

use Exception;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class Index implements HttpPostActionInterface
{
    /**
     * @var PageFactory
     */
    protected PageFactory $_resultPageFactory;

    /**
     * @var JsonFactory
     */
    protected JsonFactory $_resultJsonFactory;

    /**
     * @var RequestInterface $request
     */
    protected RequestInterface $request;

    /**
     * @var CollectionFactory $customerCollectionFactory
     */
    protected CollectionFactory $customerCollectionFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param RequestInterface $request
     * @param CollectionFactory $customerCollectionFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        RequestInterface $request,
        CollectionFactory $customerCollectionFactory
    ) {
        $this->_resultPageFactory        = $resultPageFactory;
        $this->_resultJsonFactory        = $resultJsonFactory;
        $this->request                   = $request;
        $this->customerCollectionFactory = $customerCollectionFactory;
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $result  = $this->_resultJsonFactory->create();
        $ssn     = $this->request->getParam('ssn');

        try {
            $data = ['ssn' => $ssn, 'email' => null];
            $collection = $this->customerCollectionFactory->create();
            $collection->addAttributeToSelect('*');
            $collection->addAttributeToFilter('bankid_personalnumber', ['eq' => $ssn]);
            $match = $collection->getFirstItem();

            if ($match->getId()) {
                $data['email'] = $match->getData('email');
            } else {
                $data['email'] = '';
            }
        } catch (Exception $e) {
            $data = ['error' => $e->getMessage(), 'email' => ''];
        }

        $result->setData($data);

        return $result;
    }
}
