<?php

namespace NWT\Signapp\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface ApplicationInterface extends ExtensibleDataInterface
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
    public function getInitiator();

    /**
     * @param $customer
     * @return mixed
     */
    public function setInitiator($customer);

    /**
     * @return mixed
     */
    public function getType();

    /**
     * @return mixed
     */
    public function getTimeout();

    /**
     * @param $type
     * @return mixed
     */
    public function setType($type);

    /**
     * @return mixed
     */
    public function getRequest();

    /**
     * @param $request
     * @return mixed
     */
    public function setRequest($request);

    /**
     * @return mixed
     */
    public function getExtraData();

    /**
     * @param $extraData
     * @return mixed
     */
    public function setExtraData($extraData);

    /**
     * @return mixed
     */
    public function getStoreId();

    /**
     * @param $storeId
     * @return mixed
     */
    public function setStoreId($storeId);

    /**
     * @param $timeout
     * @return mixed
     */
    public function setTimeout($timeout);

}
