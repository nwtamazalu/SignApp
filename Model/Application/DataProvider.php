<?php
/** @noinspection DuplicatedCode */
namespace NWT\Signapp\Model\Application;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use NWT\Signapp\Helper\Data as Helper;
use NWT\Signapp\Model\ResourceModel\Application\CollectionFactory;

/**
 * Class DataProvider
 */
class DataProvider extends AbstractDataProvider
{
    protected $collection;

    /**
     * @var DataPersistorInterface $dataPersistor
     */
    protected DataPersistorInterface $dataPersistor;

    /**
     * @var SerializerInterface $serializer
     */
    private SerializerInterface $serializer;

    /**
     * @var Helper $helper
     */
    private Helper $helper;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $itemCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param SerializerInterface $serializer
     * @param Helper $helper
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $itemCollectionFactory,
        DataPersistorInterface $dataPersistor,
        SerializerInterface $serializer,
        Helper $helper,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $itemCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->serializer = $serializer;
        $this->helper = $helper;
        $this->meta = $this->prepareMeta($this->meta);
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Prepares Meta
     *
     * @param array $meta
     * @return array
     */
    public function prepareMeta(array $meta): array
    {
        return $meta;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData(): array
    {
        if (isset($this->loadedData)) {
            return $this->getLoadedData();
        }

        $items = $this->collection->getItems();
        foreach ($items as $_item) {
            $this->loadedData[$_item->getData('app_id')] = $_item->getData();
        }

        $data = $this->dataPersistor->get('nwt_signapp_applications');
        if (!empty($data)) {
            $item = $this->collection->getNewEmptyItem();
            $item->setData($data);
            $this->loadedData[$item->getData('app_id')] = $item->getData();

            $this->dataPersistor->clear('nwt_signapp_applications');
        }
        return $this->getLoadedData();
    }

    /**
     * @return array
     */
    public function getLoadedData(): array
    {
        return $this->loadedData;
    }
}
