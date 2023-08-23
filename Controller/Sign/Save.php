<?php
namespace NWT\Signapp\Controller\Sign;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use NWT\Signapp\Api\SignatureRepositoryInterface;
use NWT\Signapp\Model\SignatureFactory;

class Save implements ActionInterface
{
    /**
     * @var JsonFactory                      $resultJsonFactory
     */
    protected JsonFactory                    $resultJsonFactory;

    /**
     * @var Context                          $context
     */
    private Context                          $context;

    /**
     * @var Session                          $customerSession
     */
    protected Session                        $customerSession;

    /**
     * @var EventManager                     $eventManager
     */
    protected EventManager                   $eventManager;

    /**
     * @var SignatureFactory                 $signatureFactory
     */
    protected SignatureFactory               $signatureFactory;

    /**
     * @var SignatureRepositoryInterface     $signatureRepository
     */
    protected SignatureRepositoryInterface   $signatureRepository;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Session $customerSession
     * @param EventManager $eventManager
     * @param SignatureFactory $signatureFactory
     * @param SignatureRepositoryInterface $signatureRepository
     */
    public function __construct(
        Context                              $context,
        JsonFactory                          $resultJsonFactory,
        Session                              $customerSession,
        EventManager                         $eventManager,
        SignatureFactory                     $signatureFactory,
        SignatureRepositoryInterface         $signatureRepository
    ) {
        $this->context                     = $context;
        $this->resultJsonFactory           = $resultJsonFactory;
        $this->customerSession             = $customerSession;
        $this->eventManager                = $eventManager;
        $this->signatureFactory            = $signatureFactory;
        $this->signatureRepository         = $signatureRepository;
    }

    /**
     * @return Json
     */
    public function execute(): Json
    {
        $id = $this->context->getRequest()->getParam('app_id');
        $completionData = $this->context->getRequest()->getParam('completionData');

        try {
            $model = $this->signatureFactory->create();
            $data = [
                'app_id' => $id,
                'customer_id' => $this->customerSession->getCustomerId(),
                'signature' => json_encode($completionData)
            ];
            $model->setData($data);
            $this->signatureRepository->save($model);
        } catch (LocalizedException|\Exception $e) {
            return $this->returnResponse(0, __('Could not save signature.') . ' ' . $e->getMessage());
        }

        $this->eventManager->dispatch('nwt_signapp_sign_save', ['app_id' => $id, 'completionData' => $completionData, 'customer' => $this->customerSession->getCustomer()]);

//        $VisaUppgifter = $this->context->getRequest()->getParam('VisaUppgifter');
//        $Lansklubb = $this->context->getRequest()->getParam('Lansklubb');
//        $Bilagan = $this->context->getRequest()->getParam('Bilagan');
//        $Familjemedlem = $this->context->getRequest()->getParam('Familjemedlem');
//        $MedlemSpecialKl = $this->context->getRequest()->getParam('MedlemSpecialKl');

        // TODO: Send kennel form or something


        return $this->returnResponse(1, __('Signature saved successfully.'));
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
