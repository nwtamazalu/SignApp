<?php
namespace NWT\Signapp\Controller\Sign;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use NWT\BankID\Model\BankID;
use NWT\BankID\Model\BankIDResponse;
use Magento\Framework\Event\ManagerInterface as EventManager;

class Check implements ActionInterface
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
     * @var Session                $customerSession
     */
    protected Session              $customerSession;

    /**
     * @var EventManager $eventManager
     */
    protected EventManager $eventManager;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param BankID $bankid
     * @param Session $customerSession
     * @param EventManager $eventManager
     */
    public function __construct(
        Context                    $context,
        JsonFactory                $resultJsonFactory,
        BankID                     $bankid,
        Session                    $customerSession,
        EventManager               $eventManager
    ) {
        $this->context           = $context;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->bankid            = $bankid;
        $this->customerSession   = $customerSession;
        $this->eventManager      = $eventManager;
    }

    /**
     * @return Json
     */
    public function execute(): Json
    {
        $id = $this->context->getRequest()->getParam('app_id');
        $ref = $this->context->getRequest()->getParam('orderRef');
        $bankid = $this->bankid->init();
        $response = $bankid->collect($ref);

        switch ($response->getStatus()) {
            case BankIDResponse::STATUS_FAILED:
                // NO GO
                $this->eventManager->dispatch('nwt_signapp_sign_failed', ['app_id' => $id]);
                $success = 0;
                break;
            case BankIDResponse::STATUS_COMPLETE:
                // We have a GO
                $this->eventManager->dispatch('nwt_signapp_sign_complete', ['app_id' => $id]);
                $success = 1;
                break;
            case BankIDResponse::STATUS_PENDING:
                // Just return the status, it's pending
                $success = 2;
                break;
            default:
                // BankIDResponse::STATUS_OK ???
                // This doesn't seem to be documented, but was present in the library. Let's just
                // make provision for it and see if it's necessary later.
                $success = 3;
        }

        return $this->returnResponse($success, $response->getMessage(), $response->getBody());
    }

    /**
     * Return response
     *
     * @param int $success
     * @param string $message
     * @param array $details
     * @return Json
     */
    private function returnResponse(
        int $success,
        string $message = '',
        array $details = []
    ): Json
    {
        $resultJson = $this->resultJsonFactory->create();
        $response = [
            'success' => $success,
            'message' => __($message),
            'details' => $details
        ];

        $resultJson->setData($response);
        return $resultJson;
    }
}
