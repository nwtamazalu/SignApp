<?php

namespace NWT\Signapp\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface TypeInterface extends ExtensibleDataInterface
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
    public function getName();

    /**
     * @param $name
     * @return mixed
     */
    public function setName($name);

    /**
     * @return mixed
     */
    public function getSignRequestType();

    /**
     * @param $signRequestType
     * @return mixed
     */
    public function setSignRequestType($signRequestType);

    /**
     * @return mixed
     */
    public function getSignRequestTypeId();

    /**
     * @param $signRequestTypeId
     * @return mixed
     */
    public function setSignRequestTypeId($signRequestTypeId);

}
