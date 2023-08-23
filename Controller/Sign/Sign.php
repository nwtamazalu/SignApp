<?php
namespace NWT\Signapp\Controller\Sign;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use NWT\BankID\Model\BankID;
use NWT\BankID\Model\BankIDResponse;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;

class Sign implements ActionInterface
{
    /**
     * @var JsonFactory            $resultJsonFactory
     */
    protected JsonFactory          $resultJsonFactory;

    /**
     * @var BankID                 $bankid
     */
    protected BankID               $bankid;

    /**
     * @var Context                $context
     */
    private Context                $context;

    /**
     * @var RemoteAddress          $remoteAddress
     */
    protected RemoteAddress        $remoteAddress;

    /**
     * @var Session                $customerSession
     */
    protected Session              $customerSession;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param BankID $bankid
     * @param RemoteAddress $remoteAddress
     * @param Session $customerSession
     */
    public function __construct(
        Context                    $context,
        JsonFactory                $resultJsonFactory,
        BankID                     $bankid,
        RemoteAddress              $remoteAddress,
        Session                    $customerSession
    ) {
        $this->context           = $context;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->bankid            = $bankid;
        $this->remoteAddress     = $remoteAddress;
        $this->customerSession   = $customerSession;
    }

    public function execute()
    {
        $data = $this->context->getRequest()->getParam('data');
        $initName = $this->context->getRequest()->getParam('initName');
        $initEmail = $this->context->getRequest()->getParam('initEmail');
        $initSsn = $this->context->getRequest()->getParam('initSsn');
        $request = $this->context->getRequest()->getParam('request');
        $requestId = $this->context->getRequest()->getParam('requestId');
        $ssn = $this->customerSession->getCustomer()->getData('bankid_personalnumber');

        /**
         * Access (email/ssn match)
         */
        $access = false;
        foreach ($request as $req) {
            if ($req[0] == $ssn && $req[1] == $this->customerSession->getCustomer()->getEmail()) {
                $access = true;
            }
        }
        if ($this->customerSession->getCustomer()->getEmail() == $initEmail) {
            $access = true;
        }

        if (!$access) {
            return $this->returnResponse(0, __('You do not have access to this signature request.'));
        }

        /**
         * Extra data
         */
        $parsedData = "($initName) Signature request from $initSsn to $ssn , Application ID: $requestId , Data: ";
        $tempData = [];
        foreach ($data as $key => $val) {
            if (is_array($val)) {
                foreach ($val as $v) {
                    $tempData[] = $v['label'] . '=' . $v['value'];
                }
            } else {
                $tempData[] = $key . '=' . $val;
            }
        }
        $parsedData .= implode(', ', $tempData);

        /**
         * Init and send BankID request
         */
        $bankid = $this->bankid->init();
        $bankidResponse = $bankid->sign(
            $ssn,
            $this->remoteAddress->getRemoteAddress(),
            $parsedData
        );

        if ($bankidResponse->getStatus() == BankIDResponse::STATUS_FAILED) {
            return $this->returnResponse(0, $bankidResponse->getMessage());
        }

        return $this->returnResponse(1, __('Please open the BankID application and confirm the request.'), $bankidResponse->getOrderRef());
    }

    /**
     * Return response
     *
     * @param int $success
     * @param string $message
     * @param string $orderRef
     * @return Json
     */
    private function returnResponse(int $success, string $message = '', string $orderRef = ''): Json
    {
        $resultJson = $this->resultJsonFactory->create();
        $response = [
            'success' => $success,
            'message' => __($message),
            'orderRef' => $orderRef
        ];

        $resultJson->setData($response);
        return $resultJson;
    }
}
