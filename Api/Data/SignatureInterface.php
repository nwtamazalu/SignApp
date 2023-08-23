<?php

namespace NWT\Signapp\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface SignatureInterface extends ExtensibleDataInterface
{
    /**
     * Setter
     *
     * @param int $id
     * @return void
     */
    public function setId($id);

    /**
     * Getter
     *
     * @return int
     */
    public function getId();

    /**
     * @return mixed
     */
    public function getAppId();

    /**
     * @param $appId
     * @return mixed
     */
    public function setAppId($appId);

    /**
     * @return mixed
     */
    public function getCustomerId();

    /**
     * @param $customerId
     * @return mixed
     */
    public function setCustomerId($customerId);

    /**
     * @return mixed
     */
    public function getSignature();

    /**
     * @param $signature
     * @return mixed
     */
    public function setSignature($signature);

}
