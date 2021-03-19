<?php declare(strict_types=1);

namespace Coveo\Search\Model\Service\Config;

use Coveo\Search\Api\Service\Config\IndexerConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Coveo\Search\Model\Adminhtml\System\Config\Source\Attributes as SourceAttributes;
use Coveo\Search\Model\Adminhtml\System\Config\Source\SimpleAttributes as SourceSimpleAttributes;
use Magento\Store\Model\StoreManagerInterface;

class IndexerConfig implements IndexerConfigInterface
{
    const XML_PATH_INDEXER_STORES = 'coveo/indexer/stores_to_index';
    const XML_PATH_INDEXER_ATTRIBUTES = 'coveo/indexer/attributes_to_index';
    const XML_PATH_INDEXER_ATTRIBUTES_SIMPLE = 'coveo/indexer/attributes_simple_to_index';
    const XML_PATH_INDEXER_ACCESS_KEY = 'coveo/indexer/access_key';
    const XML_PATH_INDEXER_SECRET_KEY = 'coveo/indexer/secret_key';
    const XML_PATH_INDEXER_BUCKET_NAME = 'coveo/indexer/bucket';
    const XML_PATH_INDEXER_BUCKET_PATH = 'coveo/indexer/path';
    const XML_PATH_INDEXER_DRYRUN = 'coveo/indexer/dry_run_mode';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var SourceAttributes
     */
    protected $sourceAttributes;

    /**
     * @var SourceSimpleAttributes
     */
    protected $sourceSimpleAttributes;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param SourceAttributes $sourceAttributes
     * @param SourceSimpleAttributes $sourceSimpleAttributes
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        SourceAttributes $sourceAttributes,
        SourceSimpleAttributes $sourceSimpleAttributes,
        StoreManagerInterface $storeManager
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->sourceAttributes = $sourceAttributes;
        $this->sourceSimpleAttributes = $sourceSimpleAttributes;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function getStores()
    {
        $value = $this->scopeConfig->getValue(self::XML_PATH_INDEXER_STORES);
        if ($value === null) {
            $value = [];
        }
        if ((int) $value === 0) { // 0 is the value for "all stores"
            return array_map(static function ($store) {
                return $store->getId();
            }, $this->storeManager->getStores());
        }
        return explode(',', $value);
    }

    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        $value = $this->scopeConfig->getValue(self::XML_PATH_INDEXER_ATTRIBUTES);
        if ($value === null) {
            return [];
        }
        $attributes = explode(',', $value);
        return array_unique(array_merge($attributes, $this->sourceAttributes->getDefaultAttributes()));
    }

    /**
     * @inheritdoc
     */
    public function getAttributesWithoutCustoms()
    {
        $attributes = $this->getAttributes();
        $customAttributes = $this->sourceAttributes->getCustomAttributes();
        return array_diff($attributes, $customAttributes);
    }

    /**
     * @inheritdoc
     */
    public function getSimpleAttributes()
    {
        $value = $this->scopeConfig->getValue(self::XML_PATH_INDEXER_ATTRIBUTES_SIMPLE);
        if ($value === null) {
            return [];
        }
        $attributes = explode(',', $value);
        return array_unique(array_merge($attributes, $this->sourceSimpleAttributes->getDefaultAttributes()));
    }

    /**
     * @inheritdoc
     */
    public function getAwsAccessKey()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_INDEXER_ACCESS_KEY);
    }

    /**
     * @inheritdoc
     */
    public function getAwsSecretKey()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_INDEXER_SECRET_KEY);
    }

    /**
     * @inheritdoc
     */
    public function getAwsBucketName()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_INDEXER_BUCKET_NAME);
    }

    /**
     * @inheritdoc
     */
    public function getAwsBucketPath()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_INDEXER_BUCKET_PATH);
    }

    /**
     * @inheritdoc
     */
    public function isDryRunModeEnabled()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_INDEXER_DRYRUN);
    }

}
