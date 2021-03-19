<?php declare(strict_types=1);

namespace Coveo\Search\Model\Service\Config;

use Coveo\Search\Api\Service\Config\AnalyticsConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Zend\Uri\Http;

class AnalyticsConfig implements AnalyticsConfigInterface
{
  const XML_PATH_GENERAL_LOCALE_CODE = 'general/locale/code';
    const XML_PATH_ANALYTICS_INCLUDE_LIBRARY = 'coveo/analytics/include_library';
    const XML_PATH_ANALYTICS_LIBRARY_ENDPOINT = 'coveo/analytics/library_endpoint';
    const XML_PATH_ANALYTICS_API_ENDPOINT = 'coveo/analytics/api_endpoint';
    const XML_PATH_ANALYTICS_API_VERSION = 'coveo/analytics/api_version';
    const XML_PATH_ANALYTICS_KEY = 'coveo/server/api_key_search';//'coveo/analytics/key';
    const XML_PATH_ANALYTICS_PRODUCT_SELECTOR = 'coveo/analytics/product_link_selector';
    const XML_PATH_ANALYTICS_PRODUCT_CONTAINER_SELECTOR = 'coveo/analytics/product_container_selector';
    const XML_PATH_ANALYTICS_PRODUCT_ATTRIBUTE = 'coveo/analytics/product_link_attribute';
    const XML_PATH_ANALYTICS_SEARCH_ID_ATTRIBUTE = 'coveo/analytics/search_id_attribute';
    const XML_PATH_ANALYTICS_PAGINATION_TYPE = 'coveo/analytics/pagination_type';
    const XML_PATH_ANALYTICS_DEBUG_MODE = 'coveo/analytics/debug_mode';
    const XML_PATH_ANALYTICS_COOKIE_DOMAIN = 'coveo/analytics/cookie_domain';
    const XML_PATH_ANALYTICS_TRACK_USERID = 'coveo/analytics/track_userid';
    const XML_PATH_ANALYTICS_HUB = 'coveo/analytics/searchhub';
    const XML_PATH_ANALYTICS_TAB = 'coveo/analytics/tab';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Http
     */
    private $httpUriHandler;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param Http $httpUriHandler
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Http $httpUriHandler
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->httpUriHandler = $httpUriHandler;
    }

    /**
     * @inheritdoc
     */
    public function getStoreId(){
         return $this->storeManager->getStore()->getStoreId();
    }

    /**
     * Get getStoreCode
     *
     * @return string
     */
    public function getStoreCode(){
      return $this->storeManager->getStore()->getCode();
 }

     /**
     * Get getLanguage
     *
     * @return string
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
    public function getCookieDomain($default = null)
    {
        $cookieDomain = $this->scopeConfig->getValue(self::XML_PATH_ANALYTICS_COOKIE_DOMAIN);
        if ($cookieDomain === null || trim($cookieDomain) === '') {
            if ($default === null) {
                $url = $this->storeManager->getStore()->getBaseUrl();
                $domainPart = explode('.', $this->httpUriHandler->parse($url)->getHost());
                $cookieDomain = 'localhost';
                try {
                $cookieDomain = '.'.$domainPart[count($domainPart) - 2].'.'.$domainPart[count($domainPart) - 1];
                }
                catch (\Exception $e) {

                }
            } else {
                $cookieDomain = $default;
            }
        }
        return $cookieDomain;
    }

    /**
     * @inheritdoc
     */
    public function getLibraryEndpoint()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ANALYTICS_LIBRARY_ENDPOINT);
    }

    /**
     * @inheritdoc
     */
    public function getAPIEndpoint()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ANALYTICS_API_ENDPOINT);
    }

    /**
     * @inheritdoc
     */
    public function getAPIVersion()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ANALYTICS_API_VERSION);
    }

    /**
     * @inheritdoc
     */
    public function getKey()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ANALYTICS_KEY);
    }

    /**
     * @inheritdoc
     */
    public function getProductLinkSelector()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ANALYTICS_PRODUCT_SELECTOR);
    }

    /**
     * @inheritdoc
     */
    public function getProductContainerSelector()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ANALYTICS_PRODUCT_CONTAINER_SELECTOR);
    }

    /**
     * @inheritdoc
     */
    public function getProductAttributeName()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ANALYTICS_PRODUCT_ATTRIBUTE);
    }

    /**
     * @inheritdoc
     */
    public function getSearchIdAttribute()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ANALYTICS_SEARCH_ID_ATTRIBUTE);
    }

    /**
     * @inheritdoc
     */
    public function getHub()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ANALYTICS_HUB);
    }

        /**
     * @inheritdoc
     */
    public function getTab()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ANALYTICS_TAB);
    }

    /**
     * @inheritdoc
     */
    public function getPaginationType()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ANALYTICS_PAGINATION_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function isLibraryIncluded()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ANALYTICS_INCLUDE_LIBRARY);
    }

    /**
     * @inheritdoc
     */
    public function isDebugMode()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ANALYTICS_DEBUG_MODE);
    }

    /**
     * @inheritdoc
     */
    public function isUserIdTrackingEnable()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ANALYTICS_TRACK_USERID);
    }
}
