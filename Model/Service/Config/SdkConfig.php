<?php declare(strict_types=1);

namespace Coveo\Search\Model\Service\Config;

use Coveo\Search\Api\Service\Config\SdkConfigInterface;
use Coveo\Search\Api\Service\Config\SuggestionConfigInterface;
use Coveo\Search\Api\Service\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class SdkConfig implements SdkConfigInterface
{
    const XML_PATH_SDK_LIBRARY_ENDPOINT = 'coveo/sdk/library_endpoint';
    const XML_PATH_SDK_CORE_KEY = 'coveo/sdk/core_key';
    const XML_PATH_SDK_INPUT_SELECTOR = 'coveo/sdk/input_selector';
    const XML_PATH_SDK_LANGUAGE = 'coveo/sdk/language';
    const XML_PATH_SDK_DEBUG_MODE = 'coveo/sdk/debug_mode';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var SuggestionConfigInterface
     */
    protected $suggestionConfig;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param ConfigInterface $config
     * @param SuggestionConfigInterface $suggestionConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ConfigInterface $config,
        SuggestionConfigInterface $suggestionConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->config = $config;
        $this->suggestionConfig = $suggestionConfig;
    }

    /**
     * @inheritdoc
     */
    public function getLibraryEndpoint()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SDK_LIBRARY_ENDPOINT);
    }

    /**
     * @inheritdoc
     */
    public function getCoreKey()
    {
        $codeKey = $this->scopeConfig->getValue(self::XML_PATH_SDK_CORE_KEY);
        if ($codeKey === null) {
            return $this->config->getApiKey();
        }
        return $codeKey;
    }

    /**
     * @inheritdoc
     */
    public function getInputSelector()
    {
        $inputSelector = $this->scopeConfig->getValue(self::XML_PATH_SDK_INPUT_SELECTOR);
        if ($inputSelector === null) {
            return $this->suggestionConfig->getInputSelector();
        }
        return $inputSelector;
    }

    /**
     * @inheritdoc
     */
    public function getLanguage()
    {
        $inputSelector = $this->scopeConfig->getValue(self::XML_PATH_SDK_LANGUAGE);
        if ($inputSelector === null) {
            return $this->config->getLanguage();
        }
        return $inputSelector;
    }

    /**
     * @inheritdoc
     */
    public function isDebugModeEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SDK_DEBUG_MODE);
    }

}
