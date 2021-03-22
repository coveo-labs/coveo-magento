<?php declare(strict_types=1);

namespace Coveo\Search\Model\Service\Config;

use Coveo\Search\Api\Service\Config\SuggestionConfigInterface;
use Coveo\Search\Api\Service\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class SuggestionConfig implements SuggestionConfigInterface
{
    const XML_PATH_SUGGEST_LIBRARY_INCLUDE = 'coveo/suggestion/include_library';
    const XML_PATH_SUGGEST_LIBRARY_ENDPOINT = 'coveo/server/api_search_url';
    const XML_PATH_SUGGEST_INPUT_SELECTOR = 'coveo/suggestion/input_selector';

    const XML_PATH_SUGGEST_BUCKETS = 'coveo/suggestion/buckets';
    const XML_PATH_SUGGEST_LIMIT = 'coveo/suggestion/limit';
    const XML_PATH_SUGGEST_GROUPBY = 'coveo/suggestion/groupby';
    const XML_PATH_SUGGEST_NOCACHE = 'coveo/suggestion/nocache';
    const XML_PATH_SUGGEST_ONSELECT_BEHAVIOUR = 'coveo/suggestion/onselect_behaviour';
    const XML_PATH_SUGGEST_ONSELECT_CALLBACK = 'coveo/suggestion/onselect_callback';
    const XML_PATH_SUGGEST_MINCHAR = 'coveo/suggestion/minchars';
    const XML_PATH_SUGGEST_WIDTH = 'coveo/suggestion/width';
    const XML_PATH_SUGGEST_WIDTH_CUSTOM = 'coveo/suggestion/width_custom';
    const XML_PATH_SUGGEST_ZINDEX = 'coveo/suggestion/zindex';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param ConfigInterface $config
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ConfigInterface $config
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function getLibraryEndpoint()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SUGGEST_LIBRARY_ENDPOINT).'';
    }

    /**
     * @inheritdoc
     */
    public function getInputSelector()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SUGGEST_INPUT_SELECTOR);
    }

    /**
     * @inheritdoc
     */
    public function getInitParams()
    {
      $custom = [ 'context_store_id'=> $this->config->getStoreCode(),
         'context_website' => $this->config->getLanguage()];

        $data = [
            'locale' => 'en_us',
            'visitorId' => '',
            'searchHub' => '',
            'context_querysuggest' => '1',
            /*"customData"=> $custom,*/
            'context_store_id'=> $this->config->getStoreCode(),
         'context_website' => $this->config->getLanguage(),
            'autocomplete' => [
                'width' => $this->getWidthValue(),
            ]
        ];

        $locale = $this->config->getLanguage();
        if($locale !== null){
            $data['language'] = strtolower($locale);
        }

        $apiKey = $this->config->getApiKeySearch();
        if($apiKey !== null){
            $data['apiKey'] = $apiKey;
        }

        $endPoint = $this->getLibraryEndpoint();
        if($endPoint !== null){
            $data['endPoint'] = $endPoint;
        }

        $limit = $this->scopeConfig->getValue(self::XML_PATH_SUGGEST_LIMIT);
        if($limit !== null){
            $data['count'] = $limit;
        }

        $minChars = $this->scopeConfig->getValue(self::XML_PATH_SUGGEST_MINCHAR);
        if($minChars !== null){
            $data['autocomplete']['minChars'] = $minChars;
        }

        $zIndex = $this->scopeConfig->getValue(self::XML_PATH_SUGGEST_ZINDEX);
        if($zIndex !== null){
            $data['autocomplete']['zIndex'] = $zIndex;
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function getOnSelectCallbackValue()
    {
        $behaviour = $this->scopeConfig->getValue(self::XML_PATH_SUGGEST_ONSELECT_BEHAVIOUR);
        switch ($behaviour) {
            case 'submit':
                return 'function() { this.form.submit(); }';
            case 'custom':
                return $this->scopeConfig->getValue(self::XML_PATH_SUGGEST_ONSELECT_CALLBACK);
            case 'nothing':
                return 'function() { }';
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function getWidthValue()
    {
        $width = $this->scopeConfig->getValue(self::XML_PATH_SUGGEST_WIDTH);
        if($width === 'custom'){
            return $this->scopeConfig->getValue(self::XML_PATH_SUGGEST_WIDTH_CUSTOM);
        }
        return $width;
    }

    /**
     * @inheritdoc
     */
    public function isLibraryIncluded()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SUGGEST_LIBRARY_INCLUDE);
    }
}
