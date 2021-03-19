<?php declare(strict_types=1);
namespace Coveo\Search\Model\Service\Indexer;

use Coveo\Search\Api\Service\Indexer\AttributesValuesInterface;
use Coveo\Search\Api\Service\LoggerInterface;
use Coveo\Search\Api\Service\Config\IndexerConfigInterface;
use Coveo\Search\Model\Service\Indexer\Db\AttributesValuesIndexFlat;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class AttributesValues implements AttributesValuesInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var AttributesValuesIndexFlat
     */
    protected $attributesValuesIndexFlat;

    /**
     * @var array
     */
    protected $enrichers;

    /**
     * @var IndexerConfigInterface
     */
    protected $indexerConfig;

    /**
     * @var AttributeCollectionFactory
     */
    protected $attributesCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     *
     * @var LoggerInterface $logger
     * @var IndexerConfigInterface $indexerConfig
     * @var AttributesValuesIndexFlat $attributesValuesIndexFlat
     * @var AttributeCollectionFactory $attributesCollectionFactory
     * @var StoreManagerInterface $storeManager
     */
    public function __construct(
        LoggerInterface $logger,
        IndexerConfigInterface $indexerConfig,
        AttributesValuesIndexFlat $attributesValuesIndexFlat,
        AttributeCollectionFactory $attributesCollectionFactory,
        StoreManagerInterface $storeManager
    )
    {
        $this->logger = $logger;
        $this->indexerConfig = $indexerConfig;
        $this->attributesValuesIndexFlat = $attributesValuesIndexFlat;
        $this->attributesCollectionFactory = $attributesCollectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function execute($ids = null)
    {
        $attributesConfigured = $this->indexerConfig->getAttributesWithoutCustoms();

        if ($ids === null) {
           /* $this->logger->info('[Reindex attributes] Executing full reindex..');
            $attributesCollection = $this->attributesCollectionFactory->create()
                ->addFieldToFilter('attribute_code', $attributesConfigured)
                ->addFieldToFilter('frontend_input', [
                    'select',
                    'multiselect'
                ]);
            $this->logger->info('[Reindex attributes] Deleting all data from flat table..');
            $this->attributesValuesIndexFlat->truncateData();
            $this->logger->info('[Reindex attributes] All data deleted');*/
        } else {
          /*$this->logger->info('[Reindex attributes] Index specific IDS..');
          $this->logger->info('[Reindex attributes] '.implode(",", $ids));
          $this->logger->info('[Reindex attributes] '.implode(",", $attributesConfigured));
            $attributesCollection = $this->attributesCollectionFactory->create()
                ->addFilterToMap('attribute_id', 'main_table.attribute_id') // TODO: is really necessary?
                //WIM this was hard coded... necessary?
                //->addFieldToFilter('attribute_id', $ids)
                ->addFieldToFilter('attribute_code', $attributesConfigured)
                ->addFieldToFilter('frontend_input', [
                    'select',
                    'multiselect'
                ]);*/
        }

        $attributesCount = 0;//$attributesCollection->getSize();
        if ($attributesCount === 0) {
            $this->logger->warn('[Reindex attributes] No attributes to reindex, skipping logic');
            return;
        }
        $this->logger->info('[Reindex attributes] Start reindex for '.$attributesCount.' attributes');

        $stores = $this->indexerConfig->getStores();

        foreach ($stores as $storeId) {

            $this->storeManager->setCurrentStore($storeId);

            // Init data

            $data = [];

            foreach ($attributesCollection as $attribute) {
                $options = $attribute->getSource()->getAllOptions();

                foreach ($options as $option){
                    if ($option['value'] === '') {
                        continue;
                    }
                    $data[] = [
                        'attribute_id' => $option['value'],
                        'value' => $option['label'],
                    ];
                }
            }

            // Store data into flat table

            $this->attributesValuesIndexFlat->storeData($data, $storeId);
        }

        $this->logger->info('[Reindex attributes] Reindex executed!');
    }
}
