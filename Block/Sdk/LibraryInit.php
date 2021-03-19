<?php
namespace Coveo\Search\Block\Sdk;

use Coveo\Search\Api\Block\ScriptInterface;
use Coveo\Search\Api\Service\Config\SdkConfigInterface;
use Coveo\Search\Api\Service\Config\SpeechToTextConfigInterface;
use Coveo\Search\Api\Service\ConfigInterface;
use Magento\Framework\View\Element\Template\Context;

class LibraryInit extends \Magento\Framework\View\Element\Template implements ScriptInterface
{
    const SCRIPT_ID = 'coveo-sdk-library-init';

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var SdkConfigInterface
     */
    protected $sdkConfig;

    /**
     * @var SpeechToTextConfigInterface
     */
    protected $speechToTextConfig;

    /**
     * LibraryInit constructor.
     *
     * @param Context $context
     * @param ConfigInterface $config
     * @param SdkConfigInterface $sdkConfig
     * @param SpeechToTextConfigInterface $speechToTextConfig
     */
    public function __construct(
        Context $context,
        ConfigInterface $config,
        SdkConfigInterface $sdkConfig,
        SpeechToTextConfigInterface $speechToTextConfig
    ) {
        parent::__construct($context);
        $this->config = $config;
        $this->sdkConfig = $sdkConfig;
        $this->speechToTextConfig = $speechToTextConfig;
    }

    /**
     * @inheritdoc
     */
    public function getScriptId()
    {
        return self::SCRIPT_ID;
    }

    /**
     * Get initializations parameters
     *
     * @return object
     */
    public function getInitParams()
    {
        // General configurations

        $data = [
            'debug' => $this->sdkConfig->isDebugModeEnabled()
        ];

        $coreKey = $this->sdkConfig->getCoreKey();
        if($coreKey !== null){
            $data['coreKey'] = $coreKey;
        }

        $language = $this->sdkConfig->getLanguage();
        if($language !== null){
            $data['language'] = $language;
        }

        $inputSelector = $this->sdkConfig->getInputSelector();
        if($inputSelector !== null){
            $data['input'] = $inputSelector;
        }

        // Speech To Text configurations

        if ($this->config->isSpeechToTextEnabled()) {
            $data['speech'] = [];

            $speechToTextLanguage = $this->speechToTextConfig->getLanguage();
            if($speechToTextLanguage !== null){
                $data['language'] = $speechToTextLanguage;
            }

            $speechToTextInputSelector = $this->speechToTextConfig->getInputSelector();
            if($inputSelector !== null){
                $data['input'] = $speechToTextInputSelector;
            }
        }

        return (object) $data;
    }

    /**
     * @inheritdoc
     */
    public function _toHtml()
    {
        if ($this->config->isSdkEnabled() === false) {
            return '';
        }
        return parent::_toHtml();
    }
}
