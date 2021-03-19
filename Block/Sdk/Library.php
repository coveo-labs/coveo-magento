<?php
namespace Coveo\Search\Block\Sdk;

use Coveo\Search\Api\Block\ScriptInterface;
use Coveo\Search\Api\Service\Config\SdkConfigInterface;
use Coveo\Search\Api\Service\ConfigInterface;
use Magento\Framework\View\Element\Template\Context;

class Library extends \Magento\Framework\View\Element\Template implements ScriptInterface
{
    const SCRIPT_ID = 'coveo-sdk-library';

    /**
     * @var SdkConfigInterface
     */
    protected $sdkConfig;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * Library constructor.
     *
     * @param Context $context
     * @param SdkConfigInterface $sdkConfig
     * @param ConfigInterface $config
     */
    public function __construct(
        Context $context,
        SdkConfigInterface $sdkConfig,
        ConfigInterface $config
    ) {
        parent::__construct($context);
        $this->sdkConfig = $sdkConfig;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function getScriptId()
    {
        return self::SCRIPT_ID;
    }

    /**
     * Get library endpoint
     *
     * @return string|null
     */
    public function getLibraryEndpoint()
    {
        return $this->sdkConfig->getLibraryEndpoint();
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
