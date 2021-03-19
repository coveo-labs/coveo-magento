<?php declare(strict_types=1);

namespace Coveo\Search\Model\Service\Config;

use Coveo\Search\Api\Service\Config\SearchConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class SearchConfig implements SearchConfigInterface
{
    const XML_PATH_SEARCH_FALLBACK_ENABLE = 'coveo/search/fallback_enable';
    const XML_PATH_SEARCH_RESPONSE_TYPE = 'coveo/search/response_type';
    const XML_PATH_SEARCH_DEFAULT_LIMIT = 'coveo/search/default_limit';
    const XML_PATH_SEARCH_FILTER_EXCLUSION_PARAMS = 'coveo/search/exclude_params';
    const XML_PATH_MESSAGE_STYLE = 'coveo/search/message_style';
    const SEARCH_FILTER_EXCLUSION_PARAMS_DEFAULT = 'q,product_list_order,product_list_dir,parentSearchId,utm_source,utm_medium,utm_campaign,gclid,gclsrc';
    const SEARCH_FILTER_EXCLUSION_PARAMS_SEPARATOR = ',';
    const XML_PATH_SEARCH_SUPPORTED_ORDER_TYPES = 'coveo/search/supported_order_types';
    const SEARCH_SUPPORTED_ORDER_TYPES_SEPARATOR = ',';
    const SEARCH_SUPPORTED_ORDER_TYPES_DEFAULT = 'relevance,price-desc,price-asc,name-asc,name-desc';
    const SEARCH_ORDER_PARAM='sortCriteria';
    const XML_PATH_FILTER_PRICE_PARAM = 'coveo/search/filter_price_override';
    const FILTER_PRICE_PARAM_DEFAULT = 'price';
    const XML_PATH_ANALYTICS_HUB = 'coveo/analytics/searchhub';
    const XML_PATH_ANALYTICS_TAB = 'coveo/analytics/tab';


    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultLimit()
    {
        $value = $this->scopeConfig->getValue(self::XML_PATH_SEARCH_DEFAULT_LIMIT);
        if ($value !== null) {
            $value = (int) $value;
        }
        return $value;
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
    public function getSupportedOrderTypes()
    {
        $defaultParams = explode(self:: SEARCH_SUPPORTED_ORDER_TYPES_SEPARATOR,self::SEARCH_SUPPORTED_ORDER_TYPES_DEFAULT);
        $value = $this->scopeConfig->getValue(self::XML_PATH_SEARCH_SUPPORTED_ORDER_TYPES);
        if ($value === null) {
            return $defaultParams;
        }
        $params = explode(self:: SEARCH_SUPPORTED_ORDER_TYPES_SEPARATOR,$value);

        return array_map('trim', array_unique(array_merge($defaultParams, $params)));
    }

    /**
     * @inheritdoc
     */
    public function getParamFilterExclusion()
    {
        $value = $this->scopeConfig->getValue(self::XML_PATH_SEARCH_FILTER_EXCLUSION_PARAMS);
        if ($value === null || trim($value) === '') {
            $value = self::SEARCH_FILTER_EXCLUSION_PARAMS_DEFAULT;
        }
        $defaultParams = explode(self:: SEARCH_FILTER_EXCLUSION_PARAMS_SEPARATOR,self::SEARCH_FILTER_EXCLUSION_PARAMS_DEFAULT);
        $params = explode(self:: SEARCH_FILTER_EXCLUSION_PARAMS_SEPARATOR,$value);

        return array_map('trim', array_unique(array_merge($defaultParams, $params)));
    }

    /**
     * @inheritdoc
     */
    public function getFilterPriceParam()
    {
        $value =  $this->scopeConfig->getValue(self::XML_PATH_FILTER_PRICE_PARAM);
        if ($value === null) {
            return self::FILTER_PRICE_PARAM_DEFAULT;
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function getMessageStyle()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MESSAGE_STYLE);
    }

    /**
     * @inheritdoc
     */
    public function isEnriched()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SEARCH_RESPONSE_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function isFallbackEnable()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SEARCH_FALLBACK_ENABLE);
    }
}
