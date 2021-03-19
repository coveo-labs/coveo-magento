<?php declare(strict_types=1);

namespace Coveo\Search\Model\Service;

use Coveo\Search\Api\Service\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

class Config implements ConfigInterface
{
    const XML_PATH_GENERAL_LOCALE_CODE = 'general/locale/code';
    const XML_PATH_SEARCH_ACTIVE = 'coveo/active/frontend';
    const XML_PATH_TRACKING_ACTIVE = 'coveo/active/tracking';
    const XML_PATH_SUGGESTION_ACTIVE = 'coveo/active/suggestion';
    const XML_PATH_SPEECH_TO_TEXT_ACTIVE = 'coveo/active/speech_to_text';
    const XML_PATH_API_KEY_INDEX = 'coveo/server/api_key_index';
    const XML_PATH_API_KEY_SEARCH = 'coveo/server/api_key_search';
    const XML_PATH_API_BASE_URL = 'coveo/server/api_base_url';
    const XML_PATH_API_BASE = 'coveo/server/api_base';
    const XML_PATH_API_ORG = 'coveo/server/api_org';
    const XML_PATH_API_SOURCE = 'coveo/server/api_source';
    const XML_PATH_API_SEARCH_URL = 'coveo/server/api_search_url';
    const XML_PATH_DEBUG_MODE = 'coveo/server/debug_mode';
    const XML_PATH_VARIANT_AS_PRODUCT = 'coveo/server/variant_as_product';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(ScopeConfigInterface $scopeConfig, StoreManagerInterface $storeManager)
    {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function getLanguage()
    {
        $currentLocaleCode = $this->storeManager->getStore()->getLocaleCode();
        if ($currentLocaleCode !== null) {
            return $currentLocaleCode;
        }

        return $this->scopeConfig->getValue(self::XML_PATH_GENERAL_LOCALE_CODE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @inheritdoc
     */
    public function getStoreCode()
    {
        return $this->storeManager->getStore()->getCode();
    }

    /**
     * @inheritdoc
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getStoreId();
    }

    /**
     * @inheritdoc
     */
    public function getUseVariantAsProduct()
    {
      return $this->scopeConfig->getValue(self::XML_PATH_VARIANT_AS_PRODUCT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @inheritdoc
     */
    public function getApiKeyIndex()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_API_KEY_INDEX);
    }
    /**
     * @inheritdoc
     */
    public function getApiKeySearch()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_API_KEY_SEARCH);
    }

    /**
     * @inheritdoc
     */
    public function getApiVersion()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_API_VERSION);
    }

    /**
     * @inheritdoc
     */
    public function getApiBaseUrl()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_API_BASE_URL);
    }

    /**
     * @inheritdoc
     */
    public function getApiBase()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_API_BASE);
    }

    /**
     * @inheritdoc
     */
    public function getApiSource()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_API_SOURCE);
    }

    /**
     * @inheritdoc
     */
    public function getApiOrg()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_API_ORG);
    }

    /**
     * @inheritdoc
     */
    public function getApiSearchUrl()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_API_SEARCH_URL);
    }

    /**
     * @inheritdoc
     */
    public function isDebugModeEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_DEBUG_MODE);
    }

    /**
     * @inheritdoc
     */
    public function isSearchEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SEARCH_ACTIVE);
    }

    /**
     * @inheritdoc
     */
    public function isTrackingEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_TRACKING_ACTIVE);
    }

    /**
     * @inheritdoc
     */
    public function isSuggestionEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SUGGESTION_ACTIVE);
    }

    /**
     * @inheritdoc
     */
    public function isSdkEnabled()
    {
        return $this->isSpeechToTextEnabled(); // Insert here more service that require SDK library
    }

    /**
     * @inheritdoc
     */
    public function isSpeechToTextEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SPEECH_TO_TEXT_ACTIVE);
    }
}
